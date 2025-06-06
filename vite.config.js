import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/custom.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        react(),
    ],
    server:{
        host:"194.32.141.249",
        port:5173,
    },
});
