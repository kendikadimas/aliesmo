<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16">
        <h1 class="text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Info Pengiriman</h1>
        <p class="mt-3 text-sm text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#d0ceca] leading-relaxed">Semua yang perlu kamu tahu tentang pengiriman pesanan Aliesmo.</p>

        <!-- Estimasi Waktu -->
        <div class="mt-10 grid grid-cols-3 gap-3 lg:gap-4">
            <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] text-center">
                <div class="text-2xl font-bold text-maroon dark:text-[#f0eeeb]">1-2</div>
                <div class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb] mt-1">Hari Kerja</div>
                <div class="text-xs text-charcoal/50 dark:text-[#8a8a8e] mt-1">Proses Pesanan</div>
            </div>
            <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] text-center">
                <div class="text-2xl font-bold text-maroon dark:text-[#f0eeeb]">2-5</div>
                <div class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb] mt-1">Hari Kerja</div>
                <div class="text-xs text-charcoal/50 dark:text-[#8a8a8e] mt-1">Pulau Jawa</div>
            </div>
            <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] text-center">
                <div class="text-2xl font-bold text-maroon dark:text-[#f0eeeb]">5-14</div>
                <div class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb] mt-1">Hari Kerja</div>
                <div class="text-xs text-charcoal/50 dark:text-[#8a8a8e] mt-1">Luar Jawa</div>
            </div>
        </div>

        <!-- Kurir -->
        <div class="mt-10">
            <h2 class="text-base font-bold text-charcoal dark:text-[#f0eeeb] mb-5">Kurir yang Tersedia</h2>
            <!-- Loading couriers -->
            <div v-if="loadingCouriers" class="grid sm:grid-cols-2 gap-4">
                <div v-for="n in 4" :key="n" class="flex items-start gap-4 p-5">
                    <SkeletonLoader :loading="true" :radius="12" height="40px" width="40px" />
                    <div class="flex-1 space-y-2">
                        <SkeletonLoader :loading="true" :radius="99" height="14px" width="50%" />
                        <SkeletonLoader :loading="true" :radius="99" height="11px" width="75%" />
                    </div>
                </div>
            </div>
            <div v-else class="grid sm:grid-cols-2 gap-4">
                <div v-for="courier in couriers" :key="courier.name"
                    class="bg-white dark:bg-[#1c1c1e] p-5 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-maroon-50 dark:bg-maroon/20 flex items-center justify-center shrink-0">
                        <span class="text-maroon font-bold text-xs">{{ courier.code }}</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-charcoal dark:text-[#f0eeeb]">{{ courier.name }}</div>
                        <div class="text-xs text-charcoal/50 dark:text-[#8a8a8e] mt-0.5">{{ courier.desc }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ketentuan -->
        <div class="mt-10 space-y-6">
            <h2 class="text-base font-bold text-charcoal dark:text-[#f0eeeb]">Ketentuan Pengiriman</h2>

            <div class="space-y-4">
                <div class="bg-white dark:bg-[#1c1c1e] p-5 rounded-2xl border-2 border-maroon-50 dark:border-[#303032]">
                    <h3 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] mb-2">Ongkos Kirim</h3>
                    <p class="text-sm text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#d0ceca] leading-relaxed">Ongkos kirim dihitung berdasarkan berat produk dan jarak pengiriman. Biaya akan tampil otomatis saat checkout setelah kamu memilih kota tujuan dan kurir.</p>
                </div>

                <div class="bg-white dark:bg-[#1c1c1e] p-5 rounded-2xl border-2 border-maroon-50 dark:border-[#303032]">
                    <h3 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] mb-2">Pelacakan Pesanan</h3>
                    <p class="text-sm text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#d0ceca] leading-relaxed">Nomor resi akan dikirimkan melalui WhatsApp setelah paket dikirim. Kamu bisa melacak pesanan langsung di website kurir masing-masing.</p>
                </div>

                <div class="bg-white dark:bg-[#1c1c1e] p-5 rounded-2xl border-2 border-maroon-50 dark:border-[#303032]">
                    <h3 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] mb-2">Kerusakan saat Pengiriman</h3>
                    <p class="text-sm text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#d0ceca] leading-relaxed">Jika barang rusak saat pengiriman, segera foto kondisi paket dan produk sebelum dibuka sepenuhnya. Hubungi kami dalam 24 jam setelah menerima paket.</p>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="mt-10 bg-maroon-50 dark:bg-maroon/10 p-6 rounded-2xl border-2 border-maroon-100 dark:border-maroon/30">
            <p class="text-sm text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#d0ceca]">Ada pertanyaan tentang pengiriman? Hubungi kami langsung.</p>
            <a :href="`https://wa.me/${waNumber}`" target="_blank"
                class="inline-flex items-center gap-2 mt-3 px-5 py-2.5 bg-maroon text-white text-xs font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all">
                Chat via WhatsApp
            </a>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'
import { useSettings } from '../useSettings'

const { fetchSettings, get } = useSettings()
const waNumber = ref(import.meta.env.VITE_WHATSAPP_NUMBER || '')

const couriers = ref([])
const loadingCouriers = ref(true)

onMounted(async () => {
    // Fetch settings dan couriers secara paralel
    const [settingsResult, couriersResult] = await Promise.allSettled([
        fetchSettings(),
        api.get('/shipping/couriers'),
    ])

    // WA number dari settings, fallback ke env
    if (!waNumber.value) {
        waNumber.value = get('whatsapp_number', '6285196811722')
    }

    // Couriers dari API
    if (couriersResult.status === 'fulfilled') {
        const data = couriersResult.value.data?.data || []
        // Normalise: API returns array of { code, name } or similar
        couriers.value = data.map(c => ({
            code: (c.code || c.courier_code || '').toUpperCase(),
            name: c.name || c.courier_name || c.code || '',
            desc: c.services || c.description || c.service || '',
        })).filter(c => c.code)
    }

    // Fallback jika API tidak ada data
    if (!couriers.value.length) {
        couriers.value = [
            { code: 'JNE',      name: 'JNE',           desc: 'Reguler, YES, OKE' },
            { code: 'J&T',      name: 'J&T Express',   desc: 'Ekonomi, Express' },
            { code: 'SICEPAT',  name: 'SiCepat',       desc: 'Reguler, HALU' },
            { code: 'ANTERAJA', name: 'AnterAja',       desc: 'Next Day, Reguler' },
            { code: 'NINJA',    name: 'Ninja Express',  desc: 'Reguler, Express' },
            { code: 'POS',      name: 'POS Indonesia',  desc: 'Standar, Kilat Khusus' },
            { code: 'LION',     name: 'Lion Parcel',    desc: 'Reguler, Express' },
        ]
    }

    loadingCouriers.value = false
})
</script>
