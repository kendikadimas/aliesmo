<template>
    <div class="min-h-screen">
        <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
                <div class="aspect-[3/4] bg-aliesmo-100 animate-pulse"></div>
                <div class="space-y-4">
                    <div class="h-3 bg-aliesmo-200/50 w-1/4 animate-pulse"></div>
                    <div class="h-6 bg-aliesmo-200/50 w-3/4 animate-pulse"></div>
                    <div class="h-5 bg-aliesmo-200/50 w-1/3 animate-pulse"></div>
                    <div class="h-20 bg-aliesmo-200/50 w-full animate-pulse mt-6"></div>
                </div>
            </div>
        </div>

        <div v-else-if="!product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 text-center">
            <p class="text-lg text-charcoal/50">Produk tidak ditemukan.</p>
            <router-link to="/" class="inline-block mt-4 text-sm tracking-widest uppercase text-bronze hover:text-charcoal transition-colors">Kembali</router-link>
        </div>

        <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
            <router-link to="/#shop" class="inline-flex items-center gap-2 text-xs tracking-widest uppercase text-charcoal/50 hover:text-charcoal transition-colors mb-8">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Kembali
            </router-link>

            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
                <div>
                    <div class="aspect-[3/4] bg-aliesmo-100 overflow-hidden relative">
                        <img v-if="product.thumbnail" :src="product.thumbnail" :alt="product.name" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <span class="text-8xl font-light text-aliesmo-300/40 select-none">A</span>
                        </div>
                    </div>
                    <div v-if="product.images?.length" class="flex gap-3 mt-4 overflow-x-auto pb-2">
                        <div v-for="(img, i) in product.images" :key="i" class="w-20 h-20 shrink-0 bg-aliesmo-100 overflow-hidden cursor-pointer" @click="selectedImage = i">
                            <img :src="img.path" :alt="`${product.name} ${i + 1}`" class="w-full h-full object-cover" />
                        </div>
                    </div>
                </div>

                <div class="lg:sticky lg:top-28 lg:self-start">
                    <p class="text-xs tracking-[0.25em] uppercase text-bronze/70">{{ product.category?.name || 'Kemeja' }}</p>
                    <h1 class="text-3xl lg:text-4xl font-light text-charcoal mt-2">{{ product.name }}</h1>

                    <div class="mt-6 flex items-baseline gap-3">
                        <span class="text-3xl font-medium text-charcoal">Rp {{ formatPrice(product.price) }}</span>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" :class="product.stock > 0 ? 'bg-green-500' : 'bg-red-400'"></span>
                        <span class="text-sm" :class="product.stock > 0 ? 'text-green-700' : 'text-red-600'">
                            {{ product.stock > 0 ? `Tersedia (${product.stock} pcs)` : 'Stok Habis' }}
                        </span>
                    </div>

                    <div class="mt-8">
                        <p class="text-xs tracking-widest uppercase text-charcoal/50 mb-3">Jumlah</p>
                        <div class="flex items-center gap-4">
                            <button @click="decrementQty" class="w-10 h-10 border border-aliesmo-200/70 flex items-center justify-center text-lg hover:border-charcoal/30 transition-colors active:scale-95" :disabled="quantity <= 1">−</button>
                            <span class="w-12 text-center text-lg font-medium">{{ quantity }}</span>
                            <button @click="quantity++" class="w-10 h-10 border border-aliesmo-200/70 flex items-center justify-center text-lg hover:border-charcoal/30 transition-colors active:scale-95" :disabled="quantity >= product.stock">+</button>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <button @click="addToCart" :disabled="product.stock === 0" class="flex-1 px-8 py-3.5 bg-charcoal text-ivory text-sm tracking-widest uppercase hover:bg-charcoal/90 transition-all active:scale-[0.98] disabled:bg-aliesmo-300 disabled:cursor-not-allowed disabled:active:scale-100">
                            {{ product.stock === 0 ? 'Stok Habis' : 'Tambahkan ke Keranjang' }}
                        </button>
                    </div>

                    <div class="mt-8 pt-8 border-t border-aliesmo-200/50">
                        <p class="text-xs tracking-widest uppercase text-charcoal/50 mb-4">Deskripsi</p>
                        <p class="text-base text-charcoal/70 leading-relaxed">{{ product.description || 'Tidak ada deskripsi.' }}</p>
                    </div>

                    <div class="mt-6 pt-6 border-t border-aliesmo-200/50">
                        <div class="flex items-center gap-6 text-sm text-charcoal/60">
                            <span class="flex items-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                    <rect x="3" y="7" width="18" height="13" rx="2"/>
                                    <path d="M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2"/>
                                </svg>
                                Premium Packaging
                            </span>
                            <span class="flex items-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                100% Original
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import { useCartStore } from '../cart'

const route = useRoute()
const { addItem } = useCartStore()
const product = ref(null)
const loading = ref(true)
const quantity = ref(1)

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price)
}

function decrementQty() {
    if (quantity.value > 1) quantity.value--
}

function addToCart() {
    if (product.value && quantity.value > 0) {
        addItem(product.value, quantity.value)
    }
}

onMounted(async () => {
    try {
        const res = await api.get(`/products/${route.params.slug}`)
        product.value = res.data.data
        document.title = `${product.value.name} — Aliesmo`
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
})
</script>
