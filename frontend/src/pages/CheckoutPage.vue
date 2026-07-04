<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <h1 class="text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Checkout</h1>

        <div v-if="submitting" class="py-24 text-center">
            <div class="inline-block w-10 h-10 border-4 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
            <p class="mt-4 text-base text-charcoal/50">Bentar ya, kami lagi proses pesananmu...</p>
        </div>

        <form v-else @submit.prevent="submitOrder" class="mt-8 lg:mt-10 grid lg:grid-cols-5 gap-8 lg:gap-12">
            <div class="lg:col-span-3 space-y-6">

                <!-- Data Diri -->
                <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50">
                    <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Data Diri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Nama Lengkap</label>
                            <input v-model="form.customer_name" required placeholder="Masukkan nama kamu" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Email</label>
                                <input v-model="form.customer_email" type="email" required placeholder="kamu@email.com" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Telepon</label>
                                <input v-model="form.customer_phone" placeholder="0812-xxxx-xxxx" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alamat & Pengiriman -->
                <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50">
                    <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Alamat & Pengiriman</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Alamat Lengkap</label>
                            <textarea v-model="form.shipping_address" required placeholder="Contoh: Jl. Merdeka No. 123, RT 01/RW 02, Kelurahan, Kecamatan" rows="2" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors resize-none"></textarea>
                        </div>

                        <!-- Lokasi Pengiriman (Autocomplete Direct Search) -->
                        <div class="relative location-search-container">
                            <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Kota atau Kecamatan Tujuan</label>
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    @input="onSearchInput"
                                    @focus="showDropdown = true"
                                    placeholder="Ketik nama kota atau kecamatan (misal: Denpasar, Tebet)"
                                    class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors bg-white"
                                    required
                                >
                                <div v-if="loadingSearch" class="absolute right-3 top-3">
                                    <div class="w-4 h-4 border-2 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
                                </div>
                            </div>

                            <!-- Search Results Dropdown -->
                            <div v-if="showDropdown && searchResults.length" class="absolute left-0 right-0 z-50 w-full mt-1 bg-white border-2 border-maroon-50 rounded-xl shadow-lg max-h-60 overflow-y-auto divide-y divide-maroon-50">
                                <button
                                    v-for="item in searchResults"
                                    :key="item.id"
                                    type="button"
                                    @click="selectDestination(item)"
                                    class="w-full text-left px-4 py-3 text-xs text-charcoal hover:bg-maroon-50/50 transition-colors"
                                >
                                    <span class="font-semibold">{{ item.label }}</span>
                                </button>
                            </div>
                            <div v-else-if="showDropdown && searchQuery.trim().length >= 3 && !loadingSearch && !searchResults.length" class="absolute left-0 right-0 z-50 w-full mt-1 bg-white border-2 border-maroon-50 rounded-xl shadow-lg p-4 text-xs text-charcoal/50 text-center">
                                Lokasi tidak ditemukan. Coba ketik nama kota/kecamatan lain.
                            </div>
                        </div>

                        <!-- Layanan Pengiriman - tampil otomatis setelah lokasi dipilih -->
                        <div v-if="selectedCity">
                            <div v-if="loadingShipping" class="flex items-center gap-2 text-xs text-charcoal/50 py-3">
                                <div class="w-4 h-4 border-2 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
                                Mencari layanan pengiriman tersedia...
                            </div>

                            <div v-else-if="shippingOptions.length">
                                <label class="block text-xs font-semibold text-charcoal/60 mb-2">Pilih Layanan Pengiriman</label>
                                <div class="space-y-2">
                                    <label v-for="opt in shippingOptions" :key="opt.code + '-' + opt.service"
                                        class="flex items-center justify-between p-3 rounded-xl border-2 cursor-pointer transition-all"
                                        :class="selectedShipping?.code === opt.code && selectedShipping?.service === opt.service
                                            ? 'border-maroon bg-maroon-50/30'
                                            : 'border-maroon-100 hover:border-maroon-200'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" :value="opt" v-model="selectedShipping" class="accent-maroon">
                                            <div>
                                                <p class="text-xs font-bold text-charcoal">{{ opt.courier }}</p>
                                                <p class="text-xs text-charcoal/60">{{ opt.service }} · {{ opt.description }}</p>
                                                <p class="text-xs text-charcoal/40">Estimasi {{ opt.etd || '-' }} hari</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-bold text-maroon shrink-0 ml-2">Rp{{ formatPrice(opt.cost || 0) }}</span>
                                    </label>
                                </div>
                            </div>

                            <p v-if="shippingError" class="text-xs text-red-500 mt-2">{{ shippingError }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Pesanan -->
            <div class="lg:col-span-2">
                <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 lg:sticky lg:top-28">
                    <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Ringkasan Pesanan</h2>
                    <div class="space-y-3">
                        <div v-for="item in items" :key="item.product_id" class="flex justify-between text-sm">
                            <span class="text-charcoal/70 truncate mr-4">{{ item.name }} <span class="text-charcoal/40">×{{ item.quantity }}</span></span>
                            <span class="font-bold text-charcoal shrink-0">Rp{{ formatPrice(item.price * item.quantity) }}</span>
                        </div>
                    </div>

                    <!-- Kupon -->
                    <div class="mt-4 pt-4 border-t-2 border-maroon-100">
                        <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Kode Kupon</label>
                        <div class="flex gap-2">
                            <input
                                v-model="couponCode"
                                :disabled="!!appliedCoupon || couponLoading"
                                placeholder="Masukkan kode kupon"
                                class="flex-1 border-2 border-maroon-100 rounded-xl px-3 py-2 text-xs text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors disabled:opacity-50 uppercase"
                                @keyup.enter="applyCoupon"
                            >
                            <button
                                v-if="!appliedCoupon"
                                type="button"
                                @click="applyCoupon"
                                :disabled="!couponCode || couponLoading"
                                class="px-3 py-2 text-xs font-semibold bg-maroon text-white rounded-xl hover:bg-maroon-600 transition-all disabled:opacity-50"
                            >
                                {{ couponLoading ? '...' : 'Pakai' }}
                            </button>
                            <button
                                v-else
                                type="button"
                                @click="removeCoupon"
                                class="px-3 py-2 text-xs font-semibold border-2 border-maroon-100 text-charcoal/60 rounded-xl hover:border-maroon transition-all"
                            >
                                Hapus
                            </button>
                        </div>
                        <p v-if="couponError" class="text-xs text-red-500 mt-1.5">{{ couponError }}</p>
                        <p v-if="appliedCoupon" class="text-xs text-green-600 mt-1.5 font-medium">
                            Kupon <span class="font-bold">{{ appliedCoupon.code }}</span> berhasil dipakai!
                        </p>
                    </div>

                    <div class="mt-4 pt-4 border-t-2 border-maroon-100 space-y-2">
                        <div class="flex justify-between text-sm text-charcoal/60">
                            <span>Subtotal</span>
                            <span class="font-medium">Rp{{ formatPrice(total()) }}</span>
                        </div>
                        <div v-if="appliedCoupon" class="flex justify-between text-sm text-green-600">
                            <span>Diskon ({{ appliedCoupon.code }})</span>
                            <span class="font-medium">-Rp{{ formatPrice(appliedCoupon.discount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-charcoal/60">
                            <span>Ongkir</span>
                            <span v-if="shippingCost === 0 && !selectedShipping" class="text-charcoal/40 italic text-xs">Pilih kurir dulu</span>
                            <span v-else-if="shippingCost === 0" class="text-green-600 font-medium">Gratis!</span>
                            <span v-else class="font-medium">Rp{{ formatPrice(shippingCost) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-charcoal pt-3 border-t-2 border-maroon-100">
                            <span>Total</span>
                            <span class="text-maroon">Rp{{ formatPrice(grandTotal) }}</span>
                        </div>
                    </div>

                    <div v-if="error" class="mt-4 p-3 bg-red-50 rounded-xl border border-red-200 text-red-700 text-sm">{{ error }}</div>

                    <button type="submit" :disabled="!selectedShipping && selectedCity !== ''" class="w-full mt-6 px-8 py-3.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:opacity-50 disabled:cursor-not-allowed">
                        Pesan Sekarang
                    </button>

                    <p class="mt-4 text-xs text-center text-charcoal/40 leading-relaxed">Dengan pesan, kamu setuju sama <router-link to="/terms" class="text-maroon hover:text-maroon-600 underline">syarat & ketentuan</router-link> kita.</p>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'
import api from '../api'

const router = useRouter()
const { items, total, clear } = useCartStore()
const submitting = ref(false)
const error = ref('')

// Form data
const form = reactive({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    shipping_address: '',
})

// RajaOngkir state
const selectedCity = ref('') // Stores selected destination ID
const shippingOptions = ref([])
const selectedShipping = ref(null)
const loadingShipping = ref(false)
const shippingError = ref('')

// Autocomplete Direct Search state
const searchQuery = ref('')
const searchResults = ref([])
const loadingSearch = ref(false)
const showDropdown = ref(false)
const selectedDestination = ref(null)
let searchTimeout = null

const shippingCost = computed(() => {
    if (!selectedShipping.value) return 0
    return selectedShipping.value.cost || 0
})

// Coupon state
const couponCode = ref('')
const appliedCoupon = ref(null)
const couponLoading = ref(false)
const couponError = ref('')

const grandTotal = computed(() => {
    const subtotal = total()
    const discount = appliedCoupon.value?.discount || 0
    return Math.max(0, subtotal - discount + shippingCost.value)
})

async function applyCoupon() {
    if (!couponCode.value.trim()) return
    couponLoading.value = true
    couponError.value = ''
    try {
        const res = await api.post('/coupons/validate', {
            code: couponCode.value.toUpperCase(),
            order_total: total(),
        })
        appliedCoupon.value = res.data.coupon
    } catch (e) {
        couponError.value = e.response?.data?.message || 'Kode kupon tidak valid.'
        appliedCoupon.value = null
    } finally {
        couponLoading.value = false
    }
}

function removeCoupon() {
    couponCode.value = ''
    appliedCoupon.value = null
    couponError.value = ''
}

function onSearchInput() {
    selectedDestination.value = null
    selectedCity.value = ''
    shippingOptions.value = []
    selectedShipping.value = null

    if (searchTimeout) clearTimeout(searchTimeout)
    const query = searchQuery.value.trim()
    if (query.length < 3) {
        searchResults.value = []
        return
    }

    loadingSearch.value = true
    searchTimeout = setTimeout(async () => {
        try {
            const res = await api.get('/shipping/search', {
                params: { q: query }
            })
            searchResults.value = res.data.data || res.data
        } catch {
            searchResults.value = []
        } finally {
            loadingSearch.value = false
        }
    }, 400)
}

function selectDestination(destination) {
    selectedDestination.value = destination
    searchQuery.value = destination.label
    showDropdown.value = false
    selectedCity.value = destination.id
    searchResults.value = []
    selectedShipping.value = null
    fetchShippingCost()
}

// Close search dropdown on click outside
if (typeof window !== 'undefined') {
    window.addEventListener('click', (e) => {
        if (!e.target.closest('.location-search-container')) {
            showDropdown.value = false
        }
    })
}

async function fetchShippingCost() {
    if (!selectedCity.value) return
    loadingShipping.value = true
    shippingError.value = ''
    shippingOptions.value = []
    try {
        const res = await api.post('/shipping/cost', {
            destination: selectedCity.value,
            weight: items.value.reduce((sum, i) => sum + (i.weight || 300) * i.quantity, 0) || 500,
        })
        const results = res.data.data || res.data
        shippingOptions.value = Array.isArray(results) ? results : []
        if (!shippingOptions.value.length) {
            shippingError.value = 'Tidak ada layanan pengiriman tersedia untuk tujuan ini.'
        }
    } catch {
        shippingError.value = 'Gagal menghitung ongkir. Coba lagi.'
    } finally {
        loadingShipping.value = false
    }
}

async function submitOrder() {
    if (!items.value.length) {
        error.value = 'Wah, keranjangmu kosong!'
        return
    }
    submitting.value = true
    error.value = ''

    // Gabungkan alamat dengan label lokasi tujuan
    const fullAddress = [
        form.shipping_address,
        selectedDestination.value ? selectedDestination.value.label : '',
    ].filter(Boolean).join(', ')

    try {
        const payload = {
            customer_name: form.customer_name,
            customer_email: form.customer_email,
            customer_phone: form.customer_phone,
            shipping_address: fullAddress,
            shipping_cost: shippingCost.value,
            coupon_code: appliedCoupon.value?.code || null,
            items: items.value.map(i => ({
                product_id: i.product_id,
                quantity: i.quantity,
            })),
        }

        const res = await api.post('/orders', payload)
        const orderData = res.data.order
        const whatsappNumber = res.data.whatsapp_number
        const whatsappMessage = res.data.whatsapp_message

        clear()

        // Redirect ke WhatsApp dengan pesan otomatis
        if (whatsappNumber && whatsappMessage) {
            const waUrl = `https://wa.me/${whatsappNumber}?text=${whatsappMessage}`
            window.open(waUrl, '_blank')
        }

        router.push(`/order/${orderData.order_number}`)
    } catch (e) {
        const msg = e.response?.data?.message
        error.value = typeof msg === 'string' ? msg : 'Terjadi kesalahan. Coba lagi ya!'
        submitting.value = false
    }
}
</script>
