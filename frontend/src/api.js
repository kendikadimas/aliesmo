import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api/v1',
    headers: {
        'Accept': 'application/json',
    },
})

// Request interceptor — inject token
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

// Response interceptor — handle auth expired & server errors
api.interceptors.response.use(
    response => response,
    error => {
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
