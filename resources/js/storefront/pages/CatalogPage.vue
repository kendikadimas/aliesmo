<template>
    <div class="min-h-screen bg-putih">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            <!-- Skeleton saat loading -->
            <template v-if="loading">
                <div class="h-7 bg-coklat-100/50 rounded-full w-1/4 mb-3 animate-pulse"></div>
                <div class="flex gap-2 mb-4">
                    <div v-for="n in 5" :key="n" class="h-8 bg-coklat-100/50 rounded-xl animate-pulse" :class="n === 1 ? 'w-16' : 'w-20'"></div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 lg:gap-5">
                    <div v-for="n in 10" :key="n" class="animate-pulse">
                        <div class="aspect-[3/4] bg-coklat-100/50 rounded-xl"></div>
                        <div class="mt-3 space-y-2 px-1">
                            <div class="h-2.5 bg-coklat-100/50 rounded-full w-2/3"></div>
                            <div class="h-2.5 bg-coklat-100/50 rounded-full w-1/3"></div>
                        </div>
                    </div>
                </div>
            </template>

            <template v-else>
            <!-- Judul Halaman -->
            <h1 class="text-xl sm:text-2xl font-bold text-charcoal tracking-tight mb-3">
                {{ currentCategory ? currentCategory.name : 'Semua Produk' }}
            </h1>

            <!-- Filter Kategori + Sort — satu baris -->
            <div class="flex gap-2 mb-4 overflow-x-auto pb-1" style="scrollbar-width:none;-ms-overflow-style:none;">
                <router-link
                    to="/catalog"
                    class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs font-semibold rounded-xl border-2 transition-all shrink-0"
                    :class="!$route.params.slug ? 'bg-maroon text-white border-maroon' : 'bg-white text-charcoal/60 border-maroon-100 hover:border-maroon/50'"
                >
                    Semua
                </router-link>
                <router-link
                    v-for="cat in categoriesList"
                    :key="cat.id"
                    :to="`/catalog/${cat.slug}`"
                    class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs font-semibold rounded-xl border-2 transition-all shrink-0"
                    :class="$route.params.slug === cat.slug ? 'bg-maroon text-white border-maroon' : 'bg-white text-charcoal/60 border-maroon-100 hover:border-maroon/50'"
                >
                    {{ cat.name }}
                </router-link>
                <div class="ml-auto shrink-0">
                    <select
                        v-model="sortBy"
                        class="px-3 py-1.5 text-xs rounded-xl border border-zinc-200 focus:outline-none focus:border-maroon bg-white text-charcoal"
                    >
                        <option value="">Default</option>
                        <option value="price-asc">Termurah</option>
                        <option value="price-desc">Termahal</option>
                        <option value="name-asc">A–Z</option>
                        <option value="name-desc">Z–A</option>
                    </select>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!filteredProducts.length" class="text-center py-20">
                <svg class="mx-auto mb-4 text-charcoal/20" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <p class="text-base text-charcoal/50">{{ loadError || 'Hmm, tidak ada yang cocok nih' }}</p>
                <p class="mt-1 text-sm text-charcoal/40">{{ loadError ? 'Coba beberapa saat lagi.' : 'Coba kata kunci lain atau pilih kategori berbeda' }}</p>
            </div>

            <!-- Product Grid -->
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 lg:gap-5">
                <div
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="group/card cursor-pointer bg-white rounded-xl overflow-hidden border border-maroon-50 hover:border-maroon-200 transition-all hover:shadow-md active:scale-[0.98]"
                    @click="$router.push(`/products/${product.slug}`)"
                >
                    <div class="aspect-[3/4] bg-maroon-50 overflow-hidden relative">
                        <img :src="productImage(product, 0)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover/card:opacity-0" />
                        <img :src="productImage(product, 1)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 opacity-0 group-hover/card:opacity-100" />
                        <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-1.5 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ product.stock }}</div>
                        <div v-if="product.stock === 0" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                            <span class="bg-charcoal text-white text-[10px] font-semibold px-2 py-1 rounded-lg">Stok Habis</span>
                        </div>
                        <button
                            @click.stop="addToCart(product, $event)"
                            :disabled="product.stock === 0"
                            class="absolute bottom-0 left-0 right-0 py-2.5 bg-maroon text-white text-[10px] font-semibold tracking-wide translate-y-full group-hover/card:translate-y-0 transition-transform duration-300 hover:bg-maroon-600 disabled:opacity-0"
                        >
                            + Masuk Keranjang
                        </button>
                    </div>
                    <div class="p-2.5">
                        <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ product.category?.name || 'Kemeja' }}</p>
                        <h3 class="text-xs font-semibold text-charcoal mt-0.5 leading-snug line-clamp-2">{{ product.name }}</h3>
                        <p class="text-sm font-bold text-maroon mt-1.5">Rp{{ formatPrice(product.price) }}</p>
                    </div>
                </div>
            </div>

            <!-- Load More -->
            <div v-if="hasMorePages || loadingMore" class="mt-10 flex justify-center">
                <button
                    @click="loadMore"
                    :disabled="loadingMore"
                    class="px-8 py-3 border-2 border-maroon text-maroon text-sm font-semibold rounded-xl hover:bg-maroon hover:text-white transition-all active:scale-[0.97] disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <span v-if="loadingMore" class="w-4 h-4 border-2 border-maroon/30 border-t-maroon rounded-full animate-spin"></span>
                    {{ loadingMore ? 'Memuat...' : 'Tampilkan Lebih Banyak' }}
                </button>
            </div>
            </template><!-- end v-else -->
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'
import api from '../api'

const route = useRoute()
const { addItem } = useCartStore()

const categoriesList = ref([])
const products = ref([])
const loading = ref(true)
const loadingMore = ref(false)
const loadError = ref('')
const hasMorePages = ref(false)
const currentPage = ref(1)
const searchTerm = ref('')
const sortBy = ref('')

const currentCategory = computed(() =>
    categoriesList.value.find(c => c.slug === route.params.slug) || null
)

const filteredProducts = computed(() => {
    let result = [...products.value]

    if (route.params.slug) {
        result = result.filter(p => p.category?.slug === route.params.slug)
    }

    if (searchTerm.value) {
        const q = searchTerm.value.toLowerCase()
        result = result.filter(p =>
            p.name?.toLowerCase().includes(q) ||
            p.description?.toLowerCase().includes(q) ||
            p.category?.name?.toLowerCase().includes(q)
        )
    }

    if (sortBy.value === 'price-asc') result.sort((a, b) => a.price - b.price)
    else if (sortBy.value === 'price-desc') result.sort((a, b) => b.price - a.price)
    else if (sortBy.value === 'name-asc') result.sort((a, b) => a.name.localeCompare(b.name))
    else if (sortBy.value === 'name-desc') result.sort((a, b) => b.name.localeCompare(a.name))

    return result
})

const totalProducts = computed(() => filteredProducts.value.length)

function productImage(product, index) {
    if (product.images && product.images[index]) return product.images[index].path
    if (index === 0 && product.thumbnail) return product.thumbnail
    return `https://picsum.photos/seed/kemeja-${product.id || product.slug}${index > 0 ? '-' + (index + 1) : ''}/600/800`
}

function addToCart(product, event) {
    const sourceRect = event?.currentTarget?.getBoundingClientRect() || null
    addItem(product, 1, sourceRect)
}

async function fetchData() {
    loading.value = true
    currentPage.value = 1
    try {
        loadError.value = ''
        const params = { per_page: 20, page: 1 }
        if (route.params.slug) params.category = route.params.slug

        const [categoriesRes, productsRes] = await Promise.all([
            api.get('/categories'),
            api.get('/products', { params })
        ])
        categoriesList.value = categoriesRes.data.data || categoriesRes.data
        const meta = productsRes.data.meta || productsRes.data.pagination || null
        products.value = productsRes.data.data || productsRes.data
        hasMorePages.value = meta ? meta.current_page < meta.last_page : false
    } catch (e) {
        loadError.value = 'Gagal memuat produk.'
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
        const params = { per_page: 20, page: nextPage }
        if (route.params.slug) params.category = route.params.slug

        const res = await api.get('/products', { params })
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
    searchTerm.value = ''
    sortBy.value = ''
    fetchData()
})

onMounted(() => {
    fetchData()
})
</script>
