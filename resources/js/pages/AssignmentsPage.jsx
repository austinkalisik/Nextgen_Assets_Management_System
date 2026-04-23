import React, { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import apiClient from '../api/client';
import { downloadCsv } from '../utils/csv';
import { invalidateApiCache, useApi } from '../hooks/useApi';

const PNG_TIME_ZONE = 'Pacific/Port_Moresby';

const TABS = [
    { id: 'active', label: 'Active', status: 'active' },
    { id: 'new', label: 'New Assignment', status: '' },
    { id: 'returned', label: 'Returned', status: 'returned' },
    { id: 'receivers', label: 'Receivers', status: '' },
    { id: 'stock', label: 'Stock Position', status: '' },
    { id: 'history', label: 'History', status: '' },
];

function defaultForm() {
    return {
        item_id: '',
        receiver_name: '',
        department_id: '',
        quantity: '1',
    };
}

function isItemAssignable(item) {
    const quantity = Number(item?.quantity || 0);
    const status = String(item?.status || '').toLowerCase();

    return quantity > 0 && !['maintenance', 'lost', 'retired'].includes(status);
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

    return response?.message || error?.message || 'Action failed. Please try again.';
}

function statusBadge(returnedAt) {
    return returnedAt
        ? 'border-slate-200 bg-slate-50 text-slate-700'
        : 'border-emerald-200 bg-emerald-50 text-emerald-700';
}

function stockBadge(state) {
    if (state === 'Out of Stock') {
        return 'border-red-200 bg-red-50 text-red-700';
    }

    if (state === 'Low Stock') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    return 'border-emerald-200 bg-emerald-50 text-emerald-700';
}

function Pager({ page, lastPage, total, onPageChange }) {
    if (total <= 0 || lastPage <= 1) {
        return null;
    }

    return (
        <div className="flex flex-col gap-3 border-t border-slate-100 pt-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
            <span>
                Page {page} of {lastPage} | {total} record(s)
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

function AssignmentCard({ assignment, onOpen, onReturn }) {
    const receiver = assignment.receiver_name || assignment.user?.name || '-';
    const returned = Boolean(assignment.returned_at);

    return (
        <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                    <h3 className="font-semibold text-slate-950">{assignment.item?.name || '-'}</h3>
                    <p className="mt-1 text-sm text-slate-500">{assignment.item?.asset_tag || assignment.item?.sku || 'No tag'}</p>
                </div>
                <span className={`shrink-0 rounded-full border px-2.5 py-1 text-xs font-semibold ${statusBadge(assignment.returned_at)}`}>
                    {returned ? 'Returned' : 'Active'}
                </span>
            </div>

            <div className="mt-4 grid grid-cols-2 gap-2 text-sm">
                <div className="rounded-lg bg-slate-50 px-3 py-2">
                    <p className="text-xs text-slate-500">Receiver</p>
                    <p className="font-semibold text-slate-900">{receiver}</p>
                </div>
                <div className="rounded-lg bg-slate-50 px-3 py-2">
                    <p className="text-xs text-slate-500">Quantity</p>
                    <p className="font-semibold text-slate-900">{assignment.quantity || 0}</p>
                </div>
                <div className="rounded-lg bg-slate-50 px-3 py-2">
                    <p className="text-xs text-slate-500">Department</p>
                    <p className="font-semibold text-slate-900">{assignment.assigned_department?.name || '-'}</p>
                </div>
                <div className="rounded-lg bg-slate-50 px-3 py-2">
                    <p className="text-xs text-slate-500">Assigned</p>
                    <p className="font-semibold text-slate-900">{formatDateTime(assignment.assigned_at)}</p>
                </div>
            </div>

            <div className="mt-4 flex flex-wrap gap-2">
                <button type="button" onClick={() => onOpen(assignment)} className="btn-secondary !px-3 !py-2">
                    Open
                </button>
                {!returned ? (
                    <button type="button" onClick={() => onReturn(assignment.id)} className="btn-primary !px-3 !py-2">
                        Mark Returned
                    </button>
                ) : null}
            </div>
        </div>
    );
}

function AssignmentDetail({ assignment, onClose, onReturn }) {
    if (!assignment) {
        return null;
    }

    const returned = Boolean(assignment.returned_at);
    const receiver = assignment.receiver_name || assignment.user?.name || '-';

    return (
        <div className="rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p className="text-xs font-bold uppercase tracking-wide text-blue-700">Selected Assignment</p>
                    <h2 className="mt-1 text-xl font-bold text-slate-950">{assignment.item?.name || '-'}</h2>
                    <p className="mt-1 text-sm text-slate-600">Receiver: {receiver}</p>
                </div>
                <button type="button" onClick={onClose} className="btn-secondary !px-3 !py-2">
                    Close
                </button>
            </div>

            <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div className="rounded-lg bg-white px-4 py-3">
                    <p className="text-xs text-slate-500">Asset Tag / SKU</p>
                    <p className="font-semibold text-slate-950">{assignment.item?.asset_tag || assignment.item?.sku || '-'}</p>
                </div>
                <div className="rounded-lg bg-white px-4 py-3">
                    <p className="text-xs text-slate-500">Department</p>
                    <p className="font-semibold text-slate-950">{assignment.assigned_department?.name || '-'}</p>
                </div>
                <div className="rounded-lg bg-white px-4 py-3">
                    <p className="text-xs text-slate-500">Quantity</p>
                    <p className="font-semibold text-slate-950">{assignment.quantity || 0}</p>
                </div>
                <div className="rounded-lg bg-white px-4 py-3">
                    <p className="text-xs text-slate-500">Status</p>
                    <p className="font-semibold text-slate-950">{returned ? 'Returned' : 'Active'}</p>
                </div>
            </div>

            <div className="mt-4 flex flex-wrap gap-2">
                {!returned ? (
                    <button type="button" onClick={() => onReturn(assignment.id)} className="btn-primary">
                        Mark This Assignment Returned
                    </button>
                ) : null}
            </div>
        </div>
    );
}

export default function AssignmentsPage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const initialTab = searchParams.get('tab') || (searchParams.get('create') === '1' ? 'new' : 'active');
    const [activeTab, setActiveTab] = useState(TABS.some((tab) => tab.id === initialTab) ? initialTab : 'active');

    const filters = useMemo(
        () => ({
            search: searchParams.get('search') ?? '',
            status: searchParams.get('status') ?? (activeTab === 'active' ? 'active' : activeTab === 'returned' ? 'returned' : ''),
            page: Number.parseInt(searchParams.get('page') ?? '1', 10),
            per_page: 10,
        }),
        [searchParams, activeTab]
    );

    const { data, loading, error, refetch } = useApi('/assignments', { params: filters }, { ttl: 60000 });
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
    const [searchInput, setSearchInput] = useState(filters.search);
    const [form, setForm] = useState(defaultForm());
    const [itemsList, setItemsList] = useState([]);
    const [departmentsList, setDepartmentsList] = useState([]);
    const [report, setReport] = useState({ items: [], receivers: [], history: [] });
    const [reportLoading, setReportLoading] = useState(false);
    const [selectedReceiver, setSelectedReceiver] = useState('');
    const [selectedAssignment, setSelectedAssignment] = useState(null);
    const [reportPeriod, setReportPeriod] = useState('all');
    const [customDateStart, setCustomDateStart] = useState('');
    const [customDateEnd, setCustomDateEnd] = useState('');
    const [stockPage, setStockPage] = useState(1);
    const [receiverPage, setReceiverPage] = useState(1);
    const [historyPage, setHistoryPage] = useState(1);

    const reportPageSize = 8;
    const receiverPageSize = 6;

    useEffect(() => {
        setSearchInput(filters.search);
    }, [filters.search]);

    useEffect(() => {
        void fetchItemsOptions();
        void fetchDepartmentsOptions();
    }, []);

    const reportDateRange = useMemo(
        () => getPeriodRange(reportPeriod, customDateStart, customDateEnd),
        [reportPeriod, customDateStart, customDateEnd]
    );

    useEffect(() => {
        void fetchAssignmentReport();
    }, [filters.search, reportPeriod, customDateStart, customDateEnd]);

    useEffect(() => {
        if (departmentsList.length === 1 && !form.department_id) {
            setForm((prev) => ({ ...prev, department_id: String(departmentsList[0].id) }));
        }
    }, [departmentsList, form.department_id]);

    const assignableItems = useMemo(
        () => [...itemsList].filter(isItemAssignable).sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''))),
        [itemsList]
    );

    const selectedItem = useMemo(
        () => assignableItems.find((item) => String(item.id) === String(form.item_id)) || null,
        [assignableItems, form.item_id]
    );

    const requestedQuantity = Number(form.quantity);
    const maxQuantity = Number(selectedItem?.quantity || 0);
    const remainingQuantity = selectedItem && Number.isInteger(requestedQuantity) && requestedQuantity > 0
        ? Math.max(maxQuantity - requestedQuantity, 0)
        : maxQuantity;
    const quantityErrorMessage = selectedItem && form.quantity !== '' && !Number.isInteger(requestedQuantity)
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
            active: report.history.filter((entry) => entry.status === 'Active').length,
            returned: report.history.filter((entry) => entry.status === 'Returned').length,
        }),
        [report]
    );

    const pagedStockItems = useMemo(
        () => report.items.slice((stockPage - 1) * reportPageSize, stockPage * reportPageSize),
        [report.items, stockPage]
    );
    const pagedReceiverItems = useMemo(
        () => (selectedReceiverDetail?.items || []).slice((receiverPage - 1) * receiverPageSize, receiverPage * receiverPageSize),
        [selectedReceiverDetail, receiverPage]
    );
    const pagedHistoryItems = useMemo(
        () => report.history.slice((historyPage - 1) * reportPageSize, historyPage * reportPageSize),
        [report.history, historyPage]
    );

    const stockLastPage = Math.max(1, Math.ceil(report.items.length / reportPageSize));
    const receiverLastPage = Math.max(1, Math.ceil((selectedReceiverDetail?.items?.length || 0) / receiverPageSize));
    const historyLastPage = Math.max(1, Math.ceil(report.history.length / reportPageSize));

    async function fetchAssignmentReport() {
        setReportLoading(true);

        try {
            const response = await apiClient.get('/assignments/report', {
                params: {
                    search: filters.search,
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

            if (nextReport.receivers.length > 0 && (!selectedReceiver || !nextReport.receivers.some((receiver) => receiver.receiver === selectedReceiver))) {
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

    function changeTab(tabId) {
        const tab = TABS.find((item) => item.id === tabId) || TABS[0];
        setActiveTab(tab.id);
        setNotice('');
        setFormError('');
        setSelectedAssignment(null);
        updateQuery({ tab: tab.id, status: tab.status, page: 1 });
    }

    function handleSearchSubmit(event) {
        event.preventDefault();
        updateQuery({ search: searchInput.trim(), page: 1 });
    }

    function clearSearch() {
        setSearchInput('');
        updateQuery({ search: '', page: 1 });
    }

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

            await apiClient.post('/assignments', {
                item_id: Number(form.item_id),
                receiver_name: form.receiver_name.trim(),
                department_id: Number(form.department_id),
                quantity: Number.parseInt(String(form.quantity), 10),
            });

            setNotice('Assignment created successfully.');
            setForm(defaultForm());
            changeTab('active');
            await refreshAssignmentsPage();
        } catch (err) {
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
            setSelectedAssignment(null);
            await refreshAssignmentsPage();
        } catch (err) {
            setFormError(extractErrorMessage(err));
        }
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

    if (loading && !data) {
        return <div className="text-slate-500">Loading assignments...</div>;
    }

    return (
        <div className="space-y-5">
            <div className="page-header">
                <div>
                    <h1 className="page-title">Assignments</h1>
                    <p className="page-subtitle">Assign assets, return issued stock, and open receiver statements from a simple tabbed workspace.</p>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row">
                    <button type="button" onClick={() => void refreshAssignmentsPage()} className="btn-secondary">
                        {refreshing ? 'Refreshing...' : 'Refresh'}
                    </button>
                    <button type="button" onClick={() => changeTab('new')} className="btn-primary">
                        New Assignment
                    </button>
                </div>
            </div>

            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div className="metric-card">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Active Assignments</p>
                    <p className="mt-2 text-2xl font-bold text-emerald-700">{reportTotals.active}</p>
                </div>
                <div className="metric-card">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Returned Records</p>
                    <p className="mt-2 text-2xl font-bold text-slate-950">{reportTotals.returned}</p>
                </div>
                <div className="metric-card">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Receivers</p>
                    <p className="mt-2 text-2xl font-bold text-blue-700">{report.receivers.length}</p>
                </div>
                <div className="metric-card">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned Quantity</p>
                    <p className="mt-2 text-2xl font-bold text-amber-700">{reportTotals.assigned}</p>
                </div>
            </div>

            {notice ? <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{notice}</div> : null}
            {formError || error ? (
                <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {formError || error}
                </div>
            ) : null}

            <div className="panel">
                <div className="panel-body space-y-4">
                    <div className="flex gap-2 overflow-x-auto pb-1">
                        {TABS.map((tab) => (
                            <button
                                key={tab.id}
                                type="button"
                                onClick={() => changeTab(tab.id)}
                                className={[
                                    'shrink-0 rounded-lg border px-4 py-2.5 text-sm font-semibold transition',
                                    activeTab === tab.id
                                        ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50',
                                ].join(' ')}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    <form onSubmit={handleSearchSubmit} className="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex flex-col gap-2 sm:flex-row">
                            <input
                                type="search"
                                value={searchInput}
                                onChange={(event) => setSearchInput(event.target.value)}
                                placeholder="Search receiver, asset, tag, SKU, department"
                                className="input-shell w-full sm:w-96"
                            />
                            <button type="submit" className="btn-secondary">
                                Search
                            </button>
                            <button type="button" onClick={clearSearch} className="btn-secondary">
                                Clear
                            </button>
                        </div>

                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <select
                                value={reportPeriod}
                                onChange={(event) => setReportPeriod(event.target.value)}
                                className="input-shell"
                            >
                                <option value="all">All dates</option>
                                <option value="today">Today</option>
                                <option value="month">This month</option>
                                <option value="year">This year</option>
                                <option value="custom">Custom dates</option>
                            </select>
                            <input
                                type="date"
                                value={customDateStart}
                                disabled={reportPeriod !== 'custom'}
                                onChange={(event) => setCustomDateStart(event.target.value)}
                                className="input-shell disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500"
                            />
                            <input
                                type="date"
                                value={customDateEnd}
                                disabled={reportPeriod !== 'custom'}
                                onChange={(event) => setCustomDateEnd(event.target.value)}
                                className="input-shell disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500"
                            />
                        </div>
                    </form>
                </div>
            </div>

            {(activeTab === 'active' || activeTab === 'returned') ? (
                <section className="space-y-4">
                    <AssignmentDetail assignment={selectedAssignment} onClose={() => setSelectedAssignment(null)} onReturn={handleReturnAsset} />

                    <div className="mobile-card-list">
                        {assignments.length > 0 ? (
                            assignments.map((assignment) => (
                                <AssignmentCard key={assignment.id} assignment={assignment} onOpen={setSelectedAssignment} onReturn={handleReturnAsset} />
                            ))
                        ) : (
                            <div className="rounded-xl border border-slate-200 bg-white px-6 py-10 text-center text-slate-500 shadow-sm">
                                No assignments found.
                            </div>
                        )}
                    </div>

                    <div className="desktop-table table-shell">
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead className="table-head">
                                    <tr>
                                        <th className="px-6 py-4 text-left font-semibold">Asset</th>
                                        <th className="px-6 py-4 text-left font-semibold">Receiver</th>
                                        <th className="px-6 py-4 text-left font-semibold">Department</th>
                                        <th className="px-6 py-4 text-left font-semibold">Qty</th>
                                        <th className="px-6 py-4 text-left font-semibold">Assigned</th>
                                        <th className="px-6 py-4 text-left font-semibold">Status</th>
                                        <th className="px-6 py-4 text-left font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {assignments.length > 0 ? (
                                        assignments.map((assignment) => (
                                            <tr key={assignment.id} className="table-row">
                                                <td className="px-6 py-4">
                                                    <p className="font-semibold text-slate-950">{assignment.item?.name || '-'}</p>
                                                    <p className="text-xs text-slate-500">{assignment.item?.asset_tag || assignment.item?.sku || 'No tag'}</p>
                                                </td>
                                                <td className="px-6 py-4 font-medium text-slate-800">{assignment.receiver_name || assignment.user?.name || '-'}</td>
                                                <td className="px-6 py-4 text-slate-700">{assignment.assigned_department?.name || '-'}</td>
                                                <td className="px-6 py-4 text-slate-700">{assignment.quantity || 0}</td>
                                                <td className="px-6 py-4 text-slate-700">{formatDateTime(assignment.assigned_at)}</td>
                                                <td className="px-6 py-4">
                                                    <span className={`rounded-full border px-2.5 py-1 text-xs font-semibold ${statusBadge(assignment.returned_at)}`}>
                                                        {assignment.returned_at ? 'Returned' : 'Active'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="flex flex-wrap gap-2">
                                                        <button type="button" onClick={() => setSelectedAssignment(assignment)} className="btn-secondary !px-3 !py-2">
                                                            Open
                                                        </button>
                                                        {!assignment.returned_at ? (
                                                            <button type="button" onClick={() => handleReturnAsset(assignment.id)} className="btn-primary !px-3 !py-2">
                                                                Return
                                                            </button>
                                                        ) : null}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="7" className="px-6 py-10 text-center text-slate-500">
                                                No assignments found.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <Pager page={meta.current_page} lastPage={meta.last_page} total={meta.total} onPageChange={(page) => updateQuery({ page })} />
                </section>
            ) : null}

            {activeTab === 'new' ? (
                <section className="panel">
                    <div className="panel-body">
                        <div className="mb-5">
                            <h2 className="section-title">Create Assignment</h2>
                            <p className="section-subtitle">Select available stock, enter the receiver, choose department, and save.</p>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-5">
                            <div>
                                <label className="field-label">Asset / Item</label>
                                <select
                                    value={form.item_id}
                                    onChange={(event) => setForm((prev) => ({ ...prev, item_id: event.target.value }))}
                                    className="input-shell w-full"
                                    required
                                >
                                    <option value="">Select available asset</option>
                                    {assignableItems.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.name} | {item.asset_tag || item.sku || 'No tag'} | Available: {item.quantity}
                                        </option>
                                    ))}
                                </select>
                                {assignableItems.length === 0 ? <p className="mt-2 text-xs text-amber-600">No assignable items are currently available.</p> : null}
                            </div>

                            {selectedItem ? (
                                <div className="grid gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 sm:grid-cols-4">
                                    <div>
                                        <p className="text-xs font-semibold uppercase text-blue-500">Available</p>
                                        <p className="text-xl font-bold">{selectedItem.quantity}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs font-semibold uppercase text-blue-500">Assigning</p>
                                        <p className="text-xl font-bold">{Number.isInteger(requestedQuantity) && requestedQuantity > 0 ? requestedQuantity : 0}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs font-semibold uppercase text-blue-500">Left After Save</p>
                                        <p className="text-xl font-bold">{remainingQuantity}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs font-semibold uppercase text-blue-500">Identifier</p>
                                        <p className="text-xl font-bold">{selectedItem.asset_tag || selectedItem.sku || '-'}</p>
                                    </div>
                                </div>
                            ) : null}

                            <div className="grid gap-4 lg:grid-cols-3">
                                <div>
                                    <label className="field-label">Receiver Name</label>
                                    <input
                                        type="text"
                                        value={form.receiver_name}
                                        onChange={(event) => setForm((prev) => ({ ...prev, receiver_name: event.target.value }))}
                                        className="input-shell w-full"
                                        placeholder="Officer or staff receiving asset"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="field-label">Receiving Department</label>
                                    <select
                                        value={form.department_id}
                                        onChange={(event) => setForm((prev) => ({ ...prev, department_id: event.target.value }))}
                                        className="input-shell w-full"
                                        required
                                    >
                                        <option value="">Select department</option>
                                        {departmentsList.map((department) => (
                                            <option key={department.id} value={department.id}>
                                                {department.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="field-label">Quantity</label>
                                    <input
                                        type="number"
                                        min="1"
                                        max={maxQuantity || 1}
                                        value={form.quantity}
                                        onChange={(event) => setForm((prev) => ({ ...prev, quantity: event.target.value }))}
                                        className="input-shell w-full"
                                        required
                                    />
                                    {quantityErrorMessage ? <p className="mt-2 text-xs text-red-600">{quantityErrorMessage}</p> : null}
                                </div>
                            </div>

                            <div className="flex flex-col gap-2 sm:flex-row">
                                <button type="submit" disabled={!canSubmit} className="btn-primary disabled:cursor-not-allowed disabled:opacity-50">
                                    {submitting ? 'Creating...' : 'Create Assignment'}
                                </button>
                                <button type="button" onClick={() => setForm(defaultForm())} className="btn-secondary">
                                    Clear Form
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            ) : null}

            {activeTab === 'receivers' ? (
                <section className="grid gap-5 xl:grid-cols-[360px_1fr]">
                    <div className="panel">
                        <div className="panel-body">
                            <h2 className="section-title">Receivers</h2>
                            <p className="section-subtitle">Click a receiver to open their statement.</p>
                            <div className="mt-4 space-y-2">
                                {report.receivers.map((receiver) => (
                                    <button
                                        key={receiver.receiver}
                                        type="button"
                                        onClick={() => {
                                            setSelectedReceiver(receiver.receiver);
                                            setReceiverPage(1);
                                        }}
                                        className={[
                                            'w-full rounded-lg border px-4 py-3 text-left transition',
                                            selectedReceiverDetail?.receiver === receiver.receiver
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-slate-200 bg-white hover:bg-slate-50',
                                        ].join(' ')}
                                    >
                                        <p className="font-semibold text-slate-950">{receiver.receiver}</p>
                                        <p className="mt-1 text-xs text-slate-500">
                                            {receiver.active_quantity} active | {receiver.returned_quantity} returned
                                        </p>
                                    </button>
                                ))}
                                {!reportLoading && report.receivers.length === 0 ? (
                                    <p className="rounded-lg bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No receivers found.</p>
                                ) : null}
                            </div>
                        </div>
                    </div>

                    <div className="panel">
                        <div className="panel-body space-y-4">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h2 className="section-title">{selectedReceiverDetail?.receiver || 'Receiver Statement'}</h2>
                                    <p className="section-subtitle">Assigned, returned, and remaining stock for this receiver.</p>
                                </div>
                                <button type="button" onClick={exportReceiverCsv} disabled={!selectedReceiverDetail} className="btn-secondary disabled:opacity-50">
                                    Export CSV
                                </button>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-3">
                                <div className="metric-card">
                                    <p className="text-xs font-semibold uppercase text-slate-500">Active Qty</p>
                                    <p className="mt-1 text-2xl font-bold text-emerald-700">{selectedReceiverDetail?.active_quantity || 0}</p>
                                </div>
                                <div className="metric-card">
                                    <p className="text-xs font-semibold uppercase text-slate-500">Returned Qty</p>
                                    <p className="mt-1 text-2xl font-bold text-slate-950">{selectedReceiverDetail?.returned_quantity || 0}</p>
                                </div>
                                <div className="metric-card">
                                    <p className="text-xs font-semibold uppercase text-slate-500">Total Qty</p>
                                    <p className="mt-1 text-2xl font-bold text-blue-700">{selectedReceiverDetail?.total_quantity || 0}</p>
                                </div>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full text-sm">
                                    <thead className="table-head">
                                        <tr>
                                            <th className="px-4 py-3 text-left font-semibold">Item</th>
                                            <th className="px-4 py-3 text-left font-semibold">Department</th>
                                            <th className="px-4 py-3 text-left font-semibold">Assigned</th>
                                            <th className="px-4 py-3 text-left font-semibold">Returned</th>
                                            <th className="px-4 py-3 text-left font-semibold">Remaining</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100">
                                        {pagedReceiverItems.map((item) => (
                                            <tr key={`${item.item_id}-${item.department}`} className="table-row">
                                                <td className="px-4 py-3">
                                                    <p className="font-semibold text-slate-950">{item.item_name}</p>
                                                    <p className="text-xs text-slate-500">{item.asset_tag || item.sku || '-'}</p>
                                                </td>
                                                <td className="px-4 py-3 text-slate-700">{item.department || '-'}</td>
                                                <td className="px-4 py-3 text-slate-700">{item.quantity_assigned}</td>
                                                <td className="px-4 py-3 text-slate-700">{item.quantity_returned}</td>
                                                <td className="px-4 py-3 font-semibold text-slate-950">{item.quantity_remaining}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <Pager page={receiverPage} lastPage={receiverLastPage} total={selectedReceiverDetail?.items?.length || 0} onPageChange={setReceiverPage} />
                        </div>
                    </div>
                </section>
            ) : null}

            {activeTab === 'stock' ? (
                <section className="panel">
                    <div className="panel-body space-y-4">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 className="section-title">Stock Position</h2>
                                <p className="section-subtitle">Live quantity split between store stock and assigned stock.</p>
                            </div>
                            <button type="button" onClick={exportStockCsv} className="btn-secondary">
                                Export CSV
                            </button>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-3">
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
                                <p className="mt-1 text-2xl font-bold text-slate-950">{reportTotals.managed}</p>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead className="table-head">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-semibold">Item</th>
                                        <th className="px-4 py-3 text-left font-semibold">Available</th>
                                        <th className="px-4 py-3 text-left font-semibold">Assigned</th>
                                        <th className="px-4 py-3 text-left font-semibold">Managed</th>
                                        <th className="px-4 py-3 text-left font-semibold">State</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {pagedStockItems.map((item) => (
                                        <tr key={item.id} className="table-row">
                                            <td className="px-4 py-3">
                                                <p className="font-semibold text-slate-950">{item.name}</p>
                                                <p className="text-xs text-slate-500">{item.asset_tag || item.sku || '-'}</p>
                                            </td>
                                            <td className="px-4 py-3 text-slate-700">{item.available_quantity}</td>
                                            <td className="px-4 py-3 text-slate-700">{item.active_assigned_quantity}</td>
                                            <td className="px-4 py-3 text-slate-700">{item.managed_quantity}</td>
                                            <td className="px-4 py-3">
                                                <span className={`rounded-full border px-2.5 py-1 text-xs font-semibold ${stockBadge(item.stock_state)}`}>
                                                    {item.stock_state}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <Pager page={stockPage} lastPage={stockLastPage} total={report.items.length} onPageChange={setStockPage} />
                    </div>
                </section>
            ) : null}

            {activeTab === 'history' ? (
                <section className="panel">
                    <div className="panel-body space-y-4">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 className="section-title">Assignment History</h2>
                                <p className="section-subtitle">Audit-friendly movement history with receiver, quantity, department, and status.</p>
                            </div>
                            <button type="button" onClick={exportHistoryCsv} className="btn-secondary">
                                Export CSV
                            </button>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead className="table-head">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-semibold">Item</th>
                                        <th className="px-4 py-3 text-left font-semibold">Receiver</th>
                                        <th className="px-4 py-3 text-left font-semibold">Date / Time</th>
                                        <th className="px-4 py-3 text-left font-semibold">Qty</th>
                                        <th className="px-4 py-3 text-left font-semibold">Status</th>
                                        <th className="px-4 py-3 text-left font-semibold">Department</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {pagedHistoryItems.map((entry) => (
                                        <tr key={entry.id} className="table-row">
                                            <td className="px-4 py-3">
                                                <p className="font-semibold text-slate-950">{entry.item_name}</p>
                                                <p className="text-xs text-slate-500">{entry.asset_tag || entry.sku || '-'}</p>
                                            </td>
                                            <td className="px-4 py-3 text-slate-700">{entry.receiver}</td>
                                            <td className="px-4 py-3 text-slate-700">{formatDateTime(entry.assigned_at)}</td>
                                            <td className="px-4 py-3 text-slate-700">{entry.quantity}</td>
                                            <td className="px-4 py-3">
                                                <span className={`rounded-full border px-2.5 py-1 text-xs font-semibold ${entry.status === 'Returned' ? statusBadge(true) : statusBadge(false)}`}>
                                                    {entry.status}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-slate-700">{entry.department || '-'}</td>
                                        </tr>
                                    ))}
                                    {!reportLoading && report.history.length === 0 ? (
                                        <tr>
                                            <td colSpan="6" className="px-4 py-8 text-center text-slate-500">
                                                No assignment history found.
                                            </td>
                                        </tr>
                                    ) : null}
                                </tbody>
                            </table>
                        </div>

                        <Pager page={historyPage} lastPage={historyLastPage} total={report.history.length} onPageChange={setHistoryPage} />
                    </div>
                </section>
            ) : null}
        </div>
    );
}
