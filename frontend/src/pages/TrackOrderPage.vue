<template>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="text-center mb-10">
            <div class="w-14 h-14 rounded-2xl bg-maroon-50 border-2 border-maroon-100 flex items-center justify-center mx-auto mb-4">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7f1d1d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <h1 class="text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Lacak Pesanan</h1>
            <p class="mt-2 text-sm text-charcoal/50">Masukkan email dan nomor pesanan untuk melihat status pengirimanmu</p>
        </div>

        <!-- Form Lacak -->
        <div v-if="!order" class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50">
            <form @submit.prevent="trackOrder" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Email</label>
                    <input v-model="form.email" type="email" required placeholder="email yang kamu pakai saat checkout"
                        class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Nomor Pesanan</label>
                    <input v-model="form.order_number" required placeholder="Contoh: ORD-20260704-0001"
                        class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors uppercase"
                        @input="form.order_number = form.order_number.toUpperCase()">
                </div>

                <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600 font-medium">
                    {{ error }}
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    {{ loading ? 'Mencari...' : 'Cari Pesanan' }}
                </button>
            </form>
        </div>

        <!-- Hasil Pesanan -->
        <div v-else class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-charcoal/50 uppercase tracking-wide">Hasil Pencarian</h2>
                <button @click="reset" class="text-xs font-semibold text-maroon hover:text-maroon-600 transition-colors">
                    Cari Pesanan Lain
                </button>
            </div>

            <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <p class="text-xs text-charcoal/40 font-medium">No. Pesanan</p>
                        <p class="text-lg font-bold text-maroon">{{ order.order_number }}</p>
                        <p class="text-xs text-charcoal/40 mt-0.5">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold self-start sm:self-center"
                        :class="statusClass(order.status)">
                        {{ statusLabel(order.status) }}
                    </span>
                </div>

                <!-- Status Timeline -->
                <div class="mb-6">
                    <div class="flex items-center gap-0">
                        <div v-for="(step, i) in statusSteps" :key="step.key"
                            class="flex items-center" :class="i < statusSteps.length - 1 ? 'flex-1' : ''">
                            <div class="flex flex-col items-center">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center border-2 transition-all"
                                    :class="isStepDone(step.key) ? 'bg-maroon border-maroon' : isStepCurrent(step.key) ? 'bg-white border-maroon' : 'bg-white border-maroon-100'">
                                    <svg v-if="isStepDone(step.key)" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="square"><polyline points="20 6 9 17 4 12"/></svg>
                                    <div v-else class="w-2 h-2 rounded-full" :class="isStepCurrent(step.key) ? 'bg-maroon' : 'bg-maroon-100'"></div>
                                </div>
                                <p class="text-[9px] font-semibold mt-1 text-center w-14"
                                    :class="isStepDone(step.key) || isStepCurrent(step.key) ? 'text-maroon' : 'text-charcoal/30'">
                                    {{ step.label }}
                                </p>
                            </div>
                            <div v-if="i < statusSteps.length - 1"
                                class="flex-1 h-0.5 mb-5 transition-all"
                                :class="isStepDone(step.key) ? 'bg-maroon' : 'bg-maroon-100'">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="border-t border-maroon-100 pt-4 space-y-2 mb-4">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                        <span class="text-charcoal/70">{{ item.product_name }} <span class="text-charcoal/40">×{{ item.quantity }}</span></span>
                        <span class="font-medium text-charcoal">Rp{{ formatPrice(item.subtotal) }}</span>
                    </div>
                </div>

                <!-- Totals -->
                <div class="border-t border-maroon-100 pt-4 space-y-1.5 text-sm">
                    <div class="flex justify-between text-charcoal/60">
                        <span>Subtotal</span>
                        <span>Rp{{ formatPrice(order.subtotal) }}</span>
                    </div>
                    <div v-if="order.coupon_discount > 0" class="flex justify-between text-green-600">
                        <span>Diskon <span v-if="order.coupon_code" class="font-mono text-xs">{{ order.coupon_code }}</span></span>
                        <span>-Rp{{ formatPrice(order.coupon_discount) }}</span>
                    </div>
                    <div v-if="order.shipping_cost > 0" class="flex justify-between text-charcoal/60">
                        <span>Ongkir</span>
                        <span>Rp{{ formatPrice(order.shipping_cost) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base text-charcoal pt-2 border-t border-maroon-100">
                        <span>Total</span>
                        <span class="text-maroon">Rp{{ formatPrice(order.total) }}</span>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="border-t border-maroon-100 mt-4 pt-4">
                    <p class="text-xs font-semibold text-charcoal/50 mb-1">Alamat Pengiriman</p>
                    <p class="text-sm text-charcoal/70">{{ order.shipping_address }}</p>
                </div>

                <!-- CTA Bayar -->
                <div v-if="order.status === 'pending'" class="mt-6 pt-4 border-t border-maroon-100">
                    <button @click="payNow"
                        class="w-full py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Konfirmasi Pembayaran via WhatsApp
                    </button>
                </div>
            </div>
        </div>

        <!-- Link ke login -->
        <div class="mt-6 text-center">
            <p class="text-xs text-charcoal/40">
                Punya akun?
                <router-link to="/login" class="font-semibold text-maroon hover:text-maroon-600 transition-colors">Login</router-link>
                untuk lihat semua riwayat pesananmu
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { formatPrice } from '../mock-data'
import api from '../api'

const route = useRoute()
const order = ref(null)
const loading = ref(false)
const error = ref('')
const form = ref({ email: '', order_number: '' })

const statusSteps = [
    { key: 'pending',    label: 'Menunggu' },
    { key: 'paid',       label: 'Dibayar' },
    { key: 'processing', label: 'Diproses' },
    { key: 'shipped',    label: 'Dikirim' },
    { key: 'completed',  label: 'Selesai' },
]

const statusOrder = ['pending', 'paid', 'processing', 'shipped', 'completed']

function isStepDone(stepKey) {
    if (!order.value) return false
    const current = statusOrder.indexOf(order.value.status)
    const step = statusOrder.indexOf(stepKey)
    return step < current
}

function isStepCurrent(stepKey) {
    return order.value?.status === stepKey
}

async function trackOrder() {
    loading.value = true
    error.value = ''
    try {
        const res = await api.post('/orders/track', form.value)
        order.value = res.data.data || res.data
    } catch (e) {
        error.value = e.response?.data?.message || 'Pesanan tidak ditemukan. Periksa kembali email dan nomor pesanan.'
    } finally {
        loading.value = false
    }
}

async function trackByToken(token) {
    loading.value = true
    try {
        const res = await api.get(`/orders/token/${token}`)
        order.value = res.data.data || res.data
    } catch {
        error.value = 'Link pesanan tidak valid atau sudah kadaluarsa.'
    } finally {
        loading.value = false
    }
}

function reset() {
    order.value = null
    error.value = ''
    form.value = { email: '', order_number: '' }
}

function payNow() {
    const whatsappNumber = import.meta.env.VITE_WHATSAPP_NUMBER || '6285196811722'
    let message = `Halo, saya ingin konfirmasi pembayaran:\n\n`
    message += `*Order #${order.value.order_number}*\n`
    message += `Total: Rp${formatPrice(order.value.total)}\n\n`
    message += `Mohon info rekening untuk transfer. Terima kasih!`
    window.open(`https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`, '_blank')
}

function statusLabel(status) {
    const labels = {
        pending: 'Menunggu Pembayaran', paid: 'Dibayar', processing: 'Diproses',
        shipped: 'Dikirim', completed: 'Selesai', cancelled: 'Dibatalkan', expired: 'Kadaluarsa',
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
    return classes[status] || 'bg-maroon-50 text-maroon border border-maroon-100'
}

function formatDate(dateString) {
    if (!dateString) return ''
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }).format(new Date(dateString))
}

onMounted(() => {
    // Kalau ada token di query string (dari link email), langsung fetch
    if (route.query.token) {
        trackByToken(route.query.token)
    }
})
</script>
