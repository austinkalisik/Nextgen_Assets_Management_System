import React, { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import apiClient from '../api/client';
import { invalidateApiCache, useApi } from '../hooks/useApi';

function defaultForm() {
    return {
        item_id: '',
        receiver_name: '',
        department_id: '',
        quantity: 1,
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

    return new Date(value).toLocaleDateString();
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
    const [showForm, setShowForm] = useState(searchParams.get('create') === '1');
    const [searchInput, setSearchInput] = useState(filters.search);
    const [form, setForm] = useState(defaultForm());
    const [itemsList, setItemsList] = useState([]);
    const [departmentsList, setDepartmentsList] = useState([]);

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

    async function refreshAssignmentsPage() {
        invalidateApiCache('/assignments');
        invalidateApiCache('/items');
        await Promise.all([refetch(), fetchItemsOptions(), fetchDepartmentsOptions()]);
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

        try {
            setNotice('');

            const payload = {
                item_id: form.item_id,
                receiver_name: form.receiver_name.trim(),
                department_id: form.department_id,
                quantity: Number.parseInt(String(form.quantity || 1), 10),
            };

            await apiClient.post('/assignments', payload);

            setNotice('Assignment created successfully.');
            setForm(defaultForm());
            setShowForm(false);

            await refreshAssignmentsPage();
        } catch (err) {
            console.error(err);
        }
    }

    async function handleReturnAsset(id) {
        if (!window.confirm('Mark this assignment as returned?')) {
            return;
        }

        try {
            setNotice('');
            await apiClient.put(`/assignments/${id}/return`);
            setNotice('Asset returned successfully.');
            await refreshAssignmentsPage();
        } catch (err) {
            console.error(err);
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

    function toggleForm() {
        setNotice('');

        if (showForm) {
            setForm(defaultForm());
            setShowForm(false);
            return;
        }

        setForm(defaultForm());
        setShowForm(true);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    if (loading && !data) {
        return <div className="text-slate-500">Loading assignments...</div>;
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">Assignments</h1>
                    <p className="mt-1 text-sm text-slate-500">
                        Assign available stock to any receiver. Quantity is validated against live stock.
                    </p>
                </div>

                <div className="flex flex-col gap-3 sm:flex-row">
                    <form onSubmit={handleSearchSubmit} className="flex items-center gap-2">
                        <input
                            type="text"
                            value={searchInput}
                            onChange={(e) => setSearchInput(e.target.value)}
                            placeholder="Search assignments..."
                            className="input-shell w-72"
                        />
                        <button type="submit" className="btn-secondary">
                            Find
                        </button>
                    </form>

                    <button type="button" onClick={toggleForm} className="btn-primary">
                        {showForm ? 'Cancel' : 'New Assignment'}
                    </button>
                </div>
            </div>

            <div className="panel">
                <div className="grid grid-cols-1 gap-3 p-4 md:grid-cols-3">
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
                        Refresh
                    </button>
                </div>
            </div>

            {notice ? (
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {notice}
                </div>
            ) : null}

            {error ? (
                <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {error}
                </div>
            ) : null}

            {showForm ? (
                <div className="panel">
                    <div className="panel-body">
                        <h2 className="mb-4 text-lg font-semibold">Create New Assignment</h2>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="mb-1 block text-sm font-medium text-slate-700">Asset</label>
                                <select
                                    value={form.item_id}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            item_id: e.target.value,
                                            quantity: 1,
                                        })
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
                                    <div>
                                        Available quantity: <strong>{selectedItem.quantity}</strong>
                                    </div>
                                    <div className="mt-1">
                                        Asset Tag: <strong>{selectedItem.asset_tag || '-'}</strong>
                                    </div>
                                    <div className="mt-1">
                                        SKU: <strong>{selectedItem.sku || '-'}</strong>
                                    </div>
                                </div>
                            ) : null}

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Receiver Name</label>
                                    <input
                                        type="text"
                                        value={form.receiver_name}
                                        onChange={(e) => setForm({ ...form, receiver_name: e.target.value })}
                                        className="input-shell w-full"
                                        placeholder="Enter receiver name"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">Receiving Department</label>
                                    <select
                                        value={form.department_id}
                                        onChange={(e) => setForm({ ...form, department_id: e.target.value })}
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
                                        max={selectedItem?.quantity || 1}
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
                            </div>

                            <div className="flex gap-3">
                                <button
                                    type="submit"
                                    disabled={!selectedItem || !form.department_id || form.quantity > Number(selectedItem.quantity || 0)}
                                    className="btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Create Assignment
                                </button>

                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">
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
                                            {assignment.receiver_name || assignment.user?.name || '-'}
                                        </td>
                                        <td className="px-6 py-4 text-slate-700">
                                            {assignment.assigned_department?.name || '-'}
                                        </td>
                                        <td className="px-6 py-4 text-slate-700">{assignment.quantity || 0}</td>
                                        <td className="px-6 py-4 text-slate-700">{formatDate(assignment.assigned_at)}</td>
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
                                                    className="text-amber-600 hover:underline"
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
            </div>
        </div>
    );
}