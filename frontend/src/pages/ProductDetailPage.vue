<template>
    <div class="min-h-screen">
        <div v-if="!product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 text-center">
            <p class="text-lg text-charcoal/50">Produk gak ditemukan nih :(</p>
            <router-link to="/" class="inline-block mt-4 text-sm font-semibold text-maroon hover:text-maroon-600 transition-colors">Kembali ke Beranda</router-link>
        </div>

        <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
            <router-link to="/#shop" class="inline-flex items-center gap-2 text-sm font-medium text-charcoal/50 hover:text-maroon transition-colors mb-6">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali
            </router-link>

            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
                <div>
                    <div class="aspect-[3/4] bg-maroon-50 rounded-2xl overflow-hidden relative">
                        <img :src="product.thumbnail" :alt="product.name" class="w-full h-full object-cover" />
                    </div>
                </div>

                <div class="lg:sticky lg:top-28 lg:self-start">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-coklat-50 rounded-full text-coklat text-xs font-semibold">{{ product.category?.name || 'Kemeja' }}</span>
                    <h1 class="text-3xl lg:text-4xl font-bold text-charcoal mt-3 tracking-tight">{{ product.name }}</h1>

                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-maroon">Rp{{ formatPrice(product.price) }}</span>
                        <span class="text-sm text-charcoal/40 line-through">Rp{{ formatPrice(product.price + 50000) }}</span>
                    </div>

                    <div class="mt-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" :class="product.stock > 0 ? 'bg-green-500' : 'bg-red-400'"></span>
                        <span class="text-sm font-medium" :class="product.stock > 0 ? 'text-green-700' : 'text-red-600'">
                            {{ product.stock > 0 ? `Stok tersedia (${product.stock} pcs)` : 'Stok habis kak :(' }}
                        </span>
                    </div>

                    <div class="mt-6">
                        <p class="text-xs font-semibold text-charcoal/50 uppercase tracking-wide mb-2">Jumlah</p>
                        <div class="flex items-center gap-3">
                            <button @click="decrementQty" class="w-10 h-10 rounded-xl border-2 border-maroon-200 flex items-center justify-center text-lg font-medium text-charcoal hover:border-maroon hover:text-maroon transition-colors active:scale-95" :disabled="quantity <= 1">−</button>
                            <span class="w-10 text-center text-lg font-bold">{{ quantity }}</span>
                            <button @click="quantity++" class="w-10 h-10 rounded-xl border-2 border-maroon-200 flex items-center justify-center text-lg font-medium text-charcoal hover:border-maroon hover:text-maroon transition-colors active:scale-95" :disabled="quantity >= product.stock">+</button>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button @click="addToCart" :disabled="product.stock === 0" class="flex-1 px-8 py-3.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:bg-maroon-200 disabled:cursor-not-allowed disabled:active:scale-100">
                            {{ product.stock === 0 ? 'Stok Habis' : 'Masukin ke Keranjang' }}
                        </button>
                    </div>

                    <div class="mt-8 pt-6 border-t border-maroon-100">
                        <p class="text-xs font-semibold text-charcoal/50 uppercase tracking-wide mb-3">Deskripsi</p>
                        <p class="text-base text-charcoal/65 leading-relaxed">{{ product.description }}</p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-maroon-100">
                        <div class="flex flex-wrap gap-4 text-sm text-charcoal/50">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-maroon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2"/></svg>
                                Kemasan Premium
                            </span>
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-maroon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                Original 100%
                            </span>
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-maroon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                Gratis Ongkir
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useCartStore } from '../cart'
import { products, formatPrice } from '../mock-data'

const route = useRoute()
const { addItem } = useCartStore()
const quantity = ref(1)

const product = computed(() => products.find(p => p.slug === route.params.slug) || null)

function decrementQty() {
    if (quantity.value > 1) quantity.value--
}

function addToCart() {
    if (product.value && quantity.value > 0) {
        addItem(product.value, quantity.value)
    }
}
</script>
