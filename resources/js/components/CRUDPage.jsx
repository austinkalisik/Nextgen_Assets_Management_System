import React, { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { invalidateApiCache, useApi } from '../hooks/useApi';
import apiClient from '../api/client';
import { downloadCsv } from '../utils/csv';

function formatValue(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
    }

    return String(value);
}

export function CRUDPage({
    title,
    endpoint,
    fields,
    searchPlaceholder = 'Search...',
    createLabel,
    csvConfig = null,
}) {
    const [searchParams, setSearchParams] = useSearchParams();

    const [items, setItems] = useState([]);
    const [error, setError] = useState('');
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [saving, setSaving] = useState(false);
    const [deletingId, setDeletingId] = useState(null);
    const [form, setForm] = useState({});
    const [meta, setMeta] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10,
    });

    const searchInputFromUrl = searchParams.get('search') ?? '';
    const pageFromUrl = Number.parseInt(searchParams.get('page') ?? '1', 10);

    const [searchInput, setSearchInput] = useState(searchInputFromUrl);

    useEffect(() => {
        const initialForm = {};
        fields.forEach((field) => {
            initialForm[field.name] = field.defaultValue ?? '';
        });
        setForm(initialForm);
    }, [fields]);

    useEffect(() => {
        setSearchInput(searchInputFromUrl);
    }, [searchInputFromUrl]);

    const requestOptions = useMemo(
        () => ({
            params: {
                search: searchInputFromUrl || undefined,
                page: pageFromUrl > 0 ? pageFromUrl : 1,
                per_page: 10,
            },
        }),
        [searchInputFromUrl, pageFromUrl]
    );

    const {
        data: payload,
        loading,
        error: loadError,
        refetch,
    } = useApi(`/${endpoint}`, requestOptions, { ttl: 180000 });

    useEffect(() => {
        if (!payload) {
            return;
        }

        setItems(payload.data || []);
        setMeta({
            current_page: payload.current_page || 1,
            last_page: payload.last_page || 1,
            total: payload.total || 0,
            per_page: payload.per_page || 10,
        });
        setError('');
    }, [payload]);

    useEffect(() => {
        if (loadError) {
            setError(loadError);
        }
    }, [loadError]);

    async function refreshList() {
        invalidateApiCache(`/${endpoint}`);
        await refetch();
    }

    async function handleSubmit(event) {
        event.preventDefault();

        try {
            setSaving(true);
            setError('');

            if (editingId) {
                await apiClient.put(`/${endpoint}/${editingId}`, form);
            } else {
                await apiClient.post(`/${endpoint}`, form);
            }

            resetForm();
            await refreshList();
        } catch (err) {
            setError(err?.response?.data?.message || 'Failed to save');
        } finally {
            setSaving(false);
        }
    }

    async function handleDelete(id) {
        if (!window.confirm('Are you sure?')) {
            return;
        }

        try {
            setDeletingId(id);
            setError('');

            await apiClient.delete(`/${endpoint}/${id}`);
            await refreshList();
        } catch (err) {
            setError(err?.response?.data?.message || 'Failed to delete');
        } finally {
            setDeletingId(null);
        }
    }

    function handleEdit(item) {
        const nextForm = {};
        fields.forEach((field) => {
            nextForm[field.name] = item[field.name] ?? field.defaultValue ?? '';
        });

        setForm(nextForm);
        setEditingId(item.id);
        setShowForm(true);
    }

    function resetForm() {
        const nextForm = {};
        fields.forEach((field) => {
            nextForm[field.name] = field.defaultValue ?? '';
        });

        setForm(nextForm);
        setEditingId(null);
        setShowForm(false);
    }

    function handleSearchSubmit(event) {
        event.preventDefault();

        const next = new URLSearchParams(searchParams);

        if (searchInput.trim()) {
            next.set('search', searchInput.trim());
        } else {
            next.delete('search');
        }

        next.set('page', '1');
        setSearchParams(next);
    }

    function goToPage(page) {
        const next = new URLSearchParams(searchParams);
        next.set('page', String(page));
        setSearchParams(next);
    }

    function handleExportCsv() {
        if (!items.length) {
            return;
        }

        const rows = csvConfig?.mapRow
            ? items.map((item) => csvConfig.mapRow(item))
            : items;

        downloadCsv(
            csvConfig?.filename || `${endpoint}.csv`,
            rows,
            csvConfig?.headers || null
        );
    }

    const headingActionLabel = useMemo(() => {
        if (createLabel) {
            return createLabel;
        }

        return `Add ${title}`;
    }, [createLabel, title]);

    if (loading && !payload) {
        return <div className="text-slate-500">Loading {endpoint}...</div>;
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">{title}</h1>
                    <p className="mt-1 text-sm text-slate-500">Manage {endpoint}</p>
                </div>

                <div className="flex flex-col gap-3 sm:flex-row">
                    <form onSubmit={handleSearchSubmit} className="flex items-center gap-2">
                        <input
                            type="text"
                            value={searchInput}
                            onChange={(event) => setSearchInput(event.target.value)}
                            placeholder={searchPlaceholder}
                            className="w-72 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        />
                        <button
                            type="submit"
                            className="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Find
                        </button>
                    </form>

                    {csvConfig ? (
                        <button
                            type="button"
                            onClick={handleExportCsv}
                            className="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Export CSV
                        </button>
                    ) : null}

                    <button
                        type="button"
                        onClick={() => setShowForm((prev) => !prev)}
                        className="rounded-xl bg-blue-600 px-4 py-2.5 font-medium text-white hover:bg-blue-700"
                    >
                        {showForm ? 'Cancel' : headingActionLabel}
                    </button>
                </div>
            </div>

            {error ? (
                <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {error}
                </div>
            ) : null}

            {showForm ? (
                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold">
                        {editingId ? `Edit ${title}` : headingActionLabel}
                    </h2>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {fields.map((field) => (
                                <div key={field.name} className={field.fullWidth ? 'md:col-span-2' : ''}>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        {field.label || field.name}
                                    </label>

                                    {field.type === 'textarea' ? (
                                        <textarea
                                            value={form[field.name] || ''}
                                            onChange={(event) =>
                                                setForm((prev) => ({ ...prev, [field.name]: event.target.value }))
                                            }
                                            className="w-full rounded-xl border border-slate-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            rows={field.rows || 3}
                                            required={field.required !== false}
                                        />
                                    ) : (
                                        <input
                                            type={field.type || 'text'}
                                            value={form[field.name] || ''}
                                            onChange={(event) =>
                                                setForm((prev) => ({ ...prev, [field.name]: event.target.value }))
                                            }
                                            className="w-full rounded-xl border border-slate-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required={field.required !== false}
                                        />
                                    )}
                                </div>
                            ))}
                        </div>

                        <div className="flex gap-3">
                            <button
                                type="submit"
                                disabled={saving}
                                className="rounded-xl bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                            >
                                {saving ? 'Saving...' : editingId ? 'Update' : 'Create'}
                            </button>

                            <button
                                type="button"
                                onClick={resetForm}
                                className="rounded-xl bg-slate-200 px-4 py-2 font-medium text-slate-700 hover:bg-slate-300"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            ) : null}

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="border-b border-slate-200 bg-slate-50 text-slate-600">
                            <tr>
                                {fields.map((field) => (
                                    <th key={field.name} className="px-6 py-4 text-left font-semibold">
                                        {field.label || field.name}
                                    </th>
                                ))}
                                <th className="px-6 py-4 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {items.length > 0 ? (
                                items.map((item) => (
                                    <tr key={item.id} className="hover:bg-slate-50">
                                        {fields.map((field) => (
                                            <td key={field.name} className="whitespace-pre-line px-6 py-4 text-slate-900">
                                                {field.render
                                                    ? field.render(item[field.name], item)
                                                    : formatValue(item[field.name])}
                                            </td>
                                        ))}
                                        <td className="space-x-2 px-6 py-4">
                                            <button
                                                type="button"
                                                onClick={() => handleEdit(item)}
                                                className="text-blue-600 hover:underline"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => handleDelete(item.id)}
                                                disabled={deletingId === item.id}
                                                className="text-red-600 hover:underline disabled:opacity-50"
                                            >
                                                {deletingId === item.id ? 'Deleting...' : 'Delete'}
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={fields.length + 1} className="px-6 py-10 text-center text-slate-500">
                                        No items found.
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
                            className="rounded-lg border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>

                        <button
                            type="button"
                            disabled={meta.current_page >= meta.last_page}
                            onClick={() => goToPage(meta.current_page + 1)}
                            className="rounded-lg border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default CRUDPage;

