<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <h1 class="text-2xl lg:text-3xl font-bold text-charcoal tracking-tight">Checkout</h1>

        <div v-if="submitting" class="py-24 text-center">
            <div class="inline-block w-8 h-8 border-2 border-maroon-200/60 border-t-maroon rounded-full animate-spin"></div>
            <p class="mt-4 text-sm text-charcoal/60">Memproses pesanan Anda...</p>
        </div>

        <form v-else @submit.prevent="submitOrder" class="mt-6 lg:mt-8 grid lg:grid-cols-5 gap-6 lg:gap-8">
            <div class="lg:col-span-3 space-y-4">
                <div class="bg-white p-5 lg:p-6 rounded-xl border-2 border-maroon-50">
                    <h2 class="text-xs font-bold text-charcoal/70 uppercase tracking-wide mb-4">Informasi Pelanggan</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-charcoal/50 mb-1">Nama Lengkap</label>
                            <input v-model="form.customer_name" required placeholder="John Doe" class="w-full border-2 border-maroon-100 rounded-lg px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-charcoal/50 mb-1">Email</label>
                                <input v-model="form.customer_email" type="email" required placeholder="john@example.com" class="w-full border-2 border-maroon-100 rounded-lg px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-charcoal/50 mb-1">Telepon</label>
                                <input v-model="form.customer_phone" placeholder="0812-3456-7890" class="w-full border-2 border-maroon-100 rounded-lg px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 lg:p-6 rounded-xl border-2 border-maroon-50">
                    <h2 class="text-xs font-bold text-charcoal/70 uppercase tracking-wide mb-4">Cek Ongkos Kirim</h2>
                    <div class="space-y-3">
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-charcoal/50 mb-1">Provinsi Tujuan</label>
                                <select v-model="form.province_id" @change="loadCities" class="w-full border-2 border-maroon-100 rounded-lg px-4 py-2.5 text-sm text-charcoal focus:border-maroon focus:outline-none transition-colors bg-white">
                                    <option value="">Pilih Provinsi</option>
                                    <option v-for="p in provinces" :key="p.province_id" :value="p.province_id">{{ p.province }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-charcoal/50 mb-1">Kota/Kabupaten</label>
                                <select v-model="form.city_id" @change="checkCost" class="w-full border-2 border-maroon-100 rounded-lg px-4 py-2.5 text-sm text-charcoal focus:border-maroon focus:outline-none transition-colors bg-white" :disabled="!form.province_id">
                                    <option value="">Pilih Kota</option>
                                    <option v-for="c in cities" :key="c.city_id" :value="c.city_id">{{ c.type }} {{ c.city_name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-charcoal/50 mb-1">Kurir</label>
                                <select v-model="form.courier" @change="checkCost" class="w-full border-2 border-maroon-100 rounded-lg px-4 py-2.5 text-sm text-charcoal focus:border-maroon focus:outline-none transition-colors bg-white">
                                    <option value="">Pilih Kurir</option>
                                    <option v-for="c in couriers" :key="c.code" :value="c.code">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-charcoal/50 mb-1">Berat (gram)</label>
                                <input v-model="form.weight" type="number" readonly class="w-full border-2 border-maroon-100 rounded-lg px-4 py-2.5 text-sm text-charcoal bg-maroon-50/30 focus:border-maroon focus:outline-none transition-colors">
                            </div>
                            <div class="flex items-end">
                                <button type="button" @click="checkCost" :disabled="!form.city_id || !form.courier || shippingLoading" class="w-full px-4 py-2.5 bg-maroon text-white text-sm font-semibold rounded-lg hover:bg-maroon-600 transition-all active:scale-[0.98] disabled:bg-maroon-200 disabled:cursor-not-allowed">
                                    {{ shippingLoading ? '...' : 'Cek Ongkir' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="shippingCosts.length" class="bg-coklat-50/40 rounded-lg p-3 space-y-2">
                            <p class="text-[10px] font-bold text-charcoal/70 uppercase tracking-wide">Pilih Layanan</p>
                            <label v-for="sc in shippingCosts" :key="sc.service" class="flex items-center gap-3 p-2.5 rounded-lg border-2 cursor-pointer transition-all" :class="form.selected_cost?.service === sc.service ? 'border-maroon bg-maroon-50' : 'border-maroon-100 hover:border-maroon-200'" @click="selectShipping(sc)">
                                <input type="radio" name="shipping" :checked="form.selected_cost?.service === sc.service" class="accent-maroon">
                                <div class="flex-1 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-semibold text-charcoal">{{ sc.service }}</p>
                                        <p class="text-[10px] text-charcoal/50">Estimasi {{ sc.etd }} hari</p>
                                    </div>
                                    <p class="text-sm font-bold text-maroon">Rp{{ formatPrice(sc.cost) }}</p>
                                </div>
                            </label>
                        </div>

                        <div v-if="shippingError" class="text-xs text-red-600 font-semibold">{{ shippingError }}</div>

                        <div v-if="!form.city_id && !shippingLoading" class="text-xs text-charcoal/50">Pilih provinsi dan kota untuk menghitung ongkos kirim.</div>
                    </div>
                </div>

                <div class="bg-white p-5 lg:p-6 rounded-xl border-2 border-maroon-50">
                    <h2 class="text-xs font-bold text-charcoal/70 uppercase tracking-wide mb-4">Alamat Lengkap</h2>
                    <div>
                        <textarea v-model="form.shipping_address" required placeholder="Jl. Merdeka No. 123, RT 01/RW 02, Kelurahan Contoh, Kecamatan Contoh, Jakarta Pusat, DKI Jakarta" rows="3" class="w-full border-2 border-maroon-100 rounded-lg px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-maroon focus:outline-none transition-colors resize-none"></textarea>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white p-5 lg:p-6 rounded-xl border-2 border-maroon-50 lg:sticky lg:top-24">
                    <h2 class="text-xs font-bold text-charcoal/70 uppercase tracking-wide mb-4">Ringkasan Pesanan</h2>
                    <div class="space-y-3">
                        <div v-for="item in items" :key="item.product_id" class="flex justify-between text-sm">
                            <span class="text-charcoal/70 truncate mr-4">{{ item.name }} <span class="text-maroon-400">×{{ item.quantity }}</span></span>
                            <span class="text-charcoal font-bold shrink-0">Rp{{ formatPrice(item.price * item.quantity) }}</span>
                        </div>
                    </div>
                    <div class="border-t-2 border-maroon-50 mt-4 pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-charcoal/70">
                            <span>Subtotal</span>
                            <span class="font-semibold">Rp{{ formatPrice(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-charcoal/70">
                            <span>Ongkos Kirim</span>
                            <span class="font-semibold" :class="form.selected_cost ? 'text-charcoal' : 'text-maroon-400'">{{ form.selected_cost ? 'Rp' + formatPrice(form.selected_cost.cost) : 'Belum dihitung' }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-charcoal pt-2 border-t-2 border-maroon-50">
                            <span>Total</span>
                            <span class="text-maroon">Rp{{ formatPrice(grandTotal) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t-2 border-maroon-50 bg-maroon-50/40 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#25D366" stroke="none"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            <div>
                                <p class="text-xs font-bold text-charcoal">Pembayaran via WhatsApp</p>
                                <p class="text-[10px] text-charcoal/50">Kirim detail pesanan ke WhatsApp kami untuk konfirmasi</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="error" class="mt-4 p-3 bg-red-50 border-2 border-red-200 rounded-lg text-red-700 text-xs font-semibold">{{ error }}</div>

                    <button type="submit" :disabled="!form.selected_cost" class="w-full mt-5 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.98] disabled:bg-maroon-200 disabled:cursor-not-allowed shadow-lg">
                        Pesan Sekarang
                    </button>

                    <p class="mt-3 text-[10px] text-center text-charcoal/40 leading-relaxed">Dengan melakukan pemesanan, Anda menyetujui syarat & ketentuan kami.</p>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import { useCartStore } from '../cart'

const router = useRouter()
const { items, total, clear } = useCartStore()
const submitting = ref(false)
const error = ref('')

const provinces = ref([])
const cities = ref([])
const couriers = ref([])
const shippingCosts = ref([])
const shippingLoading = ref(false)
const shippingError = ref('')

const form = reactive({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    shipping_address: '',
    province_id: '',
    city_id: '',
    courier: '',
    weight: 300,
    selected_cost: null,
})

const subtotal = computed(() => total())

const grandTotal = computed(() => {
    const shipping = form.selected_cost?.cost || 0
    return subtotal.value + shipping
})

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price)
}

function selectShipping(sc) {
    form.selected_cost = sc
}

async function loadProvinces() {
    try {
        const res = await api.get('/shipping/provinces')
        provinces.value = res.data.data
    } catch (e) {
        console.error('Gagal memuat provinsi')
    }
}

async function loadCities() {
    form.city_id = ''
    form.selected_cost = null
    shippingCosts.value = []
    if (!form.province_id) return
    try {
        const res = await api.get(`/shipping/cities/${form.province_id}`)
        cities.value = res.data.data
    } catch (e) {
        console.error('Gagal memuat kota')
    }
}

async function loadCouriers() {
    try {
        const res = await api.get('/shipping/couriers')
        couriers.value = res.data.data
    } catch (e) {
        couriers.value = [{ code: 'jne', name: 'JNE' }, { code: 'tiki', name: 'TIKI' }, { code: 'pos', name: 'POS Indonesia' }]
    }
}

async function checkCost() {
    shippingError.value = ''
    form.selected_cost = null
    shippingCosts.value = []
    if (!form.city_id || !form.courier) return

    shippingLoading.value = true
    try {
        const res = await api.post('/shipping/cost', {
            destination: form.city_id,
            weight: form.weight,
            courier: form.courier,
        })
        shippingCosts.value = res.data.data
        if (!res.data.data.length) {
            shippingError.value = 'Tidak ada layanan tersedia untuk tujuan ini.'
        }
    } catch (e) {
        shippingError.value = 'Gagal mengecek ongkir. Pastikan API Key RajaOngkir sudah diisi.'
    } finally {
        shippingLoading.value = false
    }
}

async function submitOrder() {
    if (!items.value.length) {
        error.value = 'Keranjang belanja kosong.'
        return
    }
    if (!form.selected_cost) {
        error.value = 'Silakan pilih layanan pengiriman terlebih dahulu.'
        return
    }
    submitting.value = true
    error.value = ''

    try {
        const payload = {
            items: items.value.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
            customer_name: form.customer_name,
            customer_email: form.customer_email,
            customer_phone: form.customer_phone,
            shipping_address: form.shipping_address,
            shipping_cost: form.selected_cost.cost,
            payment_method: 'whatsapp',
        }

        const res = await api.post('/orders', payload)
        const orderData = res.data.order
        const waNumber = res.data.whatsapp_number
        const cartItems = [...items.value]
        clear()

        const itemsText = cartItems.map(i => `• ${i.name} x${i.quantity} = Rp${formatPrice(i.price * i.quantity)}`).join('%0a')
        const message = `Halo, saya ingin konfirmasi pesanan:%0a%0a`
            + `*No. Pesanan:* ${orderData.order_number}%0a`
            + `*Nama:* ${form.customer_name}%0a`
            + `*Email:* ${form.customer_email}%0a`
            + `*Telepon:* ${form.customer_phone || '-'}%0a`
            + `*Alamat:* ${form.shipping_address}%0a`
            + `*Kurir:* ${form.courier.toUpperCase()} - ${form.selected_cost.service}%0a`
            + `*Ongkir:* Rp${formatPrice(form.selected_cost.cost)}%0a%0a`
            + `*Pesanan:*%0a${itemsText}%0a%0a`
            + `*Total:* Rp${formatPrice(grandTotal.value)}%0a%0a`
            + `Mohon konfirmasi ketersediaan dan total pembayaran. Terima kasih!`

        window.location.href = `https://wa.me/${waNumber}?text=${message}`
    } catch (e) {
        error.value = e.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
    } finally {
        submitting.value = false
    }
}

onMounted(() => {
    loadProvinces()
    loadCouriers()
    const qty = items.value.reduce((sum, i) => sum + i.quantity, 0)
    form.weight = qty * 300
})
</script>
