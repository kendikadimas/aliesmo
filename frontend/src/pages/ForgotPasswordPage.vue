<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-maroon flex items-center justify-center mx-auto">
                    <span class="text-2xl font-bold text-white">A</span>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-charcoal dark:text-[#f0eeeb]">Lupa Password?</h1>
                <p class="mt-1 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Masukkan email kamu, kami kirim link reset</p>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Email</label>
                    <input v-model="email" type="email" required placeholder="kamu@email.com"
                        class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:placeholder:text-[#6a6a6e] bg-white dark:bg-[#28282a] focus:border-maroon dark:focus:border-[#f0eeeb] focus:outline-none transition-colors">
                </div>

                <div v-if="error" class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">{{ error }}</div>
                <div v-if="success" class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
                    Link reset password sudah dikirim ke email kamu! Cek inbox atau spam ya.
                </div>

                <button type="submit" :disabled="submitting || success"
                    class="w-full px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ submitting ? 'Mengirim...' : 'Kirim Link Reset' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-charcoal/50 dark:text-[#8a8a8e]">
                Ingat passwordnya?
                <router-link to="/login" class="text-maroon hover:text-maroon-600 dark:hover:text-[#f0eeeb] font-semibold">Masuk sekarang</router-link>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../api'

const email = ref('')
const submitting = ref(false)
const error = ref('')
const success = ref(false)

async function handleSubmit() {
    submitting.value = true
    error.value = ''
    try {
        await api.post('/auth/forgot-password', { email: email.value })
        success.value = true
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal mengirim email. Coba lagi ya!'
    } finally {
        submitting.value = false
    }
}
</script>
