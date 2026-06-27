<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <h1 class="text-3xl lg:text-4xl font-light text-charcoal">Keranjang</h1>

        <div v-if="!items.length" class="text-center py-16 lg:py-24">
            <svg class="w-12 h-12 mx-auto text-aliesmo-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
            </svg>
            <p class="mt-4 text-lg text-charcoal/50">Keranjang belanja Anda kosong.</p>
            <router-link to="/" class="inline-block mt-6 px-8 py-3 bg-charcoal text-ivory text-sm tracking-widest uppercase hover:bg-charcoal/90 transition-all active:scale-[0.98]">
                Mulai Belanja
            </router-link>
        </div>

        <div v-else class="mt-8 lg:mt-10">
            <div class="space-y-4">
                <div v-for="item in items" :key="item.product_id" class="flex items-center gap-4 lg:gap-6 bg-white p-4 lg:p-6 border border-aliesmo-200/50">
                    <div class="w-16 h-20 lg:w-20 lg:h-24 shrink-0 bg-aliesmo-100 overflow-hidden">
                        <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-aliesmo-300/40 text-lg">A</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-charcoal truncate">{{ item.name }}</h3>
                        <p class="text-sm text-charcoal/60 mt-0.5">Rp {{ formatPrice(item.price) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="decrease(item.product_id)" class="w-8 h-8 border border-aliesmo-200/70 flex items-center justify-center text-sm hover:border-charcoal/30 transition-colors active:scale-95">−</button>
                        <span class="w-8 text-center text-sm font-medium">{{ item.quantity }}</span>
                        <button @click="updateQuantity(item.product_id, item.quantity + 1)" class="w-8 h-8 border border-aliesmo-200/70 flex items-center justify-center text-sm hover:border-charcoal/30 transition-colors active:scale-95">+</button>
                    </div>
                    <p class="font-medium text-sm w-24 text-right hidden sm:block">Rp {{ formatPrice(item.price * item.quantity) }}</p>
                    <button @click="removeItem(item.product_id)" class="text-charcoal/40 hover:text-charcoal transition-colors p-1" aria-label="Remove">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="square">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-aliesmo-200/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm text-charcoal/60">Total</p>
                    <p class="text-3xl font-light text-charcoal">Rp {{ formatPrice(total()) }}</p>
                </div>
                <div class="flex gap-3">
                    <router-link to="/" class="px-6 py-3 border border-charcoal/20 text-sm tracking-widest uppercase text-charcoal/70 hover:bg-charcoal hover:text-ivory transition-all active:scale-[0.98]">
                        Lanjut Belanja
                    </router-link>
                    <router-link to="/checkout" class="px-8 py-3 bg-charcoal text-ivory text-sm tracking-widest uppercase hover:bg-charcoal/90 transition-all active:scale-[0.98]">
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
