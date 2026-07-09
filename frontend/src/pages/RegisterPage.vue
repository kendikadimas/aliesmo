<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-maroon flex items-center justify-center mx-auto">
                    <span class="text-2xl font-bold text-white">A</span>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-charcoal dark:text-slate-100">Daftar Akun</h1>
                <p class="mt-1 text-sm text-charcoal/50 dark:text-slate-400">Gratis, kok! Daftar cuma 1 menit</p>
            </div>

            <!-- Google Register -->
            <a :href="googleLoginUrl" class="flex items-center justify-center gap-3 w-full px-4 py-2.5 border-2 border-zinc-200 dark:border-slate-600 rounded-xl text-sm font-semibold text-charcoal dark:text-slate-200 hover:border-maroon hover:bg-maroon/5 transition-all active:scale-[0.97] bg-white dark:bg-slate-800 mb-4">
                <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Daftar dengan Google
            </a>

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 h-px bg-zinc-200 dark:bg-slate-700"></div>
                <span class="text-xs text-charcoal/40 dark:text-slate-500 font-medium">atau daftar manual</span>
                <div class="flex-1 h-px bg-zinc-200 dark:bg-slate-700"></div>
            </div>

            <form @submit.prevent="handleRegister" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-slate-400 mb-1.5">Nama Lengkap</label>
                    <input v-model="form.name" required placeholder="Nama kamu" class="w-full border-2 border-maroon-100 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-slate-200 placeholder:text-charcoal/30 dark:placeholder:text-slate-500 focus:border-maroon focus:outline-none transition-colors bg-white dark:bg-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-slate-400 mb-1.5">Email</label>
                    <input v-model="form.email" type="email" required placeholder="kamu@email.com" class="w-full border-2 border-maroon-100 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-slate-200 placeholder:text-charcoal/30 dark:placeholder:text-slate-500 focus:border-maroon focus:outline-none transition-colors bg-white dark:bg-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-slate-400 mb-1.5">Kata Sandi</label>
                    <input v-model="form.password" type="password" required placeholder="Minimal 8 karakter" class="w-full border-2 border-maroon-100 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-slate-200 placeholder:text-charcoal/30 dark:placeholder:text-slate-500 focus:border-maroon focus:outline-none transition-colors bg-white dark:bg-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-slate-400 mb-1.5">Ulangi Kata Sandi</label>
                    <input v-model="form.password_confirmation" type="password" required class="w-full border-2 border-maroon-100 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-slate-200 placeholder:text-charcoal/30 dark:placeholder:text-slate-500 focus:border-maroon focus:outline-none transition-colors bg-white dark:bg-slate-800">
                </div>

                <div v-if="error" class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">{{ error }}</div>
                <div v-if="success" class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">Mantap! Akun berhasil dibuat!</div>
                <div v-if="needsVerification" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 text-sm space-y-2">
                    <p class="font-semibold">Cek emailmu ya!</p>
                    <p>Kami kirim link verifikasi ke <strong>{{ form.email }}</strong>. Klik link tersebut untuk mengaktifkan akun kamu.</p>
                    <button type="button" @click="resendVerification" :disabled="resendLoading || resendCooldown > 0" class="mt-1 text-xs font-semibold text-blue-600 dark:text-blue-400 underline disabled:opacity-50">
                        {{ resendCooldown > 0 ? `Kirim ulang (${resendCooldown}s)` : resendLoading ? 'Mengirim...' : 'Kirim ulang email verifikasi' }}
                    </button>
                </div>

                <button type="submit" :disabled="submitting" class="w-full px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:bg-maroon-200 disabled:cursor-not-allowed">
                    {{ submitting ? 'Tunggu...' : 'Daftar Gratis' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-charcoal/50 dark:text-slate-400">
                Udah punya akun?
                <router-link to="/login" class="text-maroon hover:text-maroon-600 font-semibold">Masuk sini</router-link>
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
const needsVerification = ref(false)
const resendLoading = ref(false)
const resendCooldown = ref(0)

const googleLoginUrl = computed(() => {
    const base = import.meta.env.VITE_API_URL?.replace('/api/v1', '') || ''
    return `${base}/auth/google/redirect`
})

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

async function handleRegister() {
    submitting.value = true
    error.value = ''
    if (!form.name || !form.email || !form.password) {
        error.value = 'Semua field harus diisi ya!'
        submitting.value = false
        return
    }
    if (form.password !== form.password_confirmation) {
        error.value = 'Passwordnya beda nih, coba ketik ulang!'
        submitting.value = false
        return
    }
    try {
        const res = await api.post('/auth/register', {
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        })
        setToken(res.data.token)
        success.value = true
        // Tampilkan banner verifikasi email, jangan langsung redirect
        needsVerification.value = true
        // Redirect ke home setelah 5 detik — user bisa tetap di sini untuk resend
        setTimeout(() => router.push('/'), 5000)
    } catch (e) {
        const errors = e.response?.data?.errors
        if (errors) {
            error.value = Object.values(errors).flat().join(' ')
        } else {
            error.value = e.response?.data?.message || 'Registrasi gagal, coba lagi ya!'
        }
    } finally {
        submitting.value = false
    }
}

async function resendVerification() {
    resendLoading.value = true
    try {
        await api.post('/auth/email/resend')
        // Cooldown 60 detik setelah resend
        resendCooldown.value = 60
        const interval = setInterval(() => {
            resendCooldown.value--
            if (resendCooldown.value <= 0) clearInterval(interval)
        }, 1000)
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal mengirim ulang email.'
    } finally {
        resendLoading.value = false
    }
}
</script>
