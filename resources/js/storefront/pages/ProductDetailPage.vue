<template>
    <div class="min-h-screen bg-white">
        <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
            <div class="grid lg:grid-cols-2 gap-6 lg:gap-10">
                <div class="aspect-[3/4] bg-maroon-50/60 rounded-xl animate-pulse"></div>
                <div class="space-y-3">
                    <div class="h-2.5 bg-coklat-100/50 w-1/4 rounded-full animate-pulse"></div>
                    <div class="h-5 bg-coklat-100/50 w-3/4 rounded-full animate-pulse"></div>
                    <div class="h-4 bg-coklat-100/50 w-1/3 rounded-full animate-pulse"></div>
                    <div class="h-16 bg-coklat-100/50 w-full rounded-lg animate-pulse mt-4"></div>
                </div>
            </div>
        </div>

        <div v-else-if="!product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 text-center">
            <p class="text-base text-charcoal/50">{{ loadError || 'Produk tidak ditemukan.' }}</p>
            <router-link to="/" class="inline-block mt-3 text-sm font-semibold text-maroon hover:text-maroon-700 transition-colors">Kembali ke Beranda</router-link>
        </div>

        <div v-else>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 lg:py-8">
                <router-link to="/#shop" class="inline-flex items-center gap-1.5 text-xs font-semibold text-charcoal/40 hover:text-maroon transition-colors mb-4">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Kembali
                </router-link>

                <div class="md:flex md:items-start md:gap-6 lg:gap-8 xl:gap-12">
                    <div class="md:sticky md:top-24 md:self-start w-full max-w-md mx-auto md:mx-0 shrink-0">
                        <div class="aspect-[3/4] bg-maroon-50 rounded-xl overflow-hidden relative max-h-[380px] sm:max-h-[420px] lg:max-h-[520px]">
                            <img v-if="product.thumbnail" :src="selectedImage === 0 && product.thumbnail || product.images?.[selectedImage]?.path || product.thumbnail" :alt="product.name" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <span class="text-7xl font-bold text-maroon-200/40 select-none">A</span>
                            </div>
                        </div>
                        <div v-if="product.images?.length" class="flex gap-2 mt-3 overflow-x-auto pb-1">
                            <div v-for="(img, i) in product.images" :key="i" class="w-14 h-14 shrink-0 bg-maroon-50 rounded-lg overflow-hidden border-2 cursor-pointer" :class="selectedImage === i ? 'border-maroon' : 'border-transparent hover:border-maroon-200'" @click="selectedImage = i">
                                <img :src="img.path" :alt="`${product.name} ${i + 1}`" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 min-w-0 mx-auto md:mx-0 md:pt-0 mt-4 md:mt-0">
                        <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ product.category?.name || 'Kemeja' }}</p>
                        <h1 class="text-xl lg:text-2xl font-bold text-charcoal mt-1 leading-tight">{{ product.name }}</h1>

                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-xl lg:text-2xl font-bold text-maroon">Rp{{ formatPrice(activePrice) }}</span>
                        </div>

                        <div class="mt-2 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full" :class="activeStock > 0 ? 'bg-green-500' : 'bg-red-400'"></span>
                            <span class="text-xs font-medium" :class="activeStock > 0 ? 'text-green-700' : 'text-red-600'">
                                {{ activeStock > 0 ? `Tersedia (${activeStock} pcs)` : 'Stok Habis' }}
                            </span>
                        </div>

                        <div v-if="activeVariants.length" class="mt-4">
                            <p class="text-[10px] font-semibold text-charcoal/50 mb-2">Varian</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <button
                                    v-for="variant in activeVariants"
                                    :key="variant.id"
                                    @click="selectVariant(variant)"
                                    :disabled="variant.stock <= 0"
                                    class="text-left rounded-xl border-2 p-3 transition-all disabled:opacity-45 disabled:cursor-not-allowed"
                                    :class="selectedVariant?.id === variant.id ? 'border-maroon bg-maroon-50/50' : 'border-maroon-100 hover:border-maroon/50 bg-white'"
                                >
                                    <span class="block text-xs font-bold text-charcoal">{{ variant.name }}</span>
                                    <span class="block mt-1 text-[11px] text-maroon font-semibold">Rp{{ formatPrice(variant.price) }}</span>
                                    <span class="block mt-0.5 text-[10px] text-charcoal/45">Stok {{ variant.stock }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4">
                            <p class="text-[10px] font-semibold text-charcoal/50 mb-1.5">Jumlah</p>
                            <div class="flex items-center gap-3">
                                <button @click="decrementQty" class="w-8 h-8 rounded-lg border-2 border-maroon-200/60 flex items-center justify-center text-sm font-semibold text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed" :disabled="quantity <= 1">−</button>
                                <span class="w-10 text-center text-base font-bold text-charcoal">{{ quantity }}</span>
                                <button @click="quantity++" class="w-8 h-8 rounded-lg border-2 border-maroon-200/60 flex items-center justify-center text-sm font-semibold text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed" :disabled="quantity >= activeStock">+</button>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button @click="addToCart($event)" :disabled="activeStock === 0" class="flex-1 px-4 sm:px-6 py-3 bg-maroon text-white text-xs sm:text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] disabled:bg-maroon-200 disabled:cursor-not-allowed disabled:active:scale-100 shadow-lg">
                                {{ activeStock === 0 ? 'Stok Habis' : 'Tambahkan ke Keranjang' }}
                            </button>
                            <button @click="toggleWishlist(product.id)" class="w-12 h-11 flex items-center justify-center rounded-xl border-2 transition-all active:scale-95" :class="isWishlisted(product.id) ? 'bg-red-50 border-red-200 text-red-500' : 'border-maroon-200/60 text-charcoal/50 hover:border-maroon hover:text-maroon'">
                                <svg width="18" height="18" viewBox="0 0 24 24" :fill="isWishlisted(product.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4 pt-4 border-t border-maroon-50">
                            <p v-if="isDescriptionShort" class="text-xs font-semibold text-charcoal/50 leading-relaxed">{{ product.description }}</p>
                            <div v-else>
                                <p class="text-xs font-semibold text-charcoal/50 leading-relaxed line-clamp-3">{{ product.description }}</p>
                                <button @click="showDescriptionModal = true" class="text-xs font-semibold text-maroon hover:text-maroon-600 mt-1">Lihat Selengkapnya</button>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-4 text-xs text-charcoal/50">
                            <span class="flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="7" width="18" height="13" rx="2"/>
                                    <path d="M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2"/>
                                </svg>
                                Premium Packaging
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                100% Original
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <teleport to="body">
                <div v-if="showDescriptionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showDescriptionModal = false">
                    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
                    <div class="relative bg-white rounded-2xl max-w-lg w-full max-h-[70vh] overflow-y-auto p-6 shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-charcoal">Deskripsi</h3>
                            <button @click="showDescriptionModal = false" class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-maroon-50 transition-colors">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <p class="text-sm text-charcoal/70 leading-relaxed whitespace-pre-line">{{ product.description }}</p>
                    </div>
                </div>
            </teleport>

            <section class="py-8 lg:py-12 bg-coklat-50/20 mt-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-lg lg:text-xl font-bold text-charcoal tracking-tight">Produk Terkait</h2>
                            <p class="text-xs text-charcoal/50 mt-0.5">Koleksi lain dari kategori yang sama</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="scrollRelated('left')" class="w-7 h-7 rounded-lg border border-maroon-200/60 flex items-center justify-center text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="15 18 9 12 15 6"/></svg>
                            </button>
                            <button @click="scrollRelated('right')" class="w-7 h-7 rounded-lg border border-maroon-200/60 flex items-center justify-center text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        </div>
                    </div>

                    <div ref="relatedCarousel" class="flex gap-4 overflow-x-auto scroll-smooth pb-2" style="scrollbar-width:none;-ms-overflow-style:none;">
                        <div v-for="rp in relatedProducts" :key="rp.id" class="shrink-0 w-[160px] sm:w-[190px] group/card cursor-pointer bg-white rounded-xl overflow-hidden border border-maroon-50 hover:border-maroon-200 transition-all hover:shadow-md active:scale-[0.98]" @click="$router.push(`/products/${rp.slug}`)">
                            <div class="aspect-[3/4] bg-maroon-50 overflow-hidden relative">
                                <img :src="productImage(rp, 0)" :alt="rp.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover/card:opacity-0" />
                                <img :src="productImage(rp, 1)" :alt="rp.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 opacity-0 group-hover/card:opacity-100" />
                                <div v-if="rp.stock > 0 && rp.stock <= 5" class="absolute top-1.5 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ rp.stock }}</div>
                                <div v-if="rp.stock === 0" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                    <span class="bg-charcoal text-white text-[10px] font-semibold px-2 py-1 rounded-lg">Stok Habis</span>
                                </div>
                                <button @click.stop="addToCartRelated(rp, $event)" :disabled="rp.stock === 0" class="absolute bottom-0 left-0 right-0 py-2 bg-maroon text-white text-[10px] font-semibold tracking-wide translate-y-full group-hover/card:translate-y-0 transition-transform duration-300 hover:bg-maroon-600 disabled:opacity-0">
                                    {{ rp.stock === 0 ? 'Stok Habis' : '+ Keranjang' }}
                                </button>
                            </div>
                            <div class="p-2.5">
                                <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ rp.category?.name || 'Kemeja' }}</p>
                                <h3 class="text-xs font-semibold text-charcoal mt-0.5 leading-snug line-clamp-2">{{ rp.name }}</h3>
                                <p class="text-sm font-bold text-maroon mt-1">Rp{{ formatPrice(rp.price) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="!relatedProducts.length" class="text-center py-8">
                        <p class="text-sm text-charcoal/50">Belum ada produk terkait.</p>
                    </div>
                </div>
            </section>

            <!-- Ulasan Pembeli -->
            <section class="py-8 lg:py-12 mt-2">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="mb-6">
                        <h2 class="text-lg lg:text-xl font-bold text-charcoal tracking-tight">Ulasan Pembeli</h2>
                        <p class="text-xs text-charcoal/50 mt-0.5">{{ reviews.length ? reviews.length + ' ulasan' : 'Belum ada ulasan' }}</p>
                    </div>

                    <!-- Loading reviews -->
                    <div v-if="loadingReviews" class="space-y-4">
                        <div v-for="n in 3" :key="n" class="animate-pulse flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-maroon-50 shrink-0"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-2.5 bg-maroon-50 rounded w-1/4"></div>
                                <div class="h-2 bg-maroon-50 rounded w-3/4"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Review list -->
                    <div v-else-if="reviews.length" class="space-y-4">
                        <div v-for="review in reviews" :key="review.id"
                            class="flex gap-3 p-4 rounded-xl bg-coklat-50/30 border border-maroon-50">
                            <div class="w-9 h-9 rounded-full bg-maroon flex items-center justify-center text-white text-xs font-bold shrink-0">
                                {{ review.user_name?.charAt(0)?.toUpperCase() || 'A' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-bold text-charcoal">{{ review.user_name || 'Pembeli' }}</span>
                                    <div class="flex items-center gap-0.5">
                                        <svg v-for="s in 5" :key="s" width="11" height="11" viewBox="0 0 24 24"
                                            :fill="s <= review.rating ? '#171717' : 'none'"
                                            stroke="#171717" stroke-width="2">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    </div>
                                    <span class="text-[10px] text-charcoal/40">{{ formatDate(review.created_at) }}</span>
                                </div>
                                <p class="text-xs text-charcoal/70 mt-1 leading-relaxed">{{ review.body }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="text-center py-10 bg-coklat-50/20 rounded-xl border border-maroon-50">
                        <svg class="mx-auto mb-3 text-charcoal/20" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <p class="text-sm text-charcoal/50">Belum ada ulasan untuk produk ini.</p>
                        <p class="text-xs text-charcoal/40 mt-1">Jadilah yang pertama memberikan ulasan!</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import { useCartStore } from '../cart'
import { useToast } from '../toast'
import { formatPrice } from '../mock-data'

const route = useRoute()
const { addItem } = useCartStore()
const { show: showToast } = useToast()
const product = ref(null)
const allProducts = ref([])
const loading = ref(true)
const loadError = ref('')
const quantity = ref(1)
const selectedImage = ref(0)
const selectedVariant = ref(null)
const showDescriptionModal = ref(false)
const wishlist = ref(new Set())
const relatedCarousel = ref(null)
const reviews = ref([])
const loadingReviews = ref(false)

const relatedProducts = computed(() => {
    if (!product.value) return []
    const catSlug = product.value.category?.slug
    if (!catSlug) return []
    const source = allProducts.value
    return source.filter(p => p.category?.slug === catSlug && p.id !== product.value.id).slice(0, 10)
})

const activeVariants = computed(() => (product.value?.variants || []).filter(v => v.is_active !== false))
const activePrice = computed(() => selectedVariant.value?.price ?? product.value?.price ?? 0)
const activeStock = computed(() => selectedVariant.value?.stock ?? product.value?.stock ?? 0)

const isDescriptionShort = computed(() => {
    if (!product.value?.description) return true
    return product.value.description.length < 150
})

function productImage(p, index) {
    if (p.images && p.images[index]) return p.images[index].path
    if (index === 0 && p.thumbnail) return p.thumbnail
    return `https://picsum.photos/seed/${p.id || p.slug}${index > 0 ? '-' + (index + 1) : ''}/600/800`
}

function decrementQty() {
    if (quantity.value > 1) quantity.value--
}

function selectVariant(variant) {
    selectedVariant.value = variant
    if (quantity.value > variant.stock) quantity.value = Math.max(1, variant.stock)
}

function addToCart(event) {
    if (product.value && quantity.value > 0) {
        const sourceRect = event?.currentTarget?.getBoundingClientRect() || null
        addItem({ ...product.value, selectedVariant: selectedVariant.value }, quantity.value, sourceRect)
        const variantText = selectedVariant.value ? ` (${selectedVariant.value.name})` : ''
        showToast(`${product.value.name}${variantText} ditambahkan ke keranjang!`)
    }
}

function addToCartRelated(rp, event) {
    if (rp.stock > 0) {
        const sourceRect = event?.currentTarget?.getBoundingClientRect() || null
        addItem(rp, 1, sourceRect)
        showToast(`${rp.name} ditambahkan ke keranjang!`)
    }
}

function toggleWishlist(id) {
    const set = wishlist.value
    if (set.has(id)) set.delete(id)
    else set.add(id)
    wishlist.value = new Set(set)
}

function isWishlisted(id) {
    return wishlist.value.has(id)
}

function scrollRelated(dir) {
    if (!relatedCarousel.value) return
    relatedCarousel.value.scrollBy({ left: dir === 'right' ? 340 : -340, behavior: 'smooth' })
}

function formatDate(dateStr) {
    if (!dateStr) return ''
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(dateStr))
}

onMounted(async () => {
    try {
        loadError.value = ''
        const [prodRes, allRes] = await Promise.all([
            api.get(`/products/${route.params.slug}`),
            api.get('/products', { params: { per_page: 50 } }).catch(() => ({ data: { data: [] } })),
        ])
        product.value = prodRes.data.data
        if (activeVariants.value.length) selectedVariant.value = activeVariants.value.find(v => v.stock > 0) || activeVariants.value[0]
        allProducts.value = allRes.data?.data || []
        document.title = `${product.value.name} — Aliesmo`

        // Fetch reviews setelah produk loaded
        loadingReviews.value = true
        api.get(`/products/${route.params.slug}/reviews`)
            .then(res => { reviews.value = res.data.data || [] })
            .catch(() => { reviews.value = [] })
            .finally(() => { loadingReviews.value = false })
    } catch (e) {
        console.error(e)
        loadError.value = 'Gagal memuat produk. Silakan coba lagi.'
    } finally {
        loading.value = false
    }
})
</script>
