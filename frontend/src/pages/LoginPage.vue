<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="flex items-center justify-center mx-auto">
                    <img src="/aliesmo.png" alt="Aliesmo" class="w-[168px] h-[168px] object-contain invert dark:invert-0">
                </div>
                <h1 class="mt-4 text-2xl font-bold text-charcoal dark:text-[#f0eeeb]">Masuk dulu yuk!</h1>
                <p class="mt-1 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Masuk buat liat riwayat pesanan kamu</p>
            </div>

            <!-- Google Login -->
            <a :href="googleLoginUrl" class="flex items-center justify-center gap-3 w-full px-4 py-2.5 border-2 border-zinc-200 dark:border-[#303032] rounded-xl text-sm font-semibold text-charcoal dark:text-[#f0eeeb] hover:border-maroon dark:hover:border-maroon/60 hover:bg-maroon/5 dark:hover:bg-maroon/10 transition-all active:scale-[0.97] bg-white dark:bg-[#1c1c1e] mb-4">
                <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Masuk dengan Google
            </a>

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 h-px bg-zinc-200 dark:bg-[#28282a]"></div>
                <span class="text-xs text-charcoal/40 dark:text-[#6a6a6e] font-medium">atau</span>
                <div class="flex-1 h-px bg-zinc-200 dark:bg-[#28282a]"></div>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Email</label>
                    <input v-model="form.email" type="email" required placeholder="kamu@email.com" class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:text-[#6a6a6e]/60 dark:placeholder:text-[#6a6a6e] focus:border-maroon focus:outline-none transition-colors bg-white dark:bg-[#1c1c1e]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Kata Sandi</label>
                    <input v-model="form.password" type="password" required placeholder="Masukkan password" class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:text-[#6a6a6e]/60 dark:placeholder:text-[#6a6a6e] focus:border-maroon focus:outline-none transition-colors bg-white dark:bg-[#1c1c1e]">
                </div>

                <div v-if="error" class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">{{ error }}</div>
                <div v-if="success" class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">Yeay, berhasil login!</div>
                <div v-if="claimedMsg" class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 text-sm font-medium">{{ claimedMsg }}</div>

                <button type="submit" :disabled="submitting" class="w-full px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:bg-maroon-200 disabled:cursor-not-allowed">
                    {{ submitting ? 'Tunggu...' : 'Masuk' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-charcoal/50 dark:text-[#8a8a8e]">
                Belum punya akun?
                <router-link to="/register" class="text-maroon dark:text-[#f0eeeb] hover:text-maroon-600 dark:hover:text-[#d0ceca] font-semibold">Daftar disini</router-link>
            </p>

            <p class="mt-3 text-center text-sm text-charcoal/50 dark:text-[#8a8a8e]">
                <router-link to="/forgot-password" class="text-maroon dark:text-[#f0eeeb] hover:text-maroon-600 dark:hover:text-[#d0ceca] font-semibold">Lupa password?</router-link>
            </p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api, { setToken } from '../api'

const router = useRouter()
const submitting = ref(false)
const error = ref('')
const success = ref(false)
const claimedMsg = ref('')

// URL langsung ke Laravel redirect — bukan AJAX, buka tab baru tidak perlu, cukup navigate
const googleLoginUrl = computed(() => {
    const base = import.meta.env.VITE_API_URL?.replace('/api/v1', '') || ''
    return `${base}/auth/google/redirect`
})

const form = reactive({
    email: '',
    password: '',
})

async function handleLogin() {
    submitting.value = true
    error.value = ''
    if (!form.email || !form.password) {
        error.value = 'Email dan password harus diisi ya!'
        submitting.value = false
        return
    }
    try {
        const res = await api.post('/auth/login', {
            email: form.email,
            password: form.password,
        })
        setToken(res.data.token)
        window.dispatchEvent(new Event('auth:login'))
        success.value = true
        // Jika ada guest orders yang ter-claim, tampilkan notif dan arahkan ke /orders
        if (res.data.claimed_orders > 0) {
            claimedMsg.value = `${res.data.claimed_orders} pesanan guest berhasil diklaim ke akunmu!`
            setTimeout(() => router.push('/orders'), 1500)
        } else {
            router.push('/profile')
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Login gagal, coba lagi ya!'
    } finally {
        submitting.value = false
    }
}
</script>
