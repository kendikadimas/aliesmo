import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'
import fs from 'fs'
import forceDarkClassPlugin from './force-dark-class-plugin.js'

const isVercel = process.env.VERCEL === '1'
const hotFile = path.resolve(__dirname, '../public/hot')
const hotUrl = 'http://127.0.0.1:5173'

// tulis/hapus public/hot agar Laravel @vite switch dev ↔ build
function laravelHotFile() {
    return {
        name: 'laravel-hot-file',
        configureServer(server) {
            fs.writeFileSync(hotFile, hotUrl)
            const cleanup = () => {
                try {
                    if (fs.existsSync(hotFile)) fs.unlinkSync(hotFile)
                } catch { /* ignore */ }
            }
            server.httpServer?.once('close', cleanup)
            process.once('exit', cleanup)
            process.once('SIGINT', () => { cleanup(); process.exit() })
            process.once('SIGTERM', () => { cleanup(); process.exit() })
        },
    }
}

export default defineConfig(({ command }) => ({
    plugins: [
        ...(!isVercel ? [laravelHotFile()] : []),
        vue(),
        tailwindcss(),
        forceDarkClassPlugin(),
    ],
    // dev: '/' | build Laravel: '/build/' | Vercel SPA: '/'
    base: (command === 'build' && !isVercel) ? '/build/' : '/',
    build: {
        outDir: isVercel ? path.resolve(__dirname, 'dist') : path.resolve(__dirname, '../public/build'),
        emptyOutDir: true,
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
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        origin: hotUrl,
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
