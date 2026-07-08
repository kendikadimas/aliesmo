<template>
    <div class="max-w-md mx-auto px-4 sm:px-6 py-12 lg:py-24">
        <h1 class="text-3xl lg:text-4xl font-light text-charcoal text-center">Daftar</h1>
        <p class="mt-2 text-center text-sm text-charcoal/60">Buat akun Aliesmo Anda</p>

        <form @submit.prevent="handleRegister" class="mt-8 space-y-4">
            <div>
                <label class="block text-xs tracking-wider uppercase text-charcoal/50 mb-1.5">Nama Lengkap</label>
                <input v-model="form.name" required placeholder="John Doe" class="w-full border border-ink-10 px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/50 focus:outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs tracking-wider uppercase text-charcoal/50 mb-1.5">Email</label>
                <input v-model="form.email" type="email" required placeholder="john@example.com" class="w-full border border-ink-10 px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/50 focus:outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs tracking-wider uppercase text-charcoal/50 mb-1.5">Kata Sandi</label>
                <input v-model="form.password" type="password" required placeholder="Minimal 8 karakter" class="w-full border border-ink-10 px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/50 focus:outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs tracking-wider uppercase text-charcoal/50 mb-1.5">Konfirmasi Kata Sandi</label>
                <input v-model="form.password_confirmation" type="password" required class="w-full border border-ink-10 px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/50 focus:outline-none transition-colors">
            </div>

            <div v-if="error" class="p-3 bg-ink-05 border border-ink-10 text-ink-60 text-sm">{{ error }}</div>

            <button type="submit" :disabled="submitting" class="w-full px-8 py-3.5 bg-charcoal text-paper text-sm tracking-widest uppercase hover:bg-charcoal/90 transition-all active:scale-[0.98] disabled:bg-ink-20 disabled:cursor-not-allowed">
                {{ submitting ? 'Memproses...' : 'Daftar' }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-charcoal/60">
            Sudah punya akun?
            <router-link to="/login" class="text-ink-60 hover:text-charcoal transition-colors font-medium underline underline-offset-2">Masuk</router-link>
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
        router.push('/')
    } catch (e) {
        error.value = e.response?.data?.message || 'Pendaftaran gagal. Silakan coba lagi.'
    } finally {
        submitting.value = false
    }
}
</script>
