<template>
    <div>
        <section class="min-h-[100dvh] grid lg:grid-cols-2">
            <div class="relative flex items-center px-4 sm:px-8 lg:px-12 xl:px-16 py-24 lg:py-0 bg-gradient-to-br from-cream to-coral-50/40">
                <div class="max-w-lg">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-coral/10 rounded-full text-coral text-xs font-medium mb-6">
                        <span class="w-1.5 h-1.5 rounded-full bg-coral animate-pulse"></span>
                        Koleksi Baru — Mulai Rp 149.000
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold leading-[1] tracking-tight text-charcoal text-balance">
                        Kemeja
                        <span class="text-coral">Keren</span><br>
                        untuk<br>
                        <span class="bg-coral/10 px-2 rounded-xl">Semua</span>
                    </h1>
                    <p class="mt-5 text-base lg:text-lg text-charcoal/60 leading-relaxed max-w-md">
                        Dari kantor hingga nongkrong, temukan kemeja yang bikin kamu tampil pede tanpa bikin kantong bolong. Kualitas premium, harga merakyat.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="#shop" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-coral text-white text-sm font-semibold rounded-xl hover:bg-coral-600 transition-all active:scale-[0.97] shadow-lg shadow-coral/25 min-w-[160px]">
                            Belanja Yuk!
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                        <a href="#shop" class="inline-flex items-center justify-center px-8 py-3.5 bg-white text-charcoal text-sm font-semibold rounded-xl border-2 border-coral-200 hover:border-coral hover:bg-coral-50 transition-all active:scale-[0.97] min-w-[160px]">
                            Lihat Koleksi
                        </a>
                    </div>
                    <div class="mt-10 flex flex-wrap items-center gap-5 text-sm text-charcoal/50">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-coral" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0112 2a8 8 0 018 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>Gratis Ongkir</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-coral" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            <span>Bayar di Tempat</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-coral" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Garansi 30 Hari</span>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 w-32 h-32 rounded-full bg-warm/20 -z-0 hidden lg:block"></div>
                <div class="absolute top-20 right-20 w-16 h-16 rounded-full bg-coral/10 -z-0 hidden lg:block"></div>
            </div>
            <div class="relative min-h-[50vh] lg:min-h-screen bg-coral-50 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-l from-cream/50 via-transparent to-transparent z-10"></div>
                <div class="absolute top-8 left-8 w-4 h-4 rounded-full bg-warm z-10"></div>
                <div class="absolute bottom-12 right-12 w-6 h-6 rounded-full bg-navy/20 z-10"></div>
                <div class="absolute inset-0 flex items-center justify-center p-8 lg:p-12">
                    <div class="relative w-full max-w-md">
                        <div class="aspect-[3/4] relative overflow-hidden rounded-2xl shadow-2xl">
                            <img src="https://picsum.photos/seed/kemeja-gaul/800/1067" alt="Kemeja Aliesmo" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-gradient-to-t from-charcoal/10 to-transparent"></div>
                        </div>
                        <div class="absolute -bottom-5 -right-5 bg-white rounded-2xl p-4 shadow-xl border border-coral-100">
                            <p class="text-xs text-charcoal/50">Mulai dari</p>
                            <p class="text-2xl font-bold text-coral">Rp 149rb</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-12 bg-cream-dark/30 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-8 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-none justify-center">
                    <div v-for="cat in categories" :key="cat.id" @click="selectedCategory = cat.slug; scrollToShop()" class="snap-start shrink-0 cursor-pointer group">
                        <div class="w-24 h-28 bg-white rounded-2xl border-2 border-coral-100/60 hover:border-coral transition-colors flex flex-col items-center justify-center gap-2 p-3 shadow-sm hover:shadow-md">
                            <span class="text-2xl font-bold text-coral-300 group-hover:text-coral transition-colors">{{ cat.name.charAt(0) }}</span>
                            <span class="text-xs font-medium text-charcoal/60 text-center leading-tight">{{ cat.name }}</span>
                        </div>
                    </div>
                    <div @click="selectedCategory = ''; scrollToShop()" class="snap-start shrink-0 cursor-pointer group">
                        <div class="w-24 h-28 bg-cream rounded-2xl border-2 border-dashed border-coral-200/60 hover:border-coral transition-colors flex flex-col items-center justify-center gap-2 p-3">
                            <svg class="w-6 h-6 text-coral-300 group-hover:text-coral transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span class="text-xs font-medium text-charcoal/50">Semua</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="shop" class="py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Koleksi <span class="text-coral">Kami</span></h2>
                        <p class="mt-1 text-sm text-charcoal/50">Temukan kemeja favoritmu di sini</p>
                    </div>
                    <div class="hidden sm:flex gap-2">
                        <button @click="prevPage" :disabled="page <= 1" class="w-10 h-10 rounded-xl border-2 border-coral-200/60 flex items-center justify-center text-charcoal/50 hover:border-coral hover:text-coral transition-colors disabled:opacity-30 disabled:cursor-not-allowed active:scale-95">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <button @click="nextPage" :disabled="page >= totalPages" class="w-10 h-10 rounded-xl border-2 border-coral-200/60 flex items-center justify-center text-charcoal/50 hover:border-coral hover:text-coral transition-colors disabled:opacity-30 disabled:cursor-not-allowed active:scale-95">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-8">
                    <button @click="selectedCategory = ''" class="px-4 py-2 text-xs font-semibold rounded-xl border-2 transition-all" :class="!selectedCategory ? 'bg-coral text-white border-coral' : 'bg-white text-charcoal/60 border-coral-100 hover:border-coral/50'">Semua</button>
                    <button v-for="cat in categories" :key="cat.id" @click="selectedCategory = cat.slug" class="px-4 py-2 text-xs font-semibold rounded-xl border-2 transition-all" :class="selectedCategory === cat.slug ? 'bg-coral text-white border-coral' : 'bg-white text-charcoal/60 border-coral-100 hover:border-coral/50'">{{ cat.name }}</button>
                </div>

                <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <div v-for="n in 8" :key="n" class="animate-pulse">
                        <div class="aspect-[3/4] bg-cream-dark rounded-2xl"></div>
                        <div class="mt-3 space-y-2 px-1">
                            <div class="h-2.5 bg-cream-dark rounded-full w-1/3"></div>
                            <div class="h-3.5 bg-cream-dark rounded-full w-2/3"></div>
                            <div class="h-3.5 bg-cream-dark rounded-full w-1/4"></div>
                        </div>
                    </div>
                </div>

                <div v-else-if="!products.length && !loading" class="text-center py-16">
                    <p class="text-lg text-charcoal/50">Belum ada produk nih, coba kategori lain yuk!</p>
                </div>

                <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <div v-for="product in displayProducts" :key="product.id" class="group cursor-pointer bg-white rounded-2xl overflow-hidden border-2 border-coral-50 hover:border-coral-200 transition-all hover:shadow-lg active:scale-[0.98]" @click="$router.push(`/products/${product.slug}`)">
                        <div class="aspect-[3/4] bg-coral-50 overflow-hidden relative">
                            <img v-if="product.thumbnail" :src="product.thumbnail" :alt="product.name" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <img :src="`https://picsum.photos/seed/kemeja-${product.id || product.slug}/600/800`" :alt="product.name" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500" />
                            </div>
                            <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-2 left-2 bg-warm text-charcoal text-[10px] font-semibold px-2 py-1 rounded-lg">
                                Sisa {{ product.stock }}
                            </div>
                            <div v-if="product.stock <= 3" class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-semibold px-2 py-1 rounded-lg animate-pulse">
                HOTS!
                            </div>
                            <div v-if="product.stock === 0" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                <span class="bg-charcoal text-white text-xs font-semibold px-3 py-1.5 rounded-lg">Stok Habis</span>
                            </div>
                            <button @click.stop="addToCart(product)" :disabled="product.stock === 0" class="absolute bottom-0 left-0 right-0 py-3 bg-coral text-white text-xs font-semibold tracking-wide translate-y-full group-hover:translate-y-0 transition-transform duration-300 hover:bg-coral-600 disabled:opacity-0">
                                {{ product.stock === 0 ? 'Stok Habis' : '+ Masuk Keranjang' }}
                            </button>
                        </div>
                        <div class="p-3 lg:p-4">
                            <p class="text-[11px] font-medium text-coral-400 uppercase tracking-wide">{{ product.category?.name || 'Kemeja' }}</p>
                            <h3 class="text-sm font-semibold text-charcoal mt-0.5 leading-snug line-clamp-2">{{ product.name }}</h3>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-base font-bold text-coral">Rp{{ formatPrice(product.price) }}</p>
                                <div class="flex items-center gap-0.5">
                                    <svg v-for="n in 5" :key="n" class="w-3 h-3" :class="n <= 4 ? 'text-warm-400' : 'text-coral-100'" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <span class="text-[10px] text-charcoal/40 ml-0.5">(86)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="hasMore && products.length" class="text-center mt-10">
                    <button @click="loadMore" class="px-8 py-3 bg-white text-charcoal text-sm font-semibold rounded-xl border-2 border-coral-200 hover:border-coral hover:bg-coral-50 transition-all active:scale-[0.97]">
                        Tampilkan Lainnya
                    </button>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-20 bg-cream-dark/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Kenapa Pilih <span class="text-coral">Aliesmo</span>?</h2>
                    <p class="mt-2 text-sm text-charcoal/50">Kami bikin kemeja yang nyaman, berkualitas, dan pastinya ramah di kantong.</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mt-10">
                    <div class="bg-white rounded-2xl p-6 border-2 border-coral-50 hover:border-coral-200 transition-all text-center">
                        <div class="w-12 h-12 rounded-xl bg-coral-50 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-coral" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-charcoal">Kualitas Premium</h3>
                        <p class="mt-1 text-sm text-charcoal/50">Bahan katun pilihan yang adem dan nyaman dipakai seharian.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border-2 border-coral-50 hover:border-coral-200 transition-all text-center">
                        <div class="w-12 h-12 rounded-xl bg-coral-50 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-coral" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12l2 2 4-4"/></svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-charcoal">Harga Merakyat</h3>
                        <p class="mt-1 text-sm text-charcoal/50">Mulai dari Rp 149.000 aja, kualitas nggak murahan.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border-2 border-coral-50 hover:border-coral-200 transition-all text-center">
                        <div class="w-12 h-12 rounded-xl bg-coral-50 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-coral" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-charcoal">Gratis Ongkir</h3>
                        <p class="mt-1 text-sm text-charcoal/50">Belanja di atas Rp 200.000, ongkir kami yang bayarin.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border-2 border-coral-50 hover:border-coral-200 transition-all text-center">
                        <div class="w-12 h-12 rounded-xl bg-coral-50 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-coral" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-charcoal">Garansi 30 Hari</h3>
                        <p class="mt-1 text-sm text-charcoal/50">Tidak cocok? Tenang, kamu bisa return dalam 30 hari.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-20 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                    <div class="relative">
                        <div class="aspect-[4/5] rounded-2xl overflow-hidden">
                            <img src="https://picsum.photos/seed/kemeja-seru/700/875" alt="Aliesmo" class="w-full h-full object-cover" />
                        </div>
                        <div class="absolute -bottom-4 -right-4 bg-coral rounded-2xl p-4 lg:p-5 text-white shadow-xl">
                            <p class="text-xs font-semibold text-white/80">Sudah Terjual</p>
                            <p class="text-2xl font-bold">12.000+</p>
                            <p class="text-xs text-white/80">kemeja ke seluruh Indonesia</p>
                        </div>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-navy-50 rounded-full text-navy text-xs font-semibold mb-4">
                            <span class="w-1.5 h-1.5 rounded-full bg-navy"></span>
                            Cerita Kami
                        </div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Kemeja <span class="text-coral">Berkualitas</span> untuk Semua Kalangan</h2>
                        <p class="mt-4 text-base text-charcoal/60 leading-relaxed">Aliesmo hadir karena kami percaya semua orang berhak tampil keren tanpa harus merogoh kocek dalam-dalam. Setiap kemeja dirancang dengan bahan yang nyaman dan model yang kekinian — cocok buat ke kantor, kondangan, atau sekadar hangout bareng teman.</p>
                        <p class="mt-3 text-base text-charcoal/60 leading-relaxed">Kami terus berinovasi ngasih yang terbaik buat kamu. Karena buat kami, senyum puas kamu adalah prioritas utama.</p>
                        <a href="#shop" class="inline-flex items-center gap-2 mt-6 text-sm font-semibold text-coral hover:text-coral-600 transition-colors group">
                            Jelajahi Koleksi
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-20 bg-gradient-to-br from-coral to-coral-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-xl mx-auto text-center text-white">
                    <h2 class="text-3xl lg:text-4xl font-bold tracking-tight">Dapatkan Info Terbaru!</h2>
                    <p class="mt-2 text-white/80">Daftar sekarang dan dapatkan promo spesial + notifikasi koleksi baru.</p>
                    <form @submit.prevent="handleSubscribe" class="mt-8 flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                        <input v-model="email" type="email" required placeholder="Masukkan email kamu" class="flex-1 px-4 py-3 rounded-xl text-sm text-charcoal placeholder:text-charcoal/40 focus:outline-none focus:ring-2 focus:ring-white/50">
                        <button type="submit" class="px-8 py-3 bg-charcoal text-white text-sm font-semibold rounded-xl hover:bg-charcoal/90 transition-all active:scale-[0.97] whitespace-nowrap shadow-lg">
                            Daftar Gratis
                        </button>
                    </form>
                    <p v-if="subscribed" class="mt-4 text-sm text-white/90 font-medium">Makasih! Kamu udah terdaftar 🎉</p>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import { useCartStore } from '../cart'
import { categories, products, formatPrice } from '../mock-data'

const route = useRoute()
const { addItem } = useCartStore()
const selectedCategory = ref(route.query.shop ? '' : '')
const loading = ref(false)
const page = ref(1)
const perPage = 8
const email = ref('')
const subscribed = ref(false)

const filteredProducts = computed(() => {
    if (!selectedCategory.value) return products
    return products.filter(p => p.category?.slug === selectedCategory.value)
})

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / perPage))

const displayProducts = computed(() => {
    const end = page.value * perPage
    return filteredProducts.value.slice(0, end)
})

const hasMore = computed(() => page.value * perPage < filteredProducts.value.length)

function prevPage() {
    if (page.value > 1) page.value--
}

function nextPage() {
    if (hasMore.value) page.value++
}

function loadMore() {
    if (hasMore.value) page.value++
}

function addToCart(product) {
    addItem(product, 1)
}

function scrollToShop() {
    page.value = 1
    document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' })
}

function handleSubscribe() {
    if (email.value) {
        subscribed.value = true
        email.value = ''
        setTimeout(() => { subscribed.value = false }, 4000)
    }
}

onMounted(() => {
    if (route.query.shop) {
        setTimeout(() => document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' }), 150)
    }
})
</script>
