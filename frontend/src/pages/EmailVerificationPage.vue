<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 py-12">
        <div class="w-full max-w-sm text-center">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 flex items-center justify-center mx-auto">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>

            <h1 class="mt-6 text-2xl font-bold text-charcoal dark:text-slate-100">Verifikasi Emailmu</h1>
            <p class="mt-2 text-sm text-charcoal/60 dark:text-slate-400 leading-relaxed">
                Kami sudah kirim link verifikasi ke emailmu. Klik link tersebut untuk mengaktifkan akun.
            </p>

            <div v-if="successMsg" class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm">
                {{ successMsg }}
            </div>
            <div v-if="errorMsg" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
                {{ errorMsg }}
            </div>

            <button
                @click="resend"
                :disabled="loading || cooldown > 0"
                class="mt-6 w-full py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ cooldown > 0 ? `Kirim ulang (${cooldown}s)` : loading ? 'Mengirim...' : 'Kirim Ulang Email Verifikasi' }}
            </button>

            <p class="mt-4 text-xs text-charcoal/40 dark:text-slate-500">
                Sudah verifikasi?
                <router-link to="/" class="text-maroon font-semibold hover:text-maroon-600">Kembali ke Beranda</router-link>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../api'

const loading = ref(false)
const cooldown = ref(0)
const successMsg = ref('')
const errorMsg = ref('')

async function resend() {
    loading.value = true
    errorMsg.value = ''
    successMsg.value = ''
    try {
        await api.post('/auth/email/resend')
        successMsg.value = 'Link verifikasi berhasil dikirim ulang. Cek inbox atau folder spam kamu.'
        cooldown.value = 60
        const interval = setInterval(() => {
            cooldown.value--
            if (cooldown.value <= 0) clearInterval(interval)
        }, 1000)
    } catch (e) {
        errorMsg.value = e.response?.data?.message || 'Gagal mengirim ulang. Coba lagi ya.'
    } finally {
        loading.value = false
    }
}
</script>
