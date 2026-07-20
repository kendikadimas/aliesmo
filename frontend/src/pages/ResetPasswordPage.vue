<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="flex items-center justify-center mx-auto">
                    <img src="/aliesmo.png" alt="Aliesmo" class="w-[168px] h-[168px] object-contain invert dark:invert-0">
                </div>
                <h1 class="mt-4 text-2xl font-bold text-charcoal dark:text-[#f0eeeb]">Reset Password</h1>
                <p class="mt-1 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Masukkan password baru kamu</p>
            </div>

            <div v-if="!token" class="p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm text-center">
                Token tidak valid. Minta link reset password baru.
                <router-link to="/forgot-password" class="block mt-2 font-semibold underline dark:text-red-300">Kirim ulang</router-link>
            </div>

            <form v-else @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Password Baru</label>
                    <input v-model="form.password" type="password" required placeholder="Minimal 8 karakter"
                        class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:placeholder:text-[#6a6a6e] bg-white dark:bg-[#28282a] focus:border-maroon dark:focus:border-[#f0eeeb] focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Ulangi Password Baru</label>
                    <input v-model="form.password_confirmation" type="password" required
                        class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:placeholder:text-[#6a6a6e] bg-white dark:bg-[#28282a] focus:border-maroon dark:focus:border-[#f0eeeb] focus:outline-none transition-colors">
                </div>

                <div v-if="error" class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">{{ error }}</div>
                <div v-if="success" class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
                    Password berhasil direset! Silakan login.
                </div>

                <button type="submit" :disabled="submitting || success"
                    class="w-full px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ submitting ? 'Menyimpan...' : 'Reset Password' }}
                </button>

                <router-link v-if="success" to="/login"
                    class="block w-full text-center px-8 py-3 border-2 border-maroon text-maroon dark:border-[#f0eeeb] dark:text-[#f0eeeb] text-sm font-semibold rounded-xl hover:bg-maroon hover:text-white dark:hover:bg-[#f0eeeb] dark:hover:text-[#161618] transition-all">
                    Masuk Sekarang
                </router-link>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'

const route = useRoute()
const token = ref('')
const submitting = ref(false)
const error = ref('')
const success = ref(false)

const form = reactive({
    password: '',
    password_confirmation: '',
})

onMounted(() => {
    token.value = route.query.token || ''
})

async function handleSubmit() {
    if (form.password !== form.password_confirmation) {
        error.value = 'Password dan konfirmasi tidak sama!'
        return
    }
    submitting.value = true
    error.value = ''
    try {
        await api.post('/auth/reset-password', {
            token: token.value,
            email: route.query.email || '',
            password: form.password,
            password_confirmation: form.password_confirmation,
        })
        success.value = true
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal reset password. Token mungkin sudah expired.'
    } finally {
        submitting.value = false
    }
}
</script>
