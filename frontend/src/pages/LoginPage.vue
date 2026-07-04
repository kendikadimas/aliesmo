<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-maroon flex items-center justify-center mx-auto">
                    <span class="text-2xl font-bold text-white">A</span>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-charcoal">Masuk dulu yuk!</h1>
                <p class="mt-1 text-sm text-charcoal/50">Masuk buat liat riwayat pesanan kamu</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Email</label>
                    <input v-model="form.email" type="email" required placeholder="kamu@email.com" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Kata Sandi</label>
                    <input v-model="form.password" type="password" required placeholder="Masukkan password" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                </div>

                <div v-if="error" class="p-3 bg-red-50 rounded-xl border border-red-200 text-red-700 text-sm">{{ error }}</div>
                <div v-if="success" class="p-3 bg-green-50 rounded-xl border border-green-200 text-green-700 text-sm font-medium">Yeay, berhasil login! 🎉</div>

                <button type="submit" :disabled="submitting" class="w-full px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:bg-maroon-200 disabled:cursor-not-allowed">
                    {{ submitting ? 'Tunggu...' : 'Masuk' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-charcoal/50">
                Belum punya akun?
                <router-link to="/register" class="text-maroon hover:text-maroon-600 font-semibold">Daftar disini</router-link>
            </p>

            <p class="mt-3 text-center text-sm text-charcoal/50">
                <router-link to="/forgot-password" class="text-maroon hover:text-maroon-600 font-semibold">Lupa password?</router-link>
            </p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api, { setToken } from '../api'

const router = useRouter()
const submitting = ref(false)
const error = ref('')
const success = ref(false)

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
        success.value = true
        setTimeout(() => router.push('/'), 800)
    } catch (e) {
        error.value = e.response?.data?.message || 'Login gagal, coba lagi ya!'
    } finally {
        submitting.value = false
    }
}
</script>
