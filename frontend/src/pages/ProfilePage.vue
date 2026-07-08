<template>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <h1 class="text-3xl lg:text-4xl font-bold text-charcoal dark:text-slate-100 tracking-tight">Profil Saya</h1>
        <p class="mt-2 text-sm text-charcoal/50 dark:text-slate-400">Kelola data akun dan keamanan kamu</p>

        <div v-if="!isLoggedIn" class="py-24 text-center">
            <svg class="w-16 h-16 mx-auto text-ink-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <h2 class="mt-4 text-xl font-bold text-charcoal">Login dulu yuk!</h2>
            <router-link to="/login" class="inline-block mt-6 px-8 py-3 bg-ink text-white text-sm font-semibold rounded-xl hover:bg-ink-60 transition-all shadow-lg">
                Login Sekarang
            </router-link>
        </div>

        <div v-else-if="loading" class="py-24 text-center">
            <div class="inline-block w-10 h-10 border-4 border-ink-10 border-t-ink rounded-full animate-spin"></div>
        </div>

        <div v-else class="mt-8 space-y-6">
            <div class="bg-white dark:bg-slate-800 p-6 lg:p-8 rounded-2xl border-2 border-ink-10 dark:border-slate-700">
                <h2 class="text-sm font-bold text-charcoal dark:text-slate-200 tracking-wide mb-6">Data Diri</h2>
                <form @submit.prevent="saveProfile" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-charcoal/60 dark:text-slate-400 mb-1.5">Nama Lengkap</label>
                        <input v-model="profileForm.name" required
                            class="w-full border-2 border-ink-10 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-slate-200 focus:border-ink focus:outline-none transition-colors bg-white dark:bg-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-charcoal/60 dark:text-slate-400 mb-1.5">Email</label>
                        <input v-model="profileForm.email" type="email" required
                            class="w-full border-2 border-ink-10 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-slate-200 focus:border-ink focus:outline-none transition-colors bg-white dark:bg-slate-700">
                    </div>

                    <div v-if="profileError" class="p-3 bg-ink-05 dark:bg-ink-80/40 rounded-xl border border-ink-10 dark:border-ink-60 text-ink-60 dark:text-ink-20 text-sm">{{ profileError }}</div>
                    <div v-if="profileSuccess" class="p-3 bg-ink-05 dark:bg-ink-80/40 rounded-xl border border-ink-10 dark:border-ink-60 text-ink dark:text-ink-05 text-sm font-medium">Profil berhasil diperbarui!</div>

                    <button type="submit" :disabled="profileSaving"
                        class="px-8 py-2.5 bg-ink text-white text-sm font-semibold rounded-xl hover:bg-ink-60 transition-all active:scale-[0.97] shadow-lg disabled:opacity-50">
                        {{ profileSaving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </form>
            </div>

            <!-- Ganti Password -->
            <div class="bg-white dark:bg-slate-800 p-6 lg:p-8 rounded-2xl border-2 border-ink-10 dark:border-slate-700">
                <h2 class="text-sm font-bold text-charcoal dark:text-slate-200 tracking-wide mb-6">Ganti Password</h2>
                <form @submit.prevent="savePassword" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-charcoal/60 dark:text-slate-400 mb-1.5">Password Lama</label>
                        <input v-model="passwordForm.current_password" type="password" required
                            class="w-full border-2 border-ink-10 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-slate-200 focus:border-ink focus:outline-none transition-colors bg-white dark:bg-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-charcoal/60 dark:text-slate-400 mb-1.5">Password Baru</label>
                        <input v-model="passwordForm.password" type="password" required placeholder="Minimal 8 karakter"
                            class="w-full border-2 border-ink-10 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-slate-200 focus:border-ink focus:outline-none transition-colors bg-white dark:bg-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-charcoal/60 dark:text-slate-400 mb-1.5">Ulangi Password Baru</label>
                        <input v-model="passwordForm.password_confirmation" type="password" required
                            class="w-full border-2 border-ink-10 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-slate-200 focus:border-ink focus:outline-none transition-colors bg-white dark:bg-slate-700">
                    </div>

                    <div v-if="passwordError" class="p-3 bg-ink-05 dark:bg-ink-80/40 rounded-xl border border-ink-10 dark:border-ink-60 text-ink-60 dark:text-ink-20 text-sm">{{ passwordError }}</div>
                    <div v-if="passwordSuccess" class="p-3 bg-ink-05 dark:bg-ink-80/40 rounded-xl border border-ink-10 dark:border-ink-60 text-ink dark:text-ink-05 text-sm font-medium">Password berhasil diperbarui!</div>

                    <button type="submit" :disabled="passwordSaving"
                        class="px-8 py-2.5 bg-ink text-white text-sm font-semibold rounded-xl hover:bg-ink-60 transition-all active:scale-[0.97] shadow-lg disabled:opacity-50">
                        {{ passwordSaving ? 'Menyimpan...' : 'Ganti Password' }}
                    </button>
                </form>
            </div>

            <!-- Quick Links -->
            <div class="flex flex-col sm:flex-row gap-3">
                <router-link to="/orders" class="flex-1 text-center px-6 py-3 border-2 border-ink text-ink text-sm font-semibold rounded-xl hover:bg-ink hover:text-white transition-all">
                    Pesanan Saya
                </router-link>
                <button @click="handleLogout" class="flex-1 px-6 py-3 border-2 border-ink-10 text-ink-40 text-sm font-semibold rounded-xl hover:border-ink-60 hover:text-ink-60 transition-all">
                    Keluar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api, { clearToken } from '../api'

const router = useRouter()
const isLoggedIn = ref(false)
const loading = ref(true)

const profileForm = reactive({ name: '', email: '' })
const profileSaving = ref(false)
const profileError = ref('')
const profileSuccess = ref(false)

const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })
const passwordSaving = ref(false)
const passwordError = ref('')
const passwordSuccess = ref(false)

onMounted(async () => {
    isLoggedIn.value = !!localStorage.getItem('token')
    if (!isLoggedIn.value) { loading.value = false; return }
    try {
        const res = await api.get('/me/profile')
        const user = res.data.data || res.data
        profileForm.name = user.name
        profileForm.email = user.email
    } catch {
        isLoggedIn.value = false
    } finally {
        loading.value = false
    }
})

async function saveProfile() {
    profileSaving.value = true
    profileError.value = ''
    profileSuccess.value = false
    try {
        await api.put('/me/profile', { name: profileForm.name, email: profileForm.email })
        profileSuccess.value = true
        setTimeout(() => { profileSuccess.value = false }, 3000)
    } catch (e) {
        const errors = e.response?.data?.errors
        profileError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Gagal menyimpan.')
    } finally {
        profileSaving.value = false
    }
}

async function savePassword() {
    if (passwordForm.password !== passwordForm.password_confirmation) {
        passwordError.value = 'Password baru dan konfirmasi tidak sama!'
        return
    }
    passwordSaving.value = true
    passwordError.value = ''
    passwordSuccess.value = false
    try {
        await api.put('/me/password', {
            current_password: passwordForm.current_password,
            password: passwordForm.password,
            password_confirmation: passwordForm.password_confirmation,
        })
        passwordSuccess.value = true
        passwordForm.current_password = ''
        passwordForm.password = ''
        passwordForm.password_confirmation = ''
        setTimeout(() => { passwordSuccess.value = false }, 3000)
    } catch (e) {
        passwordError.value = e.response?.data?.message || 'Gagal ganti password.'
    } finally {
        passwordSaving.value = false
    }
}

function handleLogout() {
    api.post('/auth/logout').catch(() => {})
    clearToken()
    localStorage.removeItem('user')
    router.push('/login')
}
</script>
