<template>
    <div>

        <!-- ===================== 1. HERO BANNER ===================== -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8">
            <div class="relative rounded-2xl overflow-hidden shadow-lg">
                <div class="relative flex transition-all duration-500" :style="{ transform: `translateX(-${activeSlide * 100}%)` }">
                    <div v-for="(banner, i) in banners" :key="banner.id"
                        class="w-full shrink-0 aspect-[2/1] sm:aspect-[3/1]">
                        <div v-if="banner.youtube_url" class="w-full h-full">
                            <iframe
                                :ref="el => { if (el) bannerIframes[i] = el }"
                                :src="getYoutubeEmbedUrl(banner.youtube_url) + '?enablejsapi=1'"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>
                        <img v-else :src="banner.image_url" :alt="banner.title" class="w-full h-full object-cover block" />
                    </div>
                </div>
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2.5 z-20">
                    <button v-for="(_, i) in banners" :key="i" @click="goToSlide(i)" class="h-2 rounded-full transition-all" :class="activeSlide === i ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/70'"></button>
                </div>
                <button @click="prevSlide" class="absolute top-1/2 -translate-y-1/2 left-3 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow hover:bg-white dark:bg-[#1c1c1e] dark:hover:bg-[#242426] transition-all">
                    <ChevronLeftIcon class="w-4 h-4" />
                </button>
                <button @click="nextSlide" class="absolute top-1/2 -translate-y-1/2 right-3 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow hover:bg-white dark:bg-[#1c1c1e] dark:hover:bg-[#242426] transition-all">
                    <ChevronRightIcon class="w-4 h-4" />
                </button>
            </div>
        </section>

        <!-- ===================== 2. TENTANG ALIESMO ===================== -->
        <section class="py-14 lg:py-20 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-14 items-center">
                    <div class="relative mb-8 lg:mb-0">
                        <div class="aspect-[4/5] rounded-2xl overflow-hidden bg-zinc-100 dark:bg-[#1c1c1e]">
                            <img
                                v-if="get('about_image')"
                                :src="get('about_image')"
                                alt="Tentang Aliesmo"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-zinc-300 dark:text-[#303032]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </div>
                        <div class="absolute -bottom-3 -right-3 lg:-bottom-5 lg:-right-5 bg-white dark:bg-[#161618] rounded-2xl shadow-xl border border-zinc-100 dark:border-[#303032] px-5 py-4 lg:px-7 lg:py-5">
                            <p class="text-2xl lg:text-3xl font-bold text-maroon dark:text-[#f0eeeb]">{{ get('about_year', '2021') }}</p>
                            <p class="text-xs text-charcoal/50 dark:text-[#8a8a8e] font-medium">Berdiri sejak</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-3">Tentang Kami</p>
                        <h2 class="text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight leading-tight">{{ get('about_title', 'ALIESMO — Kemeja Premium untuk Tampil Lebih Berkelas') }}</h2>
                        <div class="mt-5 space-y-3 text-sm text-charcoal/70 dark:text-[#8a8a8e] leading-relaxed">
                            <p>{{ get('about_description', 'ALIESMO adalah brand kemeja pria asli Indonesia yang mengedepankan kualitas bahan premium, jahitan presisi, dan desain klasik yang timeless. Setiap produk kami buat dengan penuh perhatian pada detail, menggunakan bahan-bahan terbaik seperti Oxford, Linen, dan Katun Premium.') }}</p>
                        </div>
                        <router-link to="/catalog" class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-maroon dark:text-[#f0eeeb] hover:gap-3 transition-all">
                            Lihat Koleksi
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </router-link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== 3. KEUNGGULAN BELANJA ===================== -->
        <section class="py-14 lg:py-20 bg-coklat-50/20 dark:bg-[#1c1c1e]/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-2">Mengapa Aliesmo</p>
                    <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Keunggulan Belanja di Kami</h2>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-6">
                    <div v-for="(item, i) in advantages" :key="i" class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-zinc-100 dark:border-[#303032] p-6 lg:p-7 text-center hover:shadow-md hover:border-maroon-200 dark:hover:border-[#f0eeeb]/20 transition-all">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-maroon/10 dark:bg-[#f0eeeb]/10 flex items-center justify-center" v-html="item.icon"></div>
                        <h3 class="mt-4 text-sm font-bold text-charcoal dark:text-[#f0eeeb]">{{ item.title }}</h3>
                        <p class="mt-1.5 text-xs text-charcoal/60 dark:text-[#8a8a8e] leading-relaxed">{{ item.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== 4. KATEGORI KEMEJA ===================== -->
        <section class="py-14 lg:py-20 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-1">Kategori</p>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Kategori <span class="text-maroon dark:text-[#f0eeeb]">Kemeja</span></h2>
                    </div>
                    <router-link to="/catalog" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-maroon dark:text-[#f0eeeb] hover:gap-2.5 transition-all">
                        Lihat Semua
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </router-link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-5">
                    <div v-for="cat in categoriesList" :key="cat.id" @click="$router.push(`/catalog/${cat.slug}`)" class="group cursor-pointer">
                        <div class="aspect-[21/9] bg-zinc-800 rounded-xl overflow-hidden relative">
                            <img v-if="cat.image_url" :src="cat.image_url" :alt="cat.name" class="w-full h-full object-cover group-hover:scale-[1.06] transition-transform duration-500" />
                            <div v-else class="w-full h-full bg-zinc-800 group-hover:scale-[1.06] transition-transform duration-500"></div>
                            <div class="absolute inset-0 bg-charcoal/30 group-hover:bg-charcoal dark:hover:bg-[#d0ceca]/40 transition-colors flex items-center justify-center p-3">
                                <p class="text-xs sm:text-sm font-extrabold text-white tracking-[0.12em] uppercase drop-shadow-[0_2px_4px_rgba(0,0,0,0.4)] group-hover:scale-105 transition-transform duration-300 text-center">{{ cat.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 text-center sm:hidden">
                    <router-link to="/catalog" class="inline-flex items-center gap-1.5 text-xs font-semibold text-maroon dark:text-[#f0eeeb]">
                        Lihat Semua Kategori
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- ===================== 5. BEST SELLER ===================== -->
        <section v-if="bestSellers.length" class="py-14 lg:py-20 bg-coklat-50/20 dark:bg-[#1c1c1e]/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-1">Best Seller</p>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Paling <span class="text-maroon dark:text-[#f0eeeb]">Laris</span></h2>
                    </div>
                    <router-link to="/catalog?sort=best_seller" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-maroon dark:text-[#f0eeeb] hover:gap-2.5 transition-all">
                        Lihat Semua
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </router-link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
                    <div v-for="product in bestSellers" :key="product.id" class="group/card cursor-pointer bg-white dark:bg-[#1c1c1e] rounded-xl overflow-hidden border border-maroon-50 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-[#404042] transition-all hover:shadow-md active:scale-[0.98]" @click="$router.push(`/products/${product.slug}`)">
                        <div class="aspect-square bg-maroon-50 overflow-hidden relative">
                            <img :src="productImage(product, 0)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover" />
                            <div class="absolute top-1.5 left-1.5 bg-amber-500 text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">#1 Best Seller</div>
                            <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-8 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ product.stock }}</div>
                            <div v-if="product.stock === 0" class="absolute inset-0 bg-white/80 dark:bg-[#1c1c1e]/80 flex items-center justify-center">
                                <span class="bg-charcoal dark:bg-[#303032] text-white text-[10px] font-semibold px-2 py-1 rounded-lg">Stok Habis</span>
                            </div>
                            <button @click.stop="addToCart(product)" :disabled="product.stock === 0" class="absolute bottom-0 left-0 right-0 py-2.5 bg-maroon text-white text-[10px] font-semibold tracking-wide translate-y-full group-hover/card:translate-y-0 transition-transform duration-300 hover:bg-maroon-600 dark:hover:bg-maroon/80 disabled:opacity-0">
                                {{ product.stock === 0 ? 'Stok Habis' : '+ Masuk Keranjang' }}
                            </button>
                        </div>
                        <div class="p-2.5">
                            <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ product.categories?.map(c => c.name).join(', ') || '' }}</p>
                            <h3 class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb] mt-0.5 leading-snug line-clamp-2">{{ product.name }}</h3>
                            <div class="flex items-center justify-between mt-1.5">
                                <p class="text-sm font-bold text-maroon dark:text-[#f0eeeb]">
                                    <span class="text-[10px] font-normal text-charcoal/40 dark:text-[#6a6a6e] mr-0.5" v-if="product.variants?.filter(v => v.is_active).length">mulai </span>Rp{{ formatPrice(lowestPrice(product)) }}
                                </p>
                                <span v-if="product.avg_rating" class="flex items-center gap-0.5 text-[10px] font-semibold text-amber-500">★ {{ product.avg_rating }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 text-center sm:hidden">
                    <router-link to="/catalog?sort=best_seller" class="inline-flex items-center gap-1.5 text-xs font-semibold text-maroon dark:text-[#f0eeeb]">
                        Lihat Semua Best Seller
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- ===================== 6. NEW ARRIVAL ===================== -->
        <section v-if="newArrivals.length" class="py-14 lg:py-20 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-1">New Arrival</p>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Produk <span class="text-maroon dark:text-[#f0eeeb]">Terbaru</span></h2>
                        <p class="mt-1 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Koleksi terbaru yang baru aja tiba</p>
                    </div>
                    <router-link to="/catalog?sort=newest" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-maroon dark:text-[#f0eeeb] hover:gap-2.5 transition-all">
                        Lihat Semua
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </router-link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
                    <div v-for="product in newArrivals" :key="product.id" class="group/card cursor-pointer bg-white dark:bg-[#1c1c1e] rounded-xl overflow-hidden border border-maroon-50 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-[#404042] transition-all hover:shadow-md active:scale-[0.98]" @click="$router.push(`/products/${product.slug}`)">
                        <div class="aspect-square bg-maroon-50 overflow-hidden relative">
                            <img :src="productImage(product, 0)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover" />
                            <div class="absolute top-1.5 left-1.5 bg-emerald-500 text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Baru</div>
                            <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-8 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ product.stock }}</div>
                            <div v-if="product.stock === 0" class="absolute inset-0 bg-white/80 dark:bg-[#1c1c1e]/80 flex items-center justify-center">
                                <span class="bg-charcoal dark:bg-[#303032] text-white text-[10px] font-semibold px-2 py-1 rounded-lg">Stok Habis</span>
                            </div>
                            <button @click.stop="addToCart(product)" :disabled="product.stock === 0" class="absolute bottom-0 left-0 right-0 py-2.5 bg-maroon text-white text-[10px] font-semibold tracking-wide translate-y-full group-hover/card:translate-y-0 transition-transform duration-300 hover:bg-maroon-600 dark:hover:bg-maroon/80 disabled:opacity-0">
                                {{ product.stock === 0 ? 'Stok Habis' : '+ Masuk Keranjang' }}
                            </button>
                        </div>
                        <div class="p-2.5">
                            <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ product.categories?.map(c => c.name).join(', ') || '' }}</p>
                            <h3 class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb] mt-0.5 leading-snug line-clamp-2">{{ product.name }}</h3>
                            <div class="flex items-center justify-between mt-1.5">
                                <p class="text-sm font-bold text-maroon dark:text-[#f0eeeb]">
                                    <span class="text-[10px] font-normal text-charcoal/40 dark:text-[#6a6a6e] mr-0.5" v-if="product.variants?.filter(v => v.is_active).length">mulai </span>Rp{{ formatPrice(lowestPrice(product)) }}
                                </p>
                                <span v-if="product.avg_rating" class="flex items-center gap-0.5 text-[10px] font-semibold text-amber-500">★ {{ product.avg_rating }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 text-center sm:hidden">
                    <router-link to="/catalog?sort=newest" class="inline-flex items-center gap-1.5 text-xs font-semibold text-maroon dark:text-[#f0eeeb]">
                        Lihat Semua Produk Baru
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- ===================== 7. SEMUA PRODUK ===================== -->
        <section id="shop" class="py-14 lg:py-20 bg-coklat-50/20 dark:bg-[#1c1c1e]/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Semua <span class="text-maroon dark:text-[#f0eeeb]">Produk</span></h2>
                        <p class="mt-1 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Temukan kemeja favoritmu</p>
                    </div>
                </div>

                <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
                    <div v-for="n in 8" :key="n">
                        <div class="aspect-[3/4] w-full rounded-xl overflow-hidden">
                            <SkeletonLoader :loading="true" :radius="0" height="100%" width="100%" />
                        </div>
                        <div class="mt-2 space-y-1.5 px-1">
                            <SkeletonLoader :loading="true" :radius="99" height="8px" width="33%" />
                            <SkeletonLoader :loading="true" :radius="99" height="10px" width="66%" />
                            <SkeletonLoader :loading="true" :radius="99" height="10px" width="25%" />
                        </div>
                    </div>
                </div>

                <div v-else-if="!filteredProducts.length && !loading" class="text-center py-16">
                    <p class="text-lg text-charcoal/50 dark:text-[#8a8a8e]">Belum ada produk nih.</p>
                </div>

                <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
                    <div v-for="product in filteredProducts" :key="product.id" class="group/card cursor-pointer bg-white dark:bg-[#1c1c1e] rounded-xl overflow-hidden border border-maroon-50 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-[#404042] transition-all hover:shadow-md active:scale-[0.98]" @click="$router.push(`/products/${product.slug}`)">
                        <div class="aspect-square bg-maroon-50 overflow-hidden relative">
                            <img :src="productImage(product, 0)" :alt="product.name" class="absolute inset-0 w-full h-full object-cover" />
                            <div v-if="product.stock > 0 && product.stock <= 5" class="absolute top-1.5 left-1.5 bg-coklat text-white text-[9px] font-semibold px-1.5 py-0.5 rounded-md">Sisa {{ product.stock }}</div>
                            <div v-if="product.stock <= 3" class="absolute top-1.5 right-1.5 bg-ink dark:bg-[#f0eeeb] text-white dark:text-[#161618] text-[9px] font-semibold px-1.5 py-0.5 rounded-md animate-pulse">HOTS!</div>
                            <div v-if="product.stock === 0" class="absolute inset-0 bg-white/80 dark:bg-[#1c1c1e]/80 flex items-center justify-center">
                                <span class="bg-charcoal dark:bg-[#303032] text-white text-[10px] font-semibold px-2 py-1 rounded-lg">Stok Habis</span>
                            </div>
                            <button @click.stop="addToCart(product)" :disabled="product.stock === 0" class="absolute bottom-0 left-0 right-0 py-2.5 bg-maroon text-white text-[10px] font-semibold tracking-wide translate-y-full group-hover/card:translate-y-0 transition-transform duration-300 hover:bg-maroon-600 dark:hover:bg-maroon/80 disabled:opacity-0">
                                {{ product.stock === 0 ? 'Stok Habis' : '+ Masuk Keranjang' }}
                            </button>
                        </div>
                        <div class="p-2.5">
                            <p class="text-[10px] font-medium text-maroon-400 uppercase tracking-wide">{{ product.categories?.map(c => c.name).join(', ') || '' }}</p>
                            <h3 class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb] mt-0.5 leading-snug line-clamp-2">{{ product.name }}</h3>
                            <div class="flex items-center justify-between mt-1.5">
                                <p class="text-sm font-bold text-maroon dark:text-[#f0eeeb]">
                                    <span class="text-[10px] font-normal text-charcoal/40 dark:text-[#6a6a6e] mr-0.5" v-if="product.variants?.filter(v => v.is_active).length">mulai </span>Rp{{ formatPrice(lowestPrice(product)) }}
                                </p>
                                <span v-if="product.avg_rating" class="flex items-center gap-0.5 text-[10px] font-semibold text-amber-500">★ {{ product.avg_rating }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div ref="scrollSentinel" class="h-10 mt-6 flex items-center justify-center">
                    <span v-if="loadingMore" class="w-5 h-5 border-2 border-maroon/30 border-t-maroon rounded-full animate-spin"></span>
                </div>
            </div>
        </section>

        <!-- ===================== 8. IKUTI KAMI DI IG, TIKTOK, SHOPEE ===================== -->
        <section class="py-14 lg:py-20 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-2">Ikuti Kami</p>
                    <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Dapatkan Info Terbaru di</h2>
                </div>
                <div class="flex flex-wrap justify-center gap-4 lg:gap-6">
                    <a v-if="get('social_instagram')?.trim()" :href="get('social_instagram')" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 px-6 py-4 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 rounded-2xl text-white hover:shadow-xl hover:scale-[1.03] transition-all min-w-[180px]">
                        <svg class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        <span class="text-sm font-semibold">Instagram</span>
                        <svg class="w-4 h-4 ml-auto opacity-60 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a v-if="get('social_tiktok')?.trim()" :href="get('social_tiktok')" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 px-6 py-4 bg-[#161618] rounded-2xl text-white hover:shadow-xl hover:scale-[1.03] transition-all border border-zinc-700 min-w-[180px]">
                        <svg class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        <span class="text-sm font-semibold">TikTok</span>
                        <svg class="w-4 h-4 ml-auto opacity-60 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <a v-if="get('social_shopee')?.trim()" :href="get('social_shopee')" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 px-6 py-4 bg-[#ee4d2d] rounded-2xl text-white hover:shadow-xl hover:scale-[1.03] transition-all min-w-[180px]">
                        <svg class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M14.357 2.093c-.584-.292-1.256-.343-1.883-.144-.215.068-.432.152-.65.252-.584.266-1.06.707-1.37 1.264-.215.388-.343.82-.39 1.264-.017.17-.017.34-.017.51v.595c-.584-.465-1.336-.75-2.155-.75C4.71 3.53 2.827 5.413 2.827 7.737c0 2.324 1.883 4.207 4.155 4.207.82 0 1.572-.285 2.155-.75v.595c0 .17 0 .34.017.51.047.444.175.876.39 1.264.31.557.786.998 1.37 1.264.218.1.435.184.65.252.627.199 1.299.148 1.883-.144.584-.292 1.06-.75 1.37-1.308.215-.388.343-.82.39-1.264.017-.17.017-.34.017-.51V7.58c0-.17 0-.34-.017-.51-.047-.444-.175-.876-.39-1.264-.31-.558-.786-1.016-1.37-1.308v-.001zM10.065 10.9c-.82 0-1.48-.66-1.48-1.48 0-.82.66-1.48 1.48-1.48.82 0 1.48.66 1.48 1.48 0 .82-.66 1.48-1.48 1.48zm5.817 3.17c-.17.34-.44.61-.78.78-.34.17-.71.23-1.08.2h-.12v-2.16h1.98v.03c.04.37-.02.74-.19 1.08l.21.07zM15.882 7.58v1.79c-.04.37-.17.71-.39 1.01-.31.42-.76.73-1.28.88-.34.1-.7.15-1.06.15h-.09V9.55h.84c.38 0 .74-.12 1.05-.35.31-.23.53-.55.62-.92.03-.13.05-.27.05-.41V7.58h.26z"/><path d="M21.173 7.737c0-2.324-1.883-4.207-4.155-4.207-.82 0-1.572.285-2.155.75V3.69c0-.17 0-.34-.017-.51-.047-.444-.175-.876-.39-1.264-.31-.557-.786-.998-1.37-1.264-.218-.1-.435-.184-.65-.252-.627-.199-1.299-.148-1.883.144-.584.292-1.06.75-1.37 1.308-.215.388-.343.82-.39 1.264-.017.17-.017.34-.017.51v.595c-.584-.465-1.336-.75-2.155-.75-2.272 0-4.155 1.883-4.155 4.207 0 2.324 1.883 4.207 4.155 4.207.82 0 1.572-.285 2.155-.75v.595c0 .17 0 .34.017.51.047.444.175.876.39 1.264.31.557.786.998 1.37 1.264.218.1.435.184.65.252.627.199 1.299.148 1.883-.144.584-.292 1.06-.75 1.37-1.308.215-.388.343-.82.39-1.264.017-.17.017-.34.017-.51v-.595c.584.465 1.336.75 2.155.75 2.272 0 4.155-1.883 4.155-4.207z" opacity=".5"/></svg>
                        <span class="text-sm font-semibold">Shopee</span>
                        <svg class="w-4 h-4 ml-auto opacity-60 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ===================== 9. BLOG ===================== -->
        <section v-if="articles.length" class="py-14 lg:py-20 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-1">Blog & Inspirasi</p>
                        <h2 class="text-2xl lg:text-3xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Artikel <span class="text-maroon dark:text-[#f0eeeb]">Terbaru</span></h2>
                    </div>
                    <router-link to="/blog" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-maroon dark:text-[#f0eeeb] hover:gap-2.5 transition-all">
                        Lihat Semua
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </router-link>
                </div>
                <div v-if="!articles.length" class="text-center py-12">
                    <p class="text-sm text-charcoal/40 dark:text-[#6a6a6e]">Belum ada artikel.</p>
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <router-link
                        v-for="article in articles.slice(0, 3)"
                        :key="article.id"
                        :to="`/blog/${article.slug}`"
                        class="group flex flex-col rounded-2xl overflow-hidden border border-maroon-50 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-[#f0eeeb]/20 transition-all hover:shadow-md bg-white dark:bg-[#1c1c1e]"
                    >
                        <div class="aspect-video bg-maroon-50 dark:bg-[#28282a] overflow-hidden relative">
                            <img
                                v-if="article.thumbnail"
                                :src="article.thumbnail"
                                :alt="article.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-maroon-200 dark:text-[#303032]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            </div>
                        </div>
                        <div class="p-4 flex flex-col flex-1">
                            <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-1.5">
                                {{ formatDate(article.published_at) }}
                            </p>
                            <h3 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] leading-snug line-clamp-2 group-hover:text-maroon dark:group-hover:text-[#f0eeeb] transition-colors">
                                {{ article.title }}
                            </h3>
                            <p v-if="article.excerpt" class="mt-2 text-xs text-charcoal/60 dark:text-[#8a8a8e] leading-relaxed line-clamp-2 flex-1">
                                {{ article.excerpt }}
                            </p>
                            <span class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-maroon dark:text-[#f0eeeb]">
                                Baca
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </router-link>
                </div>
                <div class="mt-8 text-center sm:hidden">
                    <router-link to="/blog" class="inline-flex items-center gap-1.5 text-xs font-semibold text-maroon dark:text-[#f0eeeb]">
                        Lihat Semua Artikel
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- ===================== VIDEO SECTION ===================== -->
        <section v-if="videos.length" class="py-16 lg:py-24 bg-charcoal relative overflow-hidden">
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-0 left-0 w-96 h-96 bg-maroon rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-coklat rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-12">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-3">{{ get('homepage_video_title', 'Lihat Koleksi Kami Beraksi') }}</h2>
                    <p v-if="get('homepage_video_subtitle')" class="text-white/60 max-w-2xl mx-auto">{{ get('homepage_video_subtitle') }}</p>
                    <p v-else class="text-white/60 max-w-2xl mx-auto">Dari detail jahitan premium hingga styling inspiratif — saksikan langsung kualitas yang bikin kamu tampil beda.</p>
                </div>

                <div :class="[
                    'grid gap-6',
                    videos.length === 1 ? 'grid-cols-1 max-w-3xl mx-auto' :
                    videos.length === 2 ? 'grid-cols-1 sm:grid-cols-2' :
                    'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3'
                ]">
                    <div v-for="video in videos" :key="video.id" class="group">
                        <div class="aspect-video rounded-2xl overflow-hidden shadow-2xl bg-black ring-1 ring-white/10 group-hover:ring-maroon/50 transition-all">
                            <iframe
                                :src="getYoutubeEmbedUrl(video.youtube_url)"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>
                        <div v-if="video.title || video.description" class="mt-4 px-1">
                            <p v-if="video.title" class="text-base font-semibold text-white">{{ video.title }}</p>
                            <p v-if="video.description" class="text-sm text-white/50 mt-1 line-clamp-2">{{ video.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import { useRoute, useRouter } from 'vue-router'
import { watch } from 'vue'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'
import api from '../api'
import { useSettings } from '../useSettings'

function lowestPrice(product) {
    const active = product.variants?.filter(v => v.is_active) || []
    if (!active.length) return product.price
    const prices = active.map(v => v.price).filter(p => p > 0)
    return prices.length ? Math.min(...prices) : product.price
}

function formatDate(dateString) {
    if (!dateString) return ''
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(dateString))
}

const { fetchSettings, get } = useSettings()

const route = useRoute()
const router = useRouter()
const { addItem } = useCartStore()

const advantageIcons = [
    '<svg class="w-6 h-6 text-maroon dark:text-[#f0eeeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>',
    '<svg class="w-6 h-6 text-maroon dark:text-[#f0eeeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>',
    '<svg class="w-6 h-6 text-maroon dark:text-[#f0eeeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 01-2.77.896m0 0a6.023 6.023 0 01-2.77-.896"/></svg>',
    '<svg class="w-6 h-6 text-maroon dark:text-[#f0eeeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>',
]

const advantages = computed(() => [
    {
        icon: advantageIcons[0],
        title: get('advantage_1_title', 'Kualitas Premium'),
        desc: get('advantage_1_desc', 'Bahan oxford & linen premium, jahitan presisi, nyaman dipakai seharian.')
    },
    {
        icon: advantageIcons[1],
        title: get('advantage_2_title', 'Pengiriman Cepat'),
        desc: get('advantage_2_desc', 'Dikemas rapi dan dikirim dari Pemalang, sampai ke seluruh Indonesia.')
    },
    {
        icon: advantageIcons[2],
        title: get('advantage_3_title', 'Garansi 30 Hari'),
        desc: get('advantage_3_desc', 'Tenang belanja dengan garansi 30 hari. Kami pastikan kamu puas.')
    },
    {
        icon: advantageIcons[3],
        title: get('advantage_4_title', 'Layanan Pelanggan'),
        desc: get('advantage_4_desc', 'CS ramah siap bantu kamu dari pilih ukuran sampai pesanan sampai.')
    }
])

const banners = ref([])
const categoriesList = ref([])
const products = ref([])
const videos = ref([])
const bestSellers = ref([])
const newArrivals = ref([])
const articles = ref([])
const loading = ref(false)
const selectedCategory = ref('')
const searchTerm = ref('')
const activeSlide = ref(0)
let slideTimer = null
const bannerIframes = ref({})
let ytPlayers = []
let isVideoPlaying = false

const loadingMore = ref(false)
const hasMorePages = ref(false)
const currentPage = ref(1)
const scrollSentinel = ref(null)
let observer = null

const filteredProducts = computed(() => {
    if (!products.value.length) return []
    let result = products.value
    if (selectedCategory.value) {
        result = result.filter(p => p.categories?.some(c => c.slug === selectedCategory.value))
    }
    if (searchTerm.value) {
        const q = searchTerm.value.toLowerCase()
        result = result.filter(p =>
            p.name?.toLowerCase().includes(q) ||
            p.description?.toLowerCase().includes(q) ||
            p.categories?.some(c => c.name?.toLowerCase().includes(q))
        )
    }
    return result
})

function productImage(product, index) {
    if (index === 0) return product.thumbnail || ''
    const imgIndex = index - 1
    if (product.images && product.images[imgIndex]) return product.images[imgIndex].path
    return product.thumbnail || ''
}

function pauseCurrentVideo() {
    if (ytPlayers[activeSlide.value]) {
        try { ytPlayers[activeSlide.value].pauseVideo() } catch (e) {}
    }
}

function prevSlide() {
    pauseCurrentVideo()
    activeSlide.value = activeSlide.value === 0 ? Math.max(banners.value.length - 1, 0) : activeSlide.value - 1
}
function nextSlide() {
    pauseCurrentVideo()
    activeSlide.value = activeSlide.value >= banners.value.length - 1 ? 0 : activeSlide.value + 1
}
function goToSlide(i) {
    pauseCurrentVideo()
    activeSlide.value = i
}
function addToCart(product) { addItem(product, 1) }

function getYoutubeEmbedUrl(url) {
    if (!url) return ''
    if (url.includes('youtube.com/embed/')) return url
    const shortMatch = url.match(/youtu\.be\/([^?&]+)/)
    if (shortMatch) return `https://www.youtube.com/embed/${shortMatch[1]}`
    const longMatch = url.match(/[?&]v=([^?&]+)/)
    if (longMatch) return `https://www.youtube.com/embed/${longMatch[1]}`
    return url
}

function selectCategory(slug) {
    selectedCategory.value = slug
    document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' })
}

function scrollToShop() {
    document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' })
}

onMounted(() => {
    fetchData()
    slideTimer = setInterval(() => {
        if (!isVideoPlaying) {
            activeSlide.value = activeSlide.value >= banners.value.length - 1 ? 0 : activeSlide.value + 1
        }
    }, 5000)
    if (route.query.shop) {
        setTimeout(() => document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' }), 150)
    }
    if (route.query.category) selectedCategory.value = route.query.category
    if (route.query.search) searchTerm.value = route.query.search

    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && hasMorePages.value && !loadingMore.value) {
            loadMore()
        }
    }, { threshold: 0.1 })

    setTimeout(() => {
        if (scrollSentinel.value) observer.observe(scrollSentinel.value)
    }, 500)

    if (!window.YT) {
        const tag = document.createElement('script')
        tag.src = 'https://www.youtube.com/iframe_api'
        document.head.appendChild(tag)
    }

    const initYTPlayers = () => {
        if (!window.YT || !window.YT.Player) { setTimeout(initYTPlayers, 200); return }
        banners.value.forEach((banner, i) => {
            if (banner.youtube_url && bannerIframes.value[i]) {
                ytPlayers[i] = new window.YT.Player(bannerIframes.value[i], {
                    events: {
                        onStateChange: (event) => {
                            isVideoPlaying = event.data === window.YT.PlayerState.PLAYING
                        }
                    }
                })
            }
        })
    }
    setTimeout(initYTPlayers, 1000)
})

watch(() => route.query.category, (val) => {
    selectedCategory.value = val || ''
})
watch(() => route.query.search, (val) => {
    searchTerm.value = val || ''
    if (val) {
        setTimeout(() => document.getElementById('shop')?.scrollIntoView({ behavior: 'smooth' }), 150)
    }
})

async function fetchData() {
    loading.value = true
    currentPage.value = 1
    try {
        const [bannersRes, settingsRes, categoriesRes, productsRes, videosRes, bestSellersRes, newArrivalsRes, testimonialsRes, articlesRes] = await Promise.all([
            api.get('/banners'),
            fetchSettings(),
            api.get('/categories'),
            api.get('/products', { params: { per_page: 12, page: 1 } }),
            api.get('/homepage-videos'),
            api.get('/products', { params: { per_page: 4, sort: 'best_seller' } }).catch(() => ({ data: { data: [] } })),
            api.get('/products', { params: { per_page: 4, sort: 'newest' } }).catch(() => ({ data: { data: [] } })),
            api.get('/articles', { params: { per_page: 3 } }).catch(() => ({ data: { data: [] } })),
        ])
        banners.value = (bannersRes.data.data || bannersRes.data).filter(b => b.is_active !== false)
        categoriesList.value = categoriesRes.data.data || categoriesRes.data
        const meta = productsRes.data.meta || productsRes.data.pagination || null
        products.value = productsRes.data.data || productsRes.data
        hasMorePages.value = meta ? meta.current_page < meta.last_page : false
        videos.value = videosRes.data.data || videosRes.data
        bestSellers.value = (bestSellersRes.data.data || bestSellersRes.data || []).slice(0, 4)
        newArrivals.value = (newArrivalsRes.data.data || newArrivalsRes.data || []).slice(0, 4)
        articles.value = articlesRes.data.data || articlesRes.data || []
    } catch (e) {
        console.error('Failed to fetch data:', e)
        categoriesList.value = []
        products.value = []
        hasMorePages.value = false
    } finally {
        loading.value = false
    }
}

async function loadMore() {
    if (loadingMore.value || !hasMorePages.value) return
    loadingMore.value = true
    try {
        const nextPage = currentPage.value + 1
        const res = await api.get('/products', { params: { per_page: 12, page: nextPage } })
        const newProducts = res.data.data || res.data
        const meta = res.data.meta || res.data.pagination || null
        products.value = [...products.value, ...newProducts]
        currentPage.value = nextPage
        hasMorePages.value = meta ? meta.current_page < meta.last_page : false
    } catch (e) {
        console.error('Failed to load more:', e)
    } finally {
        loadingMore.value = false
    }
}

onUnmounted(() => {
    if (slideTimer) clearInterval(slideTimer)
    if (observer) observer.disconnect()
})
</script>
