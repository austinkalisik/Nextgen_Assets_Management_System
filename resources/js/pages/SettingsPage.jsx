import React, { useEffect, useMemo, useState } from 'react';
import apiClient from '../api/client';
import { useSettings } from '../context/SettingsContext';
import { useAuth } from '../context/AuthContext';

const settingGroups = [
    {
        title: 'Branding',
        keys: ['system_name', 'system_tagline', 'company_name', 'company_website', 'support_email'],
    },
    {
        title: 'Operations',
        keys: ['low_stock_threshold', 'assignment_overdue_days', 'items_per_page'],
    },
    {
        title: 'Automation',
        keys: ['email_notifications_enabled', 'maintenance_alerts_enabled', 'allow_user_impersonation'],
    },
];

const labels = {
    system_name: 'System Name',
    system_tagline: 'System Tagline',
    company_name: 'Company Name',
    company_website: 'Company Website',
    support_email: 'Support Email',
    low_stock_threshold: 'Low Stock Threshold',
    assignment_overdue_days: 'Assignment Overdue Days',
    items_per_page: 'Items Per Page',
    email_notifications_enabled: 'Email Notifications Enabled',
    maintenance_alerts_enabled: 'Maintenance Alerts Enabled',
    allow_user_impersonation: 'Allow Switch User',
};

function isToggle(key) {
    return ['email_notifications_enabled', 'maintenance_alerts_enabled', 'allow_user_impersonation'].includes(key);
}

function initials(name) {
    if (!name) {
        return 'SA';
    }

    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
}

function AvatarPreview({ user, previewUrl }) {
    const imageUrl = previewUrl || user?.profile_photo_url || '';

    if (imageUrl) {
        return (
            <img
                src={imageUrl}
                alt={user?.name || 'Profile'}
                className="h-24 w-24 rounded-2xl object-cover ring-4 ring-slate-100"
            />
        );
    }

    return (
        <div className="flex h-24 w-24 items-center justify-center rounded-2xl bg-slate-900 text-xl font-bold text-white ring-4 ring-slate-100">
            {initials(user?.name)}
        </div>
    );
}

function LogoPreview({ settings, previewUrl }) {
    const imageUrl = previewUrl || settings?.system_logo_url || '';

    if (imageUrl) {
        return (
            <img
                src={imageUrl}
                alt={settings?.system_name || 'System Logo'}
                className="h-24 w-24 rounded-2xl bg-white object-contain p-2 ring-4 ring-slate-100"
            />
        );
    }

    return (
        <div className="flex h-24 w-24 items-center justify-center rounded-2xl bg-slate-900 text-sm font-bold text-white ring-4 ring-slate-100">
            LOGO
        </div>
    );
}

export default function SettingsPage() {
    const { settings, refreshSettings } = useSettings();
    const { user, refreshUser } = useAuth();

    const [form, setForm] = useState(settings);
    const [saving, setSaving] = useState(false);

    const [photoSaving, setPhotoSaving] = useState(false);
    const [photoFile, setPhotoFile] = useState(null);
    const [photoPreview, setPhotoPreview] = useState('');
    const [photoError, setPhotoError] = useState('');
    const [photoSuccess, setPhotoSuccess] = useState('');

    const [logoSaving, setLogoSaving] = useState(false);
    const [logoFile, setLogoFile] = useState(null);
    const [logoPreview, setLogoPreview] = useState('');
    const [logoError, setLogoError] = useState('');
    const [logoSuccess, setLogoSuccess] = useState('');

    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    useEffect(() => {
        setForm(settings);
    }, [settings]);

    useEffect(() => {
        return () => {
            if (photoPreview) {
                URL.revokeObjectURL(photoPreview);
            }

            if (logoPreview) {
                URL.revokeObjectURL(logoPreview);
            }
        };
    }, [photoPreview, logoPreview]);

    const groups = useMemo(
        () =>
            settingGroups.map((group) => ({
                ...group,
                entries: group.keys.map((key) => ({
                    key,
                    label: labels[key] || key,
                    value: form[key] ?? '',
                })),
            })),
        [form]
    );

    function handlePhotoChange(event) {
        const file = event.target.files?.[0] || null;

        if (photoPreview) {
            URL.revokeObjectURL(photoPreview);
        }

        setPhotoFile(file);
        setPhotoError('');
        setPhotoSuccess('');
        setPhotoPreview(file ? URL.createObjectURL(file) : '');
    }

    function handleLogoChange(event) {
        const file = event.target.files?.[0] || null;

        if (logoPreview) {
            URL.revokeObjectURL(logoPreview);
        }

        setLogoFile(file);
        setLogoError('');
        setLogoSuccess('');
        setLogoPreview(file ? URL.createObjectURL(file) : '');
    }

    async function handleSaveAll() {
        try {
            setSaving(true);
            setError('');
            setSuccess('');

            await Promise.all(
                Object.entries(form)
                    .filter(([key]) => key !== 'system_logo' && key !== 'system_logo_url')
                    .map(([key, value]) =>
                        apiClient.put(`/settings/${key}`, {
                            value,
                        })
                    )
            );

            await refreshSettings();
            setSuccess('Settings saved successfully.');
        } catch (err) {
            setError(err?.response?.data?.message || 'Failed to save settings');
        } finally {
            setSaving(false);
        }
    }

    async function handleUploadPhoto() {
        if (!photoFile) {
            setPhotoError('Please choose an image first.');
            return;
        }

        try {
            setPhotoSaving(true);
            setPhotoError('');
            setPhotoSuccess('');

            const payload = new FormData();
            payload.append('name', user?.name || '');
            payload.append('email', user?.email || '');
            payload.append('profile_photo', photoFile);
            payload.append('_method', 'PUT');

            await apiClient.post('/profile', payload, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            await refreshUser();

            if (photoPreview) {
                URL.revokeObjectURL(photoPreview);
            }

            setPhotoFile(null);
            setPhotoPreview('');
            setPhotoSuccess('Profile image updated successfully.');
        } catch (err) {
            setPhotoError(err?.response?.data?.message || 'Failed to upload profile image');
        } finally {
            setPhotoSaving(false);
        }
    }

    async function handleRemovePhoto() {
        try {
            setPhotoSaving(true);
            setPhotoError('');
            setPhotoSuccess('');

            await apiClient.delete('/profile/photo');
            await refreshUser();

            if (photoPreview) {
                URL.revokeObjectURL(photoPreview);
            }

            setPhotoFile(null);
            setPhotoPreview('');
            setPhotoSuccess('Profile image removed successfully.');
        } catch (err) {
            setPhotoError(err?.response?.data?.message || 'Failed to remove profile image');
        } finally {
            setPhotoSaving(false);
        }
    }

    async function handleUploadLogo() {
        if (!logoFile) {
            setLogoError('Please choose a logo first.');
            return;
        }

        try {
            setLogoSaving(true);
            setLogoError('');
            setLogoSuccess('');

            const payload = new FormData();
            payload.append('system_logo', logoFile);

            await apiClient.post('/settings/branding/logo', payload, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            await refreshSettings();

            if (logoPreview) {
                URL.revokeObjectURL(logoPreview);
            }

            setLogoFile(null);
            setLogoPreview('');
            setLogoSuccess('System logo updated successfully.');
        } catch (err) {
            setLogoError(err?.response?.data?.message || 'Failed to upload system logo');
        } finally {
            setLogoSaving(false);
        }
    }

    async function handleRemoveLogo() {
        try {
            setLogoSaving(true);
            setLogoError('');
            setLogoSuccess('');

            await apiClient.delete('/settings/branding/logo');
            await refreshSettings();

            if (logoPreview) {
                URL.revokeObjectURL(logoPreview);
            }

            setLogoFile(null);
            setLogoPreview('');
            setLogoSuccess('System logo removed successfully.');
        } catch (err) {
            setLogoError(err?.response?.data?.message || 'Failed to remove system logo');
        } finally {
            setLogoSaving(false);
        }
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">Settings</h1>
                    <p className="mt-1 text-sm text-slate-500">Configure branding, operations, automation, profile image, and system logo.</p>
                </div>

                <button type="button" onClick={handleSaveAll} disabled={saving} className="btn-primary disabled:opacity-60">
                    {saving ? 'Saving...' : 'Save All Changes'}
                </button>
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

            <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div className="panel">
                    <div className="panel-body">
                        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_340px]">
                            <div>
                                <h2 className="text-lg font-semibold text-slate-900">System Logo</h2>
                                <p className="mt-1 text-sm text-slate-500">Update the logo shown in the sidebar branding area.</p>
                            </div>

                            <div className="space-y-4">
                                <div className="flex items-center gap-4">
                                    <LogoPreview settings={settings} previewUrl={logoPreview} />
                                    <div className="min-w-0">
                                        <p className="text-sm font-semibold text-slate-900">{settings?.system_name || 'NextGen Assets'}</p>
                                        <p className="text-sm text-slate-500">{settings?.company_website || 'https://nextgenpng.net/'}</p>
                                    </div>
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-700">Upload Logo</label>
                                    <input
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.webp,.svg"
                                        onChange={handleLogoChange}
                                        className="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700"
                                    />
                                    <p className="mt-2 text-xs text-slate-400">Accepted: JPG, PNG, WEBP, SVG. Max size: 2MB.</p>
                                </div>

                                {logoSuccess ? (
                                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                        {logoSuccess}
                                    </div>
                                ) : null}

                                {logoError ? (
                                    <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {logoError}
                                    </div>
                                ) : null}

                                <div className="flex flex-wrap gap-3">
                                    <button type="button" onClick={handleUploadLogo} disabled={logoSaving} className="btn-primary disabled:opacity-60">
                                        {logoSaving ? 'Updating...' : 'Update System Logo'}
                                    </button>

                                    <button
                                        type="button"
                                        onClick={handleRemoveLogo}
                                        disabled={logoSaving || !settings?.system_logo_url}
                                        className="btn-secondary disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        Remove Logo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="panel">
                    <div className="panel-body">
                        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_340px]">
                            <div>
                                <h2 className="text-lg font-semibold text-slate-900">Profile Image</h2>
                                <p className="mt-1 text-sm text-slate-500">Update the avatar shown in the sidebar and header.</p>
                            </div>

                            <div className="space-y-4">
                                <div className="flex items-center gap-4">
                                    <AvatarPreview user={user} previewUrl={photoPreview} />
                                    <div className="min-w-0">
                                        <p className="text-sm font-semibold text-slate-900">{user?.name || 'System Administrator'}</p>
                                        <p className="text-sm text-slate-500">{user?.email || 'No email available'}</p>
                                    </div>
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-700">Upload Image</label>
                                    <input
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.webp"
                                        onChange={handlePhotoChange}
                                        className="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700"
                                    />
                                    <p className="mt-2 text-xs text-slate-400">Accepted: JPG, PNG, WEBP. Max size: 2MB.</p>
                                </div>

                                {photoSuccess ? (
                                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                        {photoSuccess}
                                    </div>
                                ) : null}

                                {photoError ? (
                                    <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {photoError}
                                    </div>
                                ) : null}

                                <div className="flex flex-wrap gap-3">
                                    <button type="button" onClick={handleUploadPhoto} disabled={photoSaving} className="btn-primary disabled:opacity-60">
                                        {photoSaving ? 'Updating...' : 'Update Profile Image'}
                                    </button>

                                    <button
                                        type="button"
                                        onClick={handleRemovePhoto}
                                        disabled={photoSaving || !user?.profile_photo_url}
                                        className="btn-secondary disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        Remove Image
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
                {groups.map((group) => (
                    <div key={group.title} className="panel">
                        <div className="panel-body">
                            <h2 className="text-lg font-semibold text-slate-900">{group.title}</h2>

                            <div className="mt-4 space-y-4">
                                {group.entries.map((entry) => (
                                    <div key={entry.key}>
                                        <label className="mb-2 block text-sm font-medium text-slate-700">{entry.label}</label>

                                        {isToggle(entry.key) ? (
                                            <label className="inline-flex cursor-pointer items-center gap-3">
                                                <input
                                                    type="checkbox"
                                                    checked={String(entry.value) === '1'}
                                                    onChange={(event) =>
                                                        setForm((prev) => ({
                                                            ...prev,
                                                            [entry.key]: event.target.checked ? '1' : '0',
                                                        }))
                                                    }
                                                    className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                />
                                                <span className="text-sm text-slate-600">
                                                    {String(entry.value) === '1' ? 'Enabled' : 'Disabled'}
                                                </span>
                                            </label>
                                        ) : (
                                            <input
                                                type={entry.key.includes('email') ? 'email' : 'text'}
                                                value={entry.value}
                                                onChange={(event) =>
                                                    setForm((prev) => ({
                                                        ...prev,
                                                        [entry.key]: event.target.value,
                                                    }))
                                                }
                                                className="input-shell w-full"
                                            />
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}