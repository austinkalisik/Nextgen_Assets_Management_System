import React, { useEffect, useMemo, useState } from 'react';
import apiClient from '../api/client';

function formatDateTime(value) {
    if (!value) {
        return 'N/A';
    }

    return new Date(value).toLocaleString();
}

export default function ActivityLogsPage() {
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [action, setAction] = useState('');
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);

    const actionOptions = useMemo(
        () => ['created', 'updated', 'deleted', 'assigned', 'returned', 'stock_in', 'stock_out'],
        []
    );

    async function loadLogs(nextPage = page) {
        try {
            setLoading(true);

            const response = await apiClient.get('/activity-logs', {
                params: {
                    per_page: 20,
                    page: nextPage,
                    search: search || undefined,
                    action: action || undefined,
                },
            });

            setLogs(response.data.data || []);
            setPage(response.data.current_page || 1);
            setLastPage(response.data.last_page || 1);
        } catch (error) {
            console.error('Failed to load activity logs', error);
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        void loadLogs(1);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search, action]);

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">Activity Logs</h1>
                    <p className="mt-1 text-sm text-slate-500">
                        Audit trail of asset actions, stock operations, and assignment changes.
                    </p>
                </div>
            </div>

            <div className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-3">
                <input
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Search by action, item, user, or notes"
                    className="rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none ring-blue-500 transition focus:ring"
                />

                <select
                    value={action}
                    onChange={(event) => setAction(event.target.value)}
                    className="rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none ring-blue-500 transition focus:ring"
                >
                    <option value="">All actions</option>
                    {actionOptions.map((option) => (
                        <option key={option} value={option}>
                            {option.replace('_', ' ')}
                        </option>
                    ))}
                </select>

                <button type="button" onClick={() => void loadLogs(1)} className="btn-secondary">
                    Refresh
                </button>
            </div>

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                {loading ? (
                    <div className="px-6 py-10 text-center text-slate-500">Loading activity logs...</div>
                ) : logs.length === 0 ? (
                    <div className="px-6 py-10 text-center text-slate-500">No activity logs found.</div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-semibold text-slate-700">Date</th>
                                    <th className="px-4 py-3 text-left font-semibold text-slate-700">Action</th>
                                    <th className="px-4 py-3 text-left font-semibold text-slate-700">Item</th>
                                    <th className="px-4 py-3 text-left font-semibold text-slate-700">User</th>
                                    <th className="px-4 py-3 text-left font-semibold text-slate-700">Notes</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {logs.map((log) => (
                                    <tr key={log.id} className="hover:bg-slate-50/70">
                                        <td className="px-4 py-3 text-slate-600">{formatDateTime(log.created_at)}</td>
                                        <td className="px-4 py-3 font-medium text-slate-900">{log.action}</td>
                                        <td className="px-4 py-3 text-slate-700">{log.item?.name || 'N/A'}</td>
                                        <td className="px-4 py-3 text-slate-700">{log.user?.name || 'System'}</td>
                                        <td className="px-4 py-3 text-slate-600">{log.notes || '—'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            <div className="flex items-center justify-between">
                <button
                    type="button"
                    disabled={page <= 1 || loading}
                    onClick={() => void loadLogs(page - 1)}
                    className="btn-secondary disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Previous
                </button>

                <span className="text-sm text-slate-600">
                    Page {page} of {lastPage}
                </span>

                <button
                    type="button"
                    disabled={page >= lastPage || loading}
                    onClick={() => void loadLogs(page + 1)}
                    className="btn-secondary disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Next
                </button>
            </div>
        </div>
    );
}
