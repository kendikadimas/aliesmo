import axios from 'axios'

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Accept': 'application/json',
    },
})

api.interceptors.request.use(config => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

export function setToken(token) {
    localStorage.setItem('token', token)
}

export function clearToken() {
    localStorage.removeItem('token')
}

export default api
