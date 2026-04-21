import React, { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import apiClient from '../api/client';
import { useAuth } from '../context/AuthContext';
import { useSettings } from '../context/SettingsContext';
import { invalidateApiCache, useApi } from '../hooks/useApi';

function defaultForm() {
    return {
        name: '',
        email: '',
        role: 'staff',
        password: '',
        password_confirmation: '',
    };
}

export default function UsersPage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const { user: currentUser, impersonate, stopImpersonation, refreshUser } = useAuth();
    const { settings } = useSettings();

    const canSwitchUser = String(settings.allow_user_impersonation ?? '1') === '1';

    const filters = useMemo(
        () => ({
            search: searchParams.get('search') ?? '',
            page: Number.parseInt(searchParams.get('page') ?? '1', 10),
            per_page: 10,
        }),
        [searchParams]
    );

    const { data, loading, error, refetch } = useApi('/users', { params: filters }, { ttl: 180000 });
    const users = data?.data || [];
    const meta = {
        current_page: data?.current_page || 1,
        last_page: data?.last_page || 1,
        total: data?.total || 0,
    };

    const [success, setSuccess] = useState('');
    const [showForm, setShowForm] = useState(searchParams.get('create') === '1');
    const [editingId, setEditingId] = useState(null);
    const [searchInput, setSearchInput] = useState(filters.search);
    const [form, setForm] = useState(defaultForm());

    useEffect(() => {
        setSearchInput(filters.search);
    }, [filters.search]);

    useEffect(() => {
        if (searchParams.get('create') === '1' && !editingId) {
            setShowForm(true);
        }
    }, [searchParams, editingId]);

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

    function resetForm() {
        setForm(defaultForm());
        setEditingId(null);
        setShowForm(false);
    }

    function handleEdit(user) {
        setForm({
            name: user.name || '',
            email: user.email || '',
            role: user.role || 'staff',
            password: '',
            password_confirmation: '',
        });

        setEditingId(user.id);
        setShowForm(true);
        setSuccess('');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function toggleForm() {
        setSuccess('');

        if (showForm) {
            resetForm();
            return;
        }

        setForm(defaultForm());
        setEditingId(null);
        setShowForm(true);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function refreshUsers() {
        invalidateApiCache('/users');
        await refetch();
    }

    async function handleSubmit(event) {
        event.preventDefault();

        try {
            setSuccess('');

            const payload = {
                name: form.name.trim(),
                email: form.email.trim(),
                role: form.role,
            };

            if (form.password) {
                payload.password = form.password;
                payload.password_confirmation = form.password_confirmation;
            }

            if (editingId) {
                await apiClient.put(`/users/${editingId}`, payload);
                setSuccess('User updated successfully.');
            } else {
                payload.password = form.password;
                payload.password_confirmation = form.password_confirmation;
                await apiClient.post('/users', payload);
                setSuccess('User created successfully.');
            }

            resetForm();
            await refreshUsers();
            await refreshUser();
        } catch (err) {
            console.error(err);
        }
    }

    async function handleDelete(id) {
        if (!window.confirm('Are you sure you want to delete this user?')) {
            return;
        }

        try {
            setSuccess('');
            await apiClient.delete(`/users/${id}`);
            setSuccess('User deleted successfully.');
            await refreshUsers();
        } catch (err) {
            console.error(err);
        }
    }

    async function handleImpersonate(userId) {
        try {
            setSuccess('');
            await impersonate(userId);
            setSuccess('User switched successfully.');
            await refreshUsers();
        } catch (err) {
            console.error(err);
        }
    }

    async function handleStopImpersonation() {
        try {
            setSuccess('');
            await stopImpersonation();
            setSuccess('Returned to admin successfully.');
            await refreshUsers();
            await refreshUser();
        } catch (err) {
            console.error(err);
        }
    }

    if (loading && !data) {
        return <div className="text-slate-500">Loading users...</div>;
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">Users</h1>
                    <p className="mt-1 text-sm text-slate-500">Manage system users, roles, and switch-user access.</p>
                </div>

                <div className="flex flex-col gap-3 sm:flex-row">
                    <form onSubmit={handleSearchSubmit} className="flex items-center gap-2">
                        <input
                            type="text"
                            value={searchInput}
                            onChange={(e) => setSearchInput(e.target.value)}
                            placeholder="Search users..."
                            className="input-shell w-72"
                        />
                        <button type="submit" className="btn-secondary">
                            Find
                        </button>
                    </form>

                    <button type="button" onClick={toggleForm} className="btn-primary">
                        {showForm ? 'Cancel' : 'Add User'}
                    </button>

                    {currentUser?.is_impersonating ? (
                        <button type="button" onClick={handleStopImpersonation} className="btn-secondary">
                            Return to Admin
                        </button>
                    ) : null}
                </div>
            </div>

            {success ? (
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {success}
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
                        <h2 className="mb-4 text-lg font-semibold">{editingId ? 'Edit User' : 'Add User'}</h2>

                        <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                <label className="mb-1 block text-sm font-medium text-slate-700">Email</label>
                                <input
                                    type="email"
                                    value={form.email}
                                    onChange={(e) => setForm({ ...form, email: e.target.value })}
                                    className="input-shell w-full"
                                    required
                                />
                            </div>

                            <div>
                                <label className="mb-1 block text-sm font-medium text-slate-700">Role</label>
                                <select
                                    value={form.role}
                                    onChange={(e) => setForm({ ...form, role: e.target.value })}
                                    className="input-shell w-full"
                                >
                                    <option value="staff">Staff</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <div>
                                <label className="mb-1 block text-sm font-medium text-slate-700">
                                    Password {editingId ? '(leave blank to keep current)' : ''}
                                </label>
                                <input
                                    type="password"
                                    value={form.password}
                                    onChange={(e) => setForm({ ...form, password: e.target.value })}
                                    className="input-shell w-full"
                                    required={!editingId}
                                />
                            </div>

                            <div>
                                <label className="mb-1 block text-sm font-medium text-slate-700">Confirm Password</label>
                                <input
                                    type="password"
                                    value={form.password_confirmation}
                                    onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
                                    className="input-shell w-full"
                                    required={!editingId || Boolean(form.password)}
                                />
                            </div>

                            <div className="md:col-span-2 flex gap-3 pt-2">
                                <button type="submit" className="btn-primary">
                                    {editingId ? 'Update User' : 'Create User'}
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
                                <th className="px-6 py-4 text-left font-semibold">Email</th>
                                <th className="px-6 py-4 text-left font-semibold">Role</th>
                                <th className="px-6 py-4 text-left font-semibold">Active Assignments</th>
                                <th className="px-6 py-4 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {users.length > 0 ? (
                                users.map((user) => (
                                    <tr key={user.id} className="table-row">
                                        <td className="px-6 py-4 font-medium text-slate-900">{user.name}</td>
                                        <td className="px-6 py-4 text-slate-700">{user.email}</td>
                                        <td className="px-6 py-4 text-slate-700">{user.role}</td>
                                        <td className="px-6 py-4 text-slate-700">{user.active_assignments_count ?? 0}</td>
                                        <td className="space-x-3 px-6 py-4">
                                            <button
                                                type="button"
                                                onClick={() => handleEdit(user)}
                                                className="text-blue-600 hover:underline"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                onClick={() => handleDelete(user.id)}
                                                className="text-red-600 hover:underline"
                                            >
                                                Delete
                                            </button>

                                            {canSwitchUser &&
                                            currentUser?.role === 'admin' &&
                                            !currentUser?.is_impersonating &&
                                            Number(currentUser?.id) !== Number(user.id) ? (
                                                <button
                                                    type="button"
                                                    onClick={() => handleImpersonate(user.id)}
                                                    className="text-amber-600 hover:underline"
                                                >
                                                    Switch User
                                                </button>
                                            ) : null}
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="5" className="px-6 py-10 text-center text-slate-500">
                                        No users found.
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

