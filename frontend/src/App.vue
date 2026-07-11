<template>
    <div class="min-h-screen bg-paper dark:bg-ink transition-colors duration-200">
        <!-- Promo / Announcement Bar -->
        <div class="bg-white dark:bg-[#1c1c1e] border-b border-zinc-200 dark:border-[#303032] text-[10px] font-medium tracking-[0.18em] text-charcoal/60 dark:text-[#8a8a8e] uppercase">
            <div class="max-w-7xl mx-auto px-4 py-2">
                <!-- Desktop View (3 columns) -->
                <div class="hidden lg:grid grid-cols-3 gap-4 text-center">
                    <router-link :to="promos[0]?.link || '/?shop=1'" class="hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors flex items-center justify-center gap-1.5">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        {{ promos[0]?.text || 'Bahan Premium Oxford &amp; Linen | Garansi 30 Hari' }}
                    </router-link>
                    <router-link :to="promos[1]?.link || '/?shop=1'" class="hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors flex items-center justify-center gap-1.5 border-x border-zinc-200 dark:border-[#303032]">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        {{ promos[1]?.text || 'Bahan Premium Oxford &amp; Linen | Garansi 30 Hari' }}
                    </router-link>
                    <router-link :to="promos[2]?.link || '/login'" class="hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors flex items-center justify-center gap-1.5">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                        {{ promos[2]?.text || 'Diskon 10% First Order | Kode: ALIESNEW10' }}
                    </router-link>
                </div>
                <!-- Mobile/Tablet View (Rotating Carousel) -->
                <div class="lg:hidden text-center relative h-4 overflow-hidden flex items-center justify-center">
                    <TransitionGroup name="slide-up">
                        <div v-for="(promo, index) in promos" :key="index" v-show="activePromo === index" class="absolute w-full flex items-center justify-center gap-1.5">
                            <span v-if="index === 0" class="flex items-center">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                            </span>
                            <span v-else-if="index === 1" class="flex items-center">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </span>
                            <span v-else class="flex items-center">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            </span>
                            <router-link :to="promo.link" class="hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors">{{ promo.text }}</router-link>
                        </div>
                    </TransitionGroup>
                </div>
            </div>
        </div>

        <!-- Sticky Header (Main Row + Category Row) -->
        <header class="sticky top-0 z-50 bg-white/95 dark:bg-ink-90/95 backdrop-blur-md border-b border-zinc-200/60 dark:border-ink-80/60 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Middle Row: Logo, Search, Navigation Icons -->
                <div class="flex items-center justify-between h-16 lg:h-[72px]">
                    <!-- Left: Hamburger & Logo -->
                    <div class="flex items-center gap-3">
                        <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 -ml-2 text-charcoal dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" aria-label="Menu">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square">
                                <path d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <router-link to="/" class="flex items-center">
                            <img src="/aliesmo-logo.svg" alt="Aliesmo" class="h-8 sm:h-9 lg:h-10 w-auto" />
                        </router-link>
                    </div>

                    <!-- Middle: Search Input (Desktop) -->
                    <form @submit.prevent="handleSearch" class="relative flex-1 max-w-xl mx-8 hidden lg:block">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari kemeja, batik, atau koleksi..."
                            class="w-full pl-5 pr-14 py-2.5 rounded-full border border-zinc-200 dark:border-[#303032] focus:outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/10 text-sm transition-all bg-zinc-50/20 dark:bg-[#1c1c1e] dark:text-[#f0eeeb] placeholder-zinc-400 dark:placeholder-[#8a8a8e]"
                        />
                        <button
                            type="submit"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-charcoal dark:bg-[#f0eeeb] hover:bg-maroon dark:hover:bg-maroon text-white dark:text-[#161618] flex items-center justify-center transition-colors"
                            aria-label="Cari"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </button>
                    </form>

                    <!-- Right: Auth, Wishlist, Cart -->
                    <div class="flex items-center gap-2 lg:gap-4">
                        <!-- User icon — guest: link to login, logged in: dropdown -->
                        <div class="hidden md:block relative">
                            <!-- Guest -->
                            <router-link v-if="!isLoggedIn" to="/login" class="p-2 flex items-center justify-center text-charcoal/70 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" aria-label="Masuk">
                                <UserIcon class="w-[22px] h-[22px]" />
                            </router-link>
                            <!-- Logged in: icon + dropdown -->
                            <div v-else class="relative" data-user-menu>
                                <button @click="userMenuOpen = !userMenuOpen" class="flex items-center justify-center w-8 h-8 rounded-full bg-maroon hover:bg-maroon/85 dark:hover:bg-maroon/70 transition-colors" :class="userMenuOpen ? 'ring-2 ring-maroon/40 ring-offset-1' : ''" aria-label="Akun saya">
                                    <UserIcon class="w-[18px] h-[18px] text-white" />
                                </button>
                                <!-- Dropdown -->
                                <Transition name="dropdown">
                                    <div v-if="userMenuOpen" class="absolute right-0 top-full mt-2 w-44 bg-white dark:bg-[#1c1c1e] border border-zinc-100 dark:border-[#303032] rounded-xl shadow-xl z-50 overflow-hidden py-1">
                                        <router-link @click="userMenuOpen = false" to="/profile" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-charcoal/80 dark:text-[#f0eeeb]/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#303032] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors">
                                            <UserIconOutline class="w-[15px] h-[15px] shrink-0" />
                                            Akun Saya
                                        </router-link>
                                        <router-link @click="userMenuOpen = false" to="/profile?tab=pesanan" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-charcoal/80 dark:text-[#f0eeeb]/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#303032] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors">
                                            <ShoppingBagIcon class="w-[15px] h-[15px] shrink-0" />
                                            Pesanan Saya
                                        </router-link>
                                        <div class="h-px bg-zinc-100 dark:bg-[#28282a] mx-3 my-1"></div>
                                        <button @click="userMenuOpen = false; handleLogout()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-charcoal/80 dark:text-[#f0eeeb]/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#303032] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors cursor-pointer">
                                            <ArrowRightOnRectangleIcon class="w-[15px] h-[15px] shrink-0" />
                                            Keluar
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button @click="toggle" class="p-2 text-charcoal/70 dark:text-[#8a8a8e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" :aria-label="isDark ? 'Light mode' : 'Dark mode'">
                            <!-- Sun icon (shown in dark mode) -->
                            <svg v-if="isDark" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                            </svg>
                            <!-- Moon icon (shown in light mode) -->
                            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                        </button>

                        <!-- Wishlist Heart -->
                        <button @click="showWishlistToast" class="p-2 text-charcoal/70 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors relative cursor-pointer" aria-label="Wishlist">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                        </button>

                        <!-- Cart Bag -->
                        <router-link to="/cart" class="relative p-2 text-charcoal/70 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" aria-label="Cart">
                            <svg :class="['transition-transform', cartBounce ? 'cart-bounce' : '']" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                            <span v-if="cartCount" class="absolute -top-0.5 -right-0.5 bg-maroon text-white text-[9px] font-bold rounded-full w-4.5 h-4.5 flex items-center justify-center leading-none">{{ cartCount > 9 ? '9+' : cartCount }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Mobile Search Input (Visible only on < lg screens) -->
                <div class="pb-3 lg:hidden">
                    <form @submit.prevent="handleSearch" class="relative w-full">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari kemeja, batik, atau koleksi..."
                            class="w-full pl-5 pr-12 py-2 rounded-full border border-zinc-200 dark:border-[#303032] focus:outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/10 text-xs transition-all bg-zinc-50/20 dark:bg-[#1c1c1e] dark:text-[#f0eeeb] placeholder-zinc-400 dark:placeholder-[#8a8a8e]"
                        />
                        <button
                            type="submit"
                            class="absolute right-1 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-charcoal dark:bg-[#f0eeeb] hover:bg-maroon dark:hover:bg-maroon text-white dark:text-[#161618] flex items-center justify-center transition-colors"
                            aria-label="Cari"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bottom Row: Category Navigation (Desktop only) -->
            <div class="border-t border-zinc-100 dark:border-[#303032]/60 hidden lg:block bg-white dark:bg-[#161618]">
                <div class="max-w-7xl mx-auto px-8">
                    <nav class="flex items-center justify-center gap-8 h-10">
                        <router-link
                            to="/"
                            class="text-xs font-bold tracking-wider text-charcoal/70 dark:text-[#8a8a8e] hover:text-maroon dark:hover:text-[#f0eeeb] hover:border-b-2 hover:border-maroon dark:hover:border-[#f0eeeb] transition-all py-2.5 uppercase"
                            :class="{ 'border-b-2 border-maroon text-maroon font-bold': $route.path === '/' }"
                        >
                            Semua
                        </router-link>
                        <router-link
                            v-for="cat in categories"
                            :key="cat.slug"
                            :to="`/catalog/${cat.slug}`"
                            class="text-xs font-bold tracking-wider text-charcoal/70 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] hover:border-b-2 hover:border-maroon transition-all py-2.5 uppercase"
                            :class="{ 'border-b-2 border-maroon text-maroon font-bold': $route.params.slug === cat.slug }"
                        >
                            {{ cat.name }}
                        </router-link>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Mobile Navigation Drawer -->
        <Transition name="mobile-nav">
            <div v-if="mobileOpen" class="lg:hidden border-t border-zinc-200/50 dark:border-[#303032]/50 bg-white dark:bg-[#161618] shadow-xl fixed top-[110px] left-0 right-0 z-40 max-h-[calc(100vh-110px)] overflow-y-auto">
                <nav class="px-5 py-6 space-y-5">
                    <router-link @click="mobileOpen = false" to="/" class="block text-sm font-bold text-charcoal/80 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] uppercase tracking-wide">Semua Koleksi</router-link>
                    <router-link v-if="isLoggedIn" @click="mobileOpen = false" to="/profile" class="block text-sm font-bold text-charcoal/80 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] uppercase tracking-wide">Akun Saya</router-link>
                    <router-link v-if="isLoggedIn" @click="mobileOpen = false" to="/profile?tab=pesanan" class="block text-sm font-bold text-charcoal/80 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] uppercase tracking-wide">Pesanan Saya</router-link>
                    
                    <div class="h-[1px] bg-zinc-100 dark:bg-[#28282a]"></div>
                    
                    <div>
                        <p class="text-[10px] font-bold text-zinc-400 dark:text-[#6a6a6e] uppercase tracking-widest mb-3 px-1">Kategori</p>
                        <div class="grid grid-cols-2 gap-3">
                            <router-link
                                v-for="cat in categories"
                                :key="cat.slug"
                                @click="mobileOpen = false"
                                :to="`/catalog/${cat.slug}`"
                                class="block text-xs font-semibold text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#8a8a8e] hover:text-maroon dark:hover:text-[#f0eeeb] bg-zinc-50 dark:bg-[#1c1c1e] hover:bg-maroon-50/30 dark:hover:bg-[#303032] px-3 py-2.5 rounded-lg uppercase tracking-wider text-center transition-all border border-zinc-100 dark:border-[#303032]"
                            >
                                {{ cat.name }}
                            </router-link>
                        </div>
                    </div>
                    
                    <div class="h-[1px] bg-zinc-100 dark:bg-[#28282a]"></div>
                    
                    <!-- Dark Mode Toggle (Mobile) -->
                    <button @click="toggle" class="flex items-center gap-3 text-sm font-bold text-charcoal/80 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] uppercase tracking-wide w-full">
                        <svg v-if="isDark" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                        {{ isDark ? 'Mode Terang' : 'Mode Gelap' }}
                    </button>

                    <div class="space-y-3 pb-4">
                        <router-link v-if="!isLoggedIn" @click="mobileOpen = false" to="/login" class="block text-center text-xs font-bold text-white bg-maroon py-3 rounded-lg uppercase tracking-wider transition-colors hover:bg-maroon-600 dark:hover:bg-maroon/80">Masuk / Daftar</router-link>
                        <button v-else @click="triggerMobileLogout" class="block w-full text-center text-xs font-bold text-white bg-charcoal dark:bg-[#f0eeeb] dark:text-[#161618] py-3 rounded-lg uppercase tracking-wider cursor-pointer">Keluar</button>
                    </div>
                </nav>
            </div>
        </Transition>

        <!-- Main Content -->
        <main class="min-h-[70vh] bg-white dark:bg-[#161618] transition-colors duration-200">
            <router-view />
        </main>

        <footer class="bg-zinc-800 text-white border-t border-zinc-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                    <div>
                        <div class="flex items-center gap-2">
                            <img src="/aliesmo-logo.svg" alt="Aliesmo" class="h-8 w-auto brightness-0 invert" />
                        </div>
                        <p class="mt-3 text-sm text-white/50 leading-relaxed max-w-xs">Kemeja batik dan casual berkualitas. Nyaman dipakai, bangga dengan budaya Indonesia.</p>
                        <!-- Social media links -->
                        <div class="mt-4 flex items-center gap-3">
                            <a v-if="get('social_instagram')" :href="get('social_instagram')" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors" aria-label="Instagram">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a v-if="get('social_facebook')" :href="get('social_facebook')" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors" aria-label="Facebook">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a v-if="get('social_tiktok')" :href="get('social_tiktok')" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors" aria-label="TikTok">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            </a>
                            <a v-if="get('social_youtube')" :href="get('social_youtube')" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors" aria-label="YouTube">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-white mb-4">Menu</h4>
                        <ul class="space-y-2">
                            <li><router-link to="/" class="text-sm text-white/50 hover:text-white transition-colors">Beranda</router-link></li>
                            <li><router-link to="/?shop=1" class="text-sm text-white/50 hover:text-white transition-colors">Koleksi</router-link></li>
                            <li><router-link to="/cart" class="text-sm text-white/50 hover:text-white transition-colors">Keranjang</router-link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-white mb-4">Informasi</h4>
                        <ul class="space-y-2">
                            <li><router-link to="/track-order" class="text-sm text-white/50 hover:text-white transition-colors">Lacak Pesanan</router-link></li>
                            <li><router-link to="/shipping-info" class="text-sm text-white/50 hover:text-white transition-colors">Info Pengiriman</router-link></li>
                            <li><router-link to="/size-guide" class="text-sm text-white/50 hover:text-white transition-colors">Panduan Ukuran</router-link></li>
                            <li><router-link to="/terms" class="text-sm text-white/50 hover:text-white transition-colors">Syarat & Ketentuan</router-link></li>
                            <li><router-link to="/privacy" class="text-sm text-white/50 hover:text-white transition-colors">Kebijakan Privasi</router-link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-white mb-4">Kontak</h4>
                        <ul class="space-y-2 text-sm text-white/50">
                            <li>{{ get('contact_email', 'hello@aliesmo.com') }}</li>
                            <li>{{ get('contact_phone', '+62 851-9681-1722') }}</li>
                            <li>{{ get('contact_address', 'Ulujami, Pemalang, Jawa Tengah') }}</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-zinc-700 text-center text-sm text-white/30">
                    &copy; {{ new Date().getFullYear() }} aliesmo. Developed by <a href="https://kalanalabs.com" target="_blank" rel="noopener" class="hover:text-white transition-colors">Kalana Labs</a>.
                </div>
            </div>
        </footer>

        <!-- Cart Toast Notification -->
        <Transition name="toast">
            <div v-if="showToast" class="fixed bottom-5 right-5 z-[99] w-72 bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-2xl border border-zinc-100 dark:border-[#303032] overflow-hidden">
                <!-- Green accent bar -->
                <div class="h-1 w-full bg-gradient-to-r from-emerald-400 to-emerald-500"></div>
                <div class="p-3.5 flex items-center gap-3">
                    <!-- Thumbnail -->
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-zinc-100 dark:bg-[#28282a] flex-shrink-0">
                        <img v-if="cartToast.thumbnail" :src="cartToast.thumbnail" :alt="cartToast.name" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-zinc-400">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                            </svg>
                        </div>
                    </div>
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500 flex-shrink-0">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">Ditambahkan ke keranjang</span>
                        </div>
                        <p class="text-sm font-semibold text-charcoal dark:text-[#f0eeeb] truncate">{{ cartToast.name }}</p>
                        <p class="text-xs text-charcoal/50 dark:text-[#8a8a8e]">{{ cartToast.quantity }} item</p>
                    </div>
                    <!-- Cart link -->
                    <router-link to="/cart" @click="showToast = false" class="flex-shrink-0 px-3 py-1.5 bg-maroon text-white text-[11px] font-bold rounded-lg hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-colors">
                        Lihat
                    </router-link>
                </div>
            </div>
        </Transition>

        <!-- Wishlist Toast (generic) -->
        <Transition name="toast">
            <div v-if="showWishlistToastState" class="fixed bottom-5 right-5 bg-charcoal dark:bg-[#f0eeeb] text-white dark:text-[#161618] text-xs px-4 py-3.5 rounded-xl shadow-2xl z-[98] flex items-center gap-2.5 border border-white/10">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" class="text-maroon" stroke="currentColor" stroke-width="2"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                <span>{{ toastMessage }}</span>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCartStore } from './cart'
import api, { clearToken } from './api'
import { UserIcon } from '@heroicons/vue/24/solid'
import { UserIcon as UserIconOutline, ShoppingBagIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import { useSettings } from './useSettings'
import { useDarkMode } from './useDarkMode'

const router = useRouter()
const route = useRoute()
const { items } = useCartStore()
const { fetchSettings, get } = useSettings()
const { isDark, toggle, init: initDarkMode } = useDarkMode()
const mobileOpen = ref(false)
const isLoggedIn = ref(false)
const userMenuOpen = ref(false)
const categories = ref([])

const cartCount = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0))

// Search Query sync with Route
const searchQuery = ref('')
watch(() => route.query.search, (newSearch) => {
    searchQuery.value = newSearch || ''
}, { immediate: true })

watch(() => route.fullPath, () => {
    userMenuOpen.value = false
})

// Promo Carousel for Mobile
const activePromo = ref(0)
let promoTimer = null
const promos = ref([
    { text: 'Bahan Premium Oxford & Linen | Garansi 30 Hari', link: '/?shop=1' },
    { text: 'Diskon 10% First Order | Kode: ALIESNEW10', link: '/login' }
])

// Wishlist Toast state
const showWishlistToastState = ref(false)
const toastMessage = ref('')

// Cart Toast state
const showToast = ref(false)
const cartToast = ref({ name: '', thumbnail: '', quantity: 1 })
let cartToastTimer = null

// Cart icon bounce
const cartBounce = ref(false)

// Close user dropdown on outside click — defined at module scope so onUnmounted can reference it
const handleClickOutside = (e) => {
    if (userMenuOpen.value && !e.target.closest('[data-user-menu]')) {
        userMenuOpen.value = false
    }
}

onMounted(async () => {
    // Initialize dark mode from localStorage
    initDarkMode()

    isLoggedIn.value = !!localStorage.getItem('token')

    // Sync isLoggedIn jika token berubah di tab lain (logout-all, dll)
    window.addEventListener('storage', (e) => {
        if (e.key === 'token') {
            isLoggedIn.value = !!e.newValue
        }
    })

    // Sync isLoggedIn saat auth events dari api.js interceptor
    window.addEventListener('auth:login', () => {
        isLoggedIn.value = true
    })
    window.addEventListener('auth:expired', () => {
        isLoggedIn.value = false
    })
    window.addEventListener('auth:unverified', () => {
        // Tetap login, hanya redirect — isLoggedIn tidak perlu diubah
    })

    document.addEventListener('click', handleClickOutside)

    // Cart toast listener
    window.addEventListener('cart:added', (e) => {
        cartToast.value = {
            name: e.detail.name,
            thumbnail: e.detail.thumbnail,
            quantity: e.detail.quantity,
        }
        showToast.value = true
        // Trigger bounce animation on cart icon
        cartBounce.value = true
        setTimeout(() => { cartBounce.value = false }, 600)
        if (cartToastTimer) clearTimeout(cartToastTimer)
        cartToastTimer = setTimeout(() => {
            showToast.value = false
        }, 3500)
    })

    // Auto-scroll promos on mobile
    promoTimer = setInterval(() => {
        activePromo.value = (activePromo.value + 1) % promos.value.length
    }, 4000)

    // Fetch settings (promos, contact info) dan categories secara paralel
    try {
        const [settingsRes, categoriesRes] = await Promise.allSettled([
            fetchSettings(true),
            api.get('/categories'),
        ])

        if (categoriesRes.status === 'fulfilled') {
            categories.value = categoriesRes.value.data?.data || categoriesRes.value.data
        }

        // Update promos dari settings jika tersedia
        const s = settingsRes.status === 'fulfilled' ? settingsRes.value : {}
        console.log('[App.vue] Settings loaded:', s)
        const p1 = s.announcement_1; const p1l = s.announcement_1_link
        const p2 = s.announcement_2; const p2l = s.announcement_2_link
        const p3 = s.announcement_3; const p3l = s.announcement_3_link
        console.log('[App.vue] Announcements:', { p1, p2, p3 })
        if (p1 || p2 || p3) {
            promos.value = [
                p1 ? { text: p1, link: p1l || '/?shop=1' } : null,
                p2 ? { text: p2, link: p2l || '/?shop=1' } : null,
                p3 ? { text: p3, link: p3l || '/login' } : null,
            ].filter(Boolean)
            console.log('[App.vue] Promos updated:', promos.value)
        }
    } catch (e) {
        console.error('Failed to fetch initial data:', e)
    }
})

function showWishlistToast() {
    toastMessage.value = 'Fitur Favorit/Wishlist akan segera hadir!'
    showWishlistToastState.value = true
    setTimeout(() => {
        showWishlistToastState.value = false
    }, 3000)
}

function handleSearch() {
    router.push({ path: '/', query: { ...route.query, search: searchQuery.value || undefined } })
}

async function handleLogout() {
    try { await api.post('/auth/logout') } catch (e) {}
    clearToken()
    isLoggedIn.value = false
    router.push('/')
}

async function handleLogoutAll() {
    try { await api.post('/auth/logout-all') } catch (e) {}
    clearToken()
    isLoggedIn.value = false
    router.push('/')
}

function triggerMobileLogout() {
    handleLogout()
    mobileOpen.value = false
}

onUnmounted(() => {
    if (promoTimer) clearInterval(promoTimer)
    if (cartToastTimer) clearTimeout(cartToastTimer)
    document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* Dropdown transition */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.97);
}

.mobile-nav-enter-active,
.mobile-nav-leave-active {
    transition: all 0.25s ease;
}
.mobile-nav-enter-from,
.mobile-nav-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* Slide up animation for promo bar */
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.5s ease;
}
.slide-up-enter-from {
    transform: translateY(100%);
    opacity: 0;
}
.slide-up-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}

/* Cart bounce animation */
@keyframes cart-bounce {
    0%   { transform: scale(1); }
    30%  { transform: scale(1.35) rotate(-8deg); }
    60%  { transform: scale(0.9) rotate(4deg); }
    80%  { transform: scale(1.1) rotate(-2deg); }
    100% { transform: scale(1) rotate(0deg); }
}
.cart-bounce {
    animation: cart-bounce 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97);
}

/* Toast Slide and Fade */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.toast-enter-from {
    transform: translateY(20px) scale(0.9);
    opacity: 0;
}
.toast-leave-to {
    transform: translateY(20px) scale(0.9);
    opacity: 0;
}
</style>

