<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16">
        <h1 class="text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Pesanan Saya</h1>
        <p class="mt-2 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Lihat semua pesanan dan status pengirimannya</p>

        <div v-if="loading" class="py-24 text-center">
            <div class="inline-block w-10 h-10 border-4 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
            <p class="mt-4 text-base text-charcoal/50 dark:text-[#8a8a8e]">Memuat pesanan...</p>
        </div>

        <div v-else-if="!isLoggedIn" class="py-24 text-center">
            <UserIcon class="w-16 h-16 mx-auto text-maroon-200" />
            <h2 class="mt-4 text-xl font-bold text-charcoal dark:text-[#f0eeeb]">Login dulu yuk!</h2>
            <p class="mt-2 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Kamu harus login untuk lihat pesanan</p>
            <router-link to="/login" class="inline-block mt-6 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
                Login Sekarang
            </router-link>
        </div>

        <div v-else-if="!orders.length" class="py-24 text-center">
            <ShoppingCartIcon class="w-16 h-16 mx-auto text-maroon-200" />
            <h2 class="mt-4 text-xl font-bold text-charcoal dark:text-[#f0eeeb]">Belum ada pesanan nih</h2>
            <p class="mt-2 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Yuk belanja sekarang!</p>
            <router-link to="/?shop=1" class="inline-block mt-6 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
                Mulai Belanja
            </router-link>
        </div>

        <div v-else class="mt-8 space-y-4">
            <!-- Banner claim guest orders -->
            <div v-if="claimableCount > 0" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border-2 border-blue-200 dark:border-blue-800 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Ada {{ claimableCount }} pesanan yang pernah kamu buat sebelum login!</p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Klik tombol di samping untuk menambahkan pesanan tersebut ke riwayat akunmu.</p>
                </div>
                <button @click="claimOrders" :disabled="claiming"
                    class="shrink-0 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50">
                    {{ claiming ? 'Mengklaim...' : 'Klaim Pesanan' }}
                </button>
            </div>

            <!-- Banner sukses claim -->
            <div v-if="claimedMsg" class="p-4 bg-green-50 dark:bg-green-900/20 rounded-2xl border-2 border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-400 font-medium">
                {{ claimedMsg }}
            </div>

            <div v-for="order in orders" :key="order.id" class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] hover:border-maroon-100 dark:hover:border-slate-600 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-base font-bold text-charcoal dark:text-[#f0eeeb]">{{ order.order_number }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                :class="statusClass(order.status)">
                                {{ statusLabel(order.status) }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-charcoal/50 dark:text-[#8a8a8e]">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-charcoal/50 dark:text-[#8a8a8e]">Total Pembayaran</p>
                        <p class="text-lg font-bold text-maroon dark:text-[#f0eeeb]">Rp{{ formatPrice(order.total) }}</p>
                    </div>
                </div>

                <div class="border-t border-maroon-100 dark:border-[#303032] pt-4 space-y-2">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                        <span class="text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#8a8a8e]">{{ item.product_name }} <span class="text-charcoal/40 dark:text-[#6a6a6e]">×{{ item.quantity }}</span></span>
                        <span class="font-medium text-charcoal dark:text-[#f0eeeb]">Rp{{ formatPrice(item.subtotal) }}</span>
                    </div>
                    <!-- Diskon kupon — diarsipkan sementara -->
                    <!--
                    <div v-if="order.coupon_discount > 0" class="flex justify-between text-sm text-green-600 dark:text-green-400 pt-1 border-t border-maroon-50 dark:border-[#303032]">
                        <span>Diskon Kupon <span v-if="order.coupon_code" class="font-mono text-xs bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-1.5 py-0.5 rounded ml-1">{{ order.coupon_code }}</span></span>
                        <span class="font-medium">-Rp{{ formatPrice(order.coupon_discount) }}</span>
                    </div>
                    -->
                </div>

                <!-- Info Pengiriman: Kurir + Resi -->
                <div v-if="order.courier || order.tracking_number" class="mt-3 pt-3 border-t border-maroon-100 dark:border-[#303032]">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-charcoal/60 dark:text-[#8a8a8e]">
                        <span v-if="order.courier" class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2M8 4v4h8V4M8 4h8"/></svg>
                            <span class="font-medium text-charcoal/80 dark:text-[#d0ceca]">{{ order.courier }}</span>
                        </span>
                        <span v-if="order.tracking_number" class="flex items-center gap-1">
                            <span class="text-charcoal/40 dark:text-[#6a6a6e]">No. Resi:</span>
                            <span class="font-mono font-semibold text-charcoal dark:text-[#f0eeeb] tracking-wide">{{ order.tracking_number }}</span>
                        </span>
                        <a v-if="order.tracking_url && order.tracking_number"
                            :href="order.tracking_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 text-maroon dark:text-maroon-300 font-semibold hover:underline">
                            Cek Resi
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        </a>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-maroon-100 dark:border-[#303032] flex flex-col sm:flex-row gap-3">
                    <router-link :to="`/order/${order.order_number}`" class="flex-1 text-center px-6 py-2.5 border-2 border-maroon text-maroon text-sm font-semibold rounded-xl hover:bg-maroon hover:text-white dark:hover:bg-maroon dark:hover:text-white transition-all">
                        Lihat Detail
                    </router-link>
                    <a v-if="order.payment?.snap_token && order.status === 'pending'" @click.prevent="payNow(order.payment.snap_token)" href="#" class="flex-1 text-center px-6 py-2.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all">
                        Bayar Sekarang
                    </a>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="flex justify-center gap-2 mt-8">
                <button @click="loadOrders(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="px-4 py-2 border-2 border-maroon-100 dark:border-[#303032] text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] text-sm font-semibold rounded-xl hover:border-maroon dark:hover:border-[#f0eeeb] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    Prev
                </button>
                <span class="px-4 py-2 text-sm text-charcoal/60 dark:text-[#8a8a8e]">
                    Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
                </span>
                <button @click="loadOrders(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="px-4 py-2 border-2 border-maroon-100 dark:border-[#303032] text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] text-sm font-semibold rounded-xl hover:border-maroon dark:hover:border-[#f0eeeb] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ShoppingCartIcon, UserIcon } from '@heroicons/vue/24/outline'
import { formatPrice } from '../mock-data'
import api from '../api'
import { useSettings } from '../useSettings'

const { fetchSettings, get } = useSettings()
const orders = ref([])
const pagination = ref(null)
const loading = ref(true)
const isLoggedIn = ref(false)
const claimableCount = ref(0)
const claiming = ref(false)
const claimedMsg = ref('')

async function loadOrders(page = 1) {
    loading.value = true
    try {
        const res = await api.get('/me/orders', { params: { page } })
        orders.value = res.data.data || res.data
        pagination.value = res.data.meta || res.data.pagination || null
    } catch (e) {
        if (e.response?.status === 401) {
            isLoggedIn.value = false
        }
        orders.value = []
    } finally {
        loading.value = false
    }
}

async function checkClaimable() {
    try {
        const res = await api.get('/me/orders/claimable-count')
        claimableCount.value = res.data.claimable_count || 0
    } catch {
        claimableCount.value = 0
    }
}

async function claimOrders() {
    claiming.value = true
    claimedMsg.value = ''
    try {
        const res = await api.post('/me/orders/claim')
        claimedMsg.value = res.data.message
        claimableCount.value = 0
        await loadOrders()
    } catch (e) {
        claimedMsg.value = e.response?.data?.message || 'Gagal mengklaim pesanan. Coba lagi.'
    } finally {
        claiming.value = false
    }
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
        pending: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800/50',
        paid: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50',
        processing: 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/50',
        shipped: 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50',
        completed: 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/50',
        cancelled: 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/50',
        expired: 'bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-[#d0ceca] dark:text-gray-400 border border-gray-200 dark:border-gray-700',
    }
    return classes[status] || 'bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-[#d0ceca] dark:text-gray-400'
}

function formatDate(dateString) {
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('id-ID', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date)
}

function payNow(order) {
    const whatsappNumber = get('whatsapp_number', import.meta.env.VITE_WHATSAPP_NUMBER || '6285196811722')
    let message = `Halo, saya ingin konfirmasi pembayaran:\n\n`
    message += `*Order #${order.order_number}*\n`
    message += `Total: Rp${formatPrice(order.total)}\n\n`
    message += `Mohon info rekening untuk transfer. Terima kasih!`

    const waUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`
    window.open(waUrl, '_blank')
}

onMounted(() => {
    isLoggedIn.value = !!localStorage.getItem('token')
    if (isLoggedIn.value) {
        fetchSettings()
        loadOrders()
        checkClaimable()
    } else {
        loading.value = false
    }
})
</script>
