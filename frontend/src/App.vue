<template>
    <div class="min-h-screen bg-paper dark:bg-ink transition-colors duration-200">
        <!-- Promo / Announcement Bar -->
        <div class="bg-ink dark:bg-ink-80 border-b border-white/5 text-[10px] font-medium tracking-[0.18em] text-white/40 uppercase">
            <div class="max-w-7xl mx-auto px-4 py-2">
                <!-- Desktop View (3 columns) -->
                <div class="hidden lg:grid grid-cols-3 gap-4 text-center">
                    <router-link to="/?shop=1" class="hover:text-maroon transition-colors flex items-center justify-center gap-1.5">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                        Gratis Ongkir Seluruh Indonesia | Min. Belanja Rp 200rb
                    </router-link>
                    <router-link to="/?shop=1" class="hover:text-maroon transition-colors flex items-center justify-center gap-1.5 border-x border-zinc-200">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        Bahan Premium Oxford & Linen | Garansi 30 Hari
                    </router-link>
                    <router-link to="/login" class="hover:text-maroon transition-colors flex items-center justify-center gap-1.5">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                        Diskon 10% First Order | Kode: ALIESNEW10
                    </router-link>
                </div>
                <!-- Mobile/Tablet View (Rotating Carousel) -->
                <div class="lg:hidden text-center relative h-4 overflow-hidden flex items-center justify-center">
                    <TransitionGroup name="slide-up">
                        <div v-for="(promo, index) in promos" :key="index" v-show="activePromo === index" class="absolute w-full flex items-center justify-center gap-1.5">
                            <span v-if="index === 0" class="flex items-center">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                            </span>
                            <span v-else-if="index === 1" class="flex items-center">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </span>
                            <span v-else class="flex items-center">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            </span>
                            <router-link :to="promo.link" class="hover:text-maroon transition-colors">{{ promo.text }}</router-link>
                        </div>
                    </TransitionGroup>
                </div>
            </div>
        </div>

        <!-- Sticky Header (Main Row + Category Row) -->
        <header class="sticky top-0 z-50 bg-white/95 dark:bg-ink-90/95 backdrop-blur-md border-b border-zinc-200/60 dark:border-ink-80/60 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Middle Row: Logo, Search, Navigation Icons -->
                <div class="flex items-center justify-between h-16 lg:h-[72px]">
                    <!-- Left: Hamburger & Logo -->
                    <div class="flex items-center gap-3">
                        <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 -ml-2 text-charcoal dark:text-slate-300 hover:text-maroon transition-colors" aria-label="Menu">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square">
                                <path d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <router-link to="/" class="flex items-center">
                            <span class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-[0.22em] text-charcoal dark:text-slate-100 font-serif hover:text-maroon transition-colors">ALIESMO</span>
                        </router-link>
                    </div>

                    <!-- Middle: Search Input (Desktop) -->
                    <form @submit.prevent="handleSearch" class="relative flex-1 max-w-xl mx-8 hidden lg:block">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari kemeja, batik, atau koleksi..."
                            class="w-full pl-5 pr-14 py-2.5 rounded-full border border-zinc-200 dark:border-slate-600 focus:outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/10 text-sm transition-all bg-zinc-50/20 dark:bg-slate-800 dark:text-slate-200 dark:placeholder-slate-400"
                        />
                        <button
                            type="submit"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-charcoal hover:bg-maroon text-white flex items-center justify-center transition-colors"
                            aria-label="Cari"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </button>
                    </form>

                    <!-- Right: Auth, Wishlist, Cart -->
                    <div class="flex items-center gap-2 lg:gap-4">
                        <!-- Pesanan Saya (Desktop, hanya jika login) -->
                        <router-link v-if="isLoggedIn" to="/orders" class="hidden lg:block text-[13px] font-semibold text-charcoal/80 hover:text-maroon transition-colors">Pesanan</router-link>

                        <!-- Profile (Desktop, hanya jika login) -->
                        <router-link v-if="isLoggedIn" to="/profile" class="hidden lg:block text-[13px] font-semibold text-charcoal/80 hover:text-maroon transition-colors">Profil</router-link>

                        <!-- Auth Link (Desktop) -->
                        <div class="hidden md:flex items-center gap-1.5 mr-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-charcoal/70">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <router-link v-if="!isLoggedIn" to="/login" class="text-[13px] font-semibold text-charcoal/80 hover:text-maroon transition-colors">Masuk / Daftar</router-link>
                            <button v-else @click="handleLogout" class="text-[13px] font-semibold text-charcoal/80 hover:text-maroon transition-colors cursor-pointer">Keluar</button>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button @click="toggle" class="p-2 text-charcoal/70 dark:text-slate-400 hover:text-maroon dark:hover:text-maroon transition-colors" :aria-label="isDark ? 'Light mode' : 'Dark mode'">
                            <!-- Sun icon (shown in dark mode) -->
                            <svg v-if="isDark" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                            </svg>
                            <!-- Moon icon (shown in light mode) -->
                            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                        </button>

                        <!-- Wishlist Heart -->
                        <button @click="showWishlistToast" class="p-2 text-charcoal/70 hover:text-maroon transition-colors relative cursor-pointer" aria-label="Wishlist">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                        </button>

                        <!-- Cart Bag -->
                        <router-link to="/cart" class="relative p-2 text-charcoal/70 hover:text-maroon transition-colors" aria-label="Cart">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                            <span v-if="cartCount" class="absolute -top-0.5 -right-0.5 bg-maroon text-putih text-[9px] font-bold rounded-full w-4.5 h-4.5 flex items-center justify-center leading-none">{{ cartCount > 9 ? '9+' : cartCount }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Mobile Search Input (Visible only on < lg screens) -->
                <div class="pb-3 lg:hidden">
                    <form @submit.prevent="handleSearch" class="relative w-full">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari kemeja, batik, atau koleksi..."
                            class="w-full pl-5 pr-12 py-2 rounded-full border border-zinc-200 dark:border-slate-600 focus:outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/10 text-xs transition-all bg-zinc-50/20 dark:bg-slate-800 dark:text-slate-200 dark:placeholder-slate-400"
                        />
                        <button
                            type="submit"
                            class="absolute right-1 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-charcoal hover:bg-maroon text-white flex items-center justify-center transition-colors"
                            aria-label="Cari"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bottom Row: Category Navigation (Desktop only) -->
            <div class="border-t border-zinc-100 dark:border-slate-700/60 hidden lg:block bg-white dark:bg-slate-900">
                <div class="max-w-7xl mx-auto px-8">
                    <nav class="flex items-center justify-center gap-8 h-10">
                        <router-link
                            to="/"
                            class="text-xs font-bold tracking-wider text-charcoal/70 dark:text-slate-400 hover:text-maroon dark:hover:text-maroon hover:border-b-2 hover:border-maroon transition-all py-2.5 uppercase"
                            :class="{ 'border-b-2 border-maroon text-maroon font-bold': $route.path === '/' }"
                        >
                            Semua
                        </router-link>
                        <router-link
                            v-for="cat in categories"
                            :key="cat.slug"
                            :to="`/catalog/${cat.slug}`"
                            class="text-xs font-bold tracking-wider text-charcoal/70 hover:text-maroon hover:border-b-2 hover:border-maroon transition-all py-2.5 uppercase"
                            :class="{ 'border-b-2 border-maroon text-maroon font-bold': $route.params.slug === cat.slug }"
                        >
                            {{ cat.name }}
                        </router-link>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Mobile Navigation Drawer -->
        <Transition name="mobile-nav">
            <div v-if="mobileOpen" class="lg:hidden border-t border-zinc-200/50 dark:border-slate-700/50 bg-white dark:bg-slate-900 shadow-xl fixed top-[110px] left-0 right-0 z-40 max-h-[calc(100vh-110px)] overflow-y-auto">
                <nav class="px-5 py-6 space-y-5">
                    <router-link @click="mobileOpen = false" to="/" class="block text-sm font-bold text-charcoal/80 dark:text-slate-300 hover:text-maroon uppercase tracking-wide">Semua Koleksi</router-link>
                    <router-link v-if="isLoggedIn" @click="mobileOpen = false" to="/orders" class="block text-sm font-bold text-charcoal/80 dark:text-slate-300 hover:text-maroon uppercase tracking-wide">Pesanan Saya</router-link>
                    <router-link v-if="isLoggedIn" @click="mobileOpen = false" to="/profile" class="block text-sm font-bold text-charcoal/80 dark:text-slate-300 hover:text-maroon uppercase tracking-wide">Profil</router-link>
                    
                    <div class="h-[1px] bg-zinc-100 dark:bg-slate-700"></div>
                    
                    <div>
                        <p class="text-[10px] font-bold text-zinc-400 dark:text-slate-500 uppercase tracking-widest mb-3 px-1">Kategori</p>
                        <div class="grid grid-cols-2 gap-3">
                            <router-link
                                v-for="cat in categories"
                                :key="cat.slug"
                                @click="mobileOpen = false"
                                :to="`/catalog/${cat.slug}`"
                                class="block text-xs font-semibold text-charcoal/70 dark:text-slate-400 hover:text-maroon bg-zinc-50 dark:bg-slate-800 hover:bg-maroon-50/30 px-3 py-2.5 rounded-lg uppercase tracking-wider text-center transition-all border border-zinc-100 dark:border-slate-700"
                            >
                                {{ cat.name }}
                            </router-link>
                        </div>
                    </div>
                    
                    <div class="h-[1px] bg-zinc-100 dark:bg-slate-700"></div>
                    
                    <!-- Dark Mode Toggle (Mobile) -->
                    <button @click="toggle" class="flex items-center gap-3 text-sm font-bold text-charcoal/80 dark:text-slate-300 hover:text-maroon uppercase tracking-wide w-full">
                        <svg v-if="isDark" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                        {{ isDark ? 'Mode Terang' : 'Mode Gelap' }}
                    </button>

                    <div class="space-y-3 pb-4">
                        <router-link v-if="!isLoggedIn" @click="mobileOpen = false" to="/login" class="block text-center text-xs font-bold text-white bg-maroon py-3 rounded-lg uppercase tracking-wider transition-colors hover:bg-maroon-600">Masuk / Daftar</router-link>
                        <button v-else @click="triggerMobileLogout" class="block w-full text-center text-xs font-bold text-white bg-charcoal dark:bg-slate-700 py-3 rounded-lg uppercase tracking-wider cursor-pointer">Keluar</button>
                    </div>
                </nav>
            </div>
        </Transition>

        <!-- Main Content -->
        <main class="min-h-[70vh] bg-white dark:bg-slate-900 transition-colors duration-200">
            <router-view />
        </main>

        <footer class="bg-slate-900 dark:bg-slate-950 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-bold tracking-[0.15em] text-putih font-serif">ALIESMO</span>
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
                        <h4 class="text-sm font-semibold text-maroon-200 mb-4">Informasi</h4>
                        <ul class="space-y-2">
                            <li><router-link to="/track-order" class="text-sm text-putih/60 hover:text-putih transition-colors">Lacak Pesanan</router-link></li>
                            <li><router-link to="/shipping-info" class="text-sm text-putih/60 hover:text-putih transition-colors">Info Pengiriman</router-link></li>
                            <li><router-link to="/size-guide" class="text-sm text-putih/60 hover:text-putih transition-colors">Panduan Ukuran</router-link></li>
                            <li><router-link to="/terms" class="text-sm text-putih/60 hover:text-putih transition-colors">Syarat & Ketentuan</router-link></li>
                            <li><router-link to="/privacy" class="text-sm text-putih/60 hover:text-putih transition-colors">Kebijakan Privasi</router-link></li>
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

        <!-- Wishlist Toast Notification -->
        <Transition name="toast">
            <div v-if="showToast" class="fixed bottom-5 right-5 bg-charcoal text-white text-xs px-4 py-3.5 rounded-xl shadow-2xl z-[99] flex items-center gap-2.5 border border-white/10">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" class="text-maroon" stroke="currentColor" stroke-width="2"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                <span>{{ toastMessage }}</span>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCartStore } from './cart'
import api, { clearToken } from './api'
import { useDarkMode } from './useDarkMode'

const router = useRouter()
const route = useRoute()
const { items } = useCartStore()
const { isDark, toggle, init } = useDarkMode()
const mobileOpen = ref(false)
const isLoggedIn = ref(false)
const categories = ref([])

const cartCount = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0))

// Search Query sync with Route
const searchQuery = ref('')
watch(() => route.query.search, (newSearch) => {
    searchQuery.value = newSearch || ''
}, { immediate: true })

// Promo Carousel for Mobile
const activePromo = ref(0)
let promoTimer = null
const promos = [
    { text: 'Gratis Ongkir Seluruh Indonesia | Min. Belanja Rp 200rb', link: '/?shop=1' },
    { text: 'Bahan Premium Oxford & Linen | Garansi 30 Hari', link: '/?shop=1' },
    { text: 'Diskon 10% First Order | Kode: ALIESNEW10', link: '/login' }
]

// Wishlist Toast state
const showToast = ref(false)
const toastMessage = ref('')

onMounted(async () => {
    // Init dark mode from localStorage / system preference
    init()

    isLoggedIn.value = !!localStorage.getItem('token')

    // Sync isLoggedIn jika token berubah di tab lain (logout-all, dll)
    window.addEventListener('storage', (e) => {
        if (e.key === 'token') {
            isLoggedIn.value = !!e.newValue
        }
    })

    // Sync isLoggedIn saat auth events dari api.js interceptor
    window.addEventListener('auth:expired', () => {
        isLoggedIn.value = false
    })
    window.addEventListener('auth:unverified', () => {
        // Tetap login, hanya redirect — isLoggedIn tidak perlu diubah
    })

    // Auto-scroll promos on mobile
    promoTimer = setInterval(() => {
        activePromo.value = (activePromo.value + 1) % promos.length
    }, 4000)

    // Fetch categories dari API
    try {
        const res = await api.get('/categories')
        categories.value = res.data.data || res.data
    } catch (e) {
        console.error('Failed to fetch categories:', e)
    }
})

function showWishlistToast() {
    toastMessage.value = 'Fitur Favorit/Wishlist akan segera hadir!'
    showToast.value = true
    setTimeout(() => {
        showToast.value = false
    }, 3000)
}

function handleSearch() {
    router.push({ path: '/', query: { ...route.query, search: searchQuery.value || undefined } })
}

async function handleLogout() {
    try { await api.post('/auth/logout') } catch (e) {}
    clearToken()
    isLoggedIn.value = false
    router.push('/')
}

async function handleLogoutAll() {
    try { await api.post('/auth/logout-all') } catch (e) {}
    clearToken()
    isLoggedIn.value = false
    router.push('/')
}

function triggerMobileLogout() {
    handleLogout()
    mobileOpen.value = false
}

onUnmounted(() => {
    if (promoTimer) clearInterval(promoTimer)
})
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

/* Slide up animation for promo bar */
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.5s ease;
}
.slide-up-enter-from {
    transform: translateY(100%);
    opacity: 0;
}
.slide-up-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}

/* Toast Slide and Fade */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.toast-enter-from {
    transform: translateY(20px) scale(0.9);
    opacity: 0;
}
.toast-leave-to {
    transform: translateY(20px) scale(0.9);
    opacity: 0;
}
</style>

