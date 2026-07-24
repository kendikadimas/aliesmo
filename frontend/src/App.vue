<template>
    <div class="min-h-screen bg-paper dark:bg-ink transition-colors duration-200">
        <!-- Promo / Announcement Bar -->
        <div class="bg-white dark:bg-[#1c1c1e] border-b border-zinc-200 dark:border-[#303032] text-[10px] font-medium tracking-[0.18em] text-charcoal/60 dark:text-[#8a8a8e] uppercase">
            <div class="max-w-7xl mx-auto px-4 py-2">
                <!-- Desktop View (3 columns) -->
                <div class="hidden lg:grid grid-cols-3 gap-4 text-center">
                    <router-link :to="promos[0]?.link || '/?shop=1'" class="hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors flex items-center justify-center">
                        {{ promos[0]?.text || 'Bahan Premium Oxford &amp; Linen | Garansi 30 Hari' }}
                    </router-link>
                    <router-link :to="promos[1]?.link || '/?shop=1'" class="hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors flex items-center justify-center border-x border-zinc-200 dark:border-[#303032]">
                        {{ promos[1]?.text || 'Bahan Premium Oxford &amp; Linen | Garansi 30 Hari' }}
                    </router-link>
                    <router-link :to="promos[2]?.link || '/login'" class="hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors flex items-center justify-center">
                        {{ promos[2]?.text || 'Diskon 10% First Order | Kode: ALIESNEW10' }}
                    </router-link>
                </div>
                <!-- Mobile/Tablet View (Rotating Carousel) -->
                <div class="lg:hidden text-center relative h-4 overflow-hidden flex items-center justify-center">
                    <TransitionGroup name="slide-up">
                        <div v-for="(promo, index) in promos" :key="index" v-show="activePromo === index && promo" class="absolute w-full flex items-center justify-center gap-1.5">
                            <router-link :to="promo.link" class="hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors">{{ promo.text }}</router-link>
                        </div>
                    </TransitionGroup>
                </div>
            </div>
        </div>

        <!-- Sticky Header -->
        <header class="sticky top-0 z-50 bg-white/95 dark:bg-ink-90/95 backdrop-blur-md border-b border-zinc-200/60 dark:border-ink-80/60 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 lg:h-18">
                    <!-- Left: Hamburger (mobile) + Logo -->
                    <div class="flex items-center gap-3">
                        <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 -ml-2 text-charcoal dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" aria-label="Menu">
                            <Bars3Icon class="w-6 h-6" />
                        </button>
                        <router-link to="/" class="flex items-center">
                            <img src="/aliesmo-horizontal.png" alt="Aliesmo" class="block h-8 w-auto" :class="isDark ? '' : 'brightness-0'" />
                        </router-link>
                    </div>

                    <!-- Middle: Page Menu (Desktop) -->
                    <nav class="hidden lg:flex items-center gap-1">
                        <router-link to="/" class="px-3 py-2 text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide" :class="{ 'text-maroon': $route.path === '/' }">Beranda</router-link>

                        <!-- Kategori dropdown -->
                        <div class="relative" data-cat-menu>
                            <button @click="desktopCatsOpen = !desktopCatsOpen" class="flex items-center gap-1 px-3 py-2 text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">
                                Kategori
                                <ChevronDownIcon class="w-3.5 h-3.5 transition-transform duration-150" :class="desktopCatsOpen ? 'rotate-180' : ''" />
                            </button>
                            <Transition name="dropdown">
                                <div v-if="desktopCatsOpen" class="absolute top-full left-0 mt-1 w-48 bg-white dark:bg-[#1c1c1e] rounded-xl shadow-lg border border-zinc-100 dark:border-[#303032] py-1 z-50">
                                    <router-link @click="desktopCatsOpen = false" to="/catalog" class="block px-4 py-2 text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] hover:text-maroon dark:hover:text-[#f0eeeb] hover:bg-zinc-50 dark:hover:bg-[#242426] transition-colors uppercase tracking-wider">Semua Kategori</router-link>
                                    <router-link v-for="cat in categories" :key="cat.slug" @click="desktopCatsOpen = false" :to="`/catalog/${cat.slug}`" class="block px-4 py-2 text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] hover:text-maroon dark:hover:text-[#f0eeeb] hover:bg-zinc-50 dark:hover:bg-[#242426] transition-colors uppercase tracking-wider">{{ cat.name }}</router-link>
                                </div>
                            </Transition>
                        </div>

                        <router-link to="/?shop=1" class="px-3 py-2 text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">Koleksi</router-link>
                        <router-link to="/blog" class="px-3 py-2 text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide" :class="{ 'text-maroon': $route.path.startsWith('/blog') }">Blog</router-link>
                        <a href="https://wa.me/628138883345?text=Halo%20Admin%20Aliesmo.%20Saya%20tertarik%20bergabung%20dengan%20Program%20Kerja%20Sama.%20Mohon%20informasinya.%20Terima%20kasih." target="_blank" rel="noopener noreferrer" class="px-3 py-2 text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">Affiliate</a>
                    </nav>

                    <!-- Right: Search icon + dark mode + wishlist + cart + user -->
                    <div class="flex items-center gap-1">
                        <!-- Search icon (desktop) — expands inline -->
                        <div class="hidden lg:flex items-center relative" data-search-box>
                            <Transition name="dropdown">
                                <form v-if="searchOpen" @submit.prevent="handleSearch(); searchOpen=false" class="absolute right-8 top-1/2 -translate-y-1/2 w-64">
                                    <input v-model="searchQuery" type="text" autofocus placeholder="Cari produk..." class="w-full pl-4 pr-10 py-1.5 rounded-full border border-zinc-200 dark:border-[#303032] focus:outline-none focus:border-maroon text-sm bg-white dark:bg-[#1c1c1e] dark:text-[#f0eeeb]" @blur="searchOpen=false" />
                                </form>
                            </Transition>
                            <button @click="searchOpen = !searchOpen" class="p-2 text-charcoal/70 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" aria-label="Cari">
                                <MagnifyingGlassIcon class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button @click="toggle" class="p-2 text-charcoal/70 dark:text-[#8a8a8e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" :aria-label="isDark ? 'Light mode' : 'Dark mode'">
                            <SunIcon v-if="isDark" class="w-5 h-5" />
                            <MoonIcon v-else class="w-5 h-5" />
                        </button>

                        <!-- Wishlist -->
                        <button @click="showWishlistToast" class="hidden md:flex p-2 text-charcoal/70 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" aria-label="Wishlist">
                            <HeartIcon class="w-5 h-5" />
                        </button>

                        <!-- Cart -->
                        <router-link to="/cart" class="relative p-2 text-charcoal/70 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" aria-label="Cart">
                            <ShoppingBagIcon :class="['w-5 h-5 transition-transform', cartBounce ? 'cart-bounce' : '']" />
                            <span v-if="cartCount" class="absolute -top-0.5 -right-0.5 bg-maroon text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center leading-none">{{ cartCount > 9 ? '9+' : cartCount }}</span>
                        </router-link>

                        <!-- User -->
                        <div class="hidden md:block relative" data-user-menu>
                            <router-link v-if="!isLoggedIn" to="/login" class="p-2 flex items-center justify-center text-charcoal/70 dark:text-[#d0ceca] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors" aria-label="Masuk">
                                <UserIcon class="w-5 h-5" />
                            </router-link>
                            <div v-else>
                                <button @click="userMenuOpen = !userMenuOpen" class="flex items-center justify-center w-8 h-8 rounded-full bg-maroon hover:bg-maroon/85 transition-colors" aria-label="Akun saya">
                                    <UserIcon class="w-[18px] h-[18px] text-white" />
                                </button>
                                <Transition name="dropdown">
                                    <div v-if="userMenuOpen" class="absolute right-0 top-full mt-2 w-44 bg-white dark:bg-[#1c1c1e] border border-zinc-100 dark:border-[#303032] rounded-xl shadow-xl z-50 py-1">
                                        <router-link @click="userMenuOpen = false" to="/profile" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#303032] hover:text-maroon transition-colors"><UserIconOutline class="w-4 h-4 shrink-0" />Akun Saya</router-link>
                                        <router-link @click="userMenuOpen = false" to="/profile?tab=pesanan" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#303032] hover:text-maroon transition-colors"><ShoppingBagIcon class="w-4 h-4 shrink-0" />Pesanan Saya</router-link>
                                        <div class="h-px bg-zinc-100 dark:bg-[#28282a] mx-3 my-1"></div>
                                        <button @click="userMenuOpen = false; handleLogout()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#303032] hover:text-maroon transition-colors cursor-pointer"><ArrowRightOnRectangleIcon class="w-4 h-4 shrink-0" />Keluar</button>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Search bar -->
                <div class="pb-3 lg:hidden">
                    <form @submit.prevent="handleSearch" class="relative w-full">
                        <input v-model="searchQuery" type="text" placeholder="Cari kemeja, batik, atau koleksi..." class="w-full pl-5 pr-12 py-2 rounded-full border border-zinc-200 dark:border-[#303032] focus:outline-none focus:border-maroon text-xs bg-zinc-50/20 dark:bg-[#1c1c1e] dark:text-[#f0eeeb] placeholder-zinc-400 dark:placeholder-[#8a8a8e]" />
                        <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-charcoal dark:bg-[#f0eeeb] hover:bg-maroon text-white dark:text-[#161618] flex items-center justify-center transition-colors" aria-label="Cari">
                            <MagnifyingGlassIcon class="w-3 h-3" />
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Mobile Navigation Sidebar (half-screen) -->
        <Transition name="sidebar-overlay">
            <div v-if="mobileOpen" @click="mobileOpen = false" class="lg:hidden fixed inset-0 bg-black/40 z-40"></div>
        </Transition>
        <Transition name="sidebar">
            <aside v-if="mobileOpen" class="lg:hidden fixed top-0 left-0 bottom-0 w-[75vw] max-w-[320px] bg-white dark:bg-[#161618] z-50 shadow-2xl flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-[#303032]">
                    <img src="/aliesmo-horizontal.png" alt="Aliesmo" class="block h-7 w-auto" :class="isDark ? '' : 'brightness-0'" />
                    <button @click="mobileOpen = false" class="p-2 -mr-1 text-charcoal/60 dark:text-[#8a8a8e] hover:text-charcoal dark:hover:text-[#f0eeeb] transition-colors" aria-label="Tutup">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Menu List -->
                <nav class="flex-1 overflow-y-auto px-5 py-4 space-y-1">
                    <router-link @click="mobileOpen = false" to="/" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">
                        <HomeIcon class="w-[18px] h-[18px] shrink-0" />
                        Beranda
                    </router-link>

                    <!-- Categories Dropdown -->
                    <div>
                        <button @click="mobileCatsOpen = !mobileCatsOpen" class="flex items-center justify-between w-full gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">
                            <span class="flex items-center gap-3">
                                <Squares2X2Icon class="w-[18px] h-[18px] shrink-0" />
                                Kategori
                            </span>
                            <ChevronDownIcon class="w-4 h-4 transition-transform duration-200" :class="mobileCatsOpen ? 'rotate-180' : ''" />
                        </button>
                        <Transition name="accordion">
                            <div v-if="mobileCatsOpen" class="ml-6 mt-1 space-y-0.5 border-l-2 border-zinc-100 dark:border-[#303032] pl-3">
                                <router-link @click="mobileOpen = false" to="/catalog" class="block px-3 py-2 rounded-lg text-xs font-medium text-charcoal/60 dark:text-[#8a8a8e] hover:text-maroon dark:hover:text-[#f0eeeb] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] transition-colors uppercase tracking-wider">
                                    Semua Kategori
                                </router-link>
                                <router-link
                                    v-for="cat in categories"
                                    :key="cat.slug"
                                    @click="mobileOpen = false"
                                    :to="`/catalog/${cat.slug}`"
                                    class="block px-3 py-2 rounded-lg text-xs font-medium text-charcoal/60 dark:text-[#8a8a8e] hover:text-maroon dark:hover:text-[#f0eeeb] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] transition-colors uppercase tracking-wider"
                                >
                                    {{ cat.name }}
                                </router-link>
                            </div>
                        </Transition>
                    </div>

                    <router-link @click="mobileOpen = false" to="/#shop" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">
                        <ShoppingBagIcon class="w-[18px] h-[18px] shrink-0" />
                        Semua Koleksi
                    </router-link>
                    <router-link @click="mobileOpen = false" to="/blog" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">
                        <BookOpenIcon class="w-[18px] h-[18px] shrink-0" />
                        Blog
                    </router-link>
                    <a href="https://wa.me/628138883345?text=Halo%20Admin%20Aliesmo.%20Saya%20tertarik%20bergabung%20dengan%20Program%20Kerja%20Sama.%20Mohon%20informasinya.%20Terima%20kasih." target="_blank" rel="noopener noreferrer" @click="mobileOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">
                        <UserGroupIcon class="w-[18px] h-[18px] shrink-0" />
                        Affiliate
                    </a>

                    <!-- Account Section -->
                    <router-link v-if="isLoggedIn" @click="mobileOpen = false" to="/profile" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">
                        <UserIconOutline class="w-[18px] h-[18px] shrink-0" />
                        Akun Saya
                    </router-link>
                    <router-link v-if="isLoggedIn" @click="mobileOpen = false" to="/profile?tab=pesanan" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">
                        <ShoppingBagIcon class="w-[18px] h-[18px] shrink-0" />
                        Pesanan Saya
                    </router-link>

                    <!-- Dark Mode Toggle -->
                    <button @click="toggle" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-semibold text-charcoal/80 dark:text-[#d0ceca] hover:bg-zinc-50 dark:hover:bg-[#1c1c1e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors uppercase tracking-wide">
                        <SunIcon v-if="isDark" class="w-[18px] h-[18px] shrink-0" />
                        <MoonIcon v-else class="w-[18px] h-[18px] shrink-0" />
                        {{ isDark ? 'Mode Terang' : 'Mode Gelap' }}
                    </button>
                </nav>

                <!-- Bottom CTA -->
                <div class="px-5 py-4 border-t border-zinc-100 dark:border-[#303032]">
                    <router-link v-if="!isLoggedIn" @click="mobileOpen = false" to="/login" class="block text-center text-xs font-bold text-white bg-maroon py-3 rounded-lg uppercase tracking-wider transition-colors hover:bg-maroon-600 dark:hover:bg-maroon/80">Masuk / Daftar</router-link>
                    <button v-else @click="triggerMobileLogout" class="block w-full text-center text-xs font-bold text-white bg-charcoal dark:bg-[#f0eeeb] dark:text-[#161618] py-3 rounded-lg uppercase tracking-wider cursor-pointer">Keluar</button>
                </div>
            </aside>
        </Transition>

        <!-- Main Content -->
        <main class="min-h-[70vh] bg-white dark:bg-[#161618] transition-colors duration-200 overflow-x-hidden">
            <router-view />
        </main>

        <footer class="bg-zinc-800 text-white border-t border-zinc-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                    <div>
                        <!-- ponytail: footer always dark bg → keep default (white) logo -->
                        <img src="/aliesmo-horizontal.png" alt="Aliesmo" class="block h-9 w-auto" />
                        <p class="mt-2 text-sm text-white/50 leading-relaxed max-w-xs">Kemeja batik dan casual berkualitas. Nyaman dipakai, bangga dengan budaya Indonesia.</p>
                        <!-- Social media links -->
                        <div class="mt-4 flex items-center gap-3">
                            <a :href="get('social_instagram', 'https://www.instagram.com/aliesmo.id')" target="_blank" rel="noopener noreferrer" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors" aria-label="Instagram">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a v-if="get('social_facebook')?.trim()" :href="get('social_facebook')" target="_blank" rel="noopener noreferrer" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors" aria-label="Facebook">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a :href="get('social_tiktok', 'https://www.tiktok.com/@aliesmo_')" target="_blank" rel="noopener noreferrer" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors" aria-label="TikTok">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            </a>
                            <a v-if="get('social_youtube')?.trim()" :href="get('social_youtube')" target="_blank" rel="noopener noreferrer" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors" aria-label="YouTube">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                            <a :href="get('social_shopee', 'https://shopee.co.id/aliesmo.id')" target="_blank" rel="noopener noreferrer" class="w-8 h-8 flex items-center justify-center rounded-lg bg-[#ee4d2d] hover:bg-[#d73211] transition-colors" aria-label="Shopee">
                                <img src="/Shopee_logo.svg" alt="Shopee" class="w-5 h-5 object-contain brightness-0 invert" />
                            </a>
                            <a v-if="get('social_tiktokshop')?.trim()" :href="get('social_tiktokshop')" target="_blank" rel="noopener noreferrer" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors" aria-label="TikTok Shop">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/><path d="M22 10h-4v4h-2v-4h-4V8h4V4h2v4h4v2z" opacity=".7"/></svg>
                            </a>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-white mb-4">Menu</h4>
                        <ul class="space-y-2">
                            <li><router-link to="/" class="text-sm text-white/50 hover:text-white transition-colors">Beranda</router-link></li>
                            <li><router-link to="/?shop=1" class="text-sm text-white/50 hover:text-white transition-colors">Koleksi</router-link></li>
                            <li><router-link to="/blog" class="text-sm text-white/50 hover:text-white transition-colors">Blog</router-link></li>
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
                            <li>{{ get('contact_email', 'cs@aliesmo.id') }}</li>
                            <li>{{ get('contact_phone', '+62 813-888-3345') }}</li>
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
                            <ShoppingBagIcon class="w-5 h-5 text-zinc-400" />
                        </div>
                    </div>
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <CheckIcon class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" />
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
                <HeartIconSolid class="w-4 h-4 text-maroon shrink-0" />
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
import { UserIcon, HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid'
import {
    UserIcon as UserIconOutline,
    ShoppingBagIcon,
    ArrowRightOnRectangleIcon,
    Bars3Icon,
    ChevronDownIcon,
    MagnifyingGlassIcon,
    SunIcon,
    MoonIcon,
    HeartIcon,
    XMarkIcon,
    HomeIcon,
    Squares2X2Icon,
    BookOpenIcon,
    UserGroupIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline'
import { useSettings } from './useSettings'
import { useDarkMode } from './useDarkMode'

const router = useRouter()
const route = useRoute()
const { items } = useCartStore()
const { fetchSettings, get } = useSettings()
const { isDark, toggle, init: initDarkMode } = useDarkMode()
const mobileOpen = ref(false)
const mobileCatsOpen = ref(false)
const desktopCatsOpen = ref(false)
const searchOpen = ref(false)
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

// Reset activePromo saat promos berubah agar tidak akses index yang tidak ada
watch(promos, () => {
    activePromo.value = 0
})

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
    if (userMenuOpen.value && !e.target.closest('[data-user-menu]')) userMenuOpen.value = false
    if (desktopCatsOpen.value && !e.target.closest('[data-cat-menu]')) desktopCatsOpen.value = false
    // ponytail: searchOpen closes on input blur, not click-outside
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

/* Sidebar overlay transition */
.sidebar-overlay-enter-active,
.sidebar-overlay-leave-active {
    transition: opacity 0.25s ease;
}
.sidebar-overlay-enter-from,
.sidebar-overlay-leave-to {
    opacity: 0;
}

/* Sidebar slide-in transition */
.sidebar-enter-active,
.sidebar-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar-enter-from,
.sidebar-leave-to {
    transform: translateX(-100%);
}

/* Accordion transition for categories */
.accordion-enter-active,
.accordion-leave-active {
    transition: all 0.2s ease;
    overflow: hidden;
}
.accordion-enter-from,
.accordion-leave-to {
    opacity: 0;
    max-height: 0;
    margin-top: 0 !important;
    padding-top: 0;
    padding-bottom: 0;
}
.accordion-enter-to,
.accordion-leave-from {
    opacity: 1;
    max-height: 500px;
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

