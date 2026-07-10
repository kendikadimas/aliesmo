<template>
    <div>
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8">
            <div class="relative rounded-2xl overflow-hidden shadow-lg">
                <div class="relative flex transition-all duration-500" :style="{ transform: `translateX(-${activeSlide * 100}%)` }">
                    <div v-for="banner in banners" :key="banner.id"
                        class="w-full shrink-0 aspect-[2/1] sm:aspect-[3/1]">
                        <!-- YouTube embed banner -->
                        <iframe
                            v-if="banner.youtube_url"
                            :src="getYoutubeEmbedUrl(banner.youtube_url)"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                        <!-- Image banner -->
                        <img v-else :src="banner.image_url" :alt="banner.title" class="w-full h-full object-cover block" />
                    </div>
                </div>
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2.5 z-20">
                    <button v-for="(_, i) in banners" :key="i" @click="activeSlide = i" class="h-2 rounded-full transition-all" :class="activeSlide === i ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/70'"></button>
                </div>
                <button @click="prevSlide" class="absolute top-1/2 -translate-y-1/2 left-3 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow hover:bg-white dark:bg-[#1c1c1e] dark:hover:bg-[#242426] transition-all">
                    <ChevronLeftIcon class="w-4 h-4" />
                </button>
                <button @click="nextSlide" class="absolute top-1/2 -translate-y-1/2 right-3 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow hover:bg-white dark:bg-[#1c1c1e] dark:hover:bg-[#242426] transition-all">
                    <ChevronRightIcon class="w-4 h-4" />
                </button>
            </div>
        </section>

        <!-- Stats section diarsipkan sementara -->
        <!-- <section class="py-6 lg:py-8 bg-coklat-50/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-8">
                    <div class="text-center">
                        <p class="text-3xl lg:text-4xl font-bold text-maroon">{{ get('stat_kemeja_terjual', '12.000+') }}</p>
                        <p class="text-sm text-charcoal/60 dark:text-[#8a8a8e] mt-1">{{ get('stat_kemeja_terjual_label', 'Kemeja Terjual') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl lg:text-4xl font-bold text-maroon">{{ get('stat_kota', '15+') }}</p>
                        <p class="text-sm text-charcoal/60 dark:text-[#8a8a8e] mt-1">{{ get('stat_kota_label', 'Kota di Indonesia') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl lg:text-4xl font-bold text-maroon">{{ get('stat_kualitas', '100%') }}</p>
                        <p class="text-sm text-charcoal/60 dark:text-[#8a8a8e] mt-1">{{ get('stat_kualitas_label', 'Kualitas Original') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl lg:text-4xl font-bold text-maroon">{{ get('stat_garansi', '30') }}</p>
                        <p class="text-sm text-charcoal/60 dark:text-[#8a8a8e] mt-1">{{ get('stat_garansi_label', 'Hari Garansi') }}</p>
                    </div>
                </div>
            </div>
        </section> -->

        <section class="py-12 lg:py-16 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Collection</h2>
                        <p class="mt-1 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Temukan koleksi yang cocok untukmu</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-5">
                    <div v-for="cat in categoriesList" :key="cat.id" @click="$router.push(`/catalog/${cat.slug}`)" class="group cursor-pointer">
                        <div class="aspect-[21/9] bg-zinc-800 rounded-xl overflow-hidden relative">
                            <img v-if="cat.image_url" :src="cat.image_url" :alt="cat.name" class="w-full h-full object-cover group-hover:scale-[1.06] transition-transform duration-500" />
                            <div v-else class="w-full h-full bg-zinc-800 group-hover:scale-[1.06] transition-transform duration-500"></div>
                            <div class="absolute inset-0 bg-charcoal/30 group-hover:bg-charcoal dark:hover:bg-[#d0ceca]/40 transition-colors flex items-center justify-center p-3">
                                <p class="text-xs sm:text-sm font-extrabold text-white tracking-[0.12em] uppercase drop-shadow-[0_2px_4px_rgba(0,0,0,0.4)] group-hover:scale-105 transition-transform duration-300 text-center">{{ cat.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="shop" class="py-12 lg:py-16 bg-coklat-50/20 dark:bg-[#1c1c1e]/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Semua <span class="text-maroon dark:text-[#f0eeeb]">Produk</span></h2>
                        <p class="mt-1 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Temukan kemeja favoritmu</p>
                    </div>
                </div>

                <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
                    <div v-for="n in 8" :key="n">
                        <div class="aspect-[3/4] w-full rounded-xl overflow-hidden">
                            <SkeletonLoader :loading="true" :radius="0" height="100%" width="100%" />
                        </div>
                        <div class="mt-2 space-y-1.5 px-1">
                            <SkeletonLoader :loading="true" :radius="99" height="8px" width="33%" />
                            <SkeletonLoader :loading="true" :radius="99" height="10px" width="66%" />
                            <SkeletonLoader :loading="true" :radius="99" height="10px" width="25%" />
                        </div>
                    </div>
                </div>

                <div v-else-if="!filteredProducts.length && !loading" class="text-center py-16">
                    <p class="text-lg text-charcoal/50 dark:text-[#8a8a8e]">Belum ada produk nih.</p>
                </div>

                <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
                    <div v-for="product in filteredProducts" :key="product.id" class="group/card cursor-pointer bg-white dark:bg-[#1c1c1e] rounded-xl overflow-hidden border border-maroon-50 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-[#404042] transition-all hover:shadow-md active:scale-[0.98]" @click="$router.push(`/products/${product.slug}`)">
                        <div class="aspect-[3/4] bg-maroon-50 overflow-hidden relative">
                            <img :src="productImage(product, 0)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover/card:opacity-0" />
                            <img :src="productImage(product, 1)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 opacity-0 group-hover/card:opacity-100" />
                            <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-1.5 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ product.stock }}</div>
                            <div v-if="product.stock <= 3" class="absolute top-1.5 right-1.5 bg-ink dark:bg-[#f0eeeb] text-white dark:text-[#161618] text-[9px] font-semibold px-1.5 py-0.5 rounded-md animate-pulse">HOTS!</div>
                            <div v-if="product.stock === 0" class="absolute inset-0 bg-white/80 dark:bg-[#1c1c1e]/80 flex items-center justify-center">
                                <span class="bg-charcoal dark:bg-[#303032] text-white text-[10px] font-semibold px-2 py-1 rounded-lg">Stok Habis</span>
                            </div>
                            <button @click.stop="addToCart(product)" :disabled="product.stock === 0" class="absolute bottom-0 left-0 right-0 py-2.5 bg-maroon text-white text-[10px] font-semibold tracking-wide translate-y-full group-hover/card:translate-y-0 transition-transform duration-300 hover:bg-maroon-600 dark:hover:bg-maroon/80 disabled:opacity-0">
                                {{ product.stock === 0 ? 'Stok Habis' : '+ Masuk Keranjang' }}
                            </button>
                        </div>
                        <div class="p-2.5">
                            <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ product.categories?.[0]?.name || 'Kemeja' }}</p>
                            <h3 class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb] mt-0.5 leading-snug line-clamp-2">{{ product.name }}</h3>
                            <p class="text-sm font-bold text-maroon dark:text-[#f0eeeb] mt-1.5">Rp{{ formatPrice(product.price) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Infinite scroll sentinel -->
                <div ref="scrollSentinel" class="h-10 mt-6 flex items-center justify-center">
                    <span v-if="loadingMore" class="w-5 h-5 border-2 border-maroon/30 border-t-maroon rounded-full animate-spin"></span>
                </div>
            </div>
        </section>

        <!-- Section Video -->
        <section v-if="videos.length" class="py-12 lg:py-16 bg-zinc-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">{{ get('homepage_video_title', 'Video') }}</h2>
                    <p v-if="get('homepage_video_subtitle')" class="mt-2 text-sm text-white/50">{{ get('homepage_video_subtitle') }}</p>
                </div>

                <!-- 1 video: full width. 2 video: 2 kolom. 3+: 3 kolom -->
                <div :class="[
                    'grid gap-5',
                    videos.length === 1 ? 'grid-cols-1 max-w-3xl mx-auto' :
                    videos.length === 2 ? 'grid-cols-1 sm:grid-cols-2' :
                    'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3'
                ]">
                    <div v-for="video in videos" :key="video.id" class="group">
                        <div class="aspect-video rounded-2xl overflow-hidden shadow-xl bg-zinc-800">
                            <iframe
                                :src="getYoutubeEmbedUrl(video.youtube_url)"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>
                        <div v-if="video.title || video.description" class="mt-3 px-1">
                            <p v-if="video.title" class="text-sm font-semibold text-white">{{ video.title }}</p>
                            <p v-if="video.description" class="text-xs text-white/50 mt-0.5 line-clamp-2">{{ video.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>


    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
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
const videos = ref([])
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
const scrollSentinel = ref(null)
let observer = null

const filteredProducts = computed(() => {
    if (!products.value.length) return []
    let result = products.value
    if (selectedCategory.value) {
        result = result.filter(p => p.categories?.some(c => c.slug === selectedCategory.value))
    }
    if (searchTerm.value) {
        const q = searchTerm.value.toLowerCase()
        result = result.filter(p =>
            p.name?.toLowerCase().includes(q) ||
            p.description?.toLowerCase().includes(q) ||
            p.categories?.some(c => c.name?.toLowerCase().includes(q))
        )
    }
    return result
})

const groupedProducts = computed(() => filteredProducts.value)

function productImage(product, index) {
    if (product.images && product.images[index]) return product.images[index].path
    if (index === 0 && product.thumbnail) return product.thumbnail
    if (product.thumbnail) return product.thumbnail
    return ''
}

function prevSlide() { activeSlide.value = activeSlide.value === 0 ? Math.max(banners.value.length - 1, 0) : activeSlide.value - 1 }
function nextSlide() { activeSlide.value = activeSlide.value >= banners.value.length - 1 ? 0 : activeSlide.value + 1 }
function addToCart(product) { addItem(product, 1) }

// Konversi URL YouTube biasa ke embed URL
function getYoutubeEmbedUrl(url) {
    if (!url) return ''
    // Sudah embed URL
    if (url.includes('youtube.com/embed/')) return url
    // youtu.be/VIDEO_ID
    const shortMatch = url.match(/youtu\.be\/([^?&]+)/)
    if (shortMatch) return `https://www.youtube.com/embed/${shortMatch[1]}`
    // youtube.com/watch?v=VIDEO_ID
    const longMatch = url.match(/[?&]v=([^?&]+)/)
    if (longMatch) return `https://www.youtube.com/embed/${longMatch[1]}`
    return url
}

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

    // Infinite scroll — observe sentinel div di bawah produk grid
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && hasMorePages.value && !loadingMore.value) {
            loadMore()
        }
    }, { threshold: 0.1 })

    // Tunggu sentinel di-render dulu
    setTimeout(() => {
        if (scrollSentinel.value) observer.observe(scrollSentinel.value)
    }, 500)
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
        const [bannersRes, settingsRes, categoriesRes, productsRes, videosRes] = await Promise.all([
            api.get('/banners'),
            fetchSettings(),
            api.get('/categories'),
            api.get('/products', { params: { per_page: 12, page: 1 } }),
            api.get('/homepage-videos'),
        ])
        banners.value = (bannersRes.data.data || bannersRes.data).filter(b => b.is_active !== false)
        categoriesList.value = categoriesRes.data.data || categoriesRes.data
        const meta = productsRes.data.meta || productsRes.data.pagination || null
        products.value = productsRes.data.data || productsRes.data
        hasMorePages.value = meta ? meta.current_page < meta.last_page : false
        videos.value = videosRes.data.data || videosRes.data
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
    if (observer) observer.disconnect()
})
</script>
