import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import apiClient from '../api/client';
import { downloadCsv } from '../utils/csv';
import { invalidateApiCache, useApi } from '../hooks/useApi';

const PNG_TIME_ZONE = 'Pacific/Port_Moresby';

function getStockStateBadge(item) {
    const quantity = Number(item?.quantity || 0);
    const reorderLevel = Number(item?.reorder_level ?? 5);

    if (quantity === 0) {
        return { label: 'Out of Stock', className: 'bg-rose-100 text-rose-700' };
    }

    if (quantity <= reorderLevel) {
        return { label: 'Low Stock', className: 'bg-amber-100 text-amber-700' };
    }

    return { label: 'Available', className: 'bg-emerald-100 text-emerald-700' };
}

function defaultForm() {
    return {
        tracking_mode: 'bulk',
        name: '',
        brand: '',
        sku: '',
        description: '',
        category_id: '',
        supplier_id: '',
        status: 'available',
        quantity: 1,
        reorder_level: 5,
        unit_cost: '',
        asset_tag: '',
        serial_number: '',
        location: '',
        purchase_date: '',
    };
}

function extractErrorMessage(error) {
    const response = error?.response?.data;

    if (response?.errors && typeof response.errors === 'object') {
        const firstKey = Object.keys(response.errors)[0];

        if (firstKey && Array.isArray(response.errors[firstKey]) && response.errors[firstKey][0]) {
            return response.errors[firstKey][0];
        }
    }

    return response?.message || error?.message || 'Failed to save inventory item.';
}

function formatDate(value) {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString('en-PG', {
        timeZone: PNG_TIME_ZONE,
    });
}

function formatDateTime(value) {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString('en-PG', {
        timeZone: PNG_TIME_ZONE,
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function toDateInputValue(date) {
    return date.toISOString().slice(0, 10);
}

function getPeriodRange(period, customStart = '', customEnd = '') {
    const today = new Date();
    const start = new Date(today);

    if (period === 'today') {
        return { date_start: toDateInputValue(today), date_end: toDateInputValue(today) };
    }

    if (period === 'month') {
        start.setDate(1);
        return { date_start: toDateInputValue(start), date_end: toDateInputValue(today) };
    }

    if (period === 'year') {
        start.setMonth(0, 1);
        return { date_start: toDateInputValue(start), date_end: toDateInputValue(today) };
    }

    if (period === 'custom') {
        return { date_start: customStart, date_end: customEnd };
    }

    return { date_start: '', date_end: '' };
}

export default function ItemsPage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const [notice, setNotice] = useState('');
    const [formError, setFormError] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [showForm, setShowForm] = useState(searchParams.get('create') === '1');
    const [editingId, setEditingId] = useState(null);
    const [categories, setCategories] = useState([]);
    const [suppliers, setSuppliers] = useState([]);
    const [form, setForm] = useState(defaultForm());
    const [selectedItem, setSelectedItem] = useState(null);
    const [itemReport, setItemReport] = useState({ items: [], history: [] });
    const [itemReportLoading, setItemReportLoading] = useState(false);
    const [itemReportPeriod, setItemReportPeriod] = useState('all');
    const [itemDateStart, setItemDateStart] = useState('');
    const [itemDateEnd, setItemDateEnd] = useState('');

    const filters = useMemo(
        () => ({
            search: searchParams.get('search') ?? '',
            stock: searchParams.get('stock') ?? '',
            category_id: searchParams.get('category_id') ?? '',
            page: Number.parseInt(searchParams.get('page') ?? '1', 10),
            per_page: 10,
        }),
        [searchParams]
    );

    const { data, loading, error, refetch } = useApi('/items', { params: filters }, { ttl: 180000 });
    const items = data?.data || [];
    const meta = {
        current_page: data?.current_page || 1,
        last_page: data?.last_page || 1,
        total: data?.total || 0,
    };

    const [searchInput, setSearchInput] = useState(filters.search);

    useEffect(() => {
        setSearchInput(filters.search);
    }, [filters.search]);

    useEffect(() => {
        if (searchParams.get('create') === '1' && !editingId) {
            setShowForm(true);
        }
    }, [searchParams, editingId]);

    useEffect(() => {
        void fetchCategories();
        void fetchSuppliers();
    }, []);

    useEffect(() => {
        if (selectedItem) {
            void fetchItemReport(selectedItem);
        }
    }, [selectedItem, itemReportPeriod, itemDateStart, itemDateEnd]);

    async function fetchCategories() {
        try {
            const response = await apiClient.get('/categories', { params: { per_page: 100 } });
            setCategories(response.data.data || response.data || []);
        } catch (err) {
            console.error('Failed to load categories', err);
        }
    }

    async function fetchSuppliers() {
        try {
            const response = await apiClient.get('/suppliers', { params: { per_page: 100 } });
            setSuppliers(response.data.data || response.data || []);
        } catch (err) {
            console.error('Failed to load suppliers', err);
        }
    }

    async function fetchItemReport(item) {
        setItemReportLoading(true);

        try {
            const response = await apiClient.get('/assignments/report', {
                params: {
                    item_id: item.id,
                    date_start: itemReportDateRange.date_start,
                    date_end: itemReportDateRange.date_end,
                },
            });

            setItemReport({
                items: response.data?.items || [],
                history: response.data?.history || [],
            });
        } catch (err) {
            console.error('Failed to load item assignment report', err);
        } finally {
            setItemReportLoading(false);
        }
    }

    const refreshItems = useCallback(async () => {
        invalidateApiCache('/items');
        await refetch();
    }, [refetch]);

    const resetForm = useCallback(() => {
        setForm(defaultForm());
        setEditingId(null);
        setShowForm(false);
        setFormError('');
    }, []);

    const isSerializedMode = form.tracking_mode === 'serialized';
    const itemReportDateRange = useMemo(
        () => getPeriodRange(itemReportPeriod, itemDateStart, itemDateEnd),
        [itemReportPeriod, itemDateStart, itemDateEnd]
    );
    const selectedItemPosition = useMemo(
        () => itemReport.items.find((item) => Number(item.id) === Number(selectedItem?.id)) || null,
        [itemReport.items, selectedItem]
    );

    useEffect(() => {
        if (!editingId && isSerializedMode && Number(form.quantity) !== 1) {
            setForm((prev) => ({
                ...prev,
                quantity: 1,
            }));
        }
    }, [editingId, isSerializedMode, form.quantity]);

    const handleSubmit = useCallback(
        async (event) => {
            event.preventDefault();
            setNotice('');
            setFormError('');
            const enteredAssetTag = form.asset_tag.trim();
            const enteredSerialNumber = form.serial_number.trim();
            const currentQuantity = Number(form.quantity || 0);

            if (!editingId && isSerializedMode && currentQuantity !== 1) {
                setFormError('Serialized assets are tracked one-by-one, so initial quantity must be 1.');
                return;
            }

            if (!editingId && !isSerializedMode && enteredSerialNumber) {
                setFormError('Serial Number is only for serialized assets. Clear Serial Number or switch to serialized asset.');
                return;
            }

            if (editingId && enteredSerialNumber && currentQuantity !== 1) {
                setFormError('Serial Number is only allowed when the current quantity is 1.');
                return;
            }

            try {
                setSubmitting(true);

                const payload = {
                    name: form.name.trim(),
                    brand: form.brand.trim(),
                    sku: form.sku.trim(),
                    description: form.description.trim(),
                    category_id: form.category_id,
                    supplier_id: form.supplier_id,
                    status: form.status,
                    reorder_level: Number.parseInt(String(form.reorder_level || 0), 10),
                    unit_cost: form.unit_cost === '' ? '' : form.unit_cost,
                    asset_tag: enteredAssetTag,
                    serial_number: editingId || isSerializedMode ? enteredSerialNumber : '',
                    location: form.location.trim(),
                    purchase_date: form.purchase_date || '',
                };

                if (!editingId) {
                    payload.quantity = Number.parseInt(String(form.quantity || 1), 10);
                }

                const response = editingId
                    ? await apiClient.put(`/items/${editingId}`, payload)
                    : await apiClient.post('/items', payload);

                setNotice(
                    response?.data?.message ||
                        (editingId ? 'Inventory item updated successfully.' : 'Inventory item saved successfully.')
                );

                resetForm();
                await refreshItems();
            } catch (err) {
                console.error(err);
                setFormError(extractErrorMessage(err));
            } finally {
                setSubmitting(false);
            }
        },
        [editingId, form, isSerializedMode, refreshItems, resetForm]
    );

    const handleDelete = useCallback(
        async (id) => {
            if (!window.confirm('Are you sure you want to delete this inventory item?')) {
                return;
            }

            try {
                setNotice('');
                setFormError('');
                await apiClient.delete(`/items/${id}`);
                setNotice('Inventory item deleted successfully.');
                await refreshItems();
            } catch (err) {
                console.error(err);
                setFormError(extractErrorMessage(err));
            }
        },
        [refreshItems]
    );

    const handleEdit = useCallback((item) => {
        setNotice('');
        setFormError('');
        setForm({
            tracking_mode: item.asset_tag || item.serial_number ? 'serialized' : 'bulk',
            name: item.name || '',
            brand: item.brand || '',
            sku: item.sku || '',
            description: item.description || '',
            category_id: item.category_id || '',
            supplier_id: item.supplier_id || '',
            status: item.status || 'available',
            quantity: item.quantity || 1,
            reorder_level: item.reorder_level ?? 5,
            unit_cost: item.unit_cost || '',
            asset_tag: item.asset_tag || '',
            serial_number: item.serial_number || '',
            location: item.location || '',
            purchase_date: item.purchase_date || '',
        });

        setEditingId(item.id);
        setShowForm(true);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }, []);

    function updateQuery(nextValues) {
        const next = new URLSearchParams(searchParams);

        Object.entries(nextValues).forEach(([key, value]) => {
            if (value === '' || value === null || value === undefined) {
                next.delete(key);
            } else {
                next.set(key, String(value));
            }
        });

        if (!next.get('page')) {
            next.set('page', '1');
        }

        setSearchParams(next);
    }

    function handleSearchSubmit(event) {
        event.preventDefault();
        updateQuery({ search: searchInput.trim(), page: 1 });
    }

    function handleFilterChange(key, value) {
        updateQuery({ [key]: value, page: 1 });
    }

    function handleExportCsv() {
        downloadCsv(
            'inventory.csv',
            items.map((item) => ({
                Name: item.name || '',
                'Asset Tag': item.asset_tag || '',
                Brand: item.brand || '',
                SKU: item.sku || '',
                Category: item.category?.name || '',
                Supplier: item.supplier?.name || '',
                Quantity: item.quantity ?? 0,
                'Stock State': getStockStateBadge(item).label,
                'Lifecycle Status': item.status || '',
                Location: item.location || '',
                'Purchase Date': item.purchase_date || '',
                'Date Added (PNG Time)': formatDateTime(item.date_added_at || item.date_added),
            }))
        );
    }

    function handlePdfExport() {
        window.print();
    }

    function handleItemExportCsv() {
        if (!selectedItem) {
            return;
        }

        downloadCsv(
            `${selectedItem.name || 'item'}-assignment-report.csv`,
            itemReport.history.map((entry) => ({
                Item: entry.item_name,
                'Asset Tag': entry.asset_tag || '',
                SKU: entry.sku || '',
                Receiver: entry.receiver,
                Department: entry.department || '',
                Date: formatDate(entry.assigned_at),
                'Date / Time (PNG Time)': formatDateTime(entry.assigned_at),
                Quantity: entry.quantity,
                Status: entry.status,
            }))
        );
    }

    function openItemReport(item) {
        setSelectedItem(item);
        document.getElementById('inventory-item-report')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function goToPage(page) {
        updateQuery({ page });
    }

    function toggleForm() {
        setNotice('');
        setFormError('');

        if (showForm) {
            resetForm();
            return;
        }

        setEditingId(null);
        setForm(defaultForm());
        setShowForm(true);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    if (loading && !data) {
        return <div className="text-slate-500">Loading inventory...</div>;
    }

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white px-5 py-5 shadow-[0_10px_30px_rgba(15,23,42,0.06)]">
                <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div className="min-w-0">
                    <h1 className="whitespace-nowrap text-3xl font-bold tracking-normal text-slate-900">Inventory</h1>
                    <p className="mt-1 max-w-3xl text-sm text-slate-500">
                        Manage inventory records, stock levels, and item details in one place.
                    </p>
                </div>

                <div className="flex flex-col gap-3 sm:flex-row xl:justify-end">
                    <form onSubmit={handleSearchSubmit} className="flex min-w-0 items-center gap-2">
                        <input
                            type="text"
                            value={searchInput}
                            onChange={(e) => setSearchInput(e.target.value)}
                            placeholder="Search inventory..."
                            className="input-shell w-full sm:w-80"
                        />
                        <button type="submit" className="btn-primary">
                            Find
                        </button>
                    </form>

                    <button type="button" onClick={toggleForm} className="btn-primary">
                        {showForm ? 'Cancel' : 'Add Inventory Item'}
                    </button>

                    <select
                        value=""
                        onChange={(e) => {
                            if (e.target.value === 'csv') {
                                handleExportCsv();
                            }
                            if (e.target.value === 'pdf') {
                                handlePdfExport();
                            }
                        }}
                        className="input-shell sm:w-36"
                    >
                        <option value="">Export...</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                </div>
            </div>

            <div className="panel">
                <div className="grid grid-cols-1 gap-3 p-4 md:grid-cols-[1fr_1fr_auto]">
                    <select
                        value={filters.stock}
                        onChange={(e) => handleFilterChange('stock', e.target.value)}
                        className="input-shell"
                    >
                        <option value="">All stock states</option>
                        <option value="available">Available</option>
                        <option value="low">Low Stock</option>
                        <option value="out">Out of Stock</option>
                    </select>

                    <select
                        value={filters.category_id}
                        onChange={(e) => handleFilterChange('category_id', e.target.value)}
                        className="input-shell"
                    >
                        <option value="">All categories</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </select>

                    <button type="button" onClick={() => setSearchParams({})} className="btn-secondary">
                        Clear Filters
                    </button>
                </div>
            </div>

            {notice ? (
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {notice}
                </div>
            ) : null}

            {formError ? (
                <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {formError}
                </div>
            ) : null}

            {error ? (
                <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {error}
                </div>
            ) : null}

            {selectedItem ? (
                <section id="inventory-item-report" className="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-[0_18px_45px_rgba(37,99,235,0.10)]">
                    <div className="border-b border-blue-100 bg-blue-50/50 px-6 py-5">
                        <div className="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-wide text-blue-600">Asset File</p>
                                <h2 className="mt-1 text-2xl font-bold text-slate-900">{selectedItem.name}</h2>
                                <p className="mt-1 text-sm text-slate-500">
                                    Available stock, assigned receivers, movement dates, and exportable item history.
                                </p>
                            </div>

                            <div className="flex flex-col gap-2 sm:flex-row">
                                <select
                                    value={itemReportPeriod}
                                    onChange={(e) => setItemReportPeriod(e.target.value)}
                                    className="input-shell"
                                >
                                    <option value="all">All dates</option>
                                    <option value="today">Today</option>
                                    <option value="month">This month</option>
                                    <option value="year">This year</option>
                                    <option value="custom">Custom dates</option>
                                </select>

                                <select
                                    value=""
                                    onChange={(e) => {
                                        if (e.target.value === 'csv') {
                                            handleItemExportCsv();
                                        }
                                        if (e.target.value === 'pdf') {
                                            handlePdfExport();
                                        }
                                    }}
                                    className="input-shell"
                                >
                                    <option value="">Export...</option>
                                    <option value="csv">CSV</option>
                                    <option value="pdf">PDF</option>
                                </select>

                                <button type="button" onClick={() => setSelectedItem(null)} className="btn-secondary">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>

                    <div className="panel-body space-y-4">

                        {itemReportPeriod === 'custom' ? (
                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <input
                                    type="date"
                                    value={itemDateStart}
                                    onChange={(e) => setItemDateStart(e.target.value)}
                                    className="input-shell"
                                />
                                <input
                                    type="date"
                                    value={itemDateEnd}
                                    onChange={(e) => setItemDateEnd(e.target.value)}
                                    className="input-shell"
                                />
                            </div>
                        ) : null}

                        <div className="grid grid-cols-1 gap-3 md:grid-cols-5">
                            <div className="metric-card">
                                <p className="text-xs font-semibold uppercase text-slate-500">Asset Tag</p>
                                <p className="mt-1 font-semibold text-slate-900">{selectedItem.asset_tag || '-'}</p>
                            </div>
                            <div className="metric-card">
                                <p className="text-xs font-semibold uppercase text-slate-500">Date Added</p>
                                <p className="mt-1 font-semibold text-slate-900">
                                    {formatDateTime(selectedItem.date_added_at || selectedItem.date_added)}
                                </p>
                            </div>
                            <div className="metric-card">
                                <p className="text-xs font-semibold uppercase text-slate-500">Available</p>
                                <p className="mt-1 text-2xl font-bold text-emerald-700">
                                    {selectedItemPosition?.available_quantity ?? selectedItem.quantity ?? 0}
                                </p>
                            </div>
                            <div className="metric-card">
                                <p className="text-xs font-semibold uppercase text-slate-500">Assigned</p>
                                <p className="mt-1 text-2xl font-bold text-blue-700">
                                    {selectedItemPosition?.active_assigned_quantity ?? 0}
                                </p>
                            </div>
                            <div className="metric-card">
                                <p className="text-xs font-semibold uppercase text-slate-500">Managed Total</p>
                                <p className="mt-1 text-2xl font-bold text-slate-900">
                                    {selectedItemPosition?.managed_quantity ?? selectedItem.quantity ?? 0}
                                </p>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full overflow-hidden rounded-xl text-sm">
                                <thead className="table-head">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-semibold">Receiver</th>
                                        <th className="px-4 py-3 text-left font-semibold">Date / Time</th>
                                        <th className="px-4 py-3 text-left font-semibold">Qty</th>
                                        <th className="px-4 py-3 text-left font-semibold">Status</th>
                                        <th className="px-4 py-3 text-left font-semibold">Department</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {itemReport.history.map((entry) => (
                                        <tr key={entry.id} className="table-row">
                                            <td className="px-4 py-3 font-medium text-slate-900">{entry.receiver}</td>
                                            <td className="px-4 py-3 text-slate-700">{formatDateTime(entry.assigned_at)}</td>
                                            <td className="px-4 py-3 text-slate-700">{entry.quantity}</td>
                                            <td className="px-4 py-3 text-slate-700">{entry.status}</td>
                                            <td className="px-4 py-3 text-slate-700">{entry.department || '-'}</td>
                                        </tr>
                                    ))}
                                    {!itemReportLoading && itemReport.history.length === 0 ? (
                                        <tr>
                                            <td colSpan="5" className="px-4 py-8 text-center text-slate-500">
                                                No assignments found for this item in the selected date range.
                                            </td>
                                        </tr>
                                    ) : null}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            ) : null}

            {showForm ? (
                <div className="panel">
                    <div className="panel-body">
                        <h2 className="mb-4 text-lg font-semibold">
                            {editingId ? 'Edit Inventory Item' : 'Add New Inventory Item'}
                        </h2>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {!editingId ? (
                                    <div className="md:col-span-2">
                                        <label className="mb-1 block text-sm font-medium text-slate-700">Tracking Mode</label>
                                        <select
                                            value={form.tracking_mode}
                                            onChange={(e) => {
                                                const nextMode = e.target.value;

                                                setForm({
                                                    ...form,
                                                    tracking_mode: nextMode,
                                                    quantity: nextMode === 'serialized' ? 1 : form.quantity,
                                                    serial_number: nextMode === 'bulk' ? '' : form.serial_number,
                                                });
                                            }}
                                            className="input-shell w-full"
                                        >
                                            <option value="bulk">Bulk stock - one row with many units</option>
                                            <option value="serialized">Serialized asset - one unique asset</option>
                                        </select>
                                        <p className="mt-2 text-xs text-slate-500">
                                            {isSerializedMode
                                                ? 'Asset Tag and Serial Number will be saved. Quantity is fixed at 1.'
                                                : 'Bulk stock can still have an Asset Tag. Serial Number is only for serialized assets.'}
                                        </p>
                                    </div>
                                ) : null}

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Name</label>
                                    <input
                                        type="text"
                                        value={form.name}
                                        onChange={(e) => setForm({ ...form, name: e.target.value })}
                                        className="input-shell w-full"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Brand</label>
                                    <input
                                        type="text"
                                        value={form.brand}
                                        onChange={(e) => setForm({ ...form, brand: e.target.value })}
                                        className="input-shell w-full"
                                    />
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        {isSerializedMode ? 'SKU' : 'SKU / Bulk Item Code'}
                                    </label>
                                    <input
                                        type="text"
                                        value={form.sku}
                                        onChange={(e) => setForm({ ...form, sku: e.target.value })}
                                        className="input-shell w-full"
                                    />
                                    {!editingId && !isSerializedMode ? (
                                        <p className="mt-2 text-xs text-slate-500">
                                            This appears in the SKU column. It is not an Asset Tag.
                                        </p>
                                    ) : null}
                                </div>

                                {!editingId ? (
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-slate-700">Initial Quantity</label>
                                        <input
                                            type="number"
                                            min="1"
                                            value={form.quantity}
                                            disabled={isSerializedMode}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    quantity: Number.parseInt(e.target.value || '1', 10),
                                                })
                                            }
                                            className="input-shell w-full disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500"
                                            required
                                        />
                                        <p className="mt-2 text-xs text-slate-500">
                                            {isSerializedMode
                                                ? 'Serialized assets are tracked one-by-one, so quantity is fixed at 1.'
                                                : 'This is the starting stock count. Assignments subtract from this number and returns add back to it.'}
                                        </p>
                                    </div>
                                ) : (
                                    <div className="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                                        Current quantity: <strong>{form.quantity}</strong>. Quantity changes through assignments and stock updates.
                                    </div>
                                )}

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Reorder Level</label>
                                    <input
                                        type="number"
                                        min="0"
                                        value={form.reorder_level}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                reorder_level: Number.parseInt(e.target.value || '0', 10),
                                            })
                                        }
                                        className="input-shell w-full"
                                    />
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Unit Cost</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={form.unit_cost}
                                        onChange={(e) => setForm({ ...form, unit_cost: e.target.value })}
                                        className="input-shell w-full"
                                    />
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Category</label>
                                    <select
                                        value={form.category_id}
                                        onChange={(e) => setForm({ ...form, category_id: e.target.value })}
                                        className="input-shell w-full"
                                        required
                                    >
                                        <option value="">Select Category</option>
                                        {categories.map((category) => (
                                            <option key={category.id} value={category.id}>
                                                {category.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Supplier</label>
                                    <select
                                        value={form.supplier_id}
                                        onChange={(e) => setForm({ ...form, supplier_id: e.target.value })}
                                        className="input-shell w-full"
                                        required
                                    >
                                        <option value="">Select Supplier</option>
                                        {suppliers.map((supplier) => (
                                            <option key={supplier.id} value={supplier.id}>
                                                {supplier.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Lifecycle Status</label>
                                    <select
                                        value={form.status}
                                        onChange={(e) => setForm({ ...form, status: e.target.value })}
                                        className="input-shell w-full"
                                    >
                                        <option value="available">Available</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="lost">Lost</option>
                                        <option value="retired">Retired</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Asset Tag</label>
                                    <input
                                        type="text"
                                        value={form.asset_tag}
                                        onChange={(e) => setForm({ ...form, asset_tag: e.target.value })}
                                        className="input-shell w-full"
                                    />
                                    <p className="mt-2 text-xs text-slate-500">
                                        This appears in the Asset Tag column.
                                    </p>
                                </div>

                                {(editingId || isSerializedMode) ? (
                                    <>
                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-slate-700">Serial Number</label>
                                            <input
                                                type="text"
                                                value={form.serial_number}
                                                onChange={(e) => setForm({ ...form, serial_number: e.target.value })}
                                                className="input-shell w-full"
                                            />
                                        </div>
                                    </>
                                ) : null}

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Location</label>
                                    <input
                                        type="text"
                                        value={form.location}
                                        onChange={(e) => setForm({ ...form, location: e.target.value })}
                                        className="input-shell w-full"
                                    />
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Purchase Date</label>
                                    <input
                                        type="date"
                                        value={form.purchase_date}
                                        onChange={(e) => setForm({ ...form, purchase_date: e.target.value })}
                                        className="input-shell w-full"
                                    />
                                </div>

                                <div className="md:col-span-2">
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Description</label>
                                    <textarea
                                        value={form.description}
                                        onChange={(e) => setForm({ ...form, description: e.target.value })}
                                        rows="3"
                                        className="input-shell w-full"
                                    />
                                </div>
                            </div>

                            <div className="flex gap-3">
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {submitting ? 'Saving...' : editingId ? 'Update Inventory Item' : 'Create Inventory Item'}
                                </button>

                                <button type="button" onClick={resetForm} className="btn-secondary">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            ) : null}

            <div className="table-shell">
                <div className="overflow-x-auto">
                    <table className="min-w-[1280px] w-full text-sm">
                        <thead className="table-head">
                            <tr>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">Name</th>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">Asset Tag</th>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">SKU</th>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">Brand</th>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">Category</th>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">Supplier</th>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">Quantity</th>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">Stock State</th>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">Date Added</th>
                                <th className="px-6 py-4 text-left font-semibold whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {items.length > 0 ? (
                                items.map((item) => {
                                    const badge = getStockStateBadge(item);

                                    return (
                                        <tr key={item.id} className="table-row">
                                            <td className="px-6 py-4 font-medium text-slate-900">
                                                <button
                                                    type="button"
                                                    onClick={() => openItemReport(item)}
                                                    className="font-bold text-blue-700 transition hover:text-blue-900 hover:underline"
                                                >
                                                    {item.name || '-'}
                                                </button>
                                            </td>
                                            <td className="px-6 py-4 text-slate-700">{item.asset_tag || '-'}</td>
                                            <td className="px-6 py-4 text-slate-700">{item.sku || '-'}</td>
                                            <td className="px-6 py-4 text-slate-700">{item.brand || '-'}</td>
                                            <td className="px-6 py-4 text-slate-700">{item.category?.name || '-'}</td>
                                            <td className="px-6 py-4 text-slate-700">{item.supplier?.name || '-'}</td>
                                            <td className="px-6 py-4 font-semibold text-slate-900">{item.quantity ?? 0}</td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${badge.className}`}>
                                                    {badge.label}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-slate-700 whitespace-nowrap">
                                                {formatDateTime(item.date_added_at || item.date_added)}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => handleEdit(item)}
                                                    className="inline-flex rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 transition hover:bg-blue-100"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(item.id)}
                                                    className="inline-flex rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-100"
                                                >
                                                    Delete
                                                </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan="10" className="px-6 py-10 text-center text-slate-500">
                                        No inventory items found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        Showing page {meta.current_page} of {meta.last_page} · {meta.total} total
                    </p>

                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            disabled={meta.current_page <= 1}
                            onClick={() => goToPage(meta.current_page - 1)}
                            className="btn-secondary !px-3 !py-1.5 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>

                        <button
                            type="button"
                            disabled={meta.current_page >= meta.last_page}
                            onClick={() => goToPage(meta.current_page + 1)}
                            className="btn-secondary !px-3 !py-1.5 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
