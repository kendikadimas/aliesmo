<template>
    <div>
        <section class="min-h-[100dvh] grid lg:grid-cols-2">
            <div class="relative flex items-center px-4 sm:px-8 lg:px-12 xl:px-16 py-24 lg:py-0 bg-paper">
                <div class="max-w-lg">
                    <div class="flex items-center gap-3 mb-8">
                        <span class="w-8 h-[1px] bg-ink-40"></span>
                        <span class="text-[11px] tracking-[0.25em] uppercase text-ink-40">Koleksi Premium 2025</span>
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-light leading-[0.95] tracking-tight text-charcoal text-balance">
                        Kemeja <span class="font-medium">Premium</span><br>
                        untuk Pria<br>
                        <span class="italic font-serif font-light text-ink-60">Modern</span>
                    </h1>
                    <p class="mt-6 text-base lg:text-lg text-charcoal/55 leading-relaxed max-w-md">
                        Dirancang dengan presisi dari katun premium pilihan. Aliesmo menghadirkan kemeja yang menemani setiap momen pentingmu dengan gaya dan kenyamanan.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <router-link to="/?shop=1" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-charcoal text-paper text-xs tracking-[0.2em] uppercase hover:bg-charcoal/90 transition-all active:scale-[0.98] min-w-[180px]">
                            Belanja Sekarang
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square">
                                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </router-link>
                        <router-link to="/?shop=1" class="inline-flex items-center justify-center px-8 py-3.5 border border-charcoal/20 text-charcoal/70 text-xs tracking-[0.2em] uppercase hover:bg-charcoal hover:text-paper transition-all active:scale-[0.98] min-w-[180px]">
                            Lihat Koleksi
                        </router-link>
                    </div>
                    <div class="mt-10 flex items-center gap-8 text-sm text-charcoal/40">
                        <div class="flex items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0112 2a8 8 0 018 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>JNE / J&T / SiCepat</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            <span>Bayar di Tempat</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative min-h-[50vh] lg:min-h-screen bg-ink-05 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-l from-paper/40 via-transparent to-transparent z-10"></div>
                <div class="absolute inset-0 flex items-center justify-center p-8 lg:p-12">
                    <div class="relative w-full max-w-md">
                        <div class="aspect-[3/4] relative overflow-hidden">
                            <img src="https://picsum.photos/seed/kemeja-putih-formal/800/1067" alt="Aliesmo Premium Shirt" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-gradient-to-t from-charcoal/20 to-transparent"></div>
                        </div>
                        <div class="absolute -bottom-4 -right-4 lg:-bottom-6 lg:-right-6 bg-paper p-4 lg:p-6 shadow-xl">
                            <p class="text-xs tracking-[0.2em] uppercase text-charcoal/50">Mulai dari</p>
                            <p class="text-xl lg:text-2xl font-medium text-charcoal">Rp 149.000</p>
                        </div>
                    </div>
                </div>
                <div class="absolute bottom-6 left-6 z-20 hidden lg:block">
                    <p class="text-[10px] tracking-[0.3em] uppercase text-charcoal/30">New Collection</p>
                </div>
            </div>
        </section>

        <section class="py-12 lg:py-16 bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-6 mb-8">
                    <span class="text-[11px] tracking-[0.25em] uppercase text-charcoal/40 shrink-0">Kategori</span>
                    <span class="h-[1px] flex-1 bg-ink-10"></span>
                </div>
                <div class="flex gap-4 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-none">
                    <div v-for="cat in categories" :key="cat.id" @click="selectCategory(cat.slug)" class="snap-start shrink-0 cursor-pointer group">
                        <div class="w-28 h-36 lg:w-32 lg:h-40 bg-ink-05 overflow-hidden relative">
                            <div class="absolute inset-0 flex items-center justify-center bg-ink-05 group-hover:bg-ink-10 transition-colors">
                                <span class="text-3xl font-light text-ink-20 group-hover:text-ink-40 transition-colors">{{ cat.name.charAt(0) }}</span>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-charcoal/70 to-transparent p-3">
                                <p class="text-xs font-medium text-white">{{ cat.name }}</p>
                                <p v-if="cat.products_count !== undefined" class="text-[10px] text-white/60">{{ cat.products_count }} produk</p>
                            </div>
                        </div>
                    </div>
                    <div @click="filters.category = ''; fetchProducts()" class="snap-start shrink-0 cursor-pointer group">
                        <div class="w-28 h-36 lg:w-32 lg:h-40 bg-paper border border-dashed border-ink-10 overflow-hidden flex items-center justify-center group-hover:border-charcoal/30 transition-colors">
                            <div class="text-center">
                                <svg class="w-5 h-5 mx-auto text-charcoal/30 group-hover:text-charcoal/50 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <p class="text-xs text-charcoal/40 mt-2 group-hover:text-charcoal/60 transition-colors">Semua</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="shop" class="py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-10 lg:mb-12">
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-6 h-[1px] bg-ink-40"></span>
                            <span class="text-[11px] tracking-[0.25em] uppercase text-ink-40">Produk</span>
                        </div>
                        <h2 class="text-3xl lg:text-4xl font-light text-charcoal">Koleksi <span class="font-medium">Terbaru</span></h2>
                    </div>
                    <div class="hidden sm:flex gap-3">
                        <button @click="prevPage" :disabled="page <= 1" class="w-10 h-10 border border-ink-10 flex items-center justify-center hover:border-charcoal/30 transition-colors disabled:opacity-30 disabled:cursor-not-allowed active:scale-95">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <button @click="nextPage" :disabled="!hasMore" class="w-10 h-10 border border-ink-10 flex items-center justify-center hover:border-charcoal/30 transition-colors disabled:opacity-30 disabled:cursor-not-allowed active:scale-95">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mb-8">
                    <button @click="filters.category = ''; filters.sort = 'newest'; fetchProducts()" class="px-4 py-2 text-xs tracking-widest uppercase border transition-all" :class="!filters.category ? 'bg-charcoal text-paper border-charcoal' : 'bg-white text-charcoal/60 border-ink-10 hover:border-charcoal/30'">Semua</button>
                    <button v-for="cat in categories" :key="cat.id" @click="selectCategory(cat.slug)" class="px-4 py-2 text-xs tracking-widest uppercase border transition-all" :class="filters.category === cat.slug ? 'bg-charcoal text-paper border-charcoal' : 'bg-white text-charcoal/60 border-ink-10 hover:border-charcoal/30'">{{ cat.name }}</button>
                </div>

                <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
                    <div v-for="n in 8" :key="n" class="animate-pulse">
                        <div class="aspect-[3/4] bg-ink-05/80"></div>
                        <div class="mt-4 space-y-2.5">
                            <div class="h-2.5 bg-ink-05/80 w-1/4"></div>
                            <div class="h-3.5 bg-ink-05/80 w-2/3"></div>
                            <div class="h-3.5 bg-ink-05/80 w-1/5"></div>
                        </div>
                    </div>
                </div>

                <div v-else-if="!products.length && !loading" class="text-center py-16 lg:py-24">
                    <svg class="w-10 h-10 mx-auto text-ink-20 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <p class="text-lg text-charcoal/50">Belum ada produk di kategori ini.</p>
                    <button @click="filters.category = ''; fetchProducts()" class="mt-4 text-sm text-ink-60 hover:text-charcoal transition-colors underline underline-offset-4">Lihat semua produk</button>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
                    <div v-for="product in products" :key="product.id" class="group cursor-pointer" @click="$router.push(`/products/${product.slug}`)">
                        <div class="aspect-[3/4] bg-ink-05 overflow-hidden relative">
                            <img :src="productImage(product, 0)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 ease-out group-hover:opacity-0" />
                            <img :src="productImage(product, 1)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 ease-out opacity-0 group-hover:opacity-100" />
                            <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-3 left-3 bg-ink/80 text-white text-[10px] tracking-wider uppercase px-2 py-1">
                                {{ product.stock <= 3 ? 'Hampir Habis' : 'Sisa ' + product.stock }}
                            </div>
                            <div v-if="product.stock === 0" class="absolute inset-0 bg-charcoal/60 flex items-center justify-center">
                                <span class="text-white text-xs tracking-[0.2em] uppercase">Stok Habis</span>
                            </div>
                            <button @click.stop="addToCart(product)" :disabled="product.stock === 0" class="absolute bottom-0 left-0 right-0 py-3.5 bg-charcoal/90 text-paper text-xs tracking-[0.2em] uppercase translate-y-full group-hover:translate-y-0 transition-transform duration-300 hover:bg-charcoal disabled:opacity-0">
                                {{ product.stock === 0 ? 'Stok Habis' : '+ Keranjang' }}
                            </button>
                            <button @click.stop="quickView(product)" class="absolute top-3 right-3 w-8 h-8 bg-white/90 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white active:scale-95">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <div class="mt-4 space-y-1.5">
                            <p class="text-[11px] tracking-[0.2em] uppercase text-ink-40">{{ product.category?.name || 'Kemeja' }}</p>
                            <h3 class="text-sm font-medium text-charcoal group-hover:text-ink-60 transition-colors leading-snug">{{ product.name }}</h3>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-charcoal">Rp {{ formatPrice(product.price) }}</p>
                                <div class="flex items-center gap-0.5">
                                    <svg v-for="n in 5" :key="n" class="w-3 h-3" :class="n <= 4 ? 'text-ink-60' : 'text-ink-10'" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="hasMore && products.length" class="text-center mt-12 lg:mt-16">
                        <button @click="loadMore" class="px-10 py-3.5 border border-charcoal/20 text-xs tracking-[0.2em] uppercase text-charcoal/60 hover:bg-charcoal hover:text-paper transition-all active:scale-[0.98]">
                        Muat Lainnya
                    </button>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-20 bg-charcoal text-paper overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-3 gap-8 lg:gap-12">
                    <div class="lg:col-span-1">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-6 h-[1px] bg-paper/30"></span>
                            <span class="text-[11px] tracking-[0.25em] uppercase text-paper/40">Mengapa Aliesmo</span>
                        </div>
                        <h2 class="text-3xl lg:text-4xl font-light leading-tight text-balance">
                            Setiap Detail<br>
                            <span class="font-medium">Diperhitungkan</span>
                        </h2>
                    </div>
                    <div class="lg:col-span-2 grid sm:grid-cols-2 gap-6 lg:gap-8">
                        <div class="border-t border-paper/10 pt-6">
                            <span class="text-3xl font-light text-paper/50">01</span>
                            <h3 class="mt-4 text-base font-medium text-paper">Katun Premium</h3>
                            <p class="mt-2 text-sm text-paper/50 leading-relaxed">Kain katun pilihan dengan thread count tinggi, memberikan kenyamanan maksimal sepanjang hari.</p>
                        </div>
                        <div class="border-t border-paper/10 pt-6">
                            <span class="text-3xl font-light text-paper/50">02</span>
                            <h3 class="mt-4 text-base font-medium text-paper">Jahitan Presisi</h3>
                            <p class="mt-2 text-sm text-paper/50 leading-relaxed">Dijahit oleh pengrajin berpengalaman dengan teknik fused collar untuk bentuk yang tetap sempurna.</p>
                        </div>
                        <div class="border-t border-paper/10 pt-6">
                            <span class="text-3xl font-light text-paper/50">03</span>
                            <h3 class="mt-4 text-base font-medium text-paper">Desain Klasik</h3>
                            <p class="mt-2 text-sm text-paper/50 leading-relaxed">Siluet timeless yang tetap relevan di setiap musim. Dari formal hingga casual, semua tersedia.</p>
                        </div>
                        <div class="border-t border-paper/10 pt-6">
                            <span class="text-3xl font-light text-paper/50">04</span>
                            <h3 class="mt-4 text-base font-medium text-paper">Garansi Kualitas</h3>
                            <p class="mt-2 text-sm text-paper/50 leading-relaxed">Setiap kemeja melewati quality control ketat. Tidak puas? Garansi 30 hari uang kembali.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <div class="relative order-2 lg:order-1">
                        <div class="aspect-[4/5] overflow-hidden">
                            <img src="https://picsum.photos/seed/kemeja-pengrajin/700/875" alt="Aliesmo Craftsmanship" class="w-full h-full object-cover" />
                        </div>
                        <div class="absolute -bottom-4 -left-4 lg:-bottom-6 lg:-left-6 bg-paper p-4 lg:p-6 shadow-xl max-w-[200px]">
                            <p class="text-xs tracking-[0.2em] uppercase text-charcoal/50">Pengrajin</p>
                            <p class="text-lg font-medium text-charcoal">Berpengalaman</p>
                            <p class="text-sm text-charcoal/60 mt-1">+10 tahun</p>
                        </div>
                    </div>
                    <div class="order-1 lg:order-2">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-6 h-[1px] bg-ink-40"></span>
                            <span class="text-[11px] tracking-[0.25em] uppercase text-ink-40">Cerita Kami</span>
                        </div>
                        <h2 class="text-3xl lg:text-4xl font-light text-charcoal">Kerajinan dalam <span class="font-medium">Setiap Detail</span></h2>
                        <div class="mt-6 space-y-4 text-base text-charcoal/55 leading-relaxed max-w-lg">
                            <p>Aliesmo lahir dari hasrat untuk menciptakan kemeja premium yang tidak hanya terlihat sempurna, tetapi juga terasa luar biasa. Setiap potongan kain dipilih dengan cermat, setiap jahitan dikerjakan oleh pengrajin berpengalaman.</p>
                            <p>Kami percaya bahwa kemeja yang baik adalah investasi — bukan sekadar pakaian. Dengan bahan katun premium dan konstruksi yang teliti, setiap kemeja Aliesmo dirancang untuk menemani perjalanan Anda selama bertahun-tahun.</p>
                        </div>
                        <router-link to="/?shop=1" class="inline-flex items-center gap-2 mt-6 text-xs tracking-[0.2em] uppercase text-ink-60 hover:text-charcoal transition-colors group">
                            Jelajahi Koleksi
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </router-link>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-20 bg-ink-05">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-xl mx-auto text-center">
                    <div class="flex items-center justify-center gap-3 mb-3">
                        <span class="w-6 h-[1px] bg-ink-40"></span>
                        <span class="text-[11px] tracking-[0.25em] uppercase text-ink-40">Newsletter</span>
                        <span class="w-6 h-[1px] bg-ink-40"></span>
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-light text-charcoal text-balance">Dapatkan <span class="font-medium">Info Terbaru</span></h2>
                    <p class="mt-4 text-sm text-charcoal/55">Daftar untuk mendapatkan notifikasi koleksi baru, promo eksklusif, dan inspirasi gaya.</p>
                    <form @submit.prevent="handleSubscribe" class="mt-8 flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                        <input v-model="email" type="email" required placeholder="Masukkan email Anda" class="flex-1 border border-ink-10 px-4 py-3 text-sm text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/50 focus:outline-none transition-colors bg-white">
                        <button type="submit" class="px-8 py-3 bg-charcoal text-paper text-xs tracking-[0.2em] uppercase hover:bg-charcoal/90 transition-all active:scale-[0.98] whitespace-nowrap">
                            Daftar
                        </button>
                    </form>
                    <p v-if="subscribed" class="mt-4 text-sm text-green-700">Terima kasih! Anda telah terdaftar.</p>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import { useCartStore } from '../cart'

const route = useRoute()
const { addItem } = useCartStore()
const products = ref([])
const categories = ref([])
const loading = ref(false)
const page = ref(1)
const hasMore = ref(true)
const filters = reactive({ category: '', search: '', sort: 'newest' })
const email = ref('')
const subscribed = ref(false)

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price)
}

function productImage(product, index) {
    if (product.images && product.images[index]) return product.images[index].path
    if (index === 0 && product.thumbnail) return product.thumbnail
    const seed = product.id || product.slug || Math.random().toString(36)
    return `https://picsum.photos/seed/${seed}${index > 0 ? '-' + (index + 1) : ''}/600/800`
}

async function fetchProducts() {
    loading.value = true
    page.value = 1
    try {
        const params = { page: page.value, ...filters }
        const res = await api.get('/products', { params })
        products.value = res.data.data
        hasMore.value = res.data.links?.next !== null
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

async function loadMore() {
    page.value++
    try {
        const params = { page: page.value, ...filters }
        const res = await api.get('/products', { params })
        products.value.push(...res.data.data)
        hasMore.value = res.data.links?.next !== null
    } catch (e) {
        console.error(e)
    }
}

function prevPage() {
    if (page.value > 1) {
        page.value--
        fetchProducts()
    }
}

function nextPage() {
    if (hasMore.value) {
        page.value++
        fetchProducts()
    }
}

function addToCart(product) {
    addItem(product, 1)
}

function quickView(product) {
    // placeholder for quick view modal
}

function selectCategory(slug) {
    filters.category = slug
    fetchProducts()
    document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' })
}

function handleSubscribe() {
    if (email.value) {
        subscribed.value = true
        email.value = ''
        setTimeout(() => { subscribed.value = false }, 5000)
    }
}

onMounted(async () => {
    try {
        const catRes = await api.get('/categories')
        categories.value = catRes.data.data
    } catch (e) {
        console.error(e)
    }
    fetchProducts()
    if (route.query.shop) {
        setTimeout(() => {
            document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' })
        }, 150)
    }
})
</script>
