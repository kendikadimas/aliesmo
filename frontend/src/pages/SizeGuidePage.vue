<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16">
        <h1 class="text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Panduan Ukuran</h1>
        <p class="mt-3 text-sm text-charcoal/70 dark:text-[#d0ceca] leading-relaxed">Temukan ukuran yang paling pas buat kamu dengan panduan di bawah ini.</p>

        <!-- Tips -->
        <div class="mt-8 bg-maroon-50 dark:bg-maroon/10 border-2 border-maroon-100 dark:border-maroon/30 rounded-2xl p-5">
            <h3 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] mb-2">Tips Mengukur</h3>
            <ul class="text-sm text-charcoal/70 dark:text-[#d0ceca] space-y-1.5 list-disc list-inside">
                <li>Gunakan pita ukur yang fleksibel, bukan penggaris kaku</li>
                <li>Ukur dalam posisi berdiri tegak</li>
                <li>Untuk hasil terbaik, minta bantuan orang lain saat mengukur</li>
                <li>Jika ukuranmu berada di antara dua size, pilih yang lebih besar</li>
            </ul>
        </div>

        <!-- Tabel Ukuran Pakaian -->
        <div class="mt-10">
            <h2 class="text-base font-bold text-charcoal dark:text-[#f0eeeb] mb-5">Tabel Ukuran Pakaian (cm)</h2>
            <div class="overflow-x-auto rounded-2xl border-2 border-maroon-50 dark:border-[#303032]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-maroon-50 dark:bg-[#28282a]/50">
                            <th class="px-4 py-3 text-left text-xs font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide">Ukuran</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide">Lingkar Dada</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide">Lingkar Pinggang</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide">Lingkar Pinggul</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide">Panjang Baju</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, i) in sizeChart" :key="row.size"
                            :class="i % 2 === 0 ? 'bg-white dark:bg-[#1c1c1e]' : 'bg-maroon-50/30 dark:bg-[#28282a]/30'">
                            <td class="px-4 py-3 font-bold text-maroon">{{ row.size }}</td>
                            <td class="px-4 py-3 text-center text-charcoal/70 dark:text-[#d0ceca]">{{ row.chest }}</td>
                            <td class="px-4 py-3 text-center text-charcoal/70 dark:text-[#d0ceca]">{{ row.waist }}</td>
                            <td class="px-4 py-3 text-center text-charcoal/70 dark:text-[#d0ceca]">{{ row.hip }}</td>
                            <td class="px-4 py-3 text-center text-charcoal/70 dark:text-[#d0ceca]">{{ row.length }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cara Mengukur -->
        <div class="mt-10">
            <h2 class="text-base font-bold text-charcoal dark:text-[#f0eeeb] mb-5">Cara Mengukur</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div v-for="guide in measureGuides" :key="guide.title"
                    class="bg-white dark:bg-[#1c1c1e] p-5 rounded-2xl border-2 border-maroon-50 dark:border-[#303032]">
                    <div class="text-maroon font-bold text-xs mb-2 uppercase tracking-wide">{{ guide.title }}</div>
                    <p class="text-sm text-charcoal/70 dark:text-[#d0ceca] leading-relaxed">{{ guide.desc }}</p>
                </div>
            </div>
        </div>

        <!-- Disclaimer -->
        <p class="mt-8 text-xs text-charcoal/40 dark:text-[#6a6a6e] text-center leading-relaxed">
            Ukuran bisa sedikit berbeda tergantung model dan bahan produk. Cek deskripsi tiap produk untuk detail ukuran spesifik.
        </p>
    </div>
</template>

<script setup>
const sizeChart = [
    { size: 'XS', chest: '80–84', waist: '60–64', hip: '84–88', length: '60' },
    { size: 'S',  chest: '84–88', waist: '64–68', hip: '88–92', length: '62' },
    { size: 'M',  chest: '88–92', waist: '68–72', hip: '92–96', length: '64' },
    { size: 'L',  chest: '92–96', waist: '72–76', hip: '96–100', length: '66' },
    { size: 'XL', chest: '96–100', waist: '76–80', hip: '100–104', length: '68' },
    { size: 'XXL', chest: '100–106', waist: '80–86', hip: '104–110', length: '70' },
]

const measureGuides = [
    {
        title: 'Lingkar Dada',
        desc: 'Ukur melingkar bagian dada yang paling lebar, tepat di bawah ketiak. Pastikan pita ukur horizontal dan tidak terlalu ketat.',
    },
    {
        title: 'Lingkar Pinggang',
        desc: 'Ukur di bagian pinggang terkecil, biasanya sekitar 2–3 cm di atas pusar. Jangan terlalu ketat.',
    },
    {
        title: 'Lingkar Pinggul',
        desc: 'Ukur di bagian pinggul terlebar, sekitar 18–20 cm di bawah pinggang.',
    },
    {
        title: 'Panjang Baju',
        desc: 'Ukur dari titik bahu tertinggi (ujung bahu) lurus ke bawah hingga panjang yang diinginkan.',
    },
]
</script>
