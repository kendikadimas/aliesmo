<template>
    <div class="min-h-screen bg-putih">
        <!-- Promo / Announcement Bar -->
        <div class="bg-zinc-50 border-b border-zinc-200/50 text-[11px] font-semibold text-zinc-600">
            <div class="max-w-7xl mx-auto px-4 py-2">
                <!-- Desktop View (3 columns) -->
                <div class="hidden lg:grid gap-4 text-center" :class="promos.length === 2 ? 'grid-cols-2' : 'grid-cols-3'">
                    <router-link
                        v-for="(promo, index) in promos"
                        :key="index"
                        :to="promo.link"
                        class="hover:text-maroon transition-colors flex items-center justify-center gap-1.5"
                        :class="index > 0 && index < promos.length ? 'border-l border-zinc-200' : ''"
                    >
                        <svg v-if="index === 0" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                        <svg v-else-if="index === 1" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                        {{ promo.text }}
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
        <header class="sticky top-0 z-50 bg-putih/95 backdrop-blur-md border-b border-zinc-200/60 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Middle Row: Logo, Search, Navigation Icons -->
                <div class="relative flex items-center justify-between h-16 lg:h-[72px]">
                    <!-- Left: Hamburger & Logo -->
                    <div class="flex items-center gap-3 z-10">
                        <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 -ml-2 text-charcoal hover:text-maroon transition-colors" aria-label="Menu">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square">
                                <path d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <router-link to="/" class="flex items-center">
                            <img src="/horizontal.svg" alt="Aliesmo" class="h-8 sm:h-9 lg:h-10 w-auto object-contain" />
                        </router-link>
                    </div>

                    <!-- Middle: Search Input (Desktop) — absolutely centered -->
                    <form @submit.prevent="handleSearch" class="absolute left-1/2 -translate-x-1/2 w-full max-w-md hidden lg:block">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari kemeja, batik, atau koleksi..."
                            class="w-full pl-5 pr-14 py-2.5 rounded-full border border-zinc-200 focus:outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/10 text-sm transition-all bg-zinc-50/20"
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
                    <div class="flex items-center gap-2 lg:gap-4 z-10">
                        <!-- Auth Link (Desktop) -->
                        <div class="hidden md:flex items-center gap-1.5 mr-2">
                            <router-link v-if="isLoggedIn" to="/profile" class="p-2 text-charcoal/70 hover:text-maroon transition-colors" aria-label="Profil akun">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                            </router-link>
                            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-charcoal/70">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <router-link v-if="!isLoggedIn" to="/login" class="text-[13px] font-semibold text-charcoal/80 hover:text-maroon transition-colors">Masuk / Daftar</router-link>
                        </div>

                        <!-- Wishlist Heart -->
                        <button @click="showToast('Fitur Favorit akan segera hadir!', 'wishlist')" class="p-2 text-charcoal/70 hover:text-maroon transition-colors relative cursor-pointer" aria-label="Wishlist">
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
                            class="w-full pl-5 pr-12 py-2 rounded-full border border-zinc-200 focus:outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/10 text-xs transition-all bg-zinc-50/20"
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
            <div class="border-t border-zinc-100 hidden lg:block bg-white">
                <div class="max-w-7xl mx-auto px-8">
                    <nav class="flex items-center justify-center gap-8 h-10">
                        <router-link
                            to="/catalog"
                            class="text-xs font-bold tracking-wider text-charcoal/70 hover:text-maroon hover:border-b-2 hover:border-maroon transition-all py-2.5 uppercase"
                            :class="{ 'border-b-2 border-maroon text-maroon font-bold': $route.path === '/catalog' && !$route.params.slug }"
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
            <div v-if="mobileOpen" class="lg:hidden border-t border-zinc-200/50 bg-putih shadow-xl fixed top-[110px] left-0 right-0 z-40 max-h-[calc(100vh-110px)] overflow-y-auto">
                <nav class="px-5 py-6 space-y-5">
                    <router-link @click="mobileOpen = false" to="/catalog" class="block text-sm font-bold text-charcoal/80 hover:text-maroon uppercase tracking-wide">Semua Koleksi</router-link>
                    
                    <div class="h-[1px] bg-zinc-100"></div>
                    
                    <div>
                        <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-3 px-1">Kategori</p>
                        <div class="grid grid-cols-2 gap-3">
                            <router-link
                                v-for="cat in categories"
                                :key="cat.slug"
                                @click="mobileOpen = false"
                                :to="`/catalog/${cat.slug}`"
                                class="block text-xs font-semibold text-charcoal/70 hover:text-maroon bg-zinc-50 hover:bg-maroon-50/30 px-3 py-2.5 rounded-lg uppercase tracking-wider text-center transition-all border border-zinc-100"
                            >
                                {{ cat.name }}
                            </router-link>
                        </div>
                    </div>
                    
                    <div class="h-[1px] bg-zinc-100"></div>
                    
                    <div class="space-y-3 pb-4">
                        <router-link v-if="!isLoggedIn" @click="mobileOpen = false" to="/login" class="block text-center text-xs font-bold text-white bg-maroon py-3 rounded-lg uppercase tracking-wider transition-colors hover:bg-maroon-600">Masuk / Daftar</router-link>
                        <router-link v-else @click="mobileOpen = false" to="/profile" class="block w-full text-center text-xs font-bold text-white bg-charcoal py-3 rounded-lg uppercase tracking-wider">Profil Akun</router-link>
                    </div>
                </nav>
            </div>
        </Transition>

        <!-- Main Content (Removed fixed height padding, sits naturally below header) -->
        <main class="min-h-[70vh]">
            <router-view />
        </main>

        <footer class="bg-charcoal text-putih">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                    <div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                            <img src="/aliesmo-logo.svg" alt="Aliesmo" class="h-12 sm:h-16 w-auto object-contain brightness-0 invert shrink-0" />
                            <p class="text-sm text-putih/60 leading-relaxed">Kemeja batik dan casual berkualitas. Nyaman dipakai, bangga dengan budaya Indonesia.</p>
                        </div>
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
                            <li>{{ contactEmail }}</li>
                            <li>{{ contactPhone }}</li>
                            <li>{{ contactAddress }}</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-putih/10 text-center text-sm text-putih/40">
                    &copy; {{ new Date().getFullYear() }} aliesmo. Terinspirasi dari kekayaan batik Nusantara.
                </div>
            </div>
        </footer>

        <!-- Floating WhatsApp Button -->
        <a
            :href="`https://wa.me/${waNumber}?text=${encodeURIComponent('Halo Admin Aliesmo, saya ingin bertanya tentang produk kemeja.')}`"
            target="_blank"
            rel="noopener noreferrer"
            class="fixed bottom-6 right-6 z-[98] w-14 h-14 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-lg shadow-green-500/40 flex items-center justify-center transition-all hover:scale-110 active:scale-95"
            aria-label="Chat WhatsApp"
        >
            <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>

        <!-- Toast Notification -->
        <Transition name="toast">
            <div v-if="toastState.visible" class="fixed top-20 left-1/2 -translate-x-1/2 z-[99] w-[calc(100%-2rem)] max-w-sm" :class="toastState.type === 'error' ? 'bg-ink-60' : 'bg-charcoal'">
                <div class="text-white text-xs px-4 py-3.5 rounded-xl shadow-2xl flex items-center gap-2.5 border border-white/10">
                    <template v-if="toastState.type === 'success'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-white/70" stroke-linecap="square"><polyline points="20 6 9 17 4 12"/></svg>
                    </template>
                    <template v-else-if="toastState.type === 'error'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-white/70" stroke-linecap="square"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </template>
                    <template v-else>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" class="text-white/70" stroke="currentColor" stroke-width="2"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    </template>
                    <span>{{ toastState.message }}</span>
                </div>
            </div>
        </Transition>

        <!-- Fly-to-cart animation -->
        <div v-for="anim in flyingItems" :key="anim.id" class="fly-to-cart-item" :style="anim.style">
            <img v-if="anim.thumbnail" :src="anim.thumbnail" class="w-full h-full object-cover rounded-lg" />
            <div v-else class="w-full h-full rounded-lg bg-maroon flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCartStore } from './cart'
import { useToast } from './toast'
import { onCartAnimation } from './cart'
import api, { clearToken } from './api'

const router = useRouter()
const route = useRoute()
const { items } = useCartStore()
const mobileOpen = ref(false)
const isLoggedIn = ref(false)
const categories = ref([]) // dari API, bukan mock-data

// Contact info dari API settings
const contactEmail = ref('hello@aliesmo.com')
const contactPhone = ref('+62 851-9681-1722')
const contactAddress = ref('Ulujami, Pemalang, Jawa Tengah')
const waNumber = ref('6285196811722')

const cartCount = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0))

// Fly-to-cart animation
const flyingItems = ref([])
let animId = 0

function triggerFlyToCart({ thumbnail, sourceRect }) {
    // Cari posisi icon cart di DOM
    const cartIcon = document.querySelector('[aria-label="Cart"]')
    if (!cartIcon) return
    const cartRect = cartIcon.getBoundingClientRect()
    const targetX = cartRect.left + cartRect.width / 2
    const targetY = cartRect.top + cartRect.height / 2

    // Posisi awal: dari sourceRect jika ada, atau tengah layar
    const startX = sourceRect ? sourceRect.left + sourceRect.width / 2 - 30 : window.innerWidth / 2 - 30
    const startY = sourceRect ? sourceRect.top + sourceRect.height / 2 - 30 : window.innerHeight / 2 - 30

    const id = ++animId
    flyingItems.value.push({
        id,
        thumbnail,
        style: {
            position: 'fixed',
            left: startX + 'px',
            top: startY + 'px',
            width: '60px',
            height: '60px',
            zIndex: 9999,
            borderRadius: '8px',
            overflow: 'hidden',
            pointerEvents: 'none',
            transition: 'all 0.65s cubic-bezier(0.25, 0.46, 0.45, 0.94)',
            boxShadow: '0 4px 20px rgba(0,0,0,0.3)',
            opacity: '1',
        },
    })

    // Mulai animasi setelah frame berikutnya
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            const item = flyingItems.value.find(i => i.id === id)
            if (!item) return
            item.style = {
                ...item.style,
                left: (targetX - 15) + 'px',
                top: (targetY - 15) + 'px',
                width: '30px',
                height: '30px',
                opacity: '0.2',
                borderRadius: '50%',
            }
            // Hapus setelah animasi selesai
            setTimeout(() => {
                flyingItems.value = flyingItems.value.filter(i => i.id !== id)
            }, 700)
        })
    })
}

// Search Query sync with Route
const searchQuery = ref('')
watch(() => route.query.search, (newSearch) => {
    searchQuery.value = newSearch || ''
}, { immediate: true })

// Promo Carousel for Mobile
const activePromo = ref(0)
const promos = ref([
    { text: 'Gratis Ongkir Seluruh Indonesia | Min. Belanja Rp 200rb', link: '/?shop=1' },
    { text: 'Bahan Premium Oxford & Linen | Garansi 30 Hari', link: '/?shop=1' },
    { text: 'Diskon 10% First Order | Kode: ALIESNEW10', link: '/login' }
])

const { state: toastState, show: showToast } = useToast()

async function loadAnnouncements() {
    try {
        const res = await api.get('/settings/group/announcement')
        const d = res.data.data
        const loaded = []
        for (let i = 1; i <= 3; i++) {
            const text = d[`announcement_${i}`]
            const link = d[`announcement_${i}_link`] || '/?shop=1'
            if (text) loaded.push({ text, link })
        }
        if (loaded.length) promos.value = loaded
    } catch (e) {
        // fallback ke default sudah di-set di ref
    }
}

async function loadCategories() {
    try {
        const res = await api.get('/categories')
        const data = res.data.data || res.data
        if (Array.isArray(data) && data.length) categories.value = data
    } catch (e) {
        // fallback tetap array kosong — navbar tetap tampil tanpa kategori
    }
}

async function loadContactSettings() {
    try {
        const res = await api.get('/settings/group/general')
        const d = res.data.data || {}
        if (d.contact_email)   contactEmail.value   = d.contact_email
        if (d.contact_phone)   contactPhone.value   = d.contact_phone
        if (d.contact_address) contactAddress.value = d.contact_address
        if (d.whatsapp_number) waNumber.value       = d.whatsapp_number
    } catch (e) {
        // fallback ke default
    }
}

onMounted(() => {
    isLoggedIn.value = !!localStorage.getItem('token')
    const authMessage = sessionStorage.getItem('auth:success')
    if (authMessage) {
        showToast(authMessage)
        sessionStorage.removeItem('auth:success')
    }
    loadAnnouncements()
    loadCategories()
    loadContactSettings()
    setInterval(() => {
        activePromo.value = (activePromo.value + 1) % promos.value.length
    }, 4000)
    onCartAnimation(triggerFlyToCart)
})

function handleSearch() {
    router.push({ path: '/', query: { ...route.query, search: searchQuery.value || undefined } })
}

async function handleLogout() {
    try { await api.post('/auth/logout') } catch (e) {}
    clearToken()
    isLoggedIn.value = false
    showToast('Berhasil keluar dari akun.')
    router.push('/')
}

function triggerMobileLogout() {
    handleLogout()
    mobileOpen.value = false
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
    transform: translate(-50%, -12px) scale(0.95);
    opacity: 0;
}
.toast-leave-to {
    transform: translate(-50%, -12px) scale(0.95);
    opacity: 0;
}
</style>
