import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

export default defineConfig({
    plugins: [vue(), tailwindcss()],
    build: {
        outDir: path.resolve(__dirname, '../public/build'),
        emptyOutDir: true,
        manifest: 'manifest.json',
        rollupOptions: {
            input: path.resolve(__dirname, 'src/main.js'),
        },
    },
    server: {
        proxy: {
            '/api': {
                target: process.env.VITE_API_URL || 'http://aliesmo.test',
                changeOrigin: true,
            },
        },
    },
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['./src/tests/setup.js'],
    },
})
