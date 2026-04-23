import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devServerHost = env.VITE_DEV_SERVER_HOST || '127.0.0.1';
    const devServerPort = Number(env.VITE_DEV_SERVER_PORT || 5173);
    const devServerBindHost = env.VITE_DEV_SERVER_BIND_HOST || '127.0.0.1';

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.jsx'],
                refresh: true,
            }),
            react(),
        ],
        server: {
            host: devServerBindHost,
            port: devServerPort,
            strictPort: true,
            watch: {
                usePolling: true,
                interval: 1000,
            },
            hmr: {
                host: devServerHost,
                port: devServerPort,
            },
        },
    };
});
