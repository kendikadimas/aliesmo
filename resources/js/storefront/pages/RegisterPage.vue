<template>
    <div class="max-w-md mx-auto px-4 sm:px-6 py-12 lg:py-24">
        <h1 class="text-2xl sm:text-3xl font-bold text-charcoal text-center tracking-tight">Daftar Akun</h1>
        <p class="mt-2 text-center text-sm text-charcoal/60">Buat akun Aliesmo kamu sekarang</p>

        <!-- Google Register -->
        <div class="mt-8">
            <a
                :href="googleLoginUrl"
                class="flex items-center justify-center gap-3 w-full px-4 py-3 bg-white border-2 border-maroon-100 rounded-xl text-sm font-semibold text-charcoal hover:border-maroon/40 hover:bg-maroon-50/30 transition-all active:scale-[0.98]"
            >
                <svg width="18" height="18" viewBox="0 0 48 48" fill="none">
                    <path d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.332 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z" fill="#FFC107"/>
                    <path d="M6.306 14.691l6.571 4.819C14.655 15.108 19.001 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" fill="#FF3D00"/>
                    <path d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.314 0-9.823-3.637-11.407-8.557l-6.523 5.025C9.505 39.556 16.227 44 24 44z" fill="#4CAF50"/>
                    <path d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 01-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z" fill="#1976D2"/>
                </svg>
                Daftar dengan Google
            </a>
        </div>

        <!-- Divider -->
        <div class="flex items-center gap-3 my-5">
            <div class="flex-1 h-px bg-maroon-100"></div>
            <span class="text-xs text-charcoal/40 font-medium">atau daftar manual</span>
            <div class="flex-1 h-px bg-maroon-100"></div>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Nama Lengkap</label>
                <input v-model="form.name" required placeholder="Masukkan nama kamu" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Email</label>
                <input v-model="form.email" type="email" required placeholder="kamu@email.com" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Kata Sandi</label>
                <input v-model="form.password" type="password" required placeholder="Minimal 8 karakter" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Konfirmasi Kata Sandi</label>
                <input v-model="form.password_confirmation" type="password" required placeholder="Ulangi kata sandi" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors">
            </div>

            <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">{{ error }}</div>

            <button type="submit" :disabled="submitting" class="w-full px-8 py-3.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed shadow-lg">
                {{ submitting ? 'Memproses...' : 'Daftar Sekarang' }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-charcoal/60">
            Sudah punya akun?
            <router-link to="/login" class="text-maroon hover:text-maroon-600 font-semibold underline underline-offset-2">Masuk</router-link>
        </p>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api, { setToken } from '../api'

const router = useRouter()
const submitting = ref(false)
const error = ref('')

// OAuth harus dimulai dari backend Laravel, bukan origin Vite/frontend.
const backendUrl = (import.meta.env.VITE_BACKEND_URL || window.location.origin).replace(/\/$/, '')
const googleLoginUrl = `${backendUrl}/auth/google/redirect`

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

async function handleRegister() {
    submitting.value = true
    error.value = ''
    try {
        const res = await api.post('/auth/register', form)
        setToken(res.data.token)
        sessionStorage.setItem('auth:success', 'Akun berhasil dibuat.')
        router.push('/')
    } catch (e) {
        error.value = e.response?.data?.message || 'Pendaftaran gagal. Silakan coba lagi.'
    } finally {
        submitting.value = false
    }
}
</script>
