import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost', // Use environment variable or default
            protocol: 'ws', // Use WebSocket protocol
            port: process.env.VITE_HMR_PORT || 5173, // Use environment variable or default port
        },
    },
});
