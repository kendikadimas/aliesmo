<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-maroon flex items-center justify-center mx-auto">
                    <span class="text-2xl font-bold text-white">A</span>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-charcoal">Daftar Akun</h1>
                <p class="mt-1 text-sm text-charcoal/50">Gratis, kok! Daftar cuma 1 menit</p>
            </div>

            <form @submit.prevent="handleRegister" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Nama Lengkap</label>
                    <input v-model="form.name" required placeholder="Nama kamu" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Email</label>
                    <input v-model="form.email" type="email" required placeholder="kamu@email.com" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Kata Sandi</label>
                    <input v-model="form.password" type="password" required placeholder="Minimal 8 karakter" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Ulangi Kata Sandi</label>
                    <input v-model="form.password_confirmation" type="password" required class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                </div>

                <div v-if="error" class="p-3 bg-red-50 rounded-xl border border-red-200 text-red-700 text-sm">{{ error }}</div>
                <div v-if="success" class="p-3 bg-green-50 rounded-xl border border-green-200 text-green-700 text-sm font-medium">Mantap! Akun berhasil dibuat 🎉</div>

                <button type="submit" :disabled="submitting" class="w-full px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:bg-maroon-200 disabled:cursor-not-allowed">
                    {{ submitting ? 'Tunggu...' : 'Daftar Gratis' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-charcoal/50">
                Udah punya akun?
                <router-link to="/login" class="text-maroon hover:text-maroon-600 font-semibold">Masuk sini</router-link>
            </p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { setToken } from '../api'

const router = useRouter()
const submitting = ref(false)
const error = ref('')
const success = ref(false)

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
    setToken('mock-token-' + Date.now())
    success.value = true
    setTimeout(() => router.push('/'), 800)
}
</script>
