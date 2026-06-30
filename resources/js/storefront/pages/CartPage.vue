<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <h1 class="text-2xl lg:text-3xl font-bold text-charcoal tracking-tight">Keranjang</h1>

        <div v-if="!items.length" class="text-center py-16 lg:py-20">
            <svg class="w-12 h-12 mx-auto text-maroon-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
            </svg>
            <p class="mt-4 text-base text-charcoal/50">Keranjang belanja Anda kosong.</p>
            <router-link to="/" class="inline-block mt-6 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] shadow-lg">
                Mulai Belanja
            </router-link>
        </div>

        <div v-else class="mt-6 lg:mt-8">
            <div class="space-y-3">
                <div v-for="item in items" :key="item.product_id" class="flex items-center gap-4 bg-white p-4 rounded-xl border-2 border-maroon-50 hover:border-maroon-200 transition-all">
                    <div class="w-16 h-20 lg:w-20 lg:h-24 shrink-0 bg-maroon-50 rounded-lg overflow-hidden">
                        <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-maroon-200/60 text-lg font-bold">A</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-charcoal truncate">{{ item.name }}</h3>
                        <p class="text-sm font-bold text-maroon mt-0.5">Rp{{ formatPrice(item.price) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="decrease(item.product_id)" class="w-8 h-8 rounded-lg border-2 border-maroon-200/60 flex items-center justify-center text-sm font-semibold text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">−</button>
                        <span class="w-8 text-center text-sm font-bold text-charcoal">{{ item.quantity }}</span>
                        <button @click="updateQuantity(item.product_id, item.quantity + 1)" class="w-8 h-8 rounded-lg border-2 border-maroon-200/60 flex items-center justify-center text-sm font-semibold text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">+</button>
                    </div>
                    <p class="font-bold text-sm w-20 text-right text-charcoal hidden sm:block">Rp{{ formatPrice(item.price * item.quantity) }}</p>
                    <button @click="removeItem(item.product_id)" class="text-charcoal/40 hover:text-red-500 transition-colors p-1" aria-label="Remove">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t-2 border-maroon-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-charcoal/50">Total</p>
                    <p class="text-2xl lg:text-3xl font-bold text-maroon">Rp{{ formatPrice(total()) }}</p>
                </div>
                <div class="flex gap-3">
                    <router-link to="/" class="px-6 py-3 rounded-xl border-2 border-maroon-200/60 text-sm font-semibold text-charcoal/50 hover:border-maroon hover:text-maroon transition-all active:scale-[0.98]">
                        Lanjut Belanja
                    </router-link>
                    <router-link to="/checkout" class="px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] shadow-lg">
                        Checkout
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useCartStore } from '../cart'

const { items, updateQuantity, removeItem, total } = useCartStore()

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price)
}

function decrease(productId) {
    const item = items.value.find(i => i.product_id === productId)
    if (item && item.quantity > 1) {
        updateQuantity(productId, item.quantity - 1)
    } else {
        removeItem(productId)
    }
}
</script>
