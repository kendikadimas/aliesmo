<template>
    <div class="min-h-screen bg-putih">
        <header class="fixed top-0 left-0 right-0 z-50 bg-batik-cream/90 backdrop-blur-md border-b border-maroon-100/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 lg:h-20">
                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 -ml-2 text-charcoal hover:text-maroon transition-colors" aria-label="Menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square">
                            <path d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <router-link to="/" class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-maroon flex items-center justify-center text-putih text-sm font-bold">A</span>
                        <span class="text-xl lg:text-2xl font-semibold tracking-tight text-charcoal">aliesmo</span>
                    </router-link>

                    <nav class="hidden lg:flex items-center gap-6">
                        <router-link to="/" class="text-sm font-medium text-charcoal/70 hover:text-maroon transition-colors">Beranda</router-link>
                        <router-link to="/?shop=1" class="text-sm font-medium text-charcoal/70 hover:text-maroon transition-colors">Koleksi</router-link>
                    </nav>

                    <div class="flex items-center gap-3">
                        <router-link v-if="!isLoggedIn" to="/login" class="hidden sm:inline-block text-sm font-medium text-charcoal/60 hover:text-maroon transition-colors">Masuk</router-link>
                        <button v-if="isLoggedIn" @click="handleLogout" class="hidden sm:inline-block text-sm font-medium text-charcoal/60 hover:text-maroon transition-colors">Keluar</button>
                        <router-link to="/cart" class="relative p-2 text-charcoal/70 hover:text-maroon transition-colors" aria-label="Cart">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <path d="M16 10a4 4 0 01-8 0"/>
                            </svg>
                            <span v-if="cartCount" class="absolute -top-0.5 -right-0.5 bg-maroon text-putih text-[10px] font-bold rounded-full w-4.5 h-4.5 flex items-center justify-center leading-none">{{ cartCount > 9 ? '9+' : cartCount }}</span>
                        </router-link>
                    </div>
                </div>
            </div>

            <Transition name="mobile-nav">
                <div v-if="mobileOpen" class="lg:hidden border-t border-maroon-100/50 bg-putih">
                    <nav class="px-4 py-6 space-y-4">
                        <router-link @click="mobileOpen = false" to="/" class="block text-sm font-medium text-charcoal/70 hover:text-maroon">Beranda</router-link>
                        <router-link @click="mobileOpen = false" to="/?shop=1" class="block text-sm font-medium text-charcoal/70 hover:text-maroon">Koleksi</router-link>
                        <router-link v-if="!isLoggedIn" @click="mobileOpen = false" to="/login" class="block text-sm font-medium text-charcoal/70 hover:text-maroon">Masuk</router-link>
                        <button v-if="isLoggedIn" @click="handleLogout; mobileOpen = false" class="block text-sm font-medium text-charcoal/70 hover:text-maroon">Keluar</button>
                    </nav>
                </div>
            </Transition>
        </header>

        <main class="pt-16 lg:pt-20">
            <router-view />
        </main>

        <footer class="bg-charcoal text-putih">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-maroon flex items-center justify-center text-putih text-sm font-bold">A</span>
                            <span class="text-xl font-semibold tracking-tight text-putih">aliesmo</span>
                        </div>
                        <p class="mt-3 text-sm text-putih/60 leading-relaxed max-w-xs">Kemeja batik dan casual berkualitas. Nyaman dipakai, bangga dengan budaya Indonesia.</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-maroon-200 mb-4">Menu</h4>
                        <ul class="space-y-2">
                            <li><router-link to="/" class="text-sm text-putih/60 hover:text-putih transition-colors">Beranda</router-link></li>
                            <li><router-link to="/?shop=1" class="text-sm text-putih/60 hover:text-putih transition-colors">Koleksi</router-link></li>
                            <li><router-link to="/cart" class="text-sm text-putih/60 hover:text-putih transition-colors">Keranjang</router-link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-maroon-200 mb-4">Kontak</h4>
                        <ul class="space-y-2 text-sm text-putih/60">
                            <li>hello@aliesmo.com</li>
                            <li>+62 812 3456 7890</li>
                            <li>Surakarta, Indonesia</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-putih/10 text-center text-sm text-putih/40">
                    &copy; {{ new Date().getFullYear() }} aliesmo. Terinspirasi dari kekayaan batik Nusantara.
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from './cart'
import api, { clearToken } from './api'

const router = useRouter()
const { items } = useCartStore()
const mobileOpen = ref(false)
const isLoggedIn = ref(false)

const cartCount = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0))

onMounted(() => {
    isLoggedIn.value = !!localStorage.getItem('token')
})

async function handleLogout() {
    try { await api.post('/auth/logout') } catch (e) {}
    clearToken()
    isLoggedIn.value = false
    router.push('/')
}
</script>

<style scoped>
.mobile-nav-enter-active,
.mobile-nav-leave-active {
    transition: all 0.25s ease;
}
.mobile-nav-enter-from,
.mobile-nav-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
