import React, { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import apiClient from '../api/client';
import { downloadCsv } from '../utils/csv';
import { invalidateApiCache, useApi } from '../hooks/useApi';

const PNG_TIME_ZONE = 'Pacific/Port_Moresby';

function defaultForm() {
    return {
        item_id: '',
        receiver_name: '',
        department_id: '',
        quantity: '1',
    };
}

function formatAssetOption(item) {
    const parts = [item.name];

    if (item.asset_tag) {
        parts.push(`Tag: ${item.asset_tag}`);
    }

    if (item.sku) {
        parts.push(`SKU: ${item.sku}`);
    }

    parts.push(`Available: ${item.quantity}`);

    return parts.join(' | ');
}

function isItemAssignable(item) {
    const quantity = Number(item?.quantity || 0);
    const status = String(item?.status || '').toLowerCase();

    if (quantity <= 0) {
        return false;
    }

    if (['maintenance', 'lost', 'retired'].includes(status)) {
        return false;
    }

    return true;
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

function extractErrorMessage(error) {
    const response = error?.response?.data;

    if (response?.errors && typeof response.errors === 'object') {
        const firstKey = Object.keys(response.errors)[0];

        if (firstKey && Array.isArray(response.errors[firstKey]) && response.errors[firstKey][0]) {
            return response.errors[firstKey][0];
        }
    }

    return response?.message || error?.message || 'Failed to create assignment.';
}

function ReportPager({ page, lastPage, total, onPageChange }) {
    if (total <= 0 || lastPage <= 1) {
        return null;
    }

    return (
        <div className="flex flex-col gap-3 border-t border-slate-100 pt-3 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
            <span>
                Showing page {page} of {lastPage} · {total} record(s)
            </span>
            <div className="flex items-center gap-2">
                <button
                    type="button"
                    disabled={page <= 1}
                    onClick={() => onPageChange(page - 1)}
                    className="btn-secondary !px-3 !py-1.5 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Previous
                </button>
                <button
                    type="button"
                    disabled={page >= lastPage}
                    onClick={() => onPageChange(page + 1)}
                    className="btn-secondary !px-3 !py-1.5 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Next
                </button>
            </div>
        </div>
    );
}

export default function AssignmentsPage() {
    const [searchParams, setSearchParams] = useSearchParams();

    const filters = useMemo(
        () => ({
            search: searchParams.get('search') ?? '',
            status: searchParams.get('status') ?? '',
            page: Number.parseInt(searchParams.get('page') ?? '1', 10),
            per_page: 10,
        }),
        [searchParams]
    );

    const { data, loading, error, refetch } = useApi('/assignments', { params: filters }, { ttl: 180000 });
    const assignments = data?.data || [];
    const meta = {
        current_page: data?.current_page || 1,
        last_page: data?.last_page || 1,
        total: data?.total || 0,
    };

    const [notice, setNotice] = useState('');
    const [formError, setFormError] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [refreshing, setRefreshing] = useState(false);
    const [showForm, setShowForm] = useState(searchParams.get('create') === '1');
    const [searchInput, setSearchInput] = useState(filters.search);
    const [form, setForm] = useState(defaultForm());
    const [itemsList, setItemsList] = useState([]);
    const [departmentsList, setDepartmentsList] = useState([]);
    const [report, setReport] = useState({ items: [], receivers: [], history: [] });
    const [reportLoading, setReportLoading] = useState(false);
    const [selectedReceiver, setSelectedReceiver] = useState('');
    const [reportPeriod, setReportPeriod] = useState('all');
    const [customDateStart, setCustomDateStart] = useState('');
    const [customDateEnd, setCustomDateEnd] = useState('');
    const [stockReportPage, setStockReportPage] = useState(1);
    const [receiverReportPage, setReceiverReportPage] = useState(1);
    const [historyReportPage, setHistoryReportPage] = useState(1);
    const reportPageSize = 8;
    const receiverPageSize = 6;

    useEffect(() => {
        window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
    }, []);

    useEffect(() => {
        setSearchInput(filters.search);
    }, [filters.search]);

    useEffect(() => {
        if (searchParams.get('create') === '1') {
            setShowForm(true);
        }
    }, [searchParams]);

    useEffect(() => {
        void fetchItemsOptions();
        void fetchDepartmentsOptions();
    }, []);

    useEffect(() => {
        void fetchAssignmentReport();
    }, [filters.search, filters.status, reportPeriod, customDateStart, customDateEnd]);

    useEffect(() => {
        if (departmentsList.length === 1 && !form.department_id) {
            setForm((prev) => ({
                ...prev,
                department_id: String(departmentsList[0].id),
            }));
        }
    }, [departmentsList, form.department_id]);

    const assignableItems = useMemo(
        () =>
            [...itemsList]
                .filter((item) => isItemAssignable(item))
                .sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''))),
        [itemsList]
    );

    const selectedItem = useMemo(
        () => assignableItems.find((item) => String(item.id) === String(form.item_id)) || null,
        [assignableItems, form.item_id]
    );

    const selectedDepartment = useMemo(
        () => departmentsList.find((department) => String(department.id) === String(form.department_id)) || null,
        [departmentsList, form.department_id]
    );

    const requestedQuantity = Number(form.quantity);
    const maxQuantity = Number(selectedItem?.quantity || 0);
    const remainingQuantity =
        selectedItem && Number.isInteger(requestedQuantity) && requestedQuantity > 0
            ? Math.max(maxQuantity - requestedQuantity, 0)
            : maxQuantity;
    const quantityErrorMessage =
        selectedItem && form.quantity !== '' && !Number.isInteger(requestedQuantity)
            ? 'Quantity must be a whole number.'
            : selectedItem && requestedQuantity <= 0
              ? 'Quantity must be greater than 0.'
              : selectedItem && requestedQuantity > maxQuantity
                ? `Only ${maxQuantity} unit(s) are available for assignment.`
                : '';

    const canSubmit =
        !submitting &&
        Boolean(selectedItem) &&
        Boolean(form.receiver_name.trim()) &&
        Boolean(form.department_id) &&
        Number.isInteger(requestedQuantity) &&
        requestedQuantity >= 1 &&
        requestedQuantity <= maxQuantity;

    const selectedReceiverDetail = useMemo(
        () => report.receivers.find((receiver) => receiver.receiver === selectedReceiver) || report.receivers[0] || null,
        [report.receivers, selectedReceiver]
    );

    const reportTotals = useMemo(
        () => ({
            available: report.items.reduce((sum, item) => sum + Number(item.available_quantity || 0), 0),
            assigned: report.items.reduce((sum, item) => sum + Number(item.active_assigned_quantity || 0), 0),
            managed: report.items.reduce((sum, item) => sum + Number(item.managed_quantity || 0), 0),
        }),
        [report.items]
    );
    const pagedStockItems = useMemo(
        () => report.items.slice((stockReportPage - 1) * reportPageSize, stockReportPage * reportPageSize),
        [report.items, stockReportPage]
    );
    const pagedReceiverItems = useMemo(
        () =>
            (selectedReceiverDetail?.items || []).slice(
                (receiverReportPage - 1) * receiverPageSize,
                receiverReportPage * receiverPageSize
            ),
        [selectedReceiverDetail, receiverReportPage]
    );
    const pagedHistoryItems = useMemo(
        () => report.history.slice((historyReportPage - 1) * reportPageSize, historyReportPage * reportPageSize),
        [report.history, historyReportPage]
    );
    const stockLastPage = Math.max(1, Math.ceil(report.items.length / reportPageSize));
    const receiverLastPage = Math.max(1, Math.ceil((selectedReceiverDetail?.items?.length || 0) / receiverPageSize));
    const historyLastPage = Math.max(1, Math.ceil(report.history.length / reportPageSize));
    const reportDateRange = useMemo(
        () => getPeriodRange(reportPeriod, customDateStart, customDateEnd),
        [reportPeriod, customDateStart, customDateEnd]
    );

    useEffect(() => {
        setStockReportPage(1);
        setHistoryReportPage(1);
    }, [filters.search, filters.status, reportPeriod, customDateStart, customDateEnd]);

    useEffect(() => {
        setReceiverReportPage(1);
    }, [selectedReceiver]);

    async function refreshAssignmentsPage() {
        setRefreshing(true);

        try {
            invalidateApiCache('/assignments');
            invalidateApiCache('/items');
            await Promise.all([refetch(), fetchItemsOptions(), fetchDepartmentsOptions(), fetchAssignmentReport()]);
        } finally {
            setRefreshing(false);
        }
    }

    async function fetchAssignmentReport() {
        setReportLoading(true);

        try {
            const response = await apiClient.get('/assignments/report', {
                params: {
                    search: filters.search,
                    status: filters.status,
                    date_start: reportDateRange.date_start,
                    date_end: reportDateRange.date_end,
                },
            });

            const nextReport = {
                items: response.data?.items || [],
                receivers: response.data?.receivers || [],
                history: response.data?.history || [],
            };

            setReport(nextReport);

            if (
                nextReport.receivers.length > 0 &&
                (!selectedReceiver || !nextReport.receivers.some((receiver) => receiver.receiver === selectedReceiver))
            ) {
                setSelectedReceiver(nextReport.receivers[0].receiver);
            }
        } catch (err) {
            console.error('Failed to load assignment report', err);
        } finally {
            setReportLoading(false);
        }
    }

    async function fetchItemsOptions() {
        try {
            const response = await apiClient.get('/items', { params: { per_page: 100 } });
            setItemsList(response.data.data || response.data || []);
        } catch (err) {
            console.error('Failed to load items for assignment', err);
        }
    }

    async function fetchDepartmentsOptions() {
        try {
            const response = await apiClient.get('/departments', { params: { per_page: 100 } });
            setDepartmentsList(response.data.data || response.data || []);
        } catch (err) {
            console.error('Failed to load departments for assignment', err);
        }
    }

    async function handleSubmit(event) {
        event.preventDefault();

        if (!canSubmit) {
            setFormError(quantityErrorMessage || 'Please complete all required fields before creating the assignment.');
            return;
        }

        try {
            setSubmitting(true);
            setNotice('');
            setFormError('');

            const payload = {
                item_id: Number(form.item_id),
                receiver_name: form.receiver_name.trim(),
                department_id: Number(form.department_id),
                quantity: Number.parseInt(String(form.quantity), 10),
            };

            await apiClient.post('/assignments', payload);

            setNotice('Assignment created successfully.');
            setForm(defaultForm());
            setShowForm(false);

            const next = new URLSearchParams(searchParams);
            next.delete('create');
            setSearchParams(next);

            await refreshAssignmentsPage();
        } catch (err) {
            console.error(err);
            setFormError(extractErrorMessage(err));
        } finally {
            setSubmitting(false);
        }
    }

    async function handleReturnAsset(id) {
        if (!window.confirm('Mark this assignment as returned?')) {
            return;
        }

        try {
            setNotice('');
            setFormError('');
            await apiClient.put(`/assignments/${id}/return`);
            setNotice('Asset returned successfully.');
            await refreshAssignmentsPage();
        } catch (err) {
            console.error(err);
            setFormError(extractErrorMessage(err));
        }
    }

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

    function goToPage(page) {
        updateQuery({ page });
    }

    function handlePdfExport() {
        window.print();
    }

    function exportReceiverCsv() {
        if (!selectedReceiverDetail) {
            return;
        }

        downloadCsv(
            `${selectedReceiverDetail.receiver}-asset-statement.csv`,
            selectedReceiverDetail.items.map((item) => ({
                Receiver: selectedReceiverDetail.receiver,
                Item: item.item_name,
                'Asset Tag': item.asset_tag || '',
                SKU: item.sku || '',
                'Date / Time (PNG Time)': formatDateTime(item.last_assigned_at),
                Department: item.department || '',
                'Qty Assigned': item.quantity_assigned,
                'Qty Returned': item.quantity_returned,
                'Qty Remaining': item.quantity_remaining,
            }))
        );
    }

    function exportStockCsv() {
        downloadCsv(
            'stock-position.csv',
            report.items.map((item) => ({
                Item: item.name,
                'Asset Tag': item.asset_tag || '',
                SKU: item.sku || '',
                Available: item.available_quantity,
                Assigned: item.active_assigned_quantity,
                'Total Managed': item.managed_quantity,
                State: item.stock_state,
            }))
        );
    }

    function exportHistoryCsv() {
        downloadCsv(
            'assignment-history.csv',
            report.history.map((entry) => ({
                Item: entry.item_name,
                'Asset Tag': entry.asset_tag || '',
                SKU: entry.sku || '',
                Receiver: entry.receiver,
                'Date / Time (PNG Time)': formatDateTime(entry.assigned_at),
                Quantity: entry.quantity,
                Status: entry.status,
                Department: entry.department || '',
            }))
        );
    }

    function openReceiverStatement(receiverName) {
        setSelectedReceiver(receiverName);
        document.getElementById('receiver-statement')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function scrollToSection(id) {
        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function toggleForm() {
        setNotice('');
        setFormError('');

        if (showForm) {
            setForm(defaultForm());
            setShowForm(false);

            const next = new URLSearchParams(searchParams);
            next.delete('create');
            setSearchParams(next);
            return;
        }

        setForm(defaultForm());
        setShowForm(true);

        const next = new URLSearchParams(searchParams);
        next.set('create', '1');
        setSearchParams(next);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    if (loading && !data) {
        return <div className="text-slate-500">Loading assignments...</div>;
    }

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white px-5 py-5 shadow-[0_10px_30px_rgba(15,23,42,0.06)]">
                <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div className="min-w-0">
                        <p className="text-xs font-bold uppercase tracking-wide text-blue-600">Asset Movement</p>
                        <h1 className="mt-1 whitespace-nowrap text-3xl font-bold tracking-normal text-slate-900">
                            Assignments
                        </h1>
                        <p className="mt-1 max-w-3xl text-sm text-slate-500">
                            Assign stock, review receiver statements, and return active assignments from one workspace.
                        </p>
                    </div>

                    <div className="flex flex-col gap-3 sm:flex-row xl:justify-end">
                        <form onSubmit={handleSearchSubmit} className="flex min-w-0 items-center gap-2">
                            <input
                                type="text"
                                value={searchInput}
                                onChange={(e) => setSearchInput(e.target.value)}
                                placeholder="Search assignments..."
                                className="input-shell w-full sm:w-80"
                            />
                            <button type="submit" className="btn-primary">
                                Find
                            </button>
                        </form>

                        <button type="button" onClick={toggleForm} className="btn-primary">
                            {showForm ? 'Cancel' : 'New Assignment'}
                        </button>
                    </div>
                </div>

                <div className="mt-5 grid grid-cols-2 gap-2 md:grid-cols-4">
                    <button type="button" onClick={() => scrollToSection('stock-position')} className="btn-secondary">
                        Stock Position
                    </button>
                    <button type="button" onClick={() => scrollToSection('receiver-statement')} className="btn-secondary">
                        Receiver Statement
                    </button>
                    <button type="button" onClick={() => scrollToSection('assignment-history')} className="btn-secondary">
                        History
                    </button>
                    <button type="button" onClick={() => scrollToSection('assignment-records')} className="btn-secondary">
                        Records
                    </button>
                </div>
            </div>

            <div className="panel">
                <div className="grid grid-cols-1 gap-3 p-4 md:grid-cols-[1fr_auto_auto]">
                    <select
                        value={filters.status}
                        onChange={(e) => updateQuery({ status: e.target.value, page: 1 })}
                        className="input-shell"
                    >
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="returned">Returned</option>
                    </select>

                    <button type="button" onClick={() => setSearchParams({})} className="btn-secondary">
                        Clear Filters
                    </button>

                    <button type="button" onClick={() => void refreshAssignmentsPage()} className="btn-secondary">
                        {refreshing ? 'Refreshing...' : 'Refresh'}
                    </button>
                </div>
            </div>

            <div className="panel">
                <div className="grid grid-cols-1 gap-3 p-4 md:grid-cols-[1fr_1fr_1fr_auto]">
                    <select
                        value={reportPeriod}
                        onChange={(e) => setReportPeriod(e.target.value)}
                        className="input-shell"
                    >
                        <option value="all">All report dates</option>
                        <option value="today">Today</option>
                        <option value="month">This month</option>
                        <option value="year">This year</option>
                        <option value="custom">Custom dates</option>
                    </select>

                    <input
                        type="date"
                        value={customDateStart}
                        disabled={reportPeriod !== 'custom'}
                        onChange={(e) => setCustomDateStart(e.target.value)}
                        className="input-shell disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500"
                    />

                    <input
                        type="date"
                        value={customDateEnd}
                        disabled={reportPeriod !== 'custom'}
                        onChange={(e) => setCustomDateEnd(e.target.value)}
                        className="input-shell disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500"
                    />

                    <select
                        value=""
                        onChange={(e) => {
                            if (e.target.value === 'csv') {
                                exportHistoryCsv();
                            }
                            if (e.target.value === 'pdf') {
                                handlePdfExport();
                            }
                        }}
                        className="input-shell md:w-44"
                    >
                        <option value="">Export report...</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <section id="stock-position" className="panel scroll-mt-6 xl:col-span-2">
                    <div className="panel-body space-y-4">
                        <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-wide text-blue-600">Inventory Overview</p>
                                <h2 className="mt-1 text-xl font-bold text-slate-900">Stock Position</h2>
                                <p className="text-sm text-slate-500">
                                    Live quantity split: available in store, assigned to receivers, and total managed stock.
                                </p>
                            </div>
                            <select
                                value=""
                                onChange={(e) => {
                                    if (e.target.value === 'csv') {
                                        exportStockCsv();
                                    }
                                    if (e.target.value === 'pdf') {
                                        handlePdfExport();
                                    }
                                }}
                                className="input-shell w-40"
                            >
                                <option value="">Export...</option>
                                <option value="csv">CSV</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>

                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div className="metric-card">
                                <p className="text-xs font-semibold uppercase text-slate-500">Available</p>
                                <p className="mt-1 text-2xl font-bold text-emerald-700">{reportTotals.available}</p>
                            </div>
                            <div className="metric-card">
                                <p className="text-xs font-semibold uppercase text-slate-500">Assigned</p>
                                <p className="mt-1 text-2xl font-bold text-blue-700">{reportTotals.assigned}</p>
                            </div>
                            <div className="metric-card">
                                <p className="text-xs font-semibold uppercase text-slate-500">Managed</p>
                                <p className="mt-1 text-2xl font-bold text-slate-900">{reportTotals.managed}</p>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-[880px] w-full text-sm">
                                <thead className="table-head">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-semibold">Item</th>
                                        <th className="px-4 py-3 text-left font-semibold">Tag / SKU</th>
                                        <th className="px-4 py-3 text-left font-semibold">Available</th>
                                        <th className="px-4 py-3 text-left font-semibold">Assigned</th>
                                        <th className="px-4 py-3 text-left font-semibold">Total</th>
                                        <th className="px-4 py-3 text-left font-semibold">State</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {pagedStockItems.map((item) => (
                                        <tr key={item.id} className="table-row">
                                            <td className="px-4 py-3 font-medium text-slate-900">{item.name}</td>
                                            <td className="px-4 py-3 text-slate-700">{item.asset_tag || item.sku || '-'}</td>
                                            <td className="px-4 py-3 text-slate-700">{item.available_quantity}</td>
                                            <td className="px-4 py-3 text-slate-700">{item.active_assigned_quantity}</td>
                                            <td className="px-4 py-3 text-slate-700">{item.managed_quantity}</td>
                                            <td className="px-4 py-3">
                                                <span className="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                    {item.stock_state}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                    {!reportLoading && report.items.length === 0 ? (
                                        <tr>
                                            <td colSpan="6" className="px-4 py-8 text-center text-slate-500">
                                                No stock records found.
                                            </td>
                                        </tr>
                                    ) : null}
                                </tbody>
                            </table>
                        </div>

                        <ReportPager
                            page={stockReportPage}
                            lastPage={stockLastPage}
                            total={report.items.length}
                            onPageChange={setStockReportPage}
                        />
                    </div>
                </section>

                <section id="receiver-statement" className="panel scroll-mt-6">
                    <div className="panel-body space-y-4">
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-wide text-blue-600">People</p>
                                <h2 className="mt-1 text-xl font-bold text-slate-900">Receiver Statement</h2>
                                <p className="text-sm text-slate-500">Select any receiver to review assigned and returned stock.</p>
                            </div>
                            <select
                                value=""
                                onChange={(e) => {
                                    if (e.target.value === 'csv') {
                                        exportReceiverCsv();
                                    }
                                    if (e.target.value === 'pdf') {
                                        handlePdfExport();
                                    }
                                }}
                                className="input-shell w-36"
                            >
                                <option value="">Export...</option>
                                <option value="csv">CSV</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>

                        <select
                            value={selectedReceiverDetail?.receiver || ''}
                            onChange={(e) => setSelectedReceiver(e.target.value)}
                            className="input-shell w-full"
                        >
                            {report.receivers.length === 0 ? <option value="">No receivers yet</option> : null}
                            {report.receivers.map((receiver) => (
                                <option key={receiver.receiver} value={receiver.receiver}>
                                    {receiver.receiver}
                                </option>
                            ))}
                        </select>

                        {selectedReceiverDetail ? (
                            <>
                                <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div className="metric-card">
                                        <p className="text-xs font-semibold uppercase text-slate-500">Assigned</p>
                                        <p className="mt-1 text-xl font-bold text-slate-900">
                                            {selectedReceiverDetail.total_quantity}
                                        </p>
                                    </div>
                                    <div className="metric-card">
                                        <p className="text-xs font-semibold uppercase text-slate-500">Returned</p>
                                        <p className="mt-1 text-xl font-bold text-slate-900">
                                            {selectedReceiverDetail.returned_quantity}
                                        </p>
                                    </div>
                                    <div className="metric-card">
                                        <p className="text-xs font-semibold uppercase text-slate-500">Remaining</p>
                                        <p className="mt-1 text-xl font-bold text-slate-900">
                                            {selectedReceiverDetail.active_quantity}
                                        </p>
                                    </div>
                                </div>

                                <div className="overflow-x-auto">
                                    <table className="w-full table-fixed text-xs sm:text-sm">
                                        <thead className="table-head">
                                            <tr>
                                                <th className="px-3 py-3 text-left font-semibold">Item</th>
                                                <th className="px-3 py-3 text-left font-semibold">Date / Time</th>
                                                <th className="px-3 py-3 text-left font-semibold">Assigned</th>
                                                <th className="px-3 py-3 text-left font-semibold">Returned</th>
                                                <th className="px-3 py-3 text-left font-semibold">Remaining</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100">
                                            {pagedReceiverItems.map((item) => (
                                                <tr key={`${selectedReceiverDetail.receiver}-${item.item_id}`} className="table-row">
                                                    <td className="break-words px-3 py-3 font-medium text-slate-900">{item.item_name}</td>
                                                    <td className="break-words px-3 py-3 text-slate-700">{formatDateTime(item.last_assigned_at)}</td>
                                                    <td className="px-3 py-3 text-slate-700">{item.quantity_assigned}</td>
                                                    <td className="px-3 py-3 text-slate-700">{item.quantity_returned}</td>
                                                    <td className="px-3 py-3 text-slate-700">{item.quantity_remaining}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <ReportPager
                                    page={receiverReportPage}
                                    lastPage={receiverLastPage}
                                    total={selectedReceiverDetail.items.length}
                                    onPageChange={setReceiverReportPage}
                                />
                            </>
                        ) : (
                            <p className="rounded-lg border border-slate-200 p-4 text-sm text-slate-500">
                                No receiver statement is available for the current filters.
                            </p>
                        )}
                    </div>
                </section>
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

            {showForm ? (
                <div className="panel scroll-mt-6">
                    <div className="panel-body">
                        <div className="mb-5">
                            <p className="text-xs font-bold uppercase tracking-wide text-blue-600">New Movement</p>
                            <h2 className="mt-1 text-xl font-bold text-slate-900">Create Assignment</h2>
                            <p className="text-sm text-slate-500">
                                Choose an asset, receiver, department, and quantity. Stock is checked before saving.
                            </p>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="mb-1 block text-sm font-medium text-slate-700">Asset</label>
                                <select
                                    value={form.item_id}
                                    onChange={(e) =>
                                        setForm((prev) => ({
                                            ...prev,
                                            item_id: e.target.value,
                                            quantity: '1',
                                        }))
                                    }
                                    className="input-shell w-full"
                                    required
                                >
                                    <option value="">Select Asset</option>
                                    {assignableItems.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {formatAssetOption(item)}
                                        </option>
                                    ))}
                                </select>

                                {assignableItems.length === 0 ? (
                                    <p className="mt-2 text-xs text-amber-600">
                                        No assignable items are currently available.
                                    </p>
                                ) : null}
                            </div>

                            {selectedItem ? (
                                <div className="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                                    <div className="grid gap-2 md:grid-cols-4">
                                        <div>
                                            <span className="block text-xs font-medium uppercase text-blue-500">Available now</span>
                                            <strong>{selectedItem.quantity}</strong>
                                        </div>
                                        <div>
                                            <span className="block text-xs font-medium uppercase text-blue-500">Assigning</span>
                                            <strong>{Number.isInteger(requestedQuantity) && requestedQuantity > 0 ? requestedQuantity : 0}</strong>
                                        </div>
                                        <div>
                                            <span className="block text-xs font-medium uppercase text-blue-500">Left after save</span>
                                            <strong>{remainingQuantity}</strong>
                                        </div>
                                        <div>
                                            <span className="block text-xs font-medium uppercase text-blue-500">Identifier</span>
                                            <strong>{selectedItem.asset_tag || selectedItem.sku || '-'}</strong>
                                        </div>
                                    </div>
                                </div>
                            ) : null}

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Receiver Name</label>
                                    <input
                                        type="text"
                                        value={form.receiver_name}
                                        onChange={(e) => setForm((prev) => ({ ...prev, receiver_name: e.target.value }))}
                                        className="input-shell w-full"
                                        placeholder="Enter receiver name"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Receiving Department</label>
                                    <select
                                        value={form.department_id}
                                        onChange={(e) => setForm((prev) => ({ ...prev, department_id: e.target.value }))}
                                        className="input-shell w-full"
                                        required
                                    >
                                        <option value="">Select Receiving Department</option>
                                        {departmentsList.map((department) => (
                                            <option key={department.id} value={department.id}>
                                                {department.name}
                                            </option>
                                        ))}
                                    </select>

                                    {departmentsList.length === 1 && selectedDepartment ? (
                                        <p className="mt-2 text-xs text-emerald-600">
                                            Auto-selected department: {selectedDepartment.name}
                                        </p>
                                    ) : null}

                                    {departmentsList.length === 0 ? (
                                        <p className="mt-2 text-xs text-amber-600">
                                            No departments are currently available.
                                        </p>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Quantity</label>
                                    <input
                                        type="number"
                                        min="1"
                                        max={maxQuantity || 1}
                                        value={form.quantity}
                                        onChange={(e) =>
                                            setForm((prev) => ({
                                                ...prev,
                                                quantity: e.target.value,
                                            }))
                                        }
                                        className="input-shell w-full"
                                        required
                                    />
                                    {quantityErrorMessage ? (
                                        <p className="mt-2 text-xs text-red-600">{quantityErrorMessage}</p>
                                    ) : null}
                                </div>
                            </div>

                            <div className="flex gap-3">
                                <button
                                    type="submit"
                                    disabled={!canSubmit}
                                    className="btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {submitting ? 'Creating...' : 'Create Assignment'}
                                </button>

                                <button type="button" onClick={toggleForm} className="btn-secondary">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            ) : null}

            <section id="assignment-history" className="panel scroll-mt-6">
                <div className="panel-body space-y-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-wide text-blue-600">Audit View</p>
                            <h2 className="mt-1 text-xl font-bold text-slate-900">Assignment History</h2>
                            <p className="text-sm text-slate-500">
                                Audit-friendly movement view with date, quantity, status, and receiving department.
                            </p>
                        </div>
                        <select
                            value=""
                            onChange={(e) => {
                                if (e.target.value === 'csv') {
                                    exportHistoryCsv();
                                }
                                if (e.target.value === 'pdf') {
                                    handlePdfExport();
                                }
                            }}
                            className="input-shell w-40"
                        >
                            <option value="">Export...</option>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-[980px] w-full text-sm">
                            <thead className="table-head">
                                <tr>
                                    <th className="px-4 py-3 text-left font-semibold">Item</th>
                                    <th className="px-4 py-3 text-left font-semibold">Receiver</th>
                                    <th className="px-4 py-3 text-left font-semibold">Date / Time</th>
                                    <th className="px-4 py-3 text-left font-semibold">Qty</th>
                                    <th className="px-4 py-3 text-left font-semibold">Status</th>
                                    <th className="px-4 py-3 text-left font-semibold">Dept</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {pagedHistoryItems.map((entry) => (
                                    <tr key={entry.id} className="table-row">
                                        <td className="px-4 py-3 font-medium text-slate-900">{entry.item_name}</td>
                                        <td className="px-4 py-3 text-slate-700">
                                            <button
                                                type="button"
                                                onClick={() => openReceiverStatement(entry.receiver)}
                                                className="font-bold text-blue-700 transition hover:text-blue-900 hover:underline"
                                            >
                                                {entry.receiver}
                                            </button>
                                        </td>
                                        <td className="px-4 py-3 text-slate-700">{formatDateTime(entry.assigned_at)}</td>
                                        <td className="px-4 py-3 text-slate-700">{entry.quantity}</td>
                                        <td className="px-4 py-3">
                                            <span
                                                className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${
                                                    entry.status === 'Returned'
                                                        ? 'bg-slate-100 text-slate-700'
                                                        : 'bg-emerald-100 text-emerald-700'
                                                }`}
                                            >
                                                {entry.status}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-slate-700">{entry.department || '-'}</td>
                                    </tr>
                                ))}
                                {!reportLoading && report.history.length === 0 ? (
                                    <tr>
                                        <td colSpan="6" className="px-4 py-8 text-center text-slate-500">
                                            No assignment history found for the current filters.
                                        </td>
                                    </tr>
                                ) : null}
                            </tbody>
                        </table>
                    </div>

                    <ReportPager
                        page={historyReportPage}
                        lastPage={historyLastPage}
                        total={report.history.length}
                        onPageChange={setHistoryReportPage}
                    />
                </div>
            </section>

            <section id="assignment-records" className="table-shell scroll-mt-6">
                <div className="flex flex-col gap-2 border-b border-slate-200 bg-white px-6 py-5 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-wide text-blue-600">Operations</p>
                        <h2 className="mt-1 text-xl font-bold text-slate-900">Assignment Records</h2>
                        <p className="text-sm text-slate-500">Return active assignments and open receiver statements from the table.</p>
                    </div>
                    <button type="button" onClick={() => void refreshAssignmentsPage()} className="btn-secondary">
                        {refreshing ? 'Refreshing...' : 'Refresh'}
                    </button>
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-[1120px] w-full text-sm">
                        <thead className="table-head">
                            <tr>
                                <th className="px-6 py-4 text-left font-semibold">Asset</th>
                                <th className="px-6 py-4 text-left font-semibold">Asset Tag</th>
                                <th className="px-6 py-4 text-left font-semibold">Receiver</th>
                                <th className="px-6 py-4 text-left font-semibold">Department</th>
                                <th className="px-6 py-4 text-left font-semibold">Quantity</th>
                                <th className="px-6 py-4 text-left font-semibold">Assigned On</th>
                                <th className="px-6 py-4 text-left font-semibold">Status</th>
                                <th className="px-6 py-4 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {assignments.length > 0 ? (
                                assignments.map((assignment) => (
                                    <tr key={assignment.id} className="table-row">
                                        <td className="px-6 py-4 font-medium text-slate-900">{assignment.item?.name || '-'}</td>
                                        <td className="px-6 py-4 text-slate-700">{assignment.item?.asset_tag || '-'}</td>
                                        <td className="px-6 py-4 text-slate-700">
                                            {assignment.receiver_name || assignment.user?.name ? (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        openReceiverStatement(assignment.receiver_name || assignment.user?.name)
                                                    }
                                                    className="font-bold text-blue-700 transition hover:text-blue-900 hover:underline"
                                                >
                                                    {assignment.receiver_name || assignment.user?.name}
                                                </button>
                                            ) : (
                                                '-'
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-slate-700">
                                            {assignment.assigned_department?.name || '-'}
                                        </td>
                                        <td className="px-6 py-4 text-slate-700">{assignment.quantity || 0}</td>
                                        <td className="px-6 py-4 text-slate-700">{formatDateTime(assignment.assigned_at)}</td>
                                        <td className="px-6 py-4">
                                            <span
                                                className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${
                                                    assignment.returned_at
                                                        ? 'bg-slate-100 text-slate-700'
                                                        : 'bg-emerald-100 text-emerald-700'
                                                }`}
                                            >
                                                {assignment.returned_at ? 'Returned' : 'Active'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            {!assignment.returned_at ? (
                                                <button
                                                    type="button"
                                                    onClick={() => handleReturnAsset(assignment.id)}
                                                    className="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 transition hover:bg-amber-100"
                                                >
                                                    Return
                                                </button>
                                            ) : (
                                                <span className="text-slate-400">-</span>
                                            )}
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="8" className="px-6 py-10 text-center text-slate-500">
                                        No assignments found.
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
            </section>
        </div>
    );
}
