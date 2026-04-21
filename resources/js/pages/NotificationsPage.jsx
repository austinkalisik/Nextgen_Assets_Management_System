import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import apiClient from '../api/client';

function formatDate(value) {
    if (!value) {
        return 'N/A';
    }

    return new Date(value).toLocaleString();
}

export default function NotificationsPage() {
    const navigate = useNavigate();
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [busyId, setBusyId] = useState(null);
    const unreadCount = useMemo(() => items.filter((item) => !item.is_read).length, [items]);

    async function loadNotifications() {
        try {
            setLoading(true);
            const response = await apiClient.get('/notifications?per_page=50');
            setItems(response.data.data || []);
        } catch (error) {
            console.error('Failed to load notifications', error);
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        void loadNotifications();
    }, []);

    async function handleOpen(notification) {
        try {
            setBusyId(notification.id);

            if (!notification.is_read) {
                await apiClient.patch(`/notifications/${notification.id}/read`);
            }

            navigate(notification.url || '/notifications');
        } catch (error) {
            console.error('Failed to open notification', error);
        } finally {
            setBusyId(null);
        }
    }

    async function handleToggleRead(notification) {
        try {
            setBusyId(notification.id);

            if (notification.is_read) {
                await apiClient.patch(`/notifications/${notification.id}/unread`);
            } else {
                await apiClient.patch(`/notifications/${notification.id}/read`);
            }

            setItems((prev) =>
                prev.map((item) =>
                    item.id === notification.id
                        ? {
                              ...item,
                              is_read: !notification.is_read,
                              read_at: notification.is_read ? null : new Date().toISOString(),
                          }
                        : item
                )
            );
        } catch (error) {
            console.error('Failed to update notification', error);
        } finally {
            setBusyId(null);
        }
    }

    async function handleMarkAllRead() {
        try {
            await apiClient.patch('/notifications/read-all');

            setItems((prev) =>
                prev.map((item) => ({
                    ...item,
                    is_read: true,
                    read_at: item.read_at || new Date().toISOString(),
                }))
            );
        } catch (error) {
            console.error('Failed to mark all notifications as read', error);
        }
    }

    if (loading) {
        return <div className="text-slate-500">Loading notifications...</div>;
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">Notifications</h1>
                    <p className="mt-1 text-sm text-slate-500">
                        Open alerts, mark items as read or unread, and keep track of system activity.
                    </p>
                </div>

                <div className="flex flex-wrap items-center gap-3">
                    <div className="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-600">
                        Unread: <span className="font-semibold text-slate-900">{unreadCount}</span>
                    </div>

                    <button type="button" onClick={handleMarkAllRead} className="btn-secondary">
                        Mark All Read
                    </button>
                </div>
            </div>

            <div className="space-y-4">
                {items.length > 0 ? (
                    items.map((notification) => (
                        <div
                            key={notification.id}
                            className={`rounded-2xl border p-5 shadow-sm transition ${
                                notification.is_read
                                    ? 'border-slate-200 bg-white'
                                    : 'border-blue-200 bg-blue-50/50'
                            }`}
                        >
                            <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div className="min-w-0">
                                    <div className="flex items-center gap-3">
                                        <h2 className="text-lg font-semibold text-slate-900">{notification.title}</h2>
                                        {!notification.is_read ? (
                                            <span className="inline-flex rounded-full bg-blue-600 px-2.5 py-1 text-xs font-semibold text-white">
                                                New
                                            </span>
                                        ) : null}
                                    </div>

                                    <p className="mt-2 text-sm text-slate-600">{notification.message}</p>

                                    <div className="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                        <span>{notification.type || 'general'}</span>
                                        <span>•</span>
                                        <span>{formatDate(notification.created_at)}</span>
                                    </div>
                                </div>

                                <div className="flex flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => handleOpen(notification)}
                                        disabled={busyId === notification.id}
                                        className="btn-primary disabled:opacity-60"
                                    >
                                        {busyId === notification.id ? 'Opening...' : 'Open'}
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => handleToggleRead(notification)}
                                        disabled={busyId === notification.id}
                                        className="btn-secondary disabled:opacity-60"
                                    >
                                        {notification.is_read ? 'Mark Unread' : 'Mark Read'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center text-slate-500 shadow-sm">
                        No notifications available.
                    </div>
                )}
            </div>
        </div>
    );
}