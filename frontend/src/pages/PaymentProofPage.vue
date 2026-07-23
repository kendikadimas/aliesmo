<template>
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16">
        <!-- Loading -->
        <div v-if="loading" class="space-y-4">
            <SkeletonLoader :loading="true" :radius="16" height="48px" width="60%" class="mx-auto" />
            <SkeletonLoader :loading="true" :radius="16" height="200px" />
        </div>

        <!-- Order not found -->
        <div v-else-if="!order" class="py-16 text-center">
            <InformationCircleIcon class="w-12 h-12 mx-auto text-maroon-200" />
            <p class="mt-4 text-lg text-charcoal/50 dark:text-[#8a8a8e]">Pesanan tidak ditemukan</p>
            <router-link to="/" class="inline-block mt-6 text-sm font-semibold text-charcoal dark:text-[#f0eeeb] hover:opacity-70">
                Kembali ke Beranda
            </router-link>
        </div>

        <!-- COD: konfirmasi via WhatsApp, tidak perlu upload bukti -->
        <div v-else-if="order.payment_method === 'cod'" class="py-4">
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-800 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </div>
                <h1 class="mt-4 text-xl lg:text-2xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Pesanan COD Diterima</h1>
                <p class="mt-2 text-sm text-charcoal/60 dark:text-[#8a8a8e]">Pesanan <strong class="text-charcoal dark:text-[#f0eeeb]">{{ order.order_number }}</strong></p>
            </div>

            <!-- Info total COD -->
            <div class="mb-6 p-4 bg-amber-50/60 dark:bg-[#1c1c1e] rounded-2xl border border-amber-200 dark:border-amber-800/40">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-charcoal/60 dark:text-[#8a8a8e]">Total yang Dibayar saat Terima</span>
                    <span class="text-lg font-bold text-amber-600 dark:text-amber-400">Rp{{ formatPrice(order.total) }}</span>
                </div>
                <div class="mt-3 pt-3 border-t border-amber-200 dark:border-amber-800/40">
                    <p class="text-xs text-charcoal/50 dark:text-[#6a6a6e]">Metode Pembayaran</p>
                    <p class="text-sm font-semibold text-charcoal dark:text-[#f0eeeb] mt-0.5">COD — Bayar di Tempat</p>
                </div>
            </div>

            <!-- Instruksi COD -->
            <div class="space-y-3 mb-8">
                <div class="flex gap-3 p-3 bg-white dark:bg-[#1c1c1e] rounded-xl border border-maroon-100 dark:border-[#303032]">
                    <span class="w-6 h-6 rounded-full bg-maroon text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                    <p class="text-xs text-charcoal/70 dark:text-[#8a8a8e] leading-relaxed">Pesananmu sedang diproses dan akan dikirim via kurir pilihan.</p>
                </div>
                <div class="flex gap-3 p-3 bg-white dark:bg-[#1c1c1e] rounded-xl border border-maroon-100 dark:border-[#303032]">
                    <span class="w-6 h-6 rounded-full bg-maroon text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                    <p class="text-xs text-charcoal/70 dark:text-[#8a8a8e] leading-relaxed">Siapkan uang tunai <strong class="text-charcoal dark:text-[#f0eeeb]">Rp{{ formatPrice(order.total) }}</strong> saat kurir tiba.</p>
                </div>
                <div class="flex gap-3 p-3 bg-white dark:bg-[#1c1c1e] rounded-xl border border-maroon-100 dark:border-[#303032]">
                    <span class="w-6 h-6 rounded-full bg-maroon text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                    <p class="text-xs text-charcoal/70 dark:text-[#8a8a8e] leading-relaxed">Pastikan kamu ada di lokasi pengiriman. Konfirmasi via WhatsApp jika ada kendala.</p>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <router-link :to="`/order/${order.order_number}`"
                    class="w-full px-6 py-3.5 bg-maroon text-white dark:bg-[#f0eeeb] dark:text-[#161618] text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-[#d0ceca] transition-all text-center">
                    Lihat Detail Pesanan
                </router-link>
            </div>
        </div>

        <!-- Already paid / proof submitted (non-COD) -->
        <div v-else-if="order.status !== 'pending'" class="py-16 text-center">
            <div v-if="order.status === 'processing'"
                class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-800 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div v-else
                class="w-16 h-16 rounded-2xl bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 flex items-center justify-center mx-auto">
                <CheckIcon class="w-7 h-7 text-green-600 dark:text-green-400" />
            </div>
            <h1 class="mt-6 text-2xl font-bold text-charcoal dark:text-[#f0eeeb]">
                {{ order.status === 'processing' ? 'Bukti Pembayaran Diterima' : 'Pembayaran Sudah Diverifikasi' }}
            </h1>
            <p class="mt-2 text-charcoal/60 dark:text-[#8a8a8e]">
                {{ order.status === 'processing'
                    ? 'Pesanan ' + order.order_number + ' sedang menunggu verifikasi admin.'
                    : 'Pesanan ' + order.order_number + ' sudah diproses.' }}
            </p>
            <router-link :to="`/order/${order.order_number}`" class="inline-block mt-6 text-sm font-semibold text-charcoal dark:text-[#f0eeeb] hover:opacity-70">
                Lihat Detail Pesanan
            </router-link>
        </div>

        <!-- Upload form (non-COD, status pending) -->
        <div v-else>
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-maroon-50 dark:bg-maroon-900/20 border-2 border-maroon-100 dark:border-maroon-800 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6 text-maroon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </div>
                <h1 class="mt-4 text-xl lg:text-2xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Upload Bukti Pembayaran</h1>
                <p class="mt-2 text-sm text-charcoal/60 dark:text-[#8a8a8e]">Pesanan <strong class="text-maroon dark:text-maroon">{{ order.order_number }}</strong></p>
            </div>

            <!-- Ringkasan pesanan -->
            <div class="mb-4 p-4 bg-maroon-50/30 dark:bg-[#1c1c1e] rounded-2xl border border-maroon-100 dark:border-[#303032]">
                <p class="text-xs font-semibold text-charcoal/50 dark:text-[#6a6a6e] mb-3 uppercase tracking-wide">Ringkasan Pesanan</p>

                <!-- Item produk -->
                <div v-if="order.items?.length" class="space-y-3 mb-3">
                    <div v-for="item in order.items" :key="item.id" class="flex items-center gap-3">
                        <img v-if="item.product_image" :src="item.product_image" :alt="item.product_name"
                            class="w-12 h-12 rounded-xl object-cover border border-maroon-100 dark:border-[#303032] shrink-0" />
                        <div v-else class="w-12 h-12 rounded-xl bg-maroon-50 dark:bg-[#28282a] border border-maroon-100 dark:border-[#303032] shrink-0 flex items-center justify-center">
                            <svg class="w-5 h-5 text-charcoal/20 dark:text-[#6a6a6e]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-charcoal dark:text-[#f0eeeb] truncate">{{ item.product_name }}</p>
                            <p v-if="item.variant_name" class="text-xs text-charcoal/50 dark:text-[#6a6a6e]">{{ item.variant_name }}</p>
                            <p class="text-xs text-charcoal/50 dark:text-[#6a6a6e]">{{ item.quantity }}x · Rp{{ formatPrice(item.price) }}</p>
                        </div>
                        <p class="text-sm font-semibold text-charcoal dark:text-[#f0eeeb] shrink-0">Rp{{ formatPrice(item.subtotal) }}</p>
                    </div>
                </div>

                <div class="border-t border-maroon-100 dark:border-[#303032] pt-3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-charcoal/60 dark:text-[#8a8a8e]">Nomor Pesanan</span>
                        <span class="font-semibold text-charcoal dark:text-[#f0eeeb]">{{ order.order_number }}</span>
                    </div>
                    <div v-if="order.courier" class="flex justify-between text-sm">
                        <span class="text-charcoal/60 dark:text-[#8a8a8e]">Kurir</span>
                        <span class="font-semibold text-charcoal dark:text-[#f0eeeb]">{{ order.courier }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-charcoal/60 dark:text-[#8a8a8e]">Status</span>
                        <span class="font-semibold text-amber-600 dark:text-amber-400">Menunggu Pembayaran</span>
                    </div>
                </div>
            </div>

            <!-- Payment info -->
            <div class="mb-6 p-4 bg-maroon-50/50 dark:bg-[#1c1c1e] rounded-2xl border border-maroon-100 dark:border-[#303032]">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-charcoal/60 dark:text-[#8a8a8e]">Total Pembayaran</span>
                    <span class="text-lg font-bold text-charcoal dark:text-[#f0eeeb]">Rp{{ formatPrice(order.total) }}</span>
                </div>

                <!-- Metode Pembayaran -->
                <div class="mt-3 pt-3 border-t border-maroon-100 dark:border-[#303032]">
                    <p class="text-xs text-charcoal/50 dark:text-[#6a6a6e] mb-1">Metode Pembayaran</p>
                    <p class="text-sm font-semibold text-charcoal dark:text-[#f0eeeb]">{{ paymentMethodLabel }}</p>
                </div>

                <!-- Bank Transfer -->
                <div v-if="order.payment_method === 'bank_transfer'" class="mt-3 pt-3 border-t border-maroon-100 dark:border-[#303032]">
                    <p class="text-xs text-charcoal/50 dark:text-[#6a6a6e] mb-2">Transfer ke:</p>
                    <div v-if="paymentInfo?.banks?.length" class="space-y-2">
                        <div v-for="(bank, i) in paymentInfo.banks" :key="i" class="text-sm">
                            <p class="font-semibold text-charcoal dark:text-[#f0eeeb]">{{ bank.bank_name }}</p>
                            <p class="text-charcoal/60 dark:text-[#8a8a8e] font-mono">{{ bank.account_no }}</p>
                            <p class="text-charcoal/50 dark:text-[#6a6a6e] text-xs">a.n. {{ bank.account_name }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm font-semibold text-charcoal dark:text-[#f0eeeb]">{{ bankInfo }}</p>
                </div>

                <!-- QRIS -->
                <div v-else-if="order.payment_method === 'qris'" class="mt-3 pt-3 border-t border-maroon-100 dark:border-[#303032]">
                    <p class="text-xs text-charcoal/50 dark:text-[#6a6a6e] mb-1">Scan QRIS untuk pembayaran</p>
                    <p class="text-sm text-charcoal/60 dark:text-[#8a8a8e]">Gunakan e-wallet atau m-banking apapun</p>
                </div>
            </div>

            <!-- Success message -->
            <div v-if="success" class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl">
                <div class="flex items-start gap-3">
                    <CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400 shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-green-800 dark:text-green-300">Berhasil!</p>
                        <p class="text-xs text-green-700 dark:text-green-400 mt-1">{{ success }}</p>
                    </div>
                </div>
            </div>

            <!-- Error message -->
            <div v-if="error" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl">
                <div class="flex items-start gap-3">
                    <ExclamationCircleIcon class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-red-800 dark:text-red-300">Gagal Upload</p>
                        <p class="text-xs text-red-700 dark:text-red-400 mt-1">{{ error }}</p>
                    </div>
                </div>
            </div>

            <!-- Upload area -->
            <div v-if="!success" class="space-y-4">
                <!-- Image preview -->
                <div v-if="previewUrl" class="relative">
                    <div class="rounded-2xl overflow-hidden border-2 border-maroon-100 dark:border-[#303032] bg-maroon-50/30 dark:bg-[#28282a]">
                        <img :src="previewUrl" alt="Preview bukti bayar" class="w-full max-h-80 object-contain" />
                    </div>
                    <button @click="removeImage" 
                        class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/90 dark:bg-[#3a3a3c]/90 border border-gray-200 dark:border-[#4a4a4c] flex items-center justify-center hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <XMarkIcon class="w-4 h-4 text-gray-600 dark:text-[#8a8a8e]" />
                    </button>
                </div>

                <!-- Drop zone -->
                <div v-else 
                    @click="triggerInput"
                    @dragover.prevent="isDragging = true"
                    @dragleave="isDragging = false"
                    @drop.prevent="handleDrop"
                    :class="[
                        'border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all',
                        isDragging 
                            ? 'border-maroon bg-maroon-50 dark:bg-maroon-900/20' 
                            : 'border-maroon-200 dark:border-[#303032] hover:border-maroon-400 hover:bg-maroon-50/50 dark:hover:bg-[#28282a]'
                    ]">
                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileSelect" />
                    <PhotoIcon class="w-10 h-10 mx-auto text-maroon/30 dark:text-maroon/50" />
                    <p class="mt-3 text-sm font-medium text-charcoal/60 dark:text-[#8a8a8e]">Klik atau drag foto bukti bayar</p>
                    <p class="mt-1 text-xs text-charcoal/40 dark:text-[#6a6a6e]">Format: JPG, PNG, WEBP • Maks 5MB</p>
                </div>

                <!-- Note -->
                <div>
                    <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Catatan (opsional)</label>
                    <textarea v-model="note" rows="2" placeholder="Contoh: Transfer dari BCA atas nama Ahmad"
                        class="w-full px-4 py-3 text-sm border-2 border-maroon-100 dark:border-[#303032] rounded-xl bg-white dark:bg-[#28282a] text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:placeholder:text-[#6a6a6e] focus:border-maroon focus:ring-0 resize-none transition-colors"></textarea>
                </div>

                <!-- Submit -->
                <button @click="uploadProof" :disabled="!selectedFile || uploading"
                    class="w-full py-3.5 bg-maroon text-white font-semibold text-sm rounded-xl hover:bg-maroon-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2">
                    <svg v-if="uploading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ uploading ? 'Mengunggah...' : 'Kirim Bukti Pembayaran' }}
                </button>
            </div>

            <!-- After success -->
            <div v-if="success" class="text-center mt-6">
                <router-link :to="`/order/${order.order_number}`" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-maroon text-white font-semibold text-sm rounded-xl hover:bg-maroon-600 transition-colors">
                    Lihat Status Pesanan
                    <ArrowRightIcon class="w-4 h-4" />
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { 
    InformationCircleIcon, CheckIcon, CheckCircleIcon, 
    ExclamationCircleIcon, PhotoIcon, XMarkIcon, ArrowRightIcon 
} from '@heroicons/vue/24/outline'
import { formatPrice } from '../mock-data'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import api from '../api'

const route = useRoute()

const orderNumber = route.params.orderNumber
const token = localStorage.getItem(`order_token_${orderNumber}`)

const order = ref(null)
const loading = ref(true)
const error = ref(null)
const success = ref(null)
const selectedFile = ref(null)
const previewUrl = ref(null)
const note = ref('')
const uploading = ref(false)
const isDragging = ref(false)
const fileInput = ref(null)
const paymentInfo = ref(null)

const paymentMethodLabel = computed(() => {
    if (!order.value) return ''
    const labels = {
        'bank_transfer': 'Transfer Bank',
        'qris': 'QRIS',
        'cod': 'Bayar di Tempat (COD)',
    }
    return labels[order.value.payment_method] || order.value.payment_method || 'Transfer Bank'
})

const bankInfo = computed(() => {
    if (!order.value) return ''
    const bank = order.value.selected_bank
    const bankAccounts = {
        'bca': 'BCA',
        'mandiri': 'Mandiri',
        'bni': 'BNI',
        'bri': 'BRI',
    }
    return bankAccounts[bank] || bank || 'Transfer Bank'
})

function triggerInput() {
    fileInput.value?.click()
}

function handleFileSelect(e) {
    const file = e.target.files[0]
    if (file) processFile(file)
}

function handleDrop(e) {
    isDragging.value = false
    const file = e.dataTransfer.files[0]
    if (file) processFile(file)
}

function processFile(file) {
    if (!file.type.startsWith('image/')) {
        error.value = 'File harus berupa gambar (JPG, PNG, WEBP)'
        return
    }
    if (file.size > 5 * 1024 * 1024) {
        error.value = 'Ukuran file maksimal 5MB'
        return
    }
    selectedFile.value = file
    previewUrl.value = URL.createObjectURL(file)
    error.value = null
}

function removeImage() {
    selectedFile.value = null
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
        previewUrl.value = null
    }
    if (fileInput.value) fileInput.value.value = ''
}

async function uploadProof() {
    if (!selectedFile.value || uploading.value) return
    
    uploading.value = true
    error.value = null
    success.value = null

    console.log('[PaymentProof] Starting upload', {
        orderNumber,
        hasToken: !!token,
        fileSize: selectedFile.value.size,
        fileType: selectedFile.value.type,
    })

    try {
        const formData = new FormData()
        formData.append('proof_image', selectedFile.value)
        if (note.value) formData.append('proof_note', note.value)

        const headers = { 'Content-Type': 'multipart/form-data' }
        if (token) headers['X-Lookup-Token'] = token

        console.log('[PaymentProof] Sending request to API')
        const res = await api.post(
            `/orders/${orderNumber}/payment-proof`,
            formData,
            { headers }
        )

        console.log('[PaymentProof] Upload success', {
            status: res.status,
            message: res.data.message,
        })

        success.value = res.data.message || 'Bukti pembayaran berhasil diunggah. Admin akan memverifikasi dalam 1x24 jam.'
        order.value = res.data.order
    } catch (e) {
        console.error('[PaymentProof] Upload failed', {
            status: e.response?.status,
            message: e.response?.data?.message,
            error: e.message,
            data: e.response?.data,
        })
        error.value = e.response?.data?.message || 'Gagal mengunggah bukti pembayaran. Coba lagi.'
    } finally {
        uploading.value = false
    }
}

onMounted(async () => {
    console.log('[PaymentProof] Component mounted', {
        orderNumber,
        hasToken: !!token,
    })

    try {
        const headers = {}
        if (token) headers['X-Lookup-Token'] = token
        
        console.log('[PaymentProof] Fetching order status')
        const res = await api.get(`/orders/${orderNumber}/status`, { headers })
        
        console.log('[PaymentProof] Order status received', {
            status: res.status,
            orderStatus: res.data.data?.status || res.data.order?.status,
            hasPayment: !!(res.data.data?.payment || res.data.order?.payment),
        })
        
        order.value = res.data.data || res.data.order
        paymentInfo.value = res.data.payment_info || null
    } catch (e) {
        console.error('[PaymentProof] Failed to fetch order', {
            status: e.response?.status,
            message: e.response?.data?.message,
            error: e.message,
        })
        order.value = null
    } finally {
        loading.value = false
    }
})
</script>
