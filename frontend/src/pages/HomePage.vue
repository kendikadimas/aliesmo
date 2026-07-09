<template>
    <div>
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8">
            <div class="relative rounded-2xl overflow-hidden shadow-lg">
                <div class="relative flex transition-all duration-500" :style="{ transform: `translateX(-${activeSlide * 100}%)` }">
                    <div v-for="banner in banners" :key="banner.id"
                        class="w-full shrink-0 aspect-[2/1] sm:aspect-[3/1]">
                        <img :src="banner.image_url" :alt="banner.title" class="w-full h-full object-cover block" />
                    </div>
                </div>
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2.5 z-20">
                    <button v-for="(_, i) in banners" :key="i" @click="activeSlide = i" class="h-2 rounded-full transition-all" :class="activeSlide === i ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/70'"></button>
                </div>
                <button @click="prevSlide" class="absolute top-1/2 -translate-y-1/2 left-3 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow hover:bg-white transition-all">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button @click="nextSlide" class="absolute top-1/2 -translate-y-1/2 right-3 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow hover:bg-white transition-all">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </section>

        <section class="py-6 lg:py-8 bg-coklat-50/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-8">
                    <div class="text-center">
                        <p class="text-3xl lg:text-4xl font-bold text-maroon">{{ get('stat_kemeja_terjual', '12.000+') }}</p>
                        <p class="text-sm text-charcoal/60 mt-1">{{ get('stat_kemeja_terjual_label', 'Kemeja Terjual') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl lg:text-4xl font-bold text-maroon">{{ get('stat_kota', '15+') }}</p>
                        <p class="text-sm text-charcoal/60 mt-1">{{ get('stat_kota_label', 'Kota di Indonesia') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl lg:text-4xl font-bold text-maroon">{{ get('stat_kualitas', '100%') }}</p>
                        <p class="text-sm text-charcoal/60 mt-1">{{ get('stat_kualitas_label', 'Kualitas Original') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl lg:text-4xl font-bold text-maroon">{{ get('stat_garansi', '30') }}</p>
                        <p class="text-sm text-charcoal/60 mt-1">{{ get('stat_garansi_label', 'Hari Garansi') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-12 lg:py-16 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-slate-100 tracking-tight">Collection</h2>
                        <p class="mt-1 text-sm text-charcoal/50 dark:text-slate-400">Temukan koleksi yang cocok untukmu</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-5">
                    <div v-for="cat in categoriesList" :key="cat.id" @click="$router.push(`/catalog/${cat.slug}`)" class="group cursor-pointer">
                        <div class="aspect-[21/9] bg-maroon-50 rounded-xl overflow-hidden relative">
                            <img v-if="cat.image_url" :src="cat.image_url" :alt="cat.name" class="w-full h-full object-cover group-hover:scale-[1.06] transition-transform duration-500" />
                            <div v-else class="w-full h-full bg-gradient-to-br from-maroon-50 to-maroon-100 dark:from-slate-700 dark:to-slate-600 group-hover:scale-[1.06] transition-transform duration-500"></div>
                            <div class="absolute inset-0 bg-charcoal/30 group-hover:bg-charcoal/40 transition-colors flex items-center justify-center p-3">
                                <p class="text-xs sm:text-sm font-extrabold text-white tracking-[0.12em] uppercase drop-shadow-[0_2px_4px_rgba(0,0,0,0.4)] group-hover:scale-105 transition-transform duration-300 text-center">{{ cat.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="shop" class="py-12 lg:py-16 bg-coklat-50/20 dark:bg-slate-800/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-slate-100 tracking-tight">Semua <span class="text-maroon">Produk</span></h2>
                        <p class="mt-1 text-sm text-charcoal/50 dark:text-slate-400">Temukan kemeja favoritmu</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-6">
                    <button @click="$router.push('/catalog')" class="px-4 py-2 text-xs font-semibold rounded-xl border-2 transition-all bg-white dark:bg-slate-800 text-charcoal/60 dark:text-slate-400 border-maroon-100 dark:border-slate-600 hover:border-maroon/50">Semua</button>
                    <button v-for="cat in categoriesList" :key="cat.id" @click="$router.push(`/catalog/${cat.slug}`)" class="px-4 py-2 text-xs font-semibold rounded-xl border-2 transition-all bg-white dark:bg-slate-800 text-charcoal/60 dark:text-slate-400 border-maroon-100 dark:border-slate-600 hover:border-maroon/50">{{ cat.name }}</button>
                </div>

                <div v-if="loading" class="space-y-10">
                    <div v-for="n in 3" :key="n">
                        <div class="h-5 bg-coklat-100/50 rounded-full w-1/4 mb-4"></div>
                        <div class="flex gap-3 overflow-hidden">
                            <div v-for="m in 4" :key="m" class="shrink-0 w-[160px] sm:w-[180px] animate-pulse">
                                <div class="aspect-[3/4] bg-coklat-100/50 dark:bg-slate-700/50 rounded-xl"></div>
                                <div class="mt-2 space-y-1.5 px-1">
                                    <div class="h-2 bg-coklat-100/50 dark:bg-slate-700/50 rounded-full w-1/3"></div>
                                    <div class="h-2.5 bg-coklat-100/50 dark:bg-slate-700/50 rounded-full w-2/3"></div>
                                    <div class="h-2.5 bg-coklat-100/50 dark:bg-slate-700/50 rounded-full w-1/4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else-if="!filteredProducts.length && !loading" class="text-center py-16">
                    <p class="text-lg text-charcoal/50 dark:text-slate-400">Belum ada produk nih, coba kategori lain yuk!</p>
                </div>

                <div v-else class="space-y-10">
                    <div v-for="group in groupedProducts" :key="group.category.slug">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-charcoal dark:text-slate-100">{{ group.category.name }}</h3>
                                <p class="text-xs text-charcoal/50 dark:text-slate-400">{{ group.products.length }} produk</p>
                            </div>
                            <div class="flex gap-2">
                                <button @click="scrollCarousel(group.category.slug, 'left')" class="w-8 h-8 rounded-lg border border-maroon-200/60 flex items-center justify-center text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="15 18 9 12 15 6"/></svg>
                                </button>
                                <button @click="scrollCarousel(group.category.slug, 'right')" class="w-8 h-8 rounded-lg border border-maroon-200/60 flex items-center justify-center text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="9 18 15 12 9 6"/></svg>
                                </button>
                            </div>
                        </div>

                        <div :data-carousel="group.category.slug" class="flex gap-4 sm:gap-5 overflow-x-auto scroll-smooth pb-2" style="scrollbar-width:none;-ms-overflow-style:none;">
                            <div v-for="product in group.products" :key="product.id" class="shrink-0 w-[200px] sm:w-[230px] group/card cursor-pointer bg-white dark:bg-slate-800 rounded-xl overflow-hidden border border-maroon-50 dark:border-slate-700 hover:border-maroon-200 dark:hover:border-slate-500 transition-all hover:shadow-md active:scale-[0.98]" @click="$router.push(`/products/${product.slug}`)">
                                <div class="aspect-[3/4] bg-maroon-50 overflow-hidden relative">
                                    <img :src="productImage(product, 0)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover/card:opacity-0" />
                                    <img :src="productImage(product, 1)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 opacity-0 group-hover/card:opacity-100" />
                                    <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-1.5 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ product.stock }}</div>
                                    <div v-if="product.stock <= 3" class="absolute top-1.5 right-1.5 bg-ink text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md animate-pulse">HOTS!</div>
                                    <div v-if="product.stock === 0" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                        <span class="bg-charcoal text-white text-[10px] font-semibold px-2 py-1 rounded-lg">Stok Habis</span>
                                    </div>
                                    <button @click.stop="addToCart(product)" :disabled="product.stock === 0" class="absolute bottom-0 left-0 right-0 py-2.5 bg-maroon text-white text-[10px] font-semibold tracking-wide translate-y-full group-hover/card:translate-y-0 transition-transform duration-300 hover:bg-maroon-600 disabled:opacity-0">
                                        {{ product.stock === 0 ? 'Stok Habis' : '+ Masuk Keranjang' }}
                                    </button>
                                </div>
                                <div class="p-2.5">
                                    <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ product.category?.name || 'Kemeja' }}</p>
                                    <h3 class="text-xs font-semibold text-charcoal dark:text-slate-200 mt-0.5 leading-snug line-clamp-2">{{ product.name }}</h3>
                                    <p class="text-sm font-bold text-maroon mt-1.5">Rp{{ formatPrice(product.price) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Load More -->
                <div v-if="hasMorePages || loadingMore" class="mt-10 flex justify-center">
                    <button @click="loadMore" :disabled="loadingMore" class="px-8 py-3 border-2 border-maroon text-maroon text-sm font-semibold rounded-xl hover:bg-maroon hover:text-white transition-all active:scale-[0.97] disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span v-if="loadingMore" class="w-4 h-4 border-2 border-maroon/30 border-t-maroon rounded-full animate-spin"></span>
                        {{ loadingMore ? 'Memuat...' : 'Tampilkan Lebih Banyak' }}
                    </button>
                </div>
            </div>
        </section>


    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { watch } from 'vue'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'
import api from '../api'
import { useSettings } from '../useSettings'

const { fetchSettings, get } = useSettings()

const route = useRoute()
const router = useRouter()
const { addItem } = useCartStore()
const banners = ref([])
const categoriesList = ref([])
const products = ref([])
const loading = ref(false)
const selectedCategory = ref('')
const searchTerm = ref('')
const email = ref('')
const subscribed = ref(false)
const activeSlide = ref(0)
let slideTimer = null

const loadingMore = ref(false)
const hasMorePages = ref(false)
const currentPage = ref(1)

const filteredProducts = computed(() => {
    if (!products.value.length) return []
    let result = products.value
    if (selectedCategory.value) {
        result = result.filter(p => p.category?.slug === selectedCategory.value)
    }
    if (searchTerm.value) {
        const q = searchTerm.value.toLowerCase()
        result = result.filter(p =>
            p.name?.toLowerCase().includes(q) ||
            p.description?.toLowerCase().includes(q) ||
            p.category?.name?.toLowerCase().includes(q)
        )
    }
    return result
})

const groupedProducts = computed(() => {
    const source = filteredProducts.value
    const groups = []
    for (const cat of categoriesList.value) {
        const catProducts = source.filter(p => p.category?.slug === cat.slug)
        if (catProducts.length) {
            groups.push({ category: cat, products: catProducts })
        }
    }
    return groups
})

function productImage(product, index) {
    if (product.images && product.images[index]) return product.images[index].path
    if (index === 0 && product.thumbnail) return product.thumbnail
    if (product.thumbnail) return product.thumbnail
    return ''
}

function prevSlide() { activeSlide.value = activeSlide.value === 0 ? Math.max(banners.value.length - 1, 0) : activeSlide.value - 1 }
function nextSlide() { activeSlide.value = activeSlide.value >= banners.value.length - 1 ? 0 : activeSlide.value + 1 }
function addToCart(product) { addItem(product, 1) }

function scrollCarousel(catSlug, direction) {
    const el = document.querySelector(`[data-carousel="${catSlug}"]`)
    if (!el) return
    const scrollAmount = 380
    el.scrollBy({ left: direction === 'right' ? scrollAmount : -scrollAmount, behavior: 'smooth' })
}

function selectCategory(slug) {
    selectedCategory.value = slug
    document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' })
}

function scrollToShop() {
    document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' })
}

function handleSubscribe() {
    if (email.value) {
        subscribed.value = true
        email.value = ''
        setTimeout(() => { subscribed.value = false }, 4000)
    }
}

onMounted(() => {
    fetchData()
    slideTimer = setInterval(() => { nextSlide() }, 5000)
    if (route.query.shop) {
        setTimeout(() => document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' }), 150)
    }
    // Sync state dengan URL query params saat mount
    if (route.query.category) selectedCategory.value = route.query.category
    if (route.query.search) searchTerm.value = route.query.search
})

// Watch perubahan URL query — sinkronisasi filter & search (H-03 + L-06)
watch(() => route.query.category, (val) => {
    selectedCategory.value = val || ''
})
watch(() => route.query.search, (val) => {
    searchTerm.value = val || ''
    if (val) {
        setTimeout(() => document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' }), 150)
    }
})

async function fetchData() {
    loading.value = true
    currentPage.value = 1
    try {
        const [bannersRes, settingsRes, categoriesRes, productsRes] = await Promise.all([
            api.get('/banners'),
            fetchSettings(),
            api.get('/categories'),
            api.get('/products', { params: { per_page: 12, page: 1 } })
        ])
        banners.value = (bannersRes.data.data || bannersRes.data).filter(b => b.is_active !== false)
        categoriesList.value = categoriesRes.data.data || categoriesRes.data
        const meta = productsRes.data.meta || productsRes.data.pagination || null
        products.value = productsRes.data.data || productsRes.data
        hasMorePages.value = meta ? meta.current_page < meta.last_page : false
    } catch (e) {
        console.error('Failed to fetch data:', e)
        categoriesList.value = []
        products.value = []
        hasMorePages.value = false
    } finally {
        loading.value = false
    }
}

async function loadMore() {
    if (loadingMore.value || !hasMorePages.value) return
    loadingMore.value = true
    try {
        const nextPage = currentPage.value + 1
        const res = await api.get('/products', { params: { per_page: 12, page: nextPage } })
        const newProducts = res.data.data || res.data
        const meta = res.data.meta || res.data.pagination || null
        products.value = [...products.value, ...newProducts]
        currentPage.value = nextPage
        hasMorePages.value = meta ? meta.current_page < meta.last_page : false
    } catch (e) {
        console.error('Failed to load more:', e)
    } finally {
        loadingMore.value = false
    }
}

onUnmounted(() => {
    if (slideTimer) clearInterval(slideTimer)
})
</script>
