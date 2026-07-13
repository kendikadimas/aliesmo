import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api/v1',
    headers: {
        'Accept': 'application/json',
    },
})

// Debug logging — aktif di dev, atau di production kalau localStorage.debug = 'api'
const isDev   = import.meta.env.DEV
const isDebug = () => isDev || localStorage.getItem('debug') === 'api'

const log = {
    req:  (config) => {
        if (!isDebug()) return
        const label = `[API] ${config.method?.toUpperCase()} ${config.url}`
        console.groupCollapsed(`%c${label}`, 'color:#6366f1;font-weight:bold')
        if (config.params)  console.log('params :', config.params)
        if (config.data)    console.log('body   :', typeof config.data === 'string' ? JSON.parse(config.data) : config.data)
        console.groupEnd()
    },
    res: (response) => {
        if (!isDebug()) return
        const { config, status, data } = response
        const label = `[API] ${status} ${config.method?.toUpperCase()} ${config.url}`
        const style = status < 300 ? 'color:#22c55e;font-weight:bold' : 'color:#f59e0b;font-weight:bold'
        console.groupCollapsed(`%c${label}`, style)
        console.log('source :', data?.source ?? '—')
        console.log('data   :', data)
        console.groupEnd()
    },
    err: (error) => {
        if (!isDebug()) return
        const { config, response } = error
        const status = response?.status ?? 'ERR'
        const label  = `[API] ${status} ${config?.method?.toUpperCase()} ${config?.url}`
        console.groupCollapsed(`%c${label}`, 'color:#ef4444;font-weight:bold')
        console.log('status  :', status)
        console.log('message :', response?.data?.message ?? error.message)
        console.log('payload :', response?.data)
        console.groupEnd()
    },
}

// Request interceptor — inject token + debug log
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    log.req(config)
    return config
})

// Response interceptor — handle auth expired & server errors
api.interceptors.response.use(
    response => {
        log.res(response)
        return response
    },
    error => {
        log.err(error)
        if (error.response?.status === 401) {
            // Token expired atau tidak valid — clear dan redirect ke login
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            // Hanya redirect kalau bukan dari halaman auth itu sendiri
            const currentPath = window.location.pathname
            const authPaths = ['/login', '/register', '/forgot-password', '/reset-password']
            if (!authPaths.some(p => currentPath.endsWith(p))) {
                // Dispatch custom event supaya Vue Router yang handle navigate
                window.dispatchEvent(new CustomEvent('auth:expired'))
            }
        }

        if (error.response?.status === 403) {
            const msg = error.response?.data?.message || ''
            // Deteksi 403 dari middleware verified — arahkan user untuk verifikasi email
            if (msg.toLowerCase().includes('verify') || msg.toLowerCase().includes('verif')) {
                window.dispatchEvent(new CustomEvent('auth:unverified'))
            }
        }

        if (error.response?.status >= 500) {
            // Server error — tampilkan toast global jika tersedia
            if (window.__showToast) {
                window.__showToast('Terjadi kesalahan pada server. Coba lagi sebentar.', 'error')
            }
        }

        return Promise.reject(error)
    }
)

export function setToken(token) {
    localStorage.setItem('token', token)
}

export function clearToken() {
    localStorage.removeItem('token')
}

export default api
