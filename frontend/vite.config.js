import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'
import forceDarkClassPlugin from './force-dark-class-plugin.js'

const isVercel = process.env.VERCEL === '1'

export default defineConfig(({ command }) => ({
    plugins: [vue(), tailwindcss(), forceDarkClassPlugin()],
    // dev server pakai '/' agar index.html bisa load /src/main.js
    // production build pakai '/build/' agar Laravel bisa serve assets dari public/build/
    base: (command === 'build' && !isVercel) ? '/build/' : '/',
    build: {
        outDir: isVercel ? path.resolve(__dirname, 'dist') : path.resolve(__dirname, '../public/build'),
        emptyOutDir: true,
        // Laravel needs manifest + JS entry; Vercel SPA needs index.html entry
        ...(isVercel
            ? {}
            : {
                manifest: 'manifest.json',
                rollupOptions: {
                    input: path.resolve(__dirname, 'src/main.js'),
                },
            }),
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: false,
        proxy: {
            '/api': {
                target: 'http://localhost:8000',
                changeOrigin: true,
            },
            '/storage': {
                target: 'http://localhost:8000',
                changeOrigin: true,
            },
        },
    },
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['./src/tests/setup.js'],
    },
}))
