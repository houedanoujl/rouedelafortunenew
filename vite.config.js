import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    server: {
        host: true,
        proxy: {
            '/': 'http://localhost:8888'
        },
        cors: true,
        port: 3000,
        hmr: {
            host: 'localhost',
            protocol: 'ws'
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `
                    @import "resources/scss/_variables.scss";
                `
            }
        }
    }
});
