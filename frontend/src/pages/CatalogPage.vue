<template>
    <div class="min-h-screen">
        <section class="py-10 lg:py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-slate-100 tracking-tight">
                        {{ activeCategory ? activeCategory.name : 'Semua Produk' }}
                    </h1>
                    <p class="mt-1 text-sm text-charcoal/50 dark:text-slate-400">
                        {{ activeCategory ? `Menampilkan produk kategori ${activeCategory.name}` : 'Temukan kemeja favoritmu' }}
                    </p>
                </div>

                <!-- Category Filter -->
                <div class="flex flex-wrap gap-2 mb-8">
                    <button
                        @click="setCategory(null)"
                        class="px-4 py-2 text-xs font-semibold rounded-xl border-2 transition-all"
                        :class="!selectedSlug ? 'bg-maroon text-white border-maroon' : 'bg-white dark:bg-slate-800 text-charcoal/60 dark:text-slate-400 border-maroon-100 dark:border-slate-600 hover:border-maroon/50'"
                    >Semua</button>
                    <button
                        v-for="cat in categoriesList"
                        :key="cat.id"
                        @click="setCategory(cat.slug)"
                        class="px-4 py-2 text-xs font-semibold rounded-xl border-2 transition-all"
                        :class="selectedSlug === cat.slug ? 'bg-maroon text-white border-maroon' : 'bg-white dark:bg-slate-800 text-charcoal/60 dark:text-slate-400 border-maroon-100 dark:border-slate-600 hover:border-maroon/50'"
                    >{{ cat.name }}</button>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <div v-for="n in 8" :key="n" class="animate-pulse">
                        <div class="aspect-[3/4] bg-coklat-100/50 dark:bg-slate-700/50 rounded-xl"></div>
                        <div class="mt-2 space-y-1.5 px-1">
                            <div class="h-2 bg-coklat-100/50 dark:bg-slate-700/50 rounded-full w-1/3"></div>
                            <div class="h-2.5 bg-coklat-100/50 dark:bg-slate-700/50 rounded-full w-2/3"></div>
                            <div class="h-2.5 bg-coklat-100/50 dark:bg-slate-700/50 rounded-full w-1/4"></div>
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-else-if="!filteredProducts.length" class="text-center py-16">
                    <p class="text-lg text-charcoal/50 dark:text-slate-400">Belum ada produk di kategori ini.</p>
                    <button @click="setCategory(null)" class="mt-4 px-6 py-2.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all">Lihat Semua Produk</button>
                </div>

                <!-- Product Grid -->
                <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <div
                        v-for="product in filteredProducts"
                        :key="product.id"
                        class="group/card cursor-pointer bg-white dark:bg-slate-800 rounded-xl overflow-hidden border border-maroon-50 dark:border-slate-700 hover:border-maroon-200 dark:hover:border-slate-500 transition-all hover:shadow-md active:scale-[0.98]"
                        @click="$router.push(`/products/${product.slug}`)"
                    >
                        <div class="aspect-[3/4] bg-maroon-50 overflow-hidden relative">
                            <img :src="productImage(product, 0)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover/card:opacity-0" />
                            <img :src="productImage(product, 1)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 opacity-0 group-hover/card:opacity-100" />
                            <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-1.5 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ product.stock }}</div>
                            <div v-if="product.stock <= 3 && product.stock > 0" class="absolute top-1.5 right-1.5 bg-ink text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md animate-pulse">HOTS!</div>
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCartStore } from '../cart'
import { categories as mockCategories, products as mockProducts, formatPrice } from '../mock-data'
import api from '../api'

const route = useRoute()
const router = useRouter()
const { addItem } = useCartStore()

const categoriesList = ref([])
const products = ref([])
const loading = ref(false)
const loadingMore = ref(false)
const hasMorePages = ref(false)
const currentPage = ref(1)

const selectedSlug = computed(() => route.params.slug || null)

const activeCategory = computed(() => {
    if (!selectedSlug.value) return null
    return categoriesList.value.find(c => c.slug === selectedSlug.value) || null
})

const filteredProducts = computed(() => {
    if (!products.value.length) return []
    if (!selectedSlug.value) return products.value
    return products.value.filter(p => p.category?.slug === selectedSlug.value)
})

function setCategory(slug) {
    if (slug) {
        router.push(`/catalog/${slug}`)
    } else {
        router.push('/catalog')
    }
}

function productImage(product, index) {
    if (product.images && product.images[index]) return product.images[index].path
    if (index === 0 && product.thumbnail) return product.thumbnail
    return `https://picsum.photos/seed/kemeja-${product.id || product.slug}${index > 0 ? '-' + (index + 1) : ''}/600/800`
}

function addToCart(product) {
    addItem(product, 1)
}

async function fetchData() {
    loading.value = true
    currentPage.value = 1
    try {
        const [categoriesRes, productsRes] = await Promise.all([
            api.get('/categories'),
            api.get('/products', { params: { per_page: 24, page: 1 } })
        ])
        categoriesList.value = categoriesRes.data.data || categoriesRes.data
        const meta = productsRes.data.meta || productsRes.data.pagination || null
        products.value = productsRes.data.data || productsRes.data
        hasMorePages.value = meta ? meta.current_page < meta.last_page : false
    } catch (e) {
        console.error('Failed to fetch data:', e)
        categoriesList.value = mockCategories
        products.value = mockProducts
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
        const res = await api.get('/products', { params: { per_page: 24, page: nextPage } })
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

watch(() => route.params.slug, () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
})

onMounted(() => {
    fetchData()
})
</script>
