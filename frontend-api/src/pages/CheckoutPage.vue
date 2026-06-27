<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <h1 class="text-3xl lg:text-4xl font-light text-charcoal">Checkout</h1>

        <div v-if="submitting" class="py-24 text-center">
            <div class="inline-block w-8 h-8 border-2 border-charcoal/20 border-t-charcoal rounded-full animate-spin"></div>
            <p class="mt-4 text-sm text-charcoal/60">Memproses pesanan Anda...</p>
        </div>

        <form v-else @submit.prevent="submitOrder" class="mt-8 lg:mt-10 grid lg:grid-cols-5 gap-8 lg:gap-12">
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white p-6 lg:p-8 border border-aliesmo-200/50">
                    <h2 class="text-sm tracking-widest uppercase text-charcoal/70 mb-6">Informasi Pelanggan</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs tracking-wider uppercase text-charcoal/50 mb-1.5">Nama Lengkap</label>
                            <input v-model="form.customer_name" required placeholder="John Doe" class="w-full border border-aliesmo-200/70 px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/50 focus:outline-none transition-colors">
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-charcoal/50 mb-1.5">Email</label>
                                <input v-model="form.customer_email" type="email" required placeholder="john@example.com" class="w-full border border-aliesmo-200/70 px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/50 focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-charcoal/50 mb-1.5">Telepon</label>
                                <input v-model="form.customer_phone" placeholder="0812-3456-7890" class="w-full border border-aliesmo-200/70 px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/50 focus:outline-none transition-colors">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 lg:p-8 border border-aliesmo-200/50">
                    <h2 class="text-sm tracking-widest uppercase text-charcoal/70 mb-6">Alamat Pengiriman</h2>
                    <div>
                        <label class="block text-xs tracking-wider uppercase text-charcoal/50 mb-1.5">Alamat Lengkap</label>
                        <textarea v-model="form.shipping_address" required placeholder="Jl. Merdeka No. 123, RT 01/RW 02, Kelurahan Contoh, Kecamatan Contoh, Jakarta Pusat, DKI Jakarta" rows="3" class="w-full border border-aliesmo-200/70 px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/50 focus:outline-none transition-colors resize-none"></textarea>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white p-6 lg:p-8 border border-aliesmo-200/50 lg:sticky lg:top-28">
                    <h2 class="text-sm tracking-widest uppercase text-charcoal/70 mb-6">Ringkasan Pesanan</h2>
                    <div class="space-y-3">
                        <div v-for="item in items" :key="item.product_id" class="flex justify-between text-sm">
                            <span class="text-charcoal/70 truncate mr-4">{{ item.name }} <span class="text-charcoal/40">×{{ item.quantity }}</span></span>
                            <span class="text-charcoal font-medium shrink-0">Rp {{ formatPrice(item.price * item.quantity) }}</span>
                        </div>
                    </div>
                    <div class="border-t border-aliesmo-200/50 mt-4 pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-charcoal/70">
                            <span>Subtotal</span>
                            <span>Rp {{ formatPrice(total()) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-charcoal/70">
                            <span>Ongkos Kirim</span>
                            <span>Dihitung kemudian</span>
                        </div>
                        <div class="flex justify-between text-base font-medium text-charcoal pt-2 border-t border-aliesmo-200/50">
                            <span>Total</span>
                            <span>Rp {{ formatPrice(total()) }}</span>
                        </div>
                    </div>

                    <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm">{{ error }}</div>

                    <button type="submit" class="w-full mt-6 px-8 py-3.5 bg-charcoal text-ivory text-sm tracking-widest uppercase hover:bg-charcoal/90 transition-all active:scale-[0.98]">
                        Pesan Sekarang
                    </button>

                    <p class="mt-4 text-xs text-center text-charcoal/40 leading-relaxed">Dengan melakukan pemesanan, Anda menyetujui <a href="#" class="underline hover:text-charcoal/60">syarat & ketentuan</a> kami.</p>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import { useCartStore } from '../cart'

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

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price)
}

async function submitOrder() {
    if (!items.value.length) {
        error.value = 'Keranjang belanja kosong.'
        return
    }
    submitting.value = true
    error.value = ''

    try {
        const payload = {
            items: items.value.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
            ...form,
        }

        const res = await api.post('/orders', payload)
        clear()

        if (res.data.payment?.redirect_url) {
            window.location.href = res.data.payment.redirect_url
        } else {
            router.push(`/order/${res.data.order.order_number}`)
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
    } finally {
        submitting.value = false
    }
}
</script>
