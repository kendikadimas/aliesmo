<template>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-24 text-center">
        <div v-if="loading" class="py-12 space-y-4 max-w-md mx-auto">
            <!-- icon placeholder -->
            <SkeletonLoader :loading="true" :radius="16" height="64px" width="64px" class="mx-auto" />
            <!-- title -->
            <SkeletonLoader :loading="true" :radius="99" height="28px" width="60%" class="mx-auto" />
            <!-- subtitle -->
            <SkeletonLoader :loading="true" :radius="99" height="14px" width="80%" class="mx-auto" />
            <!-- order number badge -->
            <SkeletonLoader :loading="true" :radius="12" height="36px" width="50%" class="mx-auto" />
            <!-- detail card -->
            <SkeletonLoader :loading="true" :radius="16" height="220px" width="100%" class="mt-6" />
            <!-- address card -->
            <SkeletonLoader :loading="true" :radius="16" height="100px" width="100%" />
        </div>

        <div v-else-if="!order" class="py-16">
            <InformationCircleIcon class="w-12 h-12 mx-auto text-maroon-200" />
            <p class="mt-4 text-lg text-charcoal/50 dark:text-[#8a8a8e]">Pesanan gak ditemukan :(</p>
            <router-link to="/" class="inline-block mt-6 text-sm font-semibold text-maroon hover:text-maroon-600 transition-colors">Kembali ke Beranda</router-link>
        </div>

        <div v-else>
            <div class="w-16 h-16 rounded-2xl bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 flex items-center justify-center mx-auto">
                <CheckIcon class="w-7 h-7 text-green-600" />
            </div>
            <h1 class="mt-6 text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Pesanan Berhasil!</h1>
            <p class="mt-2 text-base text-charcoal/60 dark:text-[#8a8a8e]">Makasih ya <strong>{{ order.customer_name }}</strong>, pesananmu udah kami terima!</p>

            <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-maroon-50 dark:bg-maroon/20 rounded-xl text-sm max-w-full overflow-hidden">
                <span class="font-semibold text-charcoal/60 dark:text-[#8a8a8e] shrink-0">No. Pesanan:</span>
                <span class="font-bold text-maroon truncate">{{ order.order_number }}</span>
            </div>

            <p class="mt-2 text-sm font-semibold" :class="statusClass(order.status)">
                Status: {{ statusLabel(order.status) }}
            </p>

            <div class="mt-8 bg-white dark:bg-[#1c1c1e] p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] text-left">
                <h2 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide mb-6">Detail Pesanan</h2>
                <div v-if="order.items" class="space-y-3">
                    <div v-for="(item, i) in order.items" :key="i" class="flex justify-between text-sm">
                        <span class="text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#d0ceca]">{{ item.product_name }} <span class="text-charcoal/40 dark:text-[#6a6a6e]">×{{ item.quantity }}</span></span>
                        <span class="font-bold dark:text-[#f0eeeb]">Rp{{ formatPrice(item.subtotal || item.price * item.quantity) }}</span>
                    </div>
                </div>
                <div class="border-t-2 border-maroon-100 dark:border-[#303032] mt-4 pt-4 space-y-1.5 text-sm">
                    <div class="flex justify-between text-charcoal/60 dark:text-[#8a8a8e]">
                        <span>Subtotal</span>
                        <span class="font-medium">Rp{{ formatPrice(order.subtotal) }}</span>
                    </div>
                    <!-- Diskon kupon — diarsipkan sementara -->
                    <!--
                    <div v-if="order.coupon_discount > 0" class="flex justify-between text-green-600 dark:text-green-400">
                        <span>Diskon Kupon <span v-if="order.coupon_code" class="font-mono text-xs bg-green-50 dark:bg-green-900/30 px-1.5 py-0.5 rounded ml-1">{{ order.coupon_code }}</span></span>
                        <span class="font-medium">-Rp{{ formatPrice(order.coupon_discount) }}</span>
                    </div>
                    -->
                    <div v-if="order.shipping_cost > 0" class="flex justify-between text-charcoal/60 dark:text-[#8a8a8e]">
                        <span>Ongkir</span>
                        <span class="font-medium">Rp{{ formatPrice(order.shipping_cost) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg text-charcoal dark:text-[#f0eeeb] pt-2 border-t-2 border-maroon-100 dark:border-[#303032]">
                        <span>Total</span>
                        <span class="text-maroon">Rp{{ formatPrice(order.total) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 bg-white dark:bg-[#1c1c1e] p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] text-left">
                <h2 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide mb-4">Data Pengiriman</h2>
                <div class="text-sm text-charcoal/65 dark:text-[#d0ceca] space-y-1 leading-relaxed">
                    <p><span class="font-medium text-charcoal/50 dark:text-[#8a8a8e]">Nama:</span> {{ order.customer_name }}</p>
                    <p><span class="font-medium text-charcoal/50 dark:text-[#8a8a8e]">Email:</span> {{ order.customer_email }}</p>
                    <p v-if="order.customer_phone"><span class="font-medium text-charcoal/50 dark:text-[#8a8a8e]">Telp:</span> {{ order.customer_phone }}</p>
                    <p><span class="font-medium text-charcoal/50 dark:text-[#8a8a8e]">Alamat:</span> {{ order.shipping_address }}</p>
                </div>
            </div>

            <!-- Info Pembayaran -->
            <div v-if="paymentInfo && paymentInfo.method" class="mt-4 bg-white dark:bg-[#1c1c1e] p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] text-left">
                <h2 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide mb-4">Info Pembayaran</h2>

                <!-- Bank Transfer -->
                <div v-if="paymentInfo.method === 'bank_transfer'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-5 space-y-2">
                    <div class="flex items-center gap-2 mb-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] shrink-0"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        <span class="text-sm font-bold text-charcoal dark:text-[#f0eeeb]">{{ paymentInfo.label }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div>
                            <p class="text-charcoal/40 dark:text-[#6a6a6e] text-xs">Bank</p>
                            <p class="font-semibold text-charcoal dark:text-[#f0eeeb]">{{ paymentInfo.bank_name }}</p>
                        </div>
                        <div>
                            <p class="text-charcoal/40 dark:text-[#6a6a6e] text-xs">No. Rekening</p>
                            <p class="font-semibold text-charcoal dark:text-[#f0eeeb] font-mono">{{ paymentInfo.account_no }}</p>
                        </div>
                        <div>
                            <p class="text-charcoal/40 dark:text-[#6a6a6e] text-xs">Atas Nama</p>
                            <p class="font-semibold text-charcoal dark:text-[#f0eeeb]">{{ paymentInfo.account_name }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-charcoal/50 dark:text-[#8a8a8e] mt-2">{{ paymentInfo.instruction }}</p>
                </div>

                <!-- QRIS -->
                <div v-else-if="paymentInfo.method === 'qris'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-5 text-center">
                    <div class="flex items-center justify-center gap-2 mb-3">
                        <DevicePhoneMobileIcon class="w-[18px] h-[18px] text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] shrink-0" />
                        <span class="text-sm font-bold text-charcoal dark:text-[#f0eeeb]">{{ paymentInfo.label }}</span>
                    </div>
                    <div v-if="paymentInfo.qris_image" class="flex justify-center mb-3">
                        <img :src="paymentInfo.qris_image" alt="QRIS" class="w-52 h-52 object-contain rounded-lg border border-maroon-100 dark:border-[#303032] bg-white dark:bg-[#1c1c1e] p-2">
                    </div>
                    <p class="text-sm font-semibold text-charcoal dark:text-[#f0eeeb]">{{ paymentInfo.qris_name }}</p>
                    <p class="text-xs text-charcoal/50 dark:text-[#8a8a8e] mt-1">{{ paymentInfo.instruction }}</p>
                </div>

                <!-- COD -->
                <div v-else-if="paymentInfo.method === 'cod'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <CreditCardIcon class="w-[18px] h-[18px] text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] shrink-0" />
                        <span class="text-sm font-bold text-charcoal dark:text-[#f0eeeb]">{{ paymentInfo.label }}</span>
                    </div>
                    <p class="text-sm text-charcoal/60 dark:text-[#8a8a8e]">{{ paymentInfo.instruction }}</p>
                </div>
            </div>

            <div class="mt-8">
                <p class="text-sm text-charcoal/50 dark:text-[#8a8a8e]">Konfirmasi pesanan dikirim ke email yang kamu daftarkan.</p>

                <!-- Link lacak pesanan untuk guest -->
                <div v-if="order.lookup_token" class="mt-4 p-4 bg-maroon-50 dark:bg-maroon/10 rounded-xl text-left">
                    <p class="text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-2">Simpan link ini untuk lacak pesananmu:</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 text-xs bg-white dark:bg-[#1c1c1e] border border-maroon-100 dark:border-[#303032] rounded-lg px-3 py-2 text-maroon font-mono truncate">
                            {{ trackUrl }}
                        </code>
                        <button @click="copyTrackUrl" class="shrink-0 px-3 py-2 bg-maroon text-white text-xs font-semibold rounded-lg hover:bg-maroon-600 transition-colors">
                            {{ copied ? 'Disalin!' : 'Salin' }}
                        </button>
                    </div>
                    <p class="text-xs text-charcoal/40 dark:text-[#6a6a6e] mt-2">Atau kamu bisa lacak pesanan kapan saja di halaman <router-link to="/track-order" class="text-maroon font-semibold hover:underline">Lacak Pesanan</router-link></p>
                </div>

                <router-link to="/" class="inline-block mt-4 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
                    Belanja Lagi
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { CheckIcon, InformationCircleIcon, BuildingLibraryIcon, DevicePhoneMobileIcon, CreditCardIcon } from '@heroicons/vue/24/outline'
import { useRoute } from 'vue-router'
import { formatPrice } from '../mock-data'
import api from '../api'

const route = useRoute()
const order = ref(null)
const paymentInfo = ref(null)
const loading = ref(true)
const copied = ref(false)

const trackUrl = computed(() => {
    if (!order.value?.lookup_token) return ''
    return `${window.location.origin}/track-order?token=${order.value.lookup_token}`
})

function copyTrackUrl() {
    if (!trackUrl.value) return
    navigator.clipboard.writeText(trackUrl.value).then(() => {
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    })
}

function statusLabel(status) {
    const labels = {
        pending: 'Menunggu Pembayaran',
        paid: 'Dibayar',
        processing: 'Diproses',
        shipped: 'Dikirim',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
        expired: 'Kadaluarsa',
    }
    return labels[status] || status
}

function statusClass(status) {
    const classes = {
        pending: 'text-yellow-600 dark:text-yellow-400',
        paid: 'text-blue-600 dark:text-blue-400',
        processing: 'text-purple-600 dark:text-purple-400',
        shipped: 'text-indigo-600 dark:text-indigo-400',
        completed: 'text-green-600 dark:text-green-400',
        cancelled: 'text-red-600 dark:text-red-400',
        expired: 'text-gray-500 dark:text-gray-400',
    }
    return classes[status] || 'text-maroon dark:text-maroon-400'
}

onMounted(async () => {
    try {
        const res = await api.get(`/orders/${route.params.orderNumber}/status`)
        order.value = res.data.data || res.data
        paymentInfo.value = res.data.payment_info || null
    } catch (e) {
        // fallback ke sessionStorage jika API gagal (misal belum login)
        try {
            const stored = sessionStorage.getItem('lastOrder')
            if (stored) {
                const data = JSON.parse(stored)
                if (data.order_number === route.params.orderNumber) {
                    order.value = data
                }
            }
        } catch {}
    } finally {
        loading.value = false
    }
})
</script>
