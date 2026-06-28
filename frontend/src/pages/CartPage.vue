<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <h1 class="text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Keranjang Belanja</h1>

        <div v-if="!items.length" class="text-center py-16 lg:py-24">
            <svg class="w-16 h-16 mx-auto text-coral-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
            </svg>
            <p class="mt-4 text-lg text-charcoal/50">Wah, keranjangmu masih kosong nih!</p>
            <p class="text-sm text-charcoal/40">Yuk, cari kemeja favoritmu dulu.</p>
            <router-link to="/" class="inline-block mt-6 px-8 py-3 bg-coral text-white text-sm font-semibold rounded-xl hover:bg-coral-600 transition-all active:scale-[0.97] shadow-lg shadow-coral/25">
                Mulai Belanja
            </router-link>
        </div>

        <div v-else class="mt-8 lg:mt-10">
            <div class="space-y-3">
                <div v-for="item in items" :key="item.product_id" class="flex items-center gap-4 bg-white p-4 lg:p-5 rounded-2xl border-2 border-coral-50 hover:border-coral-100 transition-all">
                    <div class="w-16 h-20 lg:w-20 lg:h-24 shrink-0 bg-coral-50 rounded-xl overflow-hidden">
                        <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-coral-300 text-lg font-bold">A</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-charcoal">{{ item.name }}</h3>
                        <p class="text-sm font-medium text-coral mt-0.5">Rp{{ formatPrice(item.price) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="decrease(item.product_id)" class="w-8 h-8 rounded-lg border-2 border-coral-200 flex items-center justify-center text-sm font-bold text-charcoal hover:border-coral hover:text-coral transition-colors active:scale-95">−</button>
                        <span class="w-8 text-center text-sm font-bold">{{ item.quantity }}</span>
                        <button @click="updateQuantity(item.product_id, item.quantity + 1)" class="w-8 h-8 rounded-lg border-2 border-coral-200 flex items-center justify-center text-sm font-bold text-charcoal hover:border-coral hover:text-coral transition-colors active:scale-95">+</button>
                    </div>
                    <p class="font-bold text-sm w-20 text-right text-charcoal hidden sm:block">Rp{{ formatPrice(item.price * item.quantity) }}</p>
                    <button @click="removeItem(item.product_id)" class="text-charcoal/30 hover:text-red-500 transition-colors p-1 active:scale-95" aria-label="Hapus">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t-2 border-coral-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm text-charcoal/50">Total Belanja</p>
                    <p class="text-3xl font-bold text-coral">Rp{{ formatPrice(total()) }}</p>
                </div>
                <div class="flex gap-3">
                    <router-link to="/" class="px-6 py-3 bg-white text-charcoal text-sm font-semibold rounded-xl border-2 border-coral-200 hover:border-coral hover:bg-coral-50 transition-all active:scale-[0.97]">
                        Lanjut Belanja
                    </router-link>
                    <router-link to="/checkout" class="px-8 py-3 bg-coral text-white text-sm font-semibold rounded-xl hover:bg-coral-600 transition-all active:scale-[0.97] shadow-lg shadow-coral/25">
                        Checkout
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'

const { items, updateQuantity, removeItem, total } = useCartStore()

function decrease(productId) {
    const item = items.value.find(i => i.product_id === productId)
    if (item && item.quantity > 1) {
        updateQuantity(productId, item.quantity - 1)
    } else {
        removeItem(productId)
    }
}
</script>
