import '../css/app.css';
import './bootstrap';

import React, { Suspense, lazy } from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';

import { AuthProvider, useAuth } from './context/AuthContext';
import { SettingsProvider } from './context/SettingsContext';
import Layout from './components/Layout';

const LoginPage = lazy(() => import('./pages/LoginPage'));
const DashboardPage = lazy(() => import('./pages/DashboardPage'));
const ItemsPage = lazy(() => import('./pages/ItemsPage'));
const AssignmentsPage = lazy(() => import('./pages/AssignmentsPage'));
const CategoriesPage = lazy(() => import('./pages/CategoriesPage'));
const DepartmentsPage = lazy(() => import('./pages/DepartmentsPage'));
const SuppliersPage = lazy(() => import('./pages/SuppliersPage'));
const NotificationsPage = lazy(() => import('./pages/NotificationsPage'));
const ProfilePage = lazy(() => import('./pages/ProfilePage'));
const UsersPage = lazy(() => import('./pages/UsersPage'));
const SettingsPage = lazy(() => import('./pages/SettingsPage'));

function preloadPages() {
    [
        import('./pages/DashboardPage'),
        import('./pages/ItemsPage'),
        import('./pages/AssignmentsPage'),
        import('./pages/CategoriesPage'),
        import('./pages/DepartmentsPage'),
        import('./pages/SuppliersPage'),
        import('./pages/NotificationsPage'),
        import('./pages/ProfilePage'),
        import('./pages/UsersPage'),
        import('./pages/SettingsPage'),
    ].forEach((pagePromise) => {
        pagePromise.catch(() => {});
    });
}

function LoadingScreen() {
    return (
        <div className="flex min-h-screen items-center justify-center bg-[#f5f7fb]">
            <div className="text-center">
                <div className="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" />
                <p className="mt-4 text-sm font-medium text-slate-600">Loading NextGen Assets...</p>
                <p className="mt-1 text-xs text-slate-500">Preparing your workspace</p>
            </div>
        </div>
    );
}

function PageLoader() {
    return (
        <div className="flex min-h-[320px] items-center justify-center">
            <div className="text-center">
                <div className="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" />
                <p className="mt-3 text-sm text-slate-600">Loading page...</p>
            </div>
        </div>
    );
}

function GuestRoute({ children }) {
    const { loading, isAuthenticated } = useAuth();

    if (loading) {
        return <LoadingScreen />;
    }

    if (isAuthenticated) {
        return <Navigate to="/dashboard" replace />;
    }

    return children;
}

function ProtectedRoute({ children }) {
    const { loading, isAuthenticated } = useAuth();

    if (loading) {
        return <LoadingScreen />;
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" replace />;
    }

    return (
        <Layout>
            <Suspense fallback={<PageLoader />}>{children}</Suspense>
        </Layout>
    );
}

function AppRoutes() {
    return (
        <Routes>
            <Route path="/" element={<Navigate to="/dashboard" replace />} />

            <Route
                path="/login"
                element={
                    <GuestRoute>
                        <LoginPage />
                    </GuestRoute>
                }
            />

            <Route
                path="/dashboard"
                element={
                    <ProtectedRoute>
                        <DashboardPage />
                    </ProtectedRoute>
                }
            />

            <Route path="/items" element={<Navigate to="/inventory" replace />} />

            <Route
                path="/inventory"
                element={
                    <ProtectedRoute>
                        <ItemsPage />
                    </ProtectedRoute>
                }
            />

            <Route
                path="/assignments"
                element={
                    <ProtectedRoute>
                        <AssignmentsPage />
                    </ProtectedRoute>
                }
            />

            <Route
                path="/categories"
                element={
                    <ProtectedRoute>
                        <CategoriesPage />
                    </ProtectedRoute>
                }
            />

            <Route
                path="/departments"
                element={
                    <ProtectedRoute>
                        <DepartmentsPage />
                    </ProtectedRoute>
                }
            />

            <Route
                path="/suppliers"
                element={
                    <ProtectedRoute>
                        <SuppliersPage />
                    </ProtectedRoute>
                }
            />

            <Route
                path="/notifications"
                element={
                    <ProtectedRoute>
                        <NotificationsPage />
                    </ProtectedRoute>
                }
            />

            <Route
                path="/profile"
                element={
                    <ProtectedRoute>
                        <ProfilePage />
                    </ProtectedRoute>
                }
            />

            <Route
                path="/users"
                element={
                    <ProtectedRoute>
                        <UsersPage />
                    </ProtectedRoute>
                }
            />

            <Route
                path="/settings"
                element={
                    <ProtectedRoute>
                        <SettingsPage />
                    </ProtectedRoute>
                }
            />

            <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
    );
}

function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <SettingsProvider>
                    <AppRoutes />
                </SettingsProvider>
            </AuthProvider>
        </BrowserRouter>
    );
}

const rootElement = document.getElementById('app');

if (rootElement) {
    if (!window.__nextgenReactRoot) {
        window.__nextgenReactRoot = ReactDOM.createRoot(rootElement);
    }

    window.__nextgenReactRoot.render(<App />);
    setTimeout(preloadPages, 150);
}