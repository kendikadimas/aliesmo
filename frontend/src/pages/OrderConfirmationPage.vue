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
            <router-link to="/" class="inline-block mt-6 text-sm font-semibold text-maroon dark:text-[#f0eeeb] hover:text-maroon-600 dark:hover:text-[#f0eeeb] transition-colors">Kembali ke Beranda</router-link>
        </div>

        <div v-else>
            <div class="w-16 h-16 rounded-2xl bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 flex items-center justify-center mx-auto">
                <CheckIcon class="w-7 h-7 text-green-600" />
            </div>
            <h1 class="mt-6 text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Pesanan Berhasil!</h1>
            <p class="mt-2 text-base text-charcoal/60 dark:text-[#8a8a8e]">Makasih ya <strong>{{ order.customer_name }}</strong>, pesananmu udah kami terima!</p>

            <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-maroon-50 dark:bg-maroon/20 rounded-xl text-sm max-w-full overflow-hidden">
                <span class="font-semibold text-charcoal/60 dark:text-[#8a8a8e] shrink-0">No. Pesanan:</span>
                <span class="font-bold text-maroon dark:text-[#f0eeeb] truncate">{{ order.order_number }}</span>
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
                        <span class="text-maroon dark:text-[#f0eeeb]">Rp{{ formatPrice(order.total) }}</span>
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
                <div v-if="paymentInfo.method === 'bank_transfer'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-5 space-y-3">
                    <div class="flex items-center gap-2 mb-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] shrink-0"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        <span class="text-sm font-bold text-charcoal dark:text-[#f0eeeb]">{{ paymentInfo.label }}</span>
                    </div>
                    <div v-for="(bank, i) in paymentInfo.banks" :key="i" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm" :class="i > 0 ? 'pt-3 border-t border-maroon-100 dark:border-[#303032]' : ''">
                        <div>
                            <p class="text-charcoal/40 dark:text-[#6a6a6e] text-xs">Bank</p>
                            <p class="font-semibold text-charcoal dark:text-[#f0eeeb]">{{ bank.bank_name }}</p>
                        </div>
                        <div>
                            <p class="text-charcoal/40 dark:text-[#6a6a6e] text-xs">No. Rekening</p>
                            <p class="font-semibold text-charcoal dark:text-[#f0eeeb] font-mono">{{ bank.account_no }}</p>
                        </div>
                        <div>
                            <p class="text-charcoal/40 dark:text-[#6a6a6e] text-xs">Atas Nama</p>
                            <p class="font-semibold text-charcoal dark:text-[#f0eeeb]">{{ bank.account_name }}</p>
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

            <!-- Tombol WhatsApp -->
            <div v-if="whatsappNumber" class="mt-4">
                <a :href="`https://wa.me/${whatsappNumber}?text=${encodeURIComponent(waMessage)}`"
                    target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-center gap-2 w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-all active:scale-[0.97] shadow-lg shadow-green-600/25">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Konfirmasi via WhatsApp
                </a>
            </div>

            <div class="mt-8">
                <p class="text-sm text-charcoal/50 dark:text-[#8a8a8e]">Konfirmasi pesanan dikirim ke email yang kamu daftarkan.</p>

                <!-- Link lacak pesanan untuk guest -->
                <div v-if="order.lookup_token" class="mt-4 p-4 bg-maroon-50 dark:bg-maroon/10 rounded-xl text-left">
                    <p class="text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-2">Simpan link ini untuk lacak pesananmu:</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 text-xs bg-white dark:bg-[#1c1c1e] border border-maroon-100 dark:border-[#303032] rounded-lg px-3 py-2 text-maroon dark:text-[#f0eeeb] font-mono truncate">
                            {{ trackUrl }}
                        </code>
                        <button @click="copyTrackUrl" class="shrink-0 px-3 py-2 bg-maroon text-white text-xs font-semibold rounded-lg hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-colors">
                            {{ copied ? 'Disalin!' : 'Salin' }}
                        </button>
                    </div>
                    <p class="text-xs text-charcoal/40 dark:text-[#6a6a6e] mt-2">Atau kamu bisa lacak pesanan kapan saja di halaman <router-link to="/track-order" class="text-maroon dark:text-[#f0eeeb] font-semibold hover:underline">Lacak Pesanan</router-link></p>
                </div>

                <router-link to="/" class="inline-block mt-4 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
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
const whatsappNumber = ref('')
const loading = ref(true)
const copied = ref(false)

const trackUrl = computed(() => {
    if (!order.value?.lookup_token) return ''
    return `${window.location.origin}/track-order?token=${order.value.lookup_token}`
})

const waMessage = computed(() => {
    if (!order.value) return ''
    const o = order.value
    let msg = `Halo, saya ingin konfirmasi pesanan:\n\n`
    msg += `*Order #${o.order_number}*\n`
    msg += `Total: Rp${formatPrice(o.total)}\n`
    if (paymentInfo.value?.method === 'bank_transfer' && paymentInfo.value.banks?.length) {
        const b = paymentInfo.value.banks[0]
        msg += `\n*Info Transfer:*\nBank: ${b.bank_name}\nNo. Rekening: ${b.account_no}\nAtas Nama: ${b.account_name}\n`
    } else if (paymentInfo.value?.method === 'qris') {
        msg += `\nMetode: QRIS\n`
    }
    msg += `\nMohon konfirmasi pembayaran. Terima kasih!`
    return msg
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
        whatsappNumber.value = res.data.whatsapp_number || ''
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
