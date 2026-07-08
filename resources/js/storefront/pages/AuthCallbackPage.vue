<template>
    <div class="min-h-screen bg-putih flex items-center justify-center">
        <div class="text-center">
            <div v-if="error" class="max-w-sm mx-auto px-6">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-red-50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-charcoal mb-1">Login dengan Google gagal</p>
                <p class="text-xs text-charcoal/50 mb-4">{{ error }}</p>
                <router-link to="/login" class="inline-block px-5 py-2 bg-maroon text-white text-xs font-semibold rounded-xl hover:bg-maroon-600 transition-all">
                    Kembali ke Login
                </router-link>
            </div>
            <div v-else class="flex flex-col items-center gap-3">
                <!-- Spinner -->
                <svg class="animate-spin w-8 h-8 text-maroon" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"/>
                </svg>
                <p class="text-sm text-charcoal/60">Memverifikasi akun Google kamu...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { setToken } from '../api'

const router = useRouter()
const route = useRoute()
const error = ref('')

onMounted(() => {
    const token = route.query.token
    const err = route.query.error

    if (err || !token) {
        error.value = 'Autentikasi dibatalkan atau gagal. Silakan coba lagi.'
        return
    }

    // Simpan token dan redirect ke homepage
    setToken(token)
    sessionStorage.setItem('auth:success', 'Berhasil masuk dengan Google.')
    router.replace('/')
})
</script>
