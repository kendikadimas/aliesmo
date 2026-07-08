<template>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-24 text-center">
        <div v-if="loading" class="py-24">
            <div class="inline-block w-8 h-8 border-2 border-charcoal/20 border-t-charcoal rounded-full animate-spin"></div>
            <p class="mt-4 text-sm text-charcoal/60">Memuat pesanan...</p>
        </div>

        <div v-else-if="!order" class="py-16">
            <svg class="w-12 h-12 mx-auto text-ink-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p class="mt-4 text-lg text-charcoal/50">Pesanan tidak ditemukan.</p>
            <router-link to="/" class="inline-block mt-6 text-sm tracking-widest uppercase text-ink-60 hover:text-charcoal transition-colors underline underline-offset-4">Kembali ke Beranda</router-link>
        </div>

        <div v-else>
            <div class="w-16 h-16 rounded-full bg-ink-05 border border-ink-10 flex items-center justify-center mx-auto">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0a0a0a" stroke-width="1.5" stroke-linecap="square">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <h1 class="mt-6 text-3xl lg:text-4xl font-light text-charcoal">Pesanan Berhasil!</h1>
            <p class="mt-3 text-base text-charcoal/60">Terima kasih, <strong>{{ order.customer_name }}</strong>. Pesanan Anda telah tercatat.</p>

            <div class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-ink-05 border border-ink-10 text-sm text-charcoal/70">
                <span class="text-xs tracking-widest uppercase text-charcoal/50">No. Pesanan:</span>
                <span class="font-medium tracking-wider">{{ order.order_number }}</span>
            </div>

            <div class="mt-2 text-sm text-ink-40">
                Status: {{ statusLabel }}
            </div>

            <div class="mt-8 bg-white p-6 lg:p-8 border border-ink-10 text-left">
                <h2 class="text-sm tracking-widest uppercase text-charcoal/70 mb-6">Detail Pesanan</h2>
                <div class="space-y-3">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                        <span class="text-charcoal/70">{{ item.product_name }} <span class="text-charcoal/40">×{{ item.quantity }}</span></span>
                        <span class="font-medium">Rp {{ formatPrice(item.subtotal) }}</span>
                    </div>
                </div>
                <div class="border-t border-ink-10 mt-4 pt-4 space-y-1.5 text-sm">
                    <div class="flex justify-between text-charcoal/70">
                        <span>Subtotal</span>
                        <span>Rp {{ formatPrice(order.subtotal) }}</span>
                    </div>
                    <div v-if="order.shipping_cost" class="flex justify-between text-charcoal/70">
                        <span>Ongkos Kirim</span>
                        <span>Rp {{ formatPrice(order.shipping_cost) }}</span>
                    </div>
                    <div class="flex justify-between font-medium text-charcoal pt-2 border-t border-ink-10 text-base">
                        <span>Total</span>
                        <span>Rp {{ formatPrice(order.total) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white p-6 lg:p-8 border border-ink-10 text-left">
                <h2 class="text-sm tracking-widest uppercase text-charcoal/70 mb-4">Informasi Pengiriman</h2>
                <div class="text-sm text-charcoal/70 space-y-1 leading-relaxed">
                    <p><span class="text-charcoal/50">Nama:</span> {{ order.customer_name }}</p>
                    <p><span class="text-charcoal/50">Email:</span> {{ order.customer_email }}</p>
                    <p v-if="order.customer_phone"><span class="text-charcoal/50">Telepon:</span> {{ order.customer_phone }}</p>
                    <p><span class="text-charcoal/50">Alamat:</span> {{ order.shipping_address }}</p>
                </div>
            </div>

            <div class="mt-10">
                <p class="text-sm text-charcoal/50">Konfirmasi akan dikirim ke <strong class="text-charcoal/70">{{ order.customer_email }}</strong></p>
                <router-link to="/" class="inline-block mt-6 px-8 py-3 bg-charcoal text-paper text-sm tracking-widest uppercase hover:bg-charcoal/90 transition-all active:scale-[0.98]">
                    Lanjut Belanja
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'

const route = useRoute()
const order = ref(null)
const loading = ref(true)

const statusLabel = computed(() => {
    const labels = {
        pending: 'Menunggu Pembayaran',
        paid: 'Lunas',
        processing: 'Diproses',
        shipped: 'Dikirim',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
        expired: 'Kadaluarsa',
    }
    return labels[order.value?.status] || order.value?.status
})

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price)
}

onMounted(async () => {
    try {
        const res = await api.get(`/orders/${route.params.orderNumber}/status`)
        order.value = res.data.data
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
})
</script>
