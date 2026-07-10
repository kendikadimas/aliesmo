<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4">
        <div class="text-center">
            <div class="w-14 h-14 rounded-2xl bg-maroon flex items-center justify-center mx-auto mb-4">
                <svg class="animate-spin" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
            </div>
            <p v-if="!errorMsg" class="text-sm font-medium text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-slate-400">Sedang masuk dengan Google...</p>
            <div v-else class="space-y-3">
                <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ errorMsg }}</p>
                <router-link to="/login" class="inline-block px-6 py-2.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all">
                    Kembali ke Login
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { setToken } from '../api.js'

const router = useRouter()
const errorMsg = ref('')

onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    const token = params.get('token')
    const error = params.get('error')

    if (error || !token) {
        errorMsg.value = 'Login Google gagal. Silakan coba lagi.'
        return
    }

    setToken(token)

    // Bersihkan query string dari URL tanpa reload
    window.history.replaceState({}, '', '/auth/callback')

    // Redirect ke homepage
    setTimeout(() => router.push('/'), 300)
})
</script>
