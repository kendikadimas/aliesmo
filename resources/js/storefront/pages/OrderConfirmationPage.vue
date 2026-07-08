<template>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16">
        <div v-if="loading" class="py-24 text-center">
            <div class="inline-block w-8 h-8 border-2 border-maroon-200/60 border-t-maroon rounded-full animate-spin"></div>
            <p class="mt-4 text-sm text-charcoal/60">Sebentar ya, kami ambilkan detail pesananmu...</p>
        </div>

        <div v-else-if="!order" class="text-center py-16">
            <svg class="w-12 h-12 mx-auto text-maroon-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p class="mt-4 text-base text-charcoal/50">Pesanan tidak ditemukan.</p>
            <p class="mt-1 text-sm text-charcoal/40">Coba cek nomor pesananmu atau hubungi admin.</p>
            <router-link to="/" class="inline-block mt-6 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] shadow-lg">Kembali ke Beranda</router-link>
        </div>

        <div v-else>
            <div class="w-16 h-16 rounded-full bg-green-50 border-2 border-green-200 flex items-center justify-center mx-auto">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#166534" stroke-width="2" stroke-linecap="square">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <h1 class="mt-5 text-2xl lg:text-3xl font-bold text-charcoal tracking-tight text-center">Pesanan Masuk!</h1>
            <p class="mt-2 text-sm text-charcoal/60 text-center">Makasih ya, <strong>{{ order.customer_name }}</strong>! Pesananmu sudah kami terima. Konfirmasi via WhatsApp supaya segera diproses.</p>

            <div class="mt-4 flex flex-col items-center gap-2">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-maroon-50 border-2 border-maroon-100 rounded-lg text-sm text-charcoal/70 max-w-full overflow-hidden">
                    <span class="text-[10px] font-bold text-charcoal/50 uppercase tracking-wide shrink-0">No. Pesanan:</span>
                    <span class="font-bold tracking-wider text-charcoal truncate">{{ order.order_number }}</span>
                </div>
                <div class="text-sm font-semibold" :class="order.status === 'paid' ? 'text-green-600' : 'text-maroon'">
                    Status: {{ statusLabel }}
                </div>
            </div>

            <div class="mt-6 bg-white p-5 lg:p-6 rounded-xl border-2 border-maroon-50">
                <h2 class="text-xs font-bold text-charcoal/70 uppercase tracking-wide mb-4">Detail Pesanan</h2>
                <div class="space-y-2.5">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                        <span class="text-charcoal/70">{{ item.product_name }} <span class="text-maroon-400">×{{ item.quantity }}</span></span>
                        <span class="font-bold">Rp{{ formatPrice(item.subtotal) }}</span>
                    </div>
                </div>
                <div class="border-t-2 border-maroon-50 mt-4 pt-4 space-y-1.5 text-sm">
                    <div class="flex justify-between text-charcoal/70">
                        <span>Subtotal</span>
                        <span class="font-semibold">Rp{{ formatPrice(order.subtotal) }}</span>
                    </div>
                    <div v-if="order.shipping_cost" class="flex justify-between text-charcoal/70">
                        <span>Ongkos Kirim</span>
                        <span class="font-semibold">Rp{{ formatPrice(order.shipping_cost) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-charcoal pt-2 border-t-2 border-maroon-50 text-base">
                        <span>Total</span>
                        <span class="text-maroon">Rp{{ formatPrice(order.total) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 bg-white p-5 lg:p-6 rounded-xl border-2 border-maroon-50">
                <h2 class="text-xs font-bold text-charcoal/70 uppercase tracking-wide mb-4">Informasi Pengiriman</h2>
                <div class="text-sm text-charcoal/70 space-y-1 leading-relaxed">
                    <p><span class="text-charcoal/50 font-semibold">Nama:</span> {{ order.customer_name }}</p>
                    <p><span class="text-charcoal/50 font-semibold">Email:</span> {{ order.customer_email }}</p>
                    <p v-if="order.customer_phone"><span class="text-charcoal/50 font-semibold">Telepon:</span> {{ order.customer_phone }}</p>
                    <p><span class="text-charcoal/50 font-semibold">Alamat:</span> {{ order.shipping_address }}</p>
                </div>
            </div>

            <div v-if="order.status === 'pending'" class="mt-4 p-4 bg-green-50 border-2 border-green-200 rounded-xl text-center">
                <p class="text-xs font-semibold text-charcoal/70 mb-3">
                    Tap tombol di bawah untuk konfirmasi pesanan dan koordinasi pembayaran via WhatsApp
                </p>
                <a :href="waUrl" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition-all active:scale-[0.98] shadow-lg">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Konfirmasi Pesanan via WhatsApp
                </a>
            </div>

            <!-- Info Pembayaran -->
            <div v-if="paymentInfo" class="mt-4 bg-white p-5 lg:p-6 rounded-xl border-2 border-maroon-50">
                <h2 class="text-xs font-bold text-charcoal/70 uppercase tracking-wide mb-4">Info Pembayaran</h2>

                <!-- Transfer Bank -->
                <template v-if="paymentInfo.method === 'bank_transfer'">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-charcoal/50 font-semibold">Bank</span>
                            <span class="font-bold text-charcoal">{{ paymentInfo.bank_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-charcoal/50 font-semibold">No. Rekening</span>
                            <span class="font-bold text-charcoal tracking-widest">{{ paymentInfo.account_no }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-charcoal/50 font-semibold">Atas Nama</span>
                            <span class="font-bold text-charcoal">{{ paymentInfo.account_name }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-maroon-50">
                            <span class="text-charcoal/50 font-semibold">Jumlah Transfer</span>
                            <span class="font-bold text-maroon text-base">Rp{{ formatPrice(order.total) }}</span>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-charcoal/50 bg-maroon-50/50 rounded-lg px-3 py-2">{{ paymentInfo.instruction }}</p>
                </template>

                <!-- QRIS -->
                <template v-else-if="paymentInfo.method === 'qris'">
                    <div class="text-center">
                        <p class="text-sm font-semibold text-charcoal mb-3">{{ paymentInfo.qris_name }}</p>
                        <div v-if="paymentInfo.qris_image" class="inline-block p-3 bg-white border-2 border-maroon-100 rounded-xl">
                            <img :src="paymentInfo.qris_image" alt="QRIS" class="w-48 h-48 object-contain">
                        </div>
                        <div v-else class="inline-flex items-center justify-center w-48 h-48 bg-maroon-50 rounded-xl border-2 border-dashed border-maroon-200">
                            <p class="text-xs text-charcoal/40 text-center px-4">QRIS belum dikonfigurasi. Hubungi admin.</p>
                        </div>
                        <div class="mt-3 font-bold text-maroon text-base">Rp{{ formatPrice(order.total) }}</div>
                    </div>
                    <p class="mt-3 text-xs text-charcoal/50 bg-maroon-50/50 rounded-lg px-3 py-2">{{ paymentInfo.instruction }}</p>
                </template>

                <!-- COD -->
                <template v-else-if="paymentInfo.method === 'cod'">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-maroon-50 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-maroon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-charcoal">Bayar di Tempat (COD)</p>
                            <p class="text-xs text-charcoal/50 mt-1">{{ paymentInfo.instruction }}</p>
                            <p class="text-sm font-bold text-maroon mt-2">Total: Rp{{ formatPrice(order.total) }}</p>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-8 text-center">
                <router-link to="/" class="inline-block px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] shadow-lg">
                    Belanja Lagi
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
const waNumber = ref(import.meta.env.VITE_WA_NUMBER || '6285196811722')
const paymentInfo = ref(null)

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

const waUrl = computed(() => {
    if (!order.value) return '#'
    const itemsText = order.value.items.map(i =>
        `• ${i.product_name} x${i.quantity} = Rp${formatPrice(i.subtotal)}`
    ).join('%0a')
    const message = `Halo, saya ingin konfirmasi pesanan:%0a%0a`
        + `*No. Pesanan:* ${order.value.order_number}%0a`
        + `*Nama:* ${order.value.customer_name}%0a`
        + `*Total:* Rp${formatPrice(order.value.total)}%0a%0a`
        + `*Pesanan:*%0a${itemsText}%0a%0a`
        + `Mohon konfirmasi pembayaran. Terima kasih!`
    return `https://wa.me/${waNumber.value}?text=${message}`
})

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price)
}

onMounted(async () => {
    // Ambil payment_info dari sessionStorage (disimpan saat checkout)
    try {
        const stored = sessionStorage.getItem('payment_info')
        if (stored) {
            paymentInfo.value = JSON.parse(stored)
            sessionStorage.removeItem('payment_info')
        }
    } catch {}

    try {
        const res = await api.get(`/orders/${route.params.orderNumber}/status`)
        order.value = res.data.data
        if (res.data.whatsapp_number) {
            waNumber.value = res.data.whatsapp_number
        }
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
})
</script>
