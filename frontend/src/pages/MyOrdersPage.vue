<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <h1 class="text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Pesanan Saya</h1>
        <p class="mt-2 text-sm text-charcoal/50">Lihat semua pesanan dan status pengirimannya</p>

        <div v-if="loading" class="py-24 text-center">
            <div class="inline-block w-10 h-10 border-4 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
            <p class="mt-4 text-base text-charcoal/50">Memuat pesanan...</p>
        </div>

        <div v-else-if="!isLoggedIn" class="py-24 text-center">
            <svg class="w-16 h-16 mx-auto text-maroon-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <h2 class="mt-4 text-xl font-bold text-charcoal">Login dulu yuk!</h2>
            <p class="mt-2 text-sm text-charcoal/50">Kamu harus login untuk lihat pesanan</p>
            <router-link to="/login" class="inline-block mt-6 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
                Login Sekarang
            </router-link>
        </div>

        <div v-else-if="!orders.length" class="py-24 text-center">
            <svg class="w-16 h-16 mx-auto text-maroon-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <h2 class="mt-4 text-xl font-bold text-charcoal">Belum ada pesanan nih</h2>
            <p class="mt-2 text-sm text-charcoal/50">Yuk belanja sekarang!</p>
            <router-link to="/?shop=1" class="inline-block mt-6 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
                Mulai Belanja
            </router-link>
        </div>

        <div v-else class="mt-8 space-y-4">
            <div v-for="order in orders" :key="order.id" class="bg-white p-6 rounded-2xl border-2 border-maroon-50 hover:border-maroon-100 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-base font-bold text-charcoal">{{ order.order_number }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                :class="statusClass(order.status)">
                                {{ statusLabel(order.status) }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-charcoal/50">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-charcoal/50">Total Pembayaran</p>
                        <p class="text-lg font-bold text-maroon">Rp{{ formatPrice(order.total) }}</p>
                    </div>
                </div>

                <div class="border-t border-maroon-100 pt-4 space-y-2">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                        <span class="text-charcoal/70">{{ item.product_name }} <span class="text-charcoal/40">×{{ item.quantity }}</span></span>
                        <span class="font-medium text-charcoal">Rp{{ formatPrice(item.subtotal) }}</span>
                    </div>
                    <div v-if="order.coupon_discount > 0" class="flex justify-between text-sm text-green-600 pt-1 border-t border-maroon-50">
                        <span>Diskon Kupon <span v-if="order.coupon_code" class="font-mono text-xs bg-green-50 px-1.5 py-0.5 rounded ml-1">{{ order.coupon_code }}</span></span>
                        <span class="font-medium">-Rp{{ formatPrice(order.coupon_discount) }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-maroon-100 flex flex-col sm:flex-row gap-3">
                    <router-link :to="`/order/${order.order_number}`" class="flex-1 text-center px-6 py-2.5 border-2 border-maroon text-maroon text-sm font-semibold rounded-xl hover:bg-maroon hover:text-white transition-all">
                        Lihat Detail
                    </router-link>
                    <a v-if="order.payment?.snap_token && order.status === 'pending'" @click.prevent="payNow(order.payment.snap_token)" href="#" class="flex-1 text-center px-6 py-2.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all">
                        Bayar Sekarang
                    </a>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="flex justify-center gap-2 mt-8">
                <button @click="loadOrders(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="px-4 py-2 border-2 border-maroon-100 text-charcoal text-sm font-semibold rounded-xl hover:border-maroon transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    Prev
                </button>
                <span class="px-4 py-2 text-sm text-charcoal/60">
                    Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
                </span>
                <button @click="loadOrders(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="px-4 py-2 border-2 border-maroon-100 text-charcoal text-sm font-semibold rounded-xl hover:border-maroon transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { formatPrice } from '../mock-data'
import api from '../api'

const orders = ref([])
const pagination = ref(null)
const loading = ref(true)
const isLoggedIn = ref(false)

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
        pending: 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        paid: 'bg-blue-50 text-blue-700 border border-blue-200',
        processing: 'bg-purple-50 text-purple-700 border border-purple-200',
        shipped: 'bg-indigo-50 text-indigo-700 border border-indigo-200',
        completed: 'bg-green-50 text-green-700 border border-green-200',
        cancelled: 'bg-red-50 text-red-700 border border-red-200',
        expired: 'bg-gray-50 text-gray-700 border border-gray-200',
    }
    return classes[status] || 'bg-gray-50 text-gray-700'
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
    // WhatsApp flow: generate message dan redirect
    const whatsappNumber = import.meta.env.VITE_WHATSAPP_NUMBER || '6285196811722'
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
        loadOrders()
    } else {
        loading.value = false
    }
})
</script>
