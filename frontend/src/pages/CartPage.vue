<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16">
        <h1 class="text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Keranjang Belanja</h1>

        <div v-if="!items.length" class="text-center py-16 lg:py-24">
            <ShoppingCartIcon class="w-16 h-16 mx-auto text-maroon-200" />
            <p class="mt-4 text-lg text-charcoal/50 dark:text-[#8a8a8e]">Wah, keranjangmu masih kosong nih!</p>
            <p class="text-sm text-charcoal/40 dark:text-[#6a6a6e]">Yuk, cari kemeja favoritmu dulu.</p>
            <router-link to="/" class="inline-block mt-6 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
                Mulai Belanja
            </router-link>
        </div>

        <div v-else class="mt-8 lg:mt-10">
            <!-- Select All -->
            <div class="flex items-center gap-3 mb-4 px-1">
                <button @click="toggleAll" class="flex items-center gap-2.5 group" aria-label="Pilih semua">
                    <span class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all shrink-0"
                        :class="isAllSelected
                            ? 'bg-maroon border-maroon dark:bg-[#f0eeeb] dark:border-[#f0eeeb]'
                            : isPartialSelected
                                ? 'bg-maroon/20 border-maroon'
                                : 'border-maroon-200 dark:border-[#303032] group-hover:border-maroon'">
                        <CheckIcon v-if="isAllSelected" class="w-3 h-3 text-white dark:text-[#161618]" />
                        <MinusIcon v-else-if="isPartialSelected" class="w-3 h-3 text-maroon" />
                    </span>
                    <span class="text-sm font-semibold text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#8a8a8e]">
                        Pilih Semua ({{ items.length }} produk)
                    </span>
                </button>
            </div>

            <div class="space-y-3">
                <div v-for="item in items" :key="item.product_id"
                    class="flex items-center gap-3 bg-white dark:bg-[#1c1c1e] p-3 lg:p-5 rounded-2xl border-2 transition-all cursor-pointer"
                    :class="selectedIds.has(item.product_id)
                        ? 'border-maroon dark:border-maroon/70'
                        : 'border-maroon-50 dark:border-[#303032] hover:border-maroon-100 dark:hover:border-slate-600'"
                    @click.self="toggleItem(item.product_id)">

                    <!-- Checkbox -->
                    <button @click="toggleItem(item.product_id)" class="shrink-0 flex items-center justify-center" aria-label="Pilih item">
                        <span class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all"
                            :class="selectedIds.has(item.product_id)
                                ? 'bg-[#f0eeeb] border-[#f0eeeb] dark:bg-[#f0eeeb] dark:border-[#f0eeeb]'
                                : 'border-maroon-200 dark:border-[#303032] hover:border-maroon'">
                            <CheckIcon v-if="selectedIds.has(item.product_id)" class="w-3 h-3 text-maroon dark:text-[#161618]" />
                        </span>
                    </button>

                    <!-- Thumbnail -->
                    <div class="w-16 h-20 lg:w-20 lg:h-24 shrink-0 bg-maroon-50 dark:bg-[#28282a] rounded-xl overflow-hidden">
                        <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-maroon-300 text-lg font-bold">A</div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-charcoal dark:text-[#f0eeeb]">{{ item.name }}</h3>
                        <p class="text-sm font-medium text-maroon dark:text-[#f0eeeb] mt-0.5">Rp{{ formatPrice(item.price) }}</p>
                    </div>

                    <!-- Qty controls -->
                    <div class="flex items-center gap-2">
                        <button @click="decrease(item.product_id)" class="w-8 h-8 rounded-lg border-2 border-maroon-200 dark:border-[#303032] flex items-center justify-center text-sm font-bold text-charcoal dark:text-[#d0ceca] hover:border-maroon hover:text-maroon dark:hover:text-[#f0eeeb] dark:hover:border-[#f0eeeb] transition-colors active:scale-95">−</button>
                        <span class="w-8 text-center text-sm font-bold dark:text-[#f0eeeb]">{{ item.quantity }}</span>
                        <button @click="updateQuantity(item.product_id, item.quantity + 1)" class="w-8 h-8 rounded-lg border-2 border-maroon-200 dark:border-[#303032] flex items-center justify-center text-sm font-bold text-charcoal dark:text-[#d0ceca] hover:border-maroon hover:text-maroon dark:hover:text-[#f0eeeb] dark:hover:border-[#f0eeeb] transition-colors active:scale-95">+</button>
                    </div>

                    <!-- Subtotal -->
                    <p class="font-bold text-xs lg:text-sm w-16 lg:w-20 text-right text-charcoal dark:text-[#f0eeeb] hidden sm:block">Rp{{ formatPrice(item.price * item.quantity) }}</p>

                    <!-- Remove -->
                    <button @click="removeItem(item.product_id)" class="text-charcoal/30 dark:text-[#6a6a6e]/60 dark:text-[#6a6a6e] hover:text-red-500 transition-colors p-1 active:scale-95" aria-label="Hapus">
                        <XMarkIcon class="w-[18px] h-[18px]" />
                    </button>
                </div>
            </div>

            <!-- Summary -->
            <div class="mt-8 pt-6 border-t-2 border-maroon-100 dark:border-[#303032] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm text-charcoal/50 dark:text-[#8a8a8e]">
                        Total Belanja
                        <span v-if="selectedIds.size" class="ml-1 text-charcoal/40 dark:text-[#6a6a6e]">({{ selectedIds.size }} produk dipilih)</span>
                    </p>
                    <p class="text-xl lg:text-3xl font-bold text-maroon dark:text-[#f0eeeb]">Rp{{ formatPrice(selectedTotal) }}</p>
                    <p v-if="!selectedIds.size" class="text-xs text-charcoal/40 dark:text-[#6a6a6e] mt-1">Pilih produk untuk melanjutkan</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <router-link to="/" class="px-5 py-2.5 bg-white dark:bg-[#1c1c1e] text-charcoal dark:text-[#f0eeeb] text-sm font-semibold rounded-xl border-2 border-maroon-200 dark:border-[#303032] hover:border-maroon hover:bg-maroon-50 dark:hover:bg-[#303032] transition-all active:scale-[0.97] text-center">
                        Lanjut Belanja
                    </router-link>
                    <button @click="goToCheckout"
                        :disabled="!selectedIds.size"
                        class="px-6 py-2.5 text-sm font-semibold rounded-xl transition-all active:scale-[0.97] text-center"
                        :class="selectedIds.size
                            ? 'bg-ink dark:bg-[#f0eeeb] text-white dark:text-[#161618] hover:bg-ink-60 dark:hover:bg-[#d0ceca] shadow-lg'
                            : 'bg-maroon-100 dark:bg-[#28282a] text-maroon-300 dark:text-[#6a6a6e] cursor-not-allowed'">
                        Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ShoppingCartIcon, XMarkIcon, CheckIcon, MinusIcon } from '@heroicons/vue/24/outline'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'

const router = useRouter()
const { items, updateQuantity, removeItem, total } = useCartStore()

// Selection state
const selectedIds = ref(new Set(items.value.map(i => i.product_id)))

const isAllSelected = computed(() => selectedIds.value.size === items.value.length && items.value.length > 0)
const isPartialSelected = computed(() => selectedIds.value.size > 0 && selectedIds.value.size < items.value.length)

const selectedTotal = computed(() =>
    items.value
        .filter(i => selectedIds.value.has(i.product_id))
        .reduce((sum, i) => sum + i.price * i.quantity, 0)
)

function toggleItem(productId) {
    const next = new Set(selectedIds.value)
    if (next.has(productId)) {
        next.delete(productId)
    } else {
        next.add(productId)
    }
    selectedIds.value = next
}

function toggleAll() {
    if (isAllSelected.value) {
        selectedIds.value = new Set()
    } else {
        selectedIds.value = new Set(items.value.map(i => i.product_id))
    }
}

function decrease(productId) {
    const item = items.value.find(i => i.product_id === productId)
    if (item && item.quantity > 1) {
        updateQuantity(productId, item.quantity - 1)
    } else {
        removeItem(productId)
        const next = new Set(selectedIds.value)
        next.delete(productId)
        selectedIds.value = next
    }
}

function goToCheckout() {
    if (!selectedIds.value.size) return
    router.push({
        path: '/checkout',
        state: { selectedIds: [...selectedIds.value] }
    })
}
</script>
