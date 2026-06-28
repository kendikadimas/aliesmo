<template>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-24 text-center">
        <div v-if="!order" class="py-16">
            <svg class="w-12 h-12 mx-auto text-maroon-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p class="mt-4 text-lg text-charcoal/50">Pesanan gak ditemukan :(</p>
            <router-link to="/" class="inline-block mt-6 text-sm font-semibold text-maroon hover:text-maroon-600 transition-colors">Kembali ke Beranda</router-link>
        </div>

        <div v-else>
            <div class="w-16 h-16 rounded-2xl bg-green-50 border-2 border-green-200 flex items-center justify-center mx-auto">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="square"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h1 class="mt-6 text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Pesanan Berhasil!</h1>
            <p class="mt-2 text-base text-charcoal/60">Makasih ya <strong>{{ order.customer_name }}</strong>, pesananmu udah kami terima!</p>

            <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-maroon-50 rounded-xl text-sm">
                <span class="font-semibold text-charcoal/60">No. Pesanan:</span>
                <span class="font-bold text-maroon">{{ order.order_number }}</span>
            </div>

            <p class="mt-2 text-sm text-maroon font-semibold">Status: Menunggu Pembayaran</p>

            <div class="mt-8 bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 text-left">
                <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Detail Pesanan</h2>
                <div v-if="order.items" class="space-y-3">
                    <div v-for="(item, i) in order.items" :key="i" class="flex justify-between text-sm">
                        <span class="text-charcoal/70">{{ item.product_name }} <span class="text-charcoal/40">×{{ item.quantity }}</span></span>
                        <span class="font-bold">Rp{{ formatPrice(item.subtotal || item.price * item.quantity) }}</span>
                    </div>
                </div>
                <div class="border-t-2 border-maroon-100 mt-4 pt-4 space-y-1.5 text-sm">
                    <div class="flex justify-between text-charcoal/60">
                        <span>Subtotal</span>
                        <span class="font-medium">Rp{{ formatPrice(order.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg text-charcoal pt-2 border-t-2 border-maroon-100">
                        <span>Total</span>
                        <span class="text-maroon">Rp{{ formatPrice(order.total) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 text-left">
                <h2 class="text-sm font-bold text-charcoal tracking-wide mb-4">Data Pengiriman</h2>
                <div class="text-sm text-charcoal/65 space-y-1 leading-relaxed">
                    <p><span class="font-medium text-charcoal/50">Nama:</span> {{ order.customer_name }}</p>
                    <p><span class="font-medium text-charcoal/50">Email:</span> {{ order.customer_email }}</p>
                    <p v-if="order.customer_phone"><span class="font-medium text-charcoal/50">Telp:</span> {{ order.customer_phone }}</p>
                    <p><span class="font-medium text-charcoal/50">Alamat:</span> {{ order.shipping_address }}</p>
                </div>
            </div>

            <div class="mt-8">
                <p class="text-sm text-charcoal/50">Konfirmasi bakal dikirim ke <strong class="text-charcoal">{{ order.customer_email }}</strong></p>
                <router-link to="/" class="inline-block mt-4 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
                    Belanja Lagi
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { formatPrice } from '../mock-data'

const route = useRoute()
const order = ref(null)

onMounted(() => {
    try {
        const stored = sessionStorage.getItem('lastOrder')
        if (stored) {
            const data = JSON.parse(stored)
            if (data.order_number === route.params.orderNumber) {
                order.value = data
            }
        }
    } catch (e) {}
})
</script>
