<template>
    <div class="min-h-screen bg-white dark:bg-[#161618]">
        <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 lg:py-8">
            <!-- back link placeholder -->
            <SkeletonLoader :loading="true" :radius="99" height="14px" width="80px" class="mb-4" />
            <div class="lg:flex lg:items-start lg:gap-8 xl:gap-12">
                <!-- image column -->
                <div class="w-full max-w-md mx-auto lg:mx-0 shrink-0">
                    <SkeletonLoader :loading="true" :radius="12" height="100%" class="aspect-[3/4] max-h-[420px] lg:max-h-[520px] w-full" />
                    <div class="flex gap-2 mt-3">
                        <SkeletonLoader v-for="t in 4" :key="t" :loading="true" :radius="8" height="64px" width="64px" />
                    </div>
                </div>
                <!-- info column -->
                <div class="mt-6 lg:mt-0 flex-1 space-y-4">
                    <SkeletonLoader :loading="true" :radius="99" height="12px" width="80px" />
                    <SkeletonLoader :loading="true" :radius="8" height="32px" width="75%" />
                    <SkeletonLoader :loading="true" :radius="99" height="24px" width="120px" />
                    <div class="space-y-2 pt-2">
                        <SkeletonLoader :loading="true" :radius="99" height="10px" width="100%" />
                        <SkeletonLoader :loading="true" :radius="99" height="10px" width="90%" />
                        <SkeletonLoader :loading="true" :radius="99" height="10px" width="80%" />
                    </div>
                    <div class="flex gap-2 pt-2">
                        <SkeletonLoader v-for="s in 5" :key="s" :loading="true" :radius="8" height="40px" width="48px" />
                    </div>
                    <SkeletonLoader :loading="true" :radius="12" height="48px" width="100%" class="mt-4" />
                </div>
            </div>
        </div>

        <div v-else-if="notFound || !product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 text-center">
            <p class="text-base text-charcoal/50 dark:text-[#8a8a8e]">Produk gak ditemukan nih :(</p>
            <router-link to="/" class="inline-block mt-3 text-sm font-semibold text-maroon dark:text-[#f0eeeb] hover:text-maroon-700 dark:hover:text-[#f0eeeb] transition-colors">Kembali ke Beranda</router-link>
        </div>

        <div v-else>
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 py-8 lg:py-14">
                <router-link to="/#shop" class="inline-flex items-center gap-1.5 text-xs font-semibold text-charcoal/40 dark:text-[#6a6a6e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors mb-4">
                    <ArrowLeftIcon class="w-3 h-3" />
                    Kembali
                </router-link>

                <div class="lg:flex lg:items-start lg:gap-8 xl:gap-12">
                    <div class="lg:sticky lg:top-24 lg:self-start w-full max-w-md mx-auto lg:mx-0 shrink-0">
                        <!-- Main media display -->
                        <div class="aspect-[3/4] bg-maroon-50 rounded-xl overflow-hidden relative max-h-[420px] lg:max-h-[520px]">
                            <!-- Video embed -->
                            <iframe
                                v-if="selectedMedia.type === 'video'"
                                :src="getYoutubeEmbedUrl(product.videos[selectedMedia.index].youtube_url)"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                            <!-- Image display -->
                            <img
                                v-else
                                :src="selectedMedia.index === 0 ? product.thumbnail : (product.images?.[selectedMedia.index - 1]?.path || product.thumbnail)"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <!-- Thumbnails strip: videos first, then images -->
                        <div v-if="product.videos?.length || product.images?.length" class="flex gap-2 mt-3 overflow-x-auto pb-1">
                            <!-- Thumbnail image (first = product.thumbnail) -->
                            <div
                                class="w-14 h-14 shrink-0 bg-maroon-50 rounded-lg overflow-hidden border-2 cursor-pointer"
                                :class="selectedMedia.type === 'image' && selectedMedia.index === 0 ? 'border-maroon' : 'border-transparent hover:border-maroon-200 dark:hover:border-[#f0eeeb]/40'"
                                @click="selectedMedia = { type: 'image', index: 0 }"
                            >
                                <img :src="product.thumbnail" :alt="product.name" class="w-full h-full object-cover" />
                            </div>

                            <!-- Video thumbnails -->
                            <div
                                v-for="(vid, i) in product.videos"
                                :key="`vid-${i}`"
                                class="w-14 h-14 shrink-0 bg-zinc-800 rounded-lg overflow-hidden border-2 cursor-pointer relative"
                                :class="selectedMedia.type === 'video' && selectedMedia.index === i ? 'border-maroon' : 'border-transparent hover:border-maroon-200 dark:hover:border-[#f0eeeb]/40'"
                                @click="selectedMedia = { type: 'video', index: i }"
                            >
                                <img :src="`https://img.youtube.com/vi/${getYoutubeVideoId(vid.youtube_url)}/mqdefault.jpg`" :alt="vid.title || `Video ${i + 1}`" class="w-full h-full object-cover" />
                                <!-- Play icon overlay -->
                                <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </div>
                            </div>

                            <!-- Additional image thumbnails -->
                            <div
                                v-for="(img, i) in product.images"
                                :key="`img-${i}`"
                                class="w-14 h-14 shrink-0 bg-maroon-50 rounded-lg overflow-hidden border-2 cursor-pointer"
                                :class="selectedMedia.type === 'image' && selectedMedia.index === i + 1 ? 'border-maroon' : 'border-transparent hover:border-maroon-200 dark:hover:border-[#f0eeeb]/40'"
                                @click="selectedMedia = { type: 'image', index: i + 1 }"
                            >
                                <img :src="img.path" :alt="`${product.name} ${i + 1}`" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 min-w-0 max-w-md mx-auto lg:mx-0 lg:pt-0">
                        <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ product.categories?.map(c => c.name).join(', ') || '' }}</p>
                        <h1 class="text-xl lg:text-2xl font-bold text-charcoal dark:text-[#f0eeeb] mt-1 leading-tight">{{ product.name }}</h1>

                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-xl lg:text-2xl font-bold text-ink dark:text-[#f0eeeb]">Rp{{ formatPrice(displayPrice) }}</span>
                            <span v-if="product.original_price && !selectedVariant" class="text-xs text-charcoal/40 dark:text-[#6a6a6e] line-through">Rp{{ formatPrice(product.original_price) }}</span>
                            <span v-if="hasVariants && !selectedVariant" class="text-[10px] text-charcoal/40 dark:text-[#6a6a6e]">mulai dari</span>
                        </div>

                        <div class="mt-2 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full" :class="displayStock > 0 ? 'bg-ink-60' : 'bg-ink-20'"></span>
                            <span class="text-xs font-medium" :class="displayStock > 0 ? 'text-ink-60 dark:text-[#8a8a8e]' : 'text-ink-40'">
                                {{ displayStock > 0 ? `Tersedia (${displayStock} pcs)` : 'Stok habis kak :(' }}
                            </span>
                        </div>

                        <!-- Deskripsi — full, di bawah harga -->
                        <div v-if="product.description" class="mt-4 pt-4 border-t border-ink-10 dark:border-[#303032]">
                            <div class="text-xs text-charcoal/60 dark:text-[#8a8a8e] leading-relaxed prose prose-xs max-w-none" v-html="product.description"></div>
                        </div>

                        <!-- Varian -->
                        <div v-if="product.variants && product.variants.filter(v => v.is_active).length > 0" class="mt-4 pt-4 border-t border-ink-10 dark:border-[#303032]">
                            <p class="text-[10px] font-semibold text-charcoal/50 dark:text-[#8a8a8e] mb-2">Varian</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="v in product.variants.filter(v => v.is_active)"
                                    :key="v.id"
                                    @click="selectedVariant = selectedVariant?.id === v.id ? null : v"
                                    :disabled="v.stock === 0"
                                    class="px-3 py-1.5 rounded-lg border-2 text-xs font-semibold transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed"
                                    :class="selectedVariant?.id === v.id
                                        ? 'border-charcoal dark:border-[#f0eeeb] bg-charcoal dark:bg-[#f0eeeb] text-white dark:text-[#161618]'
                                        : 'border-maroon-100 dark:border-[#303032] text-charcoal dark:text-[#f0eeeb] hover:border-charcoal dark:hover:border-[#f0eeeb]'"
                                >
                                    {{ v.name }}
                                    <span v-if="v.stock === 0" class="ml-1 opacity-60">(Habis)</span>
                                </button>
                            </div>
                            <p v-if="selectedVariant" class="mt-2 text-xs text-charcoal/50 dark:text-[#8a8a8e]">
                                Stok: {{ selectedVariant.stock }} pcs
                                <span v-if="selectedVariant.price !== product.price"> · Harga: Rp{{ formatPrice(selectedVariant.price) }}</span>
                            </p>
                        </div>

                        <!-- Jumlah -->
                        <div class="mt-4">
                            <p class="text-[10px] font-semibold text-charcoal/50 dark:text-[#8a8a8e] mb-1.5">Jumlah</p>
                            <div class="flex items-center gap-3">
                                <button @click="decrementQty" class="w-8 h-8 rounded-lg border-2 border-ink-10 dark:border-[#303032] flex items-center justify-center text-sm font-semibold text-charcoal/50 dark:text-[#8a8a8e] hover:border-ink hover:text-ink dark:hover:border-[#f0eeeb] dark:hover:text-[#f0eeeb] transition-colors active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed" :disabled="quantity <= 1">−</button>
                                <span class="w-10 text-center text-base font-bold text-charcoal dark:text-[#f0eeeb]">{{ quantity }}</span>
                                <button @click="quantity++" class="w-8 h-8 rounded-lg border-2 border-ink-10 dark:border-[#303032] flex items-center justify-center text-sm font-semibold text-charcoal/50 dark:text-[#8a8a8e] hover:border-ink hover:text-ink dark:hover:border-[#f0eeeb] dark:hover:text-[#f0eeeb] transition-colors active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed" :disabled="quantity >= displayStock">+</button>
                            </div>
                        </div>

                        <!-- Error varian -->
                        <p v-if="variantError" class="mt-3 text-xs font-medium text-red-500 dark:text-red-400">{{ variantError }}</p>

                        <!-- Tombol keranjang -->
                        <div class="mt-4 flex gap-2">
                            <button @click="addToCart" :disabled="!canAddToCart" class="flex-1 px-6 py-3 bg-ink dark:bg-[#f0eeeb] text-white dark:text-[#161618] text-sm font-semibold rounded-xl hover:bg-ink-60 dark:hover:bg-[#d0ceca] transition-all active:scale-[0.98] disabled:bg-ink-10 dark:disabled:bg-[#303032] disabled:cursor-not-allowed disabled:active:scale-100 shadow-lg">
                                {{ displayStock === 0 ? 'Stok Habis' : hasVariants && !selectedVariant ? 'Pilih Varian Dulu' : 'Masukin ke Keranjang' }}
                            </button>
                            <button @click="toggleWishlist(product.id)" class="w-12 h-11 flex items-center justify-center rounded-xl border-2 transition-all active:scale-95" :class="isWishlisted(product.id) ? 'bg-ink-05 dark:bg-[#242426] border-ink dark:border-[#f0eeeb] text-ink dark:text-[#f0eeeb]' : 'border-ink-10 dark:border-[#303032] text-charcoal/50 dark:text-[#8a8a8e] hover:border-ink hover:text-ink dark:hover:border-[#f0eeeb] dark:hover:text-[#f0eeeb]'">
                                <HeartIcon class="w-[18px] h-[18px]" :class="isWishlisted(product.id) ? 'fill-current' : ''" />
                            </button>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-charcoal/50 dark:text-[#8a8a8e]">
                            <span class="flex items-center gap-1.5">
                                <ArchiveBoxIcon class="w-3.5 h-3.5" />
                                Kemasan Premium
                            </span>
                            <span class="flex items-center gap-1.5">
                                <CheckIcon class="w-3.5 h-3.5" />
                                Original 100%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <teleport to="body">
                <div v-if="showDescriptionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showDescriptionModal = false">
                    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
                    <div class="relative bg-white dark:bg-[#1c1c1e] rounded-2xl max-w-lg w-full max-h-[70vh] overflow-y-auto p-6 shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb]">Deskripsi</h3>
                            <button @click="showDescriptionModal = false" class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-ink-05 dark:bg-[#242426] dark:hover:bg-[#303032] transition-colors">
                                <XMarkIcon class="w-3.5 h-3.5" />
                            </button>
                        </div>
                        <p class="text-sm text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#d0ceca] leading-relaxed whitespace-pre-line">{{ product.description }}</p>
                    </div>
                </div>
            </teleport>

            <!-- Reviews Section -->
            <section class="py-8 lg:py-12 border-t border-ink-10 dark:border-[#303032] mt-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3 mb-6">
                        <h2 class="text-lg lg:text-xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Ulasan Pembeli</h2>
                        <span v-if="reviews.length" class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-maroon-50 dark:bg-maroon/20 rounded-full text-xs font-semibold text-maroon dark:text-[#f0eeeb]">
                            ★ {{ avgRating }} · {{ reviews.length }} ulasan
                        </span>
                    </div>

                    <div v-if="reviewsLoading" class="flex justify-center py-8">
                        <div class="w-6 h-6 border-2 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
                    </div>

                    <div v-else-if="!reviews.length" class="py-8 text-center">
                        <p class="text-sm text-charcoal/40 dark:text-[#6a6a6e]">Belum ada ulasan untuk produk ini.</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="review in reviews" :key="review.id" class="bg-maroon-50/40 dark:bg-[#1c1c1e] rounded-xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-charcoal dark:text-[#f0eeeb]">{{ review.user?.name || 'Pembeli' }}</p>
                                    <div class="flex items-center gap-0.5 mt-0.5">
                                        <span v-for="i in 5" :key="i" class="text-xs" :class="i <= review.rating ? 'text-yellow-400' : 'text-charcoal/20 dark:text-slate-600'">★</span>
                                    </div>
                                </div>
                                <p class="text-xs text-charcoal/40 dark:text-[#6a6a6e] shrink-0">{{ formatDate(review.created_at) }}</p>
                            </div>
                            <p v-if="review.comment" class="mt-2 text-sm text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#d0ceca] leading-relaxed">{{ review.comment }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-8 lg:py-12 bg-coklat-50/20 dark:bg-[#1c1c1e]/30 mt-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-lg lg:text-xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Produk Terkait</h2>
                            <p class="text-xs text-charcoal/50 dark:text-[#8a8a8e] mt-0.5">Koleksi lain dari kategori yang sama</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="scrollRelated('left')" class="w-7 h-7 rounded-lg border border-maroon-200/60 dark:border-[#303032] flex items-center justify-center text-charcoal/50 dark:text-[#8a8a8e] hover:border-maroon hover:text-maroon dark:hover:border-[#f0eeeb] dark:hover:text-[#f0eeeb] transition-colors active:scale-95">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="15 18 9 12 15 6"/></svg>
                            </button>
                            <button @click="scrollRelated('right')" class="w-7 h-7 rounded-lg border border-maroon-200/60 dark:border-[#303032] flex items-center justify-center text-charcoal/50 dark:text-[#8a8a8e] hover:border-maroon hover:text-maroon dark:hover:border-[#f0eeeb] dark:hover:text-[#f0eeeb] transition-colors active:scale-95">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        </div>
                    </div>

                    <div ref="relatedCarousel" class="flex gap-4 overflow-x-auto scroll-smooth pb-2" style="scrollbar-width:none;-ms-overflow-style:none;">
                        <div v-for="rp in relatedProducts" :key="rp.id" class="shrink-0 w-[160px] sm:w-[190px] group/card cursor-pointer bg-white dark:bg-[#1c1c1e] rounded-xl overflow-hidden border border-maroon-50 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-slate-500 transition-all hover:shadow-md active:scale-[0.98]" @click="$router.push(`/products/${rp.slug}`)">
                            <div class="aspect-[3/4] bg-maroon-50 overflow-hidden relative">
                                <img :src="productImage(rp, 0)" :alt="rp.name" class="absolute inset-0 w-full h-full object-cover" />
                                <div v-if="rp.stock > 0 && rp.stock <= 5" class="absolute top-1.5 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ rp.stock }}</div>
                                <div v-if="rp.stock === 0" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                    <span class="bg-charcoal dark:bg-[#f0eeeb] text-white dark:text-[#161618] text-[10px] font-semibold px-2 py-1 rounded-lg">Stok Habis</span>
                                </div>
                                <button @click.stop="addItem(rp, 1)" :disabled="rp.stock === 0" class="absolute bottom-0 left-0 right-0 py-2 bg-maroon text-white text-[10px] font-semibold tracking-wide translate-y-full group-hover/card:translate-y-0 transition-transform duration-300 hover:bg-maroon-600 dark:hover:bg-maroon/80 disabled:opacity-0">
                                    {{ rp.stock === 0 ? 'Stok Habis' : '+ Keranjang' }}
                                </button>
                            </div>
                            <div class="p-2.5">
                                <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ rp.categories?.map(c => c.name).join(', ') || '' }}</p>
                                <h3 class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb] mt-0.5 leading-snug line-clamp-2">{{ rp.name }}</h3>
                                <p class="text-sm font-bold text-maroon dark:text-[#f0eeeb] mt-1">Rp{{ formatPrice(rp.price) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="!relatedProducts.length" class="text-center py-8">
                        <p class="text-sm text-charcoal/50 dark:text-[#8a8a8e]">Belum ada produk terkait.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { HeartIcon, ArrowLeftIcon, ArchiveBoxIcon, CheckIcon, TruckIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { useRoute } from 'vue-router'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'
import api from '../api'

const route = useRoute()
const { addItem } = useCartStore()
const quantity = ref(1)
const selectedMedia = ref({ type: 'image', index: 0 })
const showDescriptionModal = ref(false)
const wishlist = ref(new Set())
const relatedCarousel = ref(null)
const product = ref(null)
const selectedVariant = ref(null)
const relatedProducts = ref([])
const reviews = ref([])
const reviewsLoading = ref(false)
const avgRating = ref(0)
const reviewsTotal = ref(0)
const notFound = ref(false)

// Varian aktif saja
const activeVariants = computed(() =>
    product.value?.variants?.filter(v => v.is_active) || []
)

// Produk ini punya varian?
const hasVariants = computed(() => activeVariants.value.length > 0)

// Harga tampil: jika ada varian dipilih pakai harga varian, jika belum tampilkan harga terendah dari varian
const displayPrice = computed(() => {
    if (selectedVariant.value) return selectedVariant.value.price
    if (hasVariants.value) {
        const prices = activeVariants.value.map(v => v.price).filter(p => p > 0)
        return prices.length ? Math.min(...prices) : product.value?.price
    }
    return product.value?.price
})

// Stok tampil: jika varian dipilih pakai stok varian, else stok produk
const displayStock = computed(() => {
    if (selectedVariant.value) return selectedVariant.value.stock
    if (hasVariants.value) {
        return activeVariants.value.reduce((sum, v) => sum + (v.stock || 0), 0)
    }
    return product.value?.stock || 0
})

// Apakah bisa add to cart
const canAddToCart = computed(() => {
    if (!product.value) return false
    if (hasVariants.value && !selectedVariant.value) return false
    return displayStock.value > 0
})

// Error message saat add to cart
const variantError = ref('')

async function fetchProduct(slug) {
    loading.value = true
    notFound.value = false
    selectedMedia.value = { type: 'image', index: 0 }
    quantity.value = 1
    reviews.value = []
    selectedVariant.value = null
    try {
        const res = await api.get(`/products/${slug}`)
        product.value = res.data.data || res.data

        // Debug: log image URLs untuk tracking 404
        console.group(`[ProductDetail] ${slug}`)
        console.log('thumbnail:', product.value?.thumbnail)
        console.log('images:', product.value?.images?.map(img => ({
            id: img.id,
            path: img.path,
            raw_path: img.raw_path,
            sort_order: img.sort_order
        })))
        console.groupEnd()

        fetchRelated(product.value)
        fetchReviews(slug)
    } catch (e) {
        if (e.response?.status === 404) {
            notFound.value = true
        }
        product.value = null
    } finally {
        loading.value = false
    }
}

async function fetchReviews(slug) {
    reviewsLoading.value = true
    try {
        const res = await api.get(`/products/${slug}/reviews`)
        // res.data.data adalah paginator object { data: [...], total: N }
        reviews.value = res.data.data?.data || []
        avgRating.value = res.data.avg_rating || 0
        reviewsTotal.value = res.data.data?.total || 0
    } catch {
        reviews.value = []
    } finally {
        reviewsLoading.value = false
    }
}

async function fetchRelated(p) {
    const categorySlug = p?.categories?.[0]?.slug
    if (!categorySlug) return
    try {
        const res = await api.get('/products', { params: { category: categorySlug, per_page: 10 } })
        const all = res.data.data || res.data
        relatedProducts.value = all.filter(r => r.id !== p.id).slice(0, 10)
    } catch {
        relatedProducts.value = []
    }
}

onMounted(() => fetchProduct(route.params.slug))
watch(() => route.params.slug, (slug) => { if (slug) fetchProduct(slug) })

const isDescriptionShort = computed(() => {
    if (!product.value?.description) return true
    return product.value.description.length < 150
})

function formatDate(dateString) {
    if (!dateString) return ''
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(dateString))
}

function productImage(p, index) {
    // index 0 = selalu thumbnail, index 1+ = gallery images[index-1]
    if (index === 0) return p.thumbnail || ''
    const imgIndex = index - 1
    if (p.images && p.images[imgIndex]) return p.images[imgIndex].path
    return p.thumbnail || ''
}

function getYoutubeVideoId(url) {
    if (!url) return ''
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
        /youtube\.com\/shorts\/([^&\n?#]+)/,
    ]
    for (const pattern of patterns) {
        const match = url.match(pattern)
        if (match) return match[1]
    }
    return ''
}

function getYoutubeEmbedUrl(url) {
    const id = getYoutubeVideoId(url)
    return id ? `https://www.youtube.com/embed/${id}` : ''
}

function decrementQty() {
    if (quantity.value > 1) quantity.value--
}

function addToCart() {
    if (!product.value) return
    if (hasVariants.value && !selectedVariant.value) {
        variantError.value = 'Pilih varian dulu ya!'
        return
    }
    variantError.value = ''
    const item = selectedVariant.value
        ? { ...product.value, selectedVariant: selectedVariant.value }
        : product.value
    addItem(item, quantity.value)
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
</script>
