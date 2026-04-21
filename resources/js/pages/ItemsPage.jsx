import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import apiClient from '../api/client';
import { downloadCsv } from '../utils/csv';
import { invalidateApiCache, useApi } from '../hooks/useApi';

function getStatusBadge(item) {
    const quantity = Number(item?.quantity || 0);

    if (item?.status === 'maintenance') {
        return { label: 'Maintenance', className: 'bg-orange-100 text-orange-700' };
    }

    if (item?.status === 'lost') {
        return { label: 'Lost', className: 'bg-red-100 text-red-700' };
    }

    if (item?.status === 'retired') {
        return { label: 'Retired', className: 'bg-slate-200 text-slate-700' };
    }

    if (quantity === 0) {
        return { label: 'Out of Stock', className: 'bg-rose-100 text-rose-700' };
    }

    if (item?.is_low_stock) {
        return { label: 'Low Stock', className: 'bg-amber-100 text-amber-700' };
    }

    return { label: 'Available', className: 'bg-emerald-100 text-emerald-700' };
}

function defaultForm() {
    return {
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

export default function ItemsPage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const [notice, setNotice] = useState('');
    const [showForm, setShowForm] = useState(searchParams.get('create') === '1');
    const [editingId, setEditingId] = useState(null);
    const [categories, setCategories] = useState([]);
    const [suppliers, setSuppliers] = useState([]);
    const [form, setForm] = useState(defaultForm());

    const filters = useMemo(
        () => ({
            search: searchParams.get('search') ?? '',
            status: searchParams.get('status') ?? '',
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

    const refreshItems = useCallback(async () => {
        invalidateApiCache('/items');
        await refetch();
    }, [refetch]);

    const resetForm = useCallback(() => {
        setForm(defaultForm());
        setEditingId(null);
        setShowForm(false);
    }, []);

    const handleSubmit = useCallback(
        async (event) => {
            event.preventDefault();

            try {
                setNotice('');

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
                    asset_tag: form.asset_tag.trim(),
                    serial_number: form.serial_number.trim(),
                    location: form.location.trim(),
                    purchase_date: form.purchase_date || '',
                };

                if (!editingId) {
                    payload.quantity = Number.parseInt(String(form.quantity || 1), 10);
                }

                const response = editingId
                    ? await apiClient.put(`/items/${editingId}`, payload)
                    : await apiClient.post('/items', payload);

                setNotice(response?.data?.message || (editingId ? 'Inventory item updated successfully.' : 'Inventory item saved successfully.'));
                resetForm();
                await refreshItems();
            } catch (err) {
                console.error(err);
            }
        },
        [editingId, form, refreshItems, resetForm]
    );

    const handleDelete = useCallback(
        async (id) => {
            if (!window.confirm('Are you sure you want to delete this inventory item?')) {
                return;
            }

            try {
                setNotice('');
                await apiClient.delete(`/items/${id}`);
                setNotice('Inventory item deleted successfully.');
                await refreshItems();
            } catch (err) {
                console.error(err);
            }
        },
        [refreshItems]
    );

    const handleEdit = useCallback((item) => {
        setNotice('');
        setForm({
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
                Status: getStatusBadge(item).label,
                Location: item.location || '',
                'Purchase Date': item.purchase_date || '',
            }))
        );
    }

    function goToPage(page) {
        updateQuery({ page });
    }

    function toggleForm() {
        setNotice('');

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
            <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">Inventory</h1>
                    <p className="mt-1 text-sm text-slate-500">
                        Manage inventory records, stock levels, and item details in one place.
                    </p>
                </div>

                <div className="flex flex-col gap-3 sm:flex-row">
                    <form onSubmit={handleSearchSubmit} className="flex items-center gap-2">
                        <input
                            type="text"
                            value={searchInput}
                            onChange={(e) => setSearchInput(e.target.value)}
                            placeholder="Search inventory..."
                            className="input-shell w-72"
                        />
                        <button type="submit" className="btn-secondary">
                            Find
                        </button>
                    </form>

                    <button type="button" onClick={toggleForm} className="btn-primary">
                        {showForm ? 'Cancel' : 'Add Inventory Item'}
                    </button>

                    <button type="button" onClick={handleExportCsv} className="btn-secondary">
                        Export CSV
                    </button>
                </div>
            </div>

            <div className="panel">
                <div className="grid grid-cols-1 gap-3 p-4 md:grid-cols-3">
                    <select
                        value={filters.status}
                        onChange={(e) => handleFilterChange('status', e.target.value)}
                        className="input-shell"
                    >
                        <option value="">All lifecycle statuses</option>
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="lost">Lost</option>
                        <option value="retired">Retired</option>
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
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{notice}</div>
            ) : null}

            {error ? (
                <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>
            ) : null}

            {showForm ? (
                <div className="panel">
                    <div className="panel-body">
                        <h2 className="mb-4 text-lg font-semibold">{editingId ? 'Edit Inventory Item' : 'Add New Inventory Item'}</h2>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                    <label className="mb-1 block text-sm font-medium text-slate-700">SKU</label>
                                    <input
                                        type="text"
                                        value={form.sku}
                                        onChange={(e) => setForm({ ...form, sku: e.target.value })}
                                        className="input-shell w-full"
                                    />
                                </div>

                                {!editingId ? (
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-slate-700">Initial Quantity</label>
                                        <input
                                            type="number"
                                            min="1"
                                            value={form.quantity}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    quantity: Number.parseInt(e.target.value || '1', 10),
                                                })
                                            }
                                            className="input-shell w-full"
                                            required
                                        />
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
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Serial Number</label>
                                    <input
                                        type="text"
                                        value={form.serial_number}
                                        onChange={(e) => setForm({ ...form, serial_number: e.target.value })}
                                        className="input-shell w-full"
                                    />
                                </div>

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
                                <button type="submit" className="btn-primary">
                                    {editingId ? 'Update' : 'Create'} Inventory Item
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
                    <table className="min-w-full text-sm">
                        <thead className="table-head">
                            <tr>
                                <th className="px-6 py-4 text-left font-semibold">Name</th>
                                <th className="px-6 py-4 text-left font-semibold">Asset Tag</th>
                                <th className="px-6 py-4 text-left font-semibold">Brand</th>
                                <th className="px-6 py-4 text-left font-semibold">Category</th>
                                <th className="px-6 py-4 text-left font-semibold">Supplier</th>
                                <th className="px-6 py-4 text-left font-semibold">Quantity</th>
                                <th className="px-6 py-4 text-left font-semibold">Stock State</th>
                                <th className="px-6 py-4 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {items.length > 0 ? (
                                items.map((item) => {
                                    const badge = getStatusBadge(item);

                                    return (
                                        <tr key={item.id} className="table-row">
                                            <td className="px-6 py-4 font-medium text-slate-900">{item.name}</td>
                                            <td className="px-6 py-4 text-slate-700">{item.asset_tag || '-'}</td>
                                            <td className="px-6 py-4 text-slate-700">{item.brand || '-'}</td>
                                            <td className="px-6 py-4 text-slate-700">{item.category?.name || '-'}</td>
                                            <td className="px-6 py-4 text-slate-700">{item.supplier?.name || '-'}</td>
                                            <td className="px-6 py-4 text-slate-700">{item.quantity}</td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${badge.className}`}>
                                                    {badge.label}
                                                </span>
                                            </td>
                                            <td className="space-x-3 px-6 py-4">
                                                <button type="button" onClick={() => handleEdit(item)} className="text-blue-600 hover:underline">
                                                    Edit
                                                </button>
                                                <button type="button" onClick={() => handleDelete(item.id)} className="text-red-600 hover:underline">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan="8" className="px-6 py-10 text-center text-slate-500">
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
