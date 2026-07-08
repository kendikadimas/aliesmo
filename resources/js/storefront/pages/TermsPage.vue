<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <template v-if="loading">
            <div class="h-9 bg-coklat-100/60 rounded-full w-2/3 animate-pulse mb-3"></div>
            <div class="h-4 bg-coklat-100/60 rounded-full w-40 animate-pulse mb-10"></div>
        </template>
        <template v-else>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-charcoal tracking-tight mb-2">{{ title }}</h1>
            <p class="text-sm text-charcoal/50 mb-10">Terakhir diperbarui: {{ updatedAt }}</p>
        </template>

        <div v-if="loading" class="space-y-8">
            <section v-for="n in 5" :key="n" class="animate-pulse">
                <div class="h-5 bg-coklat-100/60 rounded-full w-1/3 mb-3"></div>
                <div class="space-y-2">
                    <div class="h-3 bg-coklat-100/60 rounded-full w-full"></div>
                    <div class="h-3 bg-coklat-100/60 rounded-full w-11/12"></div>
                    <div class="h-3 bg-coklat-100/60 rounded-full w-2/3"></div>
                </div>
            </section>
        </div>

        <div v-else class="prose prose-sm max-w-none text-charcoal/80 space-y-8">
            <section v-for="(section, index) in sections" :key="section.title || index">
                <h2 class="text-base font-bold text-charcoal mb-2">{{ index + 1 }}. {{ section.title }}</h2>
                <p class="text-sm text-charcoal/70 leading-relaxed whitespace-pre-line">{{ section.body }}</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-charcoal mb-2">{{ sections.length + 1 }}. Hubungi Kami</h2>
                <p class="text-sm text-charcoal/70 leading-relaxed">Ada pertanyaan? Chat langsung ke admin kami via
                    <a :href="`https://wa.me/${waNumber}`" target="_blank" rel="noopener noreferrer"
                        class="text-maroon hover:text-maroon-600 underline font-medium">WhatsApp</a>.
                </p>
            </section>
        </div>

        <div class="mt-10">
            <router-link to="/" class="inline-flex items-center gap-2 text-sm text-charcoal/60 hover:text-charcoal transition-colors">
                ← Kembali ke Beranda
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../api'

const waNumber = ref('6285196811722')
const title = ref('Syarat & Ketentuan')
const updatedAt = ref('Juli 2026')
const sections = ref(defaultSections())
const loading = ref(true)

onMounted(async () => {
    try {
        const res = await api.get('/settings/group/general')
        const data = res.data.data || {}
        if (data.whatsapp_number) waNumber.value = data.whatsapp_number
        if (data.terms_title) title.value = data.terms_title
        if (data.terms_updated_at) updatedAt.value = data.terms_updated_at
        if (Array.isArray(data.terms_sections) && data.terms_sections.length) sections.value = data.terms_sections
    } catch (e) {
        // fallback ke nomor default
    } finally {
        loading.value = false
    }
})

function defaultSections() {
    return [
        { title: 'Pemesanan', body: 'Dengan melakukan pemesanan di Aliesmo, kamu menyetujui bahwa data yang kamu berikan adalah benar dan lengkap. Pesanan dianggap sah setelah dikonfirmasi oleh admin via WhatsApp.' },
        { title: 'Pembayaran', body: 'Pembayaran dilakukan melalui transfer bank, QRIS, COD, atau metode lain yang disepakati bersama admin. Pesanan akan diproses setelah pembayaran dikonfirmasi.' },
        { title: 'Pengiriman', body: 'Ongkos kirim dihitung berdasarkan berat dan tujuan pengiriman. Estimasi waktu pengiriman bergantung pada kurir yang dipilih dan kondisi di lapangan.' },
        { title: 'Pengembalian Barang', body: 'Pengembalian barang dapat dilakukan dalam 3 hari setelah barang diterima, dengan syarat barang masih dalam kondisi asli, belum dipakai, dan tag masih terpasang. Hubungi admin via WhatsApp untuk proses retur.' },
        { title: 'Privasi Data', body: 'Data pribadi kamu (nama, email, nomor telepon, alamat) hanya digunakan untuk keperluan pemrosesan pesanan dan tidak akan dibagikan ke pihak ketiga tanpa persetujuanmu.' },
    ]
}
</script>
