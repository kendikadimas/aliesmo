<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <h1 class="text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Info Pengiriman</h1>
        <p class="mt-3 text-sm text-charcoal/70 leading-relaxed">Semua yang perlu kamu tahu tentang pengiriman pesanan Aliesmo.</p>

        <!-- Estimasi Waktu -->
        <div class="mt-10 grid sm:grid-cols-3 gap-4">
            <div class="bg-white p-6 rounded-2xl border-2 border-maroon-50 text-center">
                <div class="text-2xl font-bold text-maroon">1-2</div>
                <div class="text-xs font-semibold text-charcoal mt-1">Hari Kerja</div>
                <div class="text-xs text-charcoal/50 mt-1">Proses Pesanan</div>
            </div>
            <div class="bg-white p-6 rounded-2xl border-2 border-maroon-50 text-center">
                <div class="text-2xl font-bold text-maroon">2-5</div>
                <div class="text-xs font-semibold text-charcoal mt-1">Hari Kerja</div>
                <div class="text-xs text-charcoal/50 mt-1">Pulau Jawa</div>
            </div>
            <div class="bg-white p-6 rounded-2xl border-2 border-maroon-50 text-center">
                <div class="text-2xl font-bold text-maroon">5-14</div>
                <div class="text-xs font-semibold text-charcoal mt-1">Hari Kerja</div>
                <div class="text-xs text-charcoal/50 mt-1">Luar Jawa</div>
            </div>
        </div>

        <!-- Kurir -->
        <div class="mt-10">
            <h2 class="text-base font-bold text-charcoal mb-5">Kurir yang Tersedia</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div v-for="courier in couriers" :key="courier.name"
                    class="bg-white p-5 rounded-2xl border-2 border-maroon-50 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-maroon-50 flex items-center justify-center shrink-0">
                        <span class="text-maroon font-bold text-xs">{{ courier.code }}</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-charcoal">{{ courier.name }}</div>
                        <div class="text-xs text-charcoal/50 mt-0.5">{{ courier.desc }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ketentuan -->
        <div class="mt-10 space-y-6">
            <h2 class="text-base font-bold text-charcoal">Ketentuan Pengiriman</h2>

            <div class="space-y-4">
                <div class="bg-white p-5 rounded-2xl border-2 border-maroon-50">
                    <h3 class="text-sm font-bold text-charcoal mb-2">Ongkos Kirim</h3>
                    <p class="text-sm text-charcoal/70 leading-relaxed">Ongkos kirim dihitung berdasarkan berat produk dan jarak pengiriman. Biaya akan tampil otomatis saat checkout setelah kamu memilih kota tujuan dan kurir.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border-2 border-maroon-50">
                    <h3 class="text-sm font-bold text-charcoal mb-2">Pelacakan Pesanan</h3>
                    <p class="text-sm text-charcoal/70 leading-relaxed">Nomor resi akan dikirimkan melalui WhatsApp setelah paket dikirim. Kamu bisa melacak pesanan langsung di website kurir masing-masing.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border-2 border-maroon-50">
                    <h3 class="text-sm font-bold text-charcoal mb-2">Kerusakan saat Pengiriman</h3>
                    <p class="text-sm text-charcoal/70 leading-relaxed">Jika barang rusak saat pengiriman, segera foto kondisi paket dan produk sebelum dibuka sepenuhnya. Hubungi kami dalam 24 jam setelah menerima paket.</p>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="mt-10 bg-maroon-50 p-6 rounded-2xl border-2 border-maroon-100">
            <p class="text-sm text-charcoal/70">Ada pertanyaan tentang pengiriman? Hubungi kami langsung.</p>
            <a :href="`https://wa.me/${waNumber}`" target="_blank"
                class="inline-flex items-center gap-2 mt-3 px-5 py-2.5 bg-maroon text-white text-xs font-semibold rounded-xl hover:bg-maroon-600 transition-all">
                Chat via WhatsApp
            </a>
        </div>
    </div>
</template>

<script setup>
const waNumber = import.meta.env.VITE_WHATSAPP_NUMBER || '6281234567890'

const couriers = [
    { code: 'JNE', name: 'JNE', desc: 'Reguler, YES, OKE' },
    { code: 'J&T', name: 'J&T Express', desc: 'Ekonomi, Express' },
    { code: 'SiCe', name: 'SiCepat', desc: 'Reguler, HALU' },
    { code: 'POS', name: 'POS Indonesia', desc: 'Standar, Kilat Khusus' },
    { code: 'TIKI', name: 'TIKI', desc: 'Reguler, Ekspress, ONS' },
    { code: 'AnTr', name: 'Anteraja', desc: 'Same Day, Next Day' },
]
</script>
