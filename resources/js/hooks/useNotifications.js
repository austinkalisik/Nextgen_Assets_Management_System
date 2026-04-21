import { useCallback, useEffect, useState } from 'react';
import apiClient from '../api/client';

let notificationsCache = null;
let unreadCountCache = null;
let notificationsPromise = null;
const CACHE_TTL = 5 * 60 * 1000;

function hasFreshCache() {
    if (!notificationsCache || unreadCountCache === null) {
        return false;
    }

    return Date.now() - notificationsCache.timestamp < CACHE_TTL;
}

async function fetchNotifications(force = false) {
    if (!force && hasFreshCache()) {
        return {
            notifications: notificationsCache.data,
            unreadCount: unreadCountCache,
        };
    }

    if (!force && notificationsPromise) {
        return notificationsPromise;
    }

    notificationsPromise = Promise.all([
        apiClient.get('/notifications', { params: { per_page: 8 } }),
        apiClient.get('/notifications/unread-count'),
    ])
        .then(([notificationsResponse, unreadResponse]) => {
            const notifications = notificationsResponse.data.data || [];
            const unreadCount = unreadResponse.data.count || 0;

            notificationsCache = {
                data: notifications,
                timestamp: Date.now(),
            };
            unreadCountCache = unreadCount;

            return {
                notifications,
                unreadCount,
            };
        })
        .finally(() => {
            notificationsPromise = null;
        });

    return notificationsPromise;
}

function updateNotificationCache(notifications, unreadCount) {
    notificationsCache = {
        data: notifications,
        timestamp: Date.now(),
    };
    unreadCountCache = unreadCount;
}

export function invalidateNotificationsCache() {
    notificationsCache = null;
    unreadCountCache = null;
    notificationsPromise = null;
}

export default function useNotifications() {
    const [notifications, setNotifications] = useState(() => notificationsCache?.data || []);
    const [unreadCount, setUnreadCount] = useState(() => unreadCountCache || 0);
    const [loading, setLoading] = useState(() => !hasFreshCache());

    const loadNotifications = useCallback(async (force = false) => {
        try {
            setLoading(!hasFreshCache() || force);

            const result = await fetchNotifications(force);

            setNotifications(result.notifications);
            setUnreadCount(result.unreadCount);
        } catch (error) {
            console.error('Failed to load notifications', error);
        } finally {
            setLoading(false);
        }
    }, []);

    const markRead = useCallback(
        async (id) => {
            await apiClient.patch(`/notifications/${id}/read`);

            setNotifications((prev) => {
                const next = prev.map((item) =>
                    item.id === id
                        ? { ...item, is_read: true, read_at: item.read_at || new Date().toISOString() }
                        : item
                );

                const nextUnreadCount = Math.max(0, unreadCountCache !== null ? unreadCountCache - 1 : 0);
                updateNotificationCache(next, nextUnreadCount);
                setUnreadCount(nextUnreadCount);

                return next;
            });
        },
        []
    );

    const markUnread = useCallback(async (id) => {
        await apiClient.patch(`/notifications/${id}/unread`);

        setNotifications((prev) => {
            const next = prev.map((item) =>
                item.id === id ? { ...item, is_read: false, read_at: null } : item
            );

            const nextUnreadCount = (unreadCountCache || 0) + 1;
            updateNotificationCache(next, nextUnreadCount);
            setUnreadCount(nextUnreadCount);

            return next;
        });
    }, []);

    const markAllRead = useCallback(async () => {
        await apiClient.patch('/notifications/read-all');

        setNotifications((prev) => {
            const next = prev.map((item) => ({
                ...item,
                is_read: true,
                read_at: item.read_at || new Date().toISOString(),
            }));

            updateNotificationCache(next, 0);
            return next;
        });

        setUnreadCount(0);
    }, []);

    useEffect(() => {
        void loadNotifications(false);
    }, [loadNotifications]);

    return {
        notifications,
        unreadCount,
        loading,
        loadNotifications,
        markRead,
        markUnread,
        markAllRead,
    };
}

