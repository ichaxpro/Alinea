import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/css/explore.css',
                'resources/js/app.js', 
                'resources/js/explore.js',
                'resources/js/global-search.js', 
                'resources/js/timeline.js', 
                'resources/js/klub.js', 
                'resources/js/dashboard.js', 
                'resources/js/avatar-upload.js', 
                'resources/js/profile-edit.js',
                'resources/js/katalog.js',
                'resources/js/detail_buku.js',
                'resources/js/chat.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
