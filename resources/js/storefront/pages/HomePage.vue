<template>
    <div>
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8">
            <div v-if="loading" class="relative rounded-2xl overflow-hidden shadow-lg aspect-[2/1] lg:aspect-auto lg:h-56 bg-coklat-100/50 animate-pulse">
                <div class="absolute inset-x-8 bottom-6 space-y-3 hidden sm:block">
                    <div class="h-4 bg-white/50 rounded-full w-1/3"></div>
                    <div class="h-8 bg-white/60 rounded-full w-2/3"></div>
                </div>
            </div>
            <div v-else class="relative rounded-2xl overflow-hidden shadow-lg aspect-[2/1] lg:aspect-auto lg:h-56">
                <div class="absolute inset-0 flex transition-all duration-500" :style="{ transform: `translateX(-${activeSlide * 100}%)` }">
                    <a
                        v-for="(banner, index) in banners"
                        :key="banner.id"
                        :href="banner.button_link || '#'"
                        class="w-full shrink-0 relative h-full block"
                    >
                        <img :src="banner.image_url || `https://picsum.photos/seed/banner-kemeja-${index+1}/1400/788`" :alt="banner.title" class="absolute inset-0 w-full h-full object-cover" />
                    </a>
                </div>
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2.5 z-20">
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
                <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-8">
                    <div v-for="n in 4" :key="n" class="text-center animate-pulse">
                        <div class="h-8 lg:h-10 bg-coklat-100/70 rounded-full w-20 mx-auto"></div>
                        <div class="h-3 bg-coklat-100/70 rounded-full w-28 mx-auto mt-3"></div>
                    </div>
                </div>
                <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-8">
                    <div class="text-center">
                        <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-maroon">{{ stats.stat_kemeja_terjual }}</p>
                        <p class="text-sm text-charcoal/60 mt-1">{{ stats.stat_kemeja_terjual_label }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-maroon">{{ stats.stat_kota }}</p>
                        <p class="text-sm text-charcoal/60 mt-1">{{ stats.stat_kota_label }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-maroon">{{ stats.stat_kualitas }}</p>
                        <p class="text-sm text-charcoal/60 mt-1">{{ stats.stat_kualitas_label }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-maroon">{{ stats.stat_garansi }}</p>
                        <p class="text-sm text-charcoal/60 mt-1">{{ stats.stat_garansi_label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- [ARCHIVED] Section Kategori — aktifkan kembali jika diperlukan
        <section class="py-6 lg:py-8 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal tracking-tight">Mau cari kemeja apa?</h2>
                        <p class="mt-1 text-sm text-charcoal/50">Pilih kategori sesuai kesempatan atau gayamu</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="scrollCategoryCarousel('left')" class="w-8 h-8 rounded-lg border border-maroon-200/60 flex items-center justify-center text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <button @click="scrollCategoryCarousel('right')" class="w-8 h-8 rounded-lg border border-maroon-200/60 flex items-center justify-center text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>
                <div ref="categoryCarousel" class="flex gap-4 overflow-x-auto scroll-smooth pb-2" style="scrollbar-width:none;-ms-overflow-style:none;">
                    <div
                        v-for="cat in categoriesList"
                        :key="cat.id"
                        @click="$router.push(`/catalog/${cat.slug}`)"
                        class="group cursor-pointer shrink-0 w-[160px] sm:w-[200px] lg:w-[220px]"
                    >
                        <div class="aspect-[4/3] bg-maroon-50 rounded-xl overflow-hidden relative">
                            <img :src="`https://picsum.photos/seed/kategori-${cat.slug}/500/375`" :alt="cat.name" class="w-full h-full object-cover group-hover:scale-[1.06] transition-transform duration-500" />
                            <div class="absolute inset-0 bg-charcoal/30 group-hover:bg-charcoal/40 transition-colors flex items-center justify-center p-3">
                                <p class="text-xs sm:text-sm font-extrabold text-white tracking-[0.12em] uppercase drop-shadow-[0_2px_4px_rgba(0,0,0,0.4)] group-hover:scale-105 transition-transform duration-300 text-center">{{ cat.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        -->

        <section id="shop" class="py-12 lg:py-16 bg-coklat-50/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal tracking-tight">Semua <span class="text-maroon">Produk</span></h2>
                        <p class="mt-1 text-sm text-charcoal/50">Temukan kemeja yang pas buat kamu</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-6">
                    <button @click="$router.push('/catalog')" class="px-4 py-2 text-xs font-semibold rounded-xl border-2 transition-all bg-white text-charcoal/60 border-maroon-100 hover:border-maroon/50">Semua</button>
                    <button v-for="cat in categoriesList" :key="cat.id" @click="$router.push(`/catalog/${cat.slug}`)" class="px-4 py-2 text-xs font-semibold rounded-xl border-2 transition-all bg-white text-charcoal/60 border-maroon-100 hover:border-maroon/50">{{ cat.name }}</button>
                </div>

                <div v-if="$route.query.search" class="mb-6 flex items-center justify-between bg-maroon-50/50 px-4 py-3 rounded-xl border border-maroon-100/50">
                    <p class="text-xs sm:text-sm text-charcoal/70">
                        Menampilkan hasil pencarian untuk "<span class="font-semibold text-maroon">{{ $route.query.search }}</span>"
                    </p>
                    <button @click="$router.push({ path: '/', query: { ...$route.query, search: undefined } })" class="text-xs font-bold text-maroon hover:underline cursor-pointer">
                        Hapus Pencarian
                    </button>
                </div>

                <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <div v-for="n in 8" :key="n" class="animate-pulse">
                        <div class="aspect-[3/4] bg-coklat-100/50 rounded-2xl"></div>
                        <div class="mt-3 space-y-2 px-1">
                            <div class="h-2.5 bg-coklat-100/50 rounded-full w-1/3"></div>
                            <div class="h-3.5 bg-coklat-100/50 rounded-full w-2/3"></div>
                            <div class="h-3.5 bg-coklat-100/50 rounded-full w-1/4"></div>
                        </div>
                    </div>
                </div>

                <div v-else-if="!filteredProducts.length && !loading" class="text-center py-16">
                    <p class="text-lg text-charcoal/50">{{ loadError || 'Hmm, belum ada produk di sini.' }}</p>
                    <p class="mt-1 text-sm text-charcoal/40">{{ loadError ? 'Coba beberapa saat lagi.' : 'Coba lihat kategori lain, siapa tahu ada yang cocok!' }}</p>
                </div>

                <div v-else class="space-y-10">
                    <div v-for="group in groupedProducts" :key="group.category.slug">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-charcoal">{{ group.category.name }}</h3>
                                <p class="text-xs text-charcoal/50">{{ group.products.length }} pilihan untukmu</p>
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
                            <div v-for="product in group.products" :key="product.id" class="shrink-0 w-[170px] sm:w-[200px] md:w-[230px] group/card cursor-pointer bg-white rounded-xl overflow-hidden border border-maroon-50 hover:border-maroon-200 transition-all hover:shadow-md active:scale-[0.98]" @click="$router.push(`/products/${product.slug}`)">
                                <div class="aspect-[3/4] bg-maroon-50 overflow-hidden relative">
                                    <img :src="productImage(product, 0)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover/card:opacity-0" />
                                    <img :src="productImage(product, 1)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 opacity-0 group-hover/card:opacity-100" />
                                    <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-1.5 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ product.stock }}</div>
                                    <div v-if="product.stock <= 3" class="absolute top-1.5 right-1.5 bg-charcoal text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md animate-pulse">HOTS!</div>
                                    <div v-if="product.stock === 0" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                        <span class="bg-charcoal text-white text-[10px] font-semibold px-2 py-1 rounded-lg">Stok Habis</span>
                                    </div>
                                     <button @click.stop="addToCart(product, $event)" :disabled="product.stock === 0" class="absolute bottom-0 left-0 right-0 py-2.5 bg-maroon text-white text-[10px] font-semibold tracking-wide translate-y-full group-hover/card:translate-y-0 transition-transform duration-300 hover:bg-maroon-600 disabled:opacity-0">
                                        {{ product.stock === 0 ? 'Stok Habis' : '+ Masuk Keranjang' }}
                                    </button>
                                </div>
                                <div class="p-2.5">
                                    <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ product.category?.name || 'Kemeja' }}</p>
                                    <h3 class="text-xs font-semibold text-charcoal mt-0.5 leading-snug line-clamp-2">{{ product.name }}</h3>
                                    <p class="text-sm font-bold text-maroon mt-1.5">Rp{{ formatPrice(product.price) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'

const route = useRoute()
const { addItem } = useCartStore()
const categoriesList = ref([])
const products = ref([])
const loading = ref(true)
const loadError = ref('')
const selectedCategory = ref(route.query.category || '')
const activeSlide = ref(0)
const categoryCarousel = ref(null)
let slideTimer = null

// Banner dan stats dari API
const banners = ref([])
const stats = ref({
    stat_kemeja_terjual: '12.000+',
    stat_kota: '15+',
    stat_kualitas: '100%',
    stat_garansi: '30',
})

watch(() => route.query.category, (newCat) => {
    selectedCategory.value = newCat || ''
    if (newCat) {
        scrollToShop()
    }
})

watch(() => route.query.search, (newSearch) => {
    if (newSearch) {
        scrollToShop()
    }
})

const filteredProducts = computed(() => {
    let source = products.value
    if (selectedCategory.value) {
        source = source.filter(p => p.category?.slug === selectedCategory.value)
    }
    if (route.query.search) {
        const query = route.query.search.toLowerCase()
        source = source.filter(p => 
            p.name.toLowerCase().includes(query) || 
            (p.description && p.description.toLowerCase().includes(query)) ||
            (p.category && p.category.name.toLowerCase().includes(query))
        )
    }
    return source
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
    return `https://picsum.photos/seed/kemeja-${product.id || product.slug}${index > 0 ? '-' + (index + 1) : ''}/600/800`
}

function prevSlide() { activeSlide.value = activeSlide.value === 0 ? banners.value.length - 1 : activeSlide.value - 1 }
function nextSlide() { activeSlide.value = activeSlide.value === banners.value.length - 1 ? 0 : activeSlide.value + 1 }
function addToCart(product, event) {
    const sourceRect = event?.currentTarget?.getBoundingClientRect() || null
    addItem(product, 1, sourceRect)
}

function scrollCarousel(catSlug, direction) {
    const el = document.querySelector(`[data-carousel="${catSlug}"]`)
    if (!el) return
    const scrollAmount = 380
    el.scrollBy({ left: direction === 'right' ? scrollAmount : -scrollAmount, behavior: 'smooth' })
}

function scrollCategoryCarousel(direction) {
    if (!categoryCarousel.value) return
    categoryCarousel.value.scrollBy({ left: direction === 'right' ? 480 : -480, behavior: 'smooth' })
}

function selectCategory(slug) {
    selectedCategory.value = slug
    document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' })
}

function scrollToShop() {
    document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' })
}


onMounted(async () => {
    try {
        loadError.value = ''
        const [catRes, prodRes, bannerRes, statsRes] = await Promise.all([
            api.get('/categories'),
            api.get('/products'),
            api.get('/banners'),
            api.get('/settings/group/stats'),
        ])
        categoriesList.value = catRes.data.data
        products.value = prodRes.data.data
        if (bannerRes.data.data?.length) banners.value = bannerRes.data.data
        if (statsRes.data.data) stats.value = { ...stats.value, ...statsRes.data.data }
    } catch (e) {
        loadError.value = 'Gagal memuat data toko. Silakan refresh halaman.'
        categoriesList.value = []
        products.value = []
    }
    loading.value = false
    slideTimer = setInterval(() => { nextSlide() }, 5000)
    if (route.query.shop || route.query.category || route.query.search) {
        setTimeout(() => document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' }), 150)
    }
})

onUnmounted(() => {
    if (slideTimer) clearInterval(slideTimer)
})
</script>
