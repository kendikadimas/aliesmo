<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <h1 class="text-2xl lg:text-3xl font-bold text-charcoal tracking-tight">Keranjang Kamu</h1>

        <div v-if="!items.length" class="text-center py-16 lg:py-20">
            <svg class="w-12 h-12 mx-auto text-maroon-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
            </svg>
            <p class="mt-4 text-base text-charcoal/50">Keranjangmu masih kosong nih.</p>
            <p class="mt-1 text-sm text-charcoal/40">Yuk, pilih kemeja yang kamu suka!</p>
            <router-link to="/" class="inline-block mt-6 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] shadow-lg">
                Lihat Koleksi
            </router-link>
        </div>

        <div v-else class="mt-6 lg:mt-8">
            <!-- Select All Bar -->
            <div class="flex items-center justify-between mb-3 px-1">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        :checked="allSelected"
                        :indeterminate="someSelected"
                        @change="toggleSelectAll"
                        class="w-4 h-4 rounded accent-maroon cursor-pointer"
                    >
                    <span class="text-xs font-semibold text-charcoal/70">
                        Pilih Semua ({{ items.length }} produk)
                    </span>
                </label>
                <button
                    v-if="selectedIds.length > 0"
                    @click="removeSelected"
                    class="text-xs font-semibold text-red-500 hover:text-red-600 transition-colors"
                >
                    Hapus Terpilih ({{ selectedIds.length }})
                </button>
            </div>

            <!-- Item List -->
            <div class="space-y-3">
                <div
                    v-for="item in items"
                    :key="item.cart_key || item.product_id"
                    class="flex items-center gap-3 sm:gap-4 bg-white p-3 sm:p-4 rounded-xl border-2 transition-all"
                    :class="isSelected(item)
                        ? 'border-maroon bg-maroon-50/20'
                        : 'border-maroon-50 opacity-60'"
                >
                    <!-- Checkbox -->
                    <div class="shrink-0">
                        <input
                            type="checkbox"
                            :checked="isSelected(item)"
                            @change="toggleSelect(item)"
                            class="w-4 h-4 rounded accent-maroon cursor-pointer"
                        >
                    </div>

                    <!-- Thumbnail -->
                    <div
                        class="w-14 h-18 sm:w-16 sm:h-20 lg:w-20 lg:h-24 shrink-0 bg-maroon-50 rounded-lg overflow-hidden cursor-pointer"
                        @click="toggleSelect(item)"
                    >
                        <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-maroon-200/60 text-lg font-bold">A</div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h3
                            class="text-sm font-semibold text-charcoal truncate cursor-pointer hover:text-maroon transition-colors"
                            @click="$router.push(`/products/${item.slug}`)"
                        >{{ item.name }}</h3>
                        <p v-if="item.variant_name" class="text-xs font-semibold text-charcoal/45 mt-0.5">Varian: {{ item.variant_name }}</p>
                        <p class="text-sm font-bold text-maroon mt-0.5">Rp{{ formatPrice(item.price) }}</p>
                        <p class="text-xs text-charcoal/40 mt-0.5">Subtotal: Rp{{ formatPrice(item.price * item.quantity) }}</p>
                    </div>

                    <!-- Quantity -->
                    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                        <button @click="decrease(item)" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg border-2 border-maroon-200/60 flex items-center justify-center text-sm font-semibold text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">−</button>
                        <span class="w-6 sm:w-8 text-center text-sm font-bold text-charcoal">{{ item.quantity }}</span>
                        <button @click="updateQuantity(item, item.quantity + 1)" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg border-2 border-maroon-200/60 flex items-center justify-center text-sm font-semibold text-charcoal/50 hover:border-maroon hover:text-maroon transition-colors active:scale-95">+</button>
                    </div>

                    <!-- Subtotal desktop -->
                    <p class="font-bold text-sm w-20 text-right text-charcoal hidden sm:block shrink-0">Rp{{ formatPrice(item.price * item.quantity) }}</p>

                    <!-- Remove -->
                    <button @click="removeItem(item)" class="text-charcoal/30 hover:text-red-500 transition-colors p-1 shrink-0" aria-label="Hapus">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Summary & Actions -->
            <div class="mt-6 pt-6 border-t-2 border-maroon-50">
                <!-- Selected summary -->
                <div class="bg-maroon-50/40 rounded-xl p-4 mb-4">
                    <div class="flex items-center justify-between text-xs text-charcoal/60 mb-1">
                        <span>{{ selectedIds.length }} dari {{ items.length }} produk dipilih</span>
                        <span v-if="selectedIds.length === 0" class="text-maroon font-semibold">Pilih produk dulu</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-charcoal/60">Total Terpilih</span>
                        <span class="text-xl sm:text-2xl font-bold text-maroon">Rp{{ formatPrice(selectedTotal()) }}</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <router-link to="/" class="px-6 py-3 rounded-xl border-2 border-maroon-200/60 text-sm font-semibold text-charcoal/50 hover:border-maroon hover:text-maroon transition-all active:scale-[0.98] text-center">
                        Tambah Lagi
                    </router-link>
                    <router-link
                        to="/checkout"
                        class="px-8 py-3 text-sm font-semibold rounded-xl transition-all active:scale-[0.98] shadow-lg text-center"
                        :class="selectedIds.length > 0
                            ? 'bg-maroon text-white hover:bg-maroon-600'
                            : 'bg-maroon-100 text-charcoal/40 pointer-events-none'"
                    >
                        Lanjut Pesan {{ selectedIds.length > 0 ? `(${selectedIds.length} produk)` : '' }}
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../cart'

const router = useRouter()
const { items, selectedIds, selectedItems, updateQuantity, removeItem, toggleSelect, selectAll, deselectAll, isSelected, selectedTotal } = useCartStore()

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price)
}

function decrease(item) {
    if (item && item.quantity > 1) {
        updateQuantity(item, item.quantity - 1)
    } else {
        removeItem(item)
    }
}

const allSelected = computed(() =>
    items.value.length > 0 && selectedIds.value.length === items.value.length
)

const someSelected = computed(() =>
    selectedIds.value.length > 0 && selectedIds.value.length < items.value.length
)

function toggleSelectAll() {
    if (allSelected.value) {
        deselectAll()
    } else {
        selectAll()
    }
}

function removeSelected() {
    const ids = [...selectedIds.value]
    ids.forEach(id => removeItem(id))
}
</script>
