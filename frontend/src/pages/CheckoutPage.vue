<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <h1 class="text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Checkout</h1>

        <div v-if="submitting" class="py-24 text-center">
            <div class="inline-block w-10 h-10 border-4 border-coral-100 border-t-coral rounded-full animate-spin"></div>
            <p class="mt-4 text-base text-charcoal/50">Bentar ya, kami lagi proses pesananmu...</p>
        </div>

        <form v-else @submit.prevent="submitOrder" class="mt-8 lg:mt-10 grid lg:grid-cols-5 gap-8 lg:gap-12">
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-coral-50">
                    <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Data Diri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Nama Lengkap</label>
                            <input v-model="form.customer_name" required placeholder="Masukkan nama kamu" class="w-full border-2 border-coral-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-coral focus:outline-none transition-colors">
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Email</label>
                                <input v-model="form.customer_email" type="email" required placeholder="kamu@email.com" class="w-full border-2 border-coral-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-coral focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Telepon</label>
                                <input v-model="form.customer_phone" placeholder="0812-xxxx-xxxx" class="w-full border-2 border-coral-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-coral focus:outline-none transition-colors">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-coral-50">
                    <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Alamat Kirim</h2>
                    <div>
                        <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Alamat Lengkap</label>
                        <textarea v-model="form.shipping_address" required placeholder="Contoh: Jl. Merdeka No. 123, RT 01/RW 02, Kelurahan, Kecamatan, Kota, Provinsi" rows="3" class="w-full border-2 border-coral-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-coral focus:outline-none transition-colors resize-none"></textarea>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-coral-50 lg:sticky lg:top-28">
                    <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Ringkasan Pesanan</h2>
                    <div class="space-y-3">
                        <div v-for="item in items" :key="item.product_id" class="flex justify-between text-sm">
                            <span class="text-charcoal/70 truncate mr-4">{{ item.name }} <span class="text-charcoal/40">×{{ item.quantity }}</span></span>
                            <span class="font-bold text-charcoal shrink-0">Rp{{ formatPrice(item.price * item.quantity) }}</span>
                        </div>
                    </div>
                    <div class="border-t-2 border-coral-100 mt-4 pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-charcoal/60">
                            <span>Subtotal</span>
                            <span class="font-medium">Rp{{ formatPrice(total()) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-charcoal/60">
                            <span>Ongkir</span>
                            <span v-if="total() >= 200000" class="text-green-600 font-medium">Gratis!</span>
                            <span v-else class="text-charcoal/40">Dihitung nanti</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-charcoal pt-3 border-t-2 border-coral-100">
                            <span>Total</span>
                            <span class="text-coral">Rp{{ formatPrice(total()) }}</span>
                        </div>
                    </div>

                    <div v-if="error" class="mt-4 p-3 bg-red-50 rounded-xl border border-red-200 text-red-700 text-sm">{{ error }}</div>

                    <button type="submit" class="w-full mt-6 px-8 py-3.5 bg-coral text-white text-sm font-semibold rounded-xl hover:bg-coral-600 transition-all active:scale-[0.97] shadow-lg shadow-coral/25">
                        Pesan Sekarang
                    </button>

                    <p class="mt-4 text-xs text-center text-charcoal/40 leading-relaxed">Dengan pesan, kamu setuju sama <a href="#" class="text-coral hover:text-coral-600 underline">syarat & ketentuan</a> kita.</p>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'

const router = useRouter()
const { items, total, clear } = useCartStore()
const submitting = ref(false)
const error = ref('')

const form = reactive({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    shipping_address: '',
})

function generateOrderNumber() {
    const date = new Date()
    const rand = String(Math.floor(Math.random() * 9000) + 1000)
    return `ALM-${date.getFullYear()}${String(date.getMonth()+1).padStart(2,'0')}${String(date.getDate()).padStart(2,'0')}-${rand}`
}

function submitOrder() {
    if (!items.value.length) {
        error.value = 'Wah, keranjangmu kosong!'
        return
    }
    submitting.value = true
    error.value = ''

    const orderData = {
        order_number: generateOrderNumber(),
        customer_name: form.customer_name,
        customer_email: form.customer_email,
        customer_phone: form.customer_phone,
        shipping_address: form.shipping_address,
        items: items.value.map(i => ({
            product_name: i.name,
            quantity: i.quantity,
            price: i.price,
            subtotal: i.price * i.quantity,
        })),
        subtotal: total(),
        shipping_cost: 0,
        total: total(),
        status: 'pending',
        created_at: new Date().toISOString(),
    }

    clear()
    sessionStorage.setItem('lastOrder', JSON.stringify(orderData))
    router.push(`/order/${orderData.order_number}`)
    submitting.value = false
}
</script>
