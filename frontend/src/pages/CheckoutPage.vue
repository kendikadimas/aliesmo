<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16">
        <h1 class="text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Checkout</h1>

        <div v-if="submitting" class="mt-8 lg:mt-10 grid lg:grid-cols-5 gap-8 lg:gap-12">
            <!-- left col skeleton -->
            <div class="lg:col-span-3 space-y-6">
                <SkeletonLoader :loading="true" :radius="16" height="220px" width="100%" />
                <SkeletonLoader :loading="true" :radius="16" height="180px" width="100%" />
                <SkeletonLoader :loading="true" :radius="16" height="140px" width="100%" />
            </div>
            <!-- right col skeleton -->
            <div class="lg:col-span-2">
                <SkeletonLoader :loading="true" :radius="16" height="320px" width="100%" />
            </div>
        </div>

        <form v-else @submit.prevent="submitOrder" class="mt-8 lg:mt-10 grid lg:grid-cols-5 gap-8 lg:gap-12">
            <div class="lg:col-span-3 space-y-6">

                <!-- Data Diri -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 dark:border-[#303032]">
                    <h2 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide mb-6">Data Diri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Nama Lengkap</label>
                            <input v-model="form.customer_name" required placeholder="Masukkan nama kamu" class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:text-[#6a6a6e]/60 dark:placeholder:text-[#6a6a6e] bg-white dark:bg-[#28282a] focus:border-maroon focus:outline-none transition-colors">
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Email</label>
                                <input v-model="form.customer_email" type="email" required placeholder="kamu@email.com" class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:text-[#6a6a6e]/60 dark:placeholder:text-[#6a6a6e] bg-white dark:bg-[#28282a] focus:border-maroon focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Telepon</label>
                                <input v-model="form.customer_phone" placeholder="0812-xxxx-xxxx" class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:text-[#6a6a6e]/60 dark:placeholder:text-[#6a6a6e] bg-white dark:bg-[#28282a] focus:border-maroon focus:outline-none transition-colors">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alamat & Pengiriman -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 dark:border-[#303032]">
                    <h2 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide mb-6">Alamat & Pengiriman</h2>
                    <div class="space-y-4">

                        <!-- Lokasi Pengiriman (Autocomplete Direct Search) -->
                        <div class="relative location-search-container">
                            <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Kota atau Kecamatan Tujuan</label>
                            <div class="relative">
                                <input
                                    ref="searchInputRef"
                                    v-model="searchQuery"
                                    @input="onSearchInput"
                                    @focus="showDropdown = true"
                                    @keydown.down.prevent="onArrowDown"
                                    @keydown.up.prevent="onArrowUp"
                                    @keydown.enter.prevent="onEnter"
                                    @keydown.escape="showDropdown = false"
                                    placeholder="Ketik nama kota atau kecamatan (misal: Denpasar, Tebet)"
                                    class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:text-[#6a6a6e]/60 dark:placeholder:text-[#6a6a6e] bg-white dark:bg-[#28282a] focus:border-maroon focus:outline-none transition-colors"
                                    autocomplete="off"
                                    required
                                >
                                <div v-if="loadingSearch" class="absolute right-3 top-3">
                                    <div class="w-4 h-4 border-2 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
                                </div>
                            </div>

                            <!-- Search Results Dropdown -->
                            <div v-if="showDropdown && (loadingSearch || searchResults.length || searchQuery.trim().length >= 3)"
                                class="absolute left-0 right-0 z-50 w-full mt-1 bg-white dark:bg-[#28282a] border-2 border-maroon-50 dark:border-[#303032] rounded-xl shadow-lg max-h-60 overflow-y-auto">

                                <!-- Loading state di dalam dropdown -->
                                <div v-if="loadingSearch" class="flex items-center gap-2 px-4 py-3 text-xs text-charcoal/50 dark:text-[#8a8a8e]">
                                    <div class="w-3.5 h-3.5 border-2 border-maroon-100 border-t-maroon rounded-full animate-spin shrink-0"></div>
                                    Mencari lokasi...
                                </div>

                                <!-- Results -->
                                <template v-else-if="searchResults.length">
                                    <button
                                        v-for="(item, index) in searchResults"
                                        :key="item.id"
                                        :ref="el => { if (el) itemRefs[index] = el }"
                                        type="button"
                                        @click="selectDestination(item)"
                                        @mouseenter="activeIndex = index"
                                        class="w-full text-left px-4 py-3 text-xs border-b border-maroon-50 dark:border-[#303032] last:border-0 transition-colors"
                                        :class="activeIndex === index
                                            ? 'bg-maroon-50/70 dark:bg-slate-600 text-charcoal dark:text-[#f0eeeb]'
                                            : 'text-charcoal dark:text-[#f0eeeb] hover:bg-maroon-50/50 dark:hover:bg-slate-600'"
                                    >
                                        <span class="font-semibold">{{ item.label }}</span>
                                    </button>
                                </template>

                                <!-- Empty state -->
                                <div v-else-if="!loadingSearch && searchQuery.trim().length >= 3"
                                    class="px-4 py-4 text-xs text-charcoal/50 dark:text-[#8a8a8e] text-center">
                                    Lokasi tidak ditemukan. Coba ketik nama kota/kecamatan lain.
                                </div>
                            </div>
                        </div>

                        <!-- Alamat Lengkap (patokan rumah) -->
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Alamat Lengkap</label>
                            <textarea v-model="form.shipping_address" required placeholder="Contoh: Jl. Merdeka No. 123, RT 01/RW 02, patokan dekat masjid Al-Ikhlas" rows="2" class="w-full border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:text-[#6a6a6e]/60 dark:placeholder:text-[#6a6a6e] bg-white dark:bg-[#28282a] focus:border-maroon focus:outline-none transition-colors resize-none"></textarea>
                            <p class="text-[10px] text-charcoal/40 dark:text-[#6a6a6e] mt-1">Nama jalan, nomor rumah, RT/RW, atau patokan terdekat.</p>
                        </div>

                        <!-- Layanan Pengiriman - tampil otomatis setelah lokasi dipilih -->
                        <div v-if="selectedCity">
                            <div v-if="loadingShipping" class="flex items-center gap-2 text-xs text-charcoal/50 dark:text-[#8a8a8e] py-3">
                                <div class="w-4 h-4 border-2 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
                                Mencari layanan pengiriman tersedia...
                            </div>

                            <div v-else-if="shippingOptions.length">
                                <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-2">Pilih Layanan Pengiriman</label>
                                <div class="space-y-2">
                                    <label v-for="opt in shippingOptions" :key="opt.code + '-' + opt.service"
                                        class="flex items-center justify-between p-3 rounded-xl border-2 cursor-pointer transition-all"
                                        :class="selectedShipping?.code === opt.code && selectedShipping?.service === opt.service
                                            ? 'border-maroon bg-maroon-50/30 dark:bg-maroon/10'
                                            : 'border-maroon-100 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-[#f0eeeb]'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" :value="opt" v-model="selectedShipping" class="accent-maroon">
                                            <div>
                                                <p class="text-xs font-bold text-charcoal dark:text-[#f0eeeb]">{{ opt.courier }}</p>
                                                <p class="text-xs text-charcoal/60 dark:text-[#8a8a8e]">{{ opt.service }} · {{ opt.description }}</p>
                                                <p class="text-xs text-charcoal/40 dark:text-[#6a6a6e]">Estimasi {{ opt.etd || '-' }} hari</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-bold text-maroon dark:text-[#f0eeeb] shrink-0 ml-2">Rp{{ formatPrice(opt.cost || 0) }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Manual fallback — tampil tombol WA jika semua provider gagal -->
                            <div v-else-if="manualShipping" class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border-2 border-amber-200 dark:border-amber-700">
                                <p class="text-xs font-semibold text-amber-800 dark:text-amber-300 mb-1">Cek ongkir otomatis tidak tersedia</p>
                                <p class="text-xs text-amber-700 dark:text-amber-400 mb-3">Hubungi admin untuk konfirmasi ongkir ke tujuanmu.</p>
                                <a
                                    :href="`https://wa.me/${manualShipping.number}?text=${encodeURIComponent(manualShipping.text)}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-xl transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    Tanya Ongkir via WhatsApp
                                </a>
                            </div>

                            <p v-if="shippingError && !manualShipping" class="text-xs text-red-500 mt-2">{{ shippingError }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Pesanan -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-[#1c1c1e] p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] lg:sticky lg:top-28">
                    <h2 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide mb-4">Ringkasan Pesanan</h2>
                    <div class="space-y-3">
                        <div v-for="item in checkoutItems" :key="item.product_id" class="flex items-center gap-3">
                            <!-- Thumbnail -->
                            <div class="w-12 h-14 shrink-0 rounded-lg overflow-hidden bg-maroon-50 dark:bg-[#28282a]">
                                <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-maroon-300 text-xs font-bold">A</div>
                            </div>
                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb] truncate">{{ item.name }}</p>
                                <p class="text-[10px] text-charcoal/40 dark:text-[#6a6a6e] mt-0.5">Rp{{ formatPrice(item.price) }} × {{ item.quantity }}</p>
                            </div>
                            <!-- Subtotal -->
                            <p class="text-xs font-bold text-charcoal dark:text-[#f0eeeb] shrink-0">Rp{{ formatPrice(item.price * item.quantity) }}</p>
                        </div>
                    </div>

                    <!-- Kupon — diarsipkan sementara -->
                    <!--
                    <div class="mt-4 pt-4 border-t-2 border-maroon-100 dark:border-[#303032]">
                        <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Kode Kupon</label>
                        <div class="flex gap-2">
                            <input
                                v-model="couponCode"
                                :disabled="!!appliedCoupon || couponLoading"
                                placeholder="Masukkan kode kupon"
                                class="flex-1 border-2 border-maroon-100 dark:border-[#303032] rounded-xl px-3 py-2 text-xs text-charcoal dark:text-[#f0eeeb] placeholder:text-charcoal/30 dark:text-[#6a6a6e]/60 dark:placeholder:text-[#6a6a6e] bg-white dark:bg-[#28282a] focus:border-maroon focus:outline-none transition-colors disabled:opacity-50 uppercase"
                                @keyup.enter="applyCoupon"
                            >
                            <button
                                v-if="!appliedCoupon"
                                type="button"
                                @click="applyCoupon"
                                :disabled="!couponCode || couponLoading"
                                class="px-3 py-2 text-xs font-semibold bg-maroon text-white rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all disabled:opacity-50"
                            >
                                {{ couponLoading ? '...' : 'Pakai' }}
                            </button>
                            <button
                                v-else
                                type="button"
                                @click="removeCoupon"
                                class="px-3 py-2 text-xs font-semibold border-2 border-maroon-100 dark:border-[#303032] text-charcoal/60 dark:text-[#8a8a8e] rounded-xl hover:border-maroon transition-all"
                            >
                                Hapus
                            </button>
                        </div>
                        <p v-if="couponError" class="text-xs text-red-500 mt-1.5">{{ couponError }}</p>
                        <p v-if="appliedCoupon" class="text-xs text-green-600 mt-1.5 font-medium">
                            Kupon <span class="font-bold">{{ appliedCoupon.code }}</span> berhasil dipakai!
                        </p>
                    </div>
                    -->

                    <div class="mt-4 pt-4 border-t-2 border-maroon-100 dark:border-[#303032] space-y-2">
                        <div class="flex justify-between text-sm text-charcoal/60 dark:text-[#8a8a8e]">
                            <span>Subtotal</span>
                            <span class="font-medium">Rp{{ formatPrice(checkoutItems.reduce((s, i) => s + i.price * i.quantity, 0)) }}</span>
                        </div>
                        <!-- Diskon kupon — diarsipkan sementara -->
                        <!--
                        <div v-if="appliedCoupon" class="flex justify-between text-sm text-green-600 dark:text-green-400">
                            <span>Diskon ({{ appliedCoupon.code }})</span>
                            <span class="font-medium">-Rp{{ formatPrice(appliedCoupon.discount) }}</span>
                        </div>
                        -->
                        <div class="flex justify-between text-sm text-charcoal/60 dark:text-[#8a8a8e]">
                            <span>Ongkir</span>
                            <span v-if="shippingCost === 0 && !selectedShipping" class="text-charcoal/40 dark:text-[#6a6a6e] italic text-xs">Pilih kurir dulu</span>
                            <span v-else-if="shippingCost === 0" class="text-green-600 dark:text-green-400 font-medium">Gratis!</span>
                            <span v-else class="font-medium">Rp{{ formatPrice(shippingCost) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-charcoal dark:text-[#f0eeeb] pt-3 border-t-2 border-maroon-100 dark:border-[#303032]">
                            <span>Total</span>
                            <span class="text-maroon dark:text-[#f0eeeb]">Rp{{ formatPrice(grandTotal) }}</span>
                        </div>
                    </div>

                    <div v-if="error" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">{{ error }}</div>

                    <!-- Metode Pembayaran -->
                    <div class="mt-4 pt-4 border-t-2 border-maroon-100 dark:border-[#303032]">
                        <h3 class="text-xs font-bold text-charcoal/60 dark:text-[#8a8a8e] tracking-wide mb-3">Metode Pembayaran</h3>
                        <div class="space-y-2">
                            <label v-for="pm in paymentMethods" :key="pm.value"
                                class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                :class="paymentMethod === pm.value
                                    ? 'border-maroon bg-maroon-50/30 dark:bg-maroon/10'
                                    : 'border-maroon-100 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-[#f0eeeb]'">
                                <input type="radio" :value="pm.value" v-model="paymentMethod" class="accent-maroon">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <component :is="pm.icon" class="w-[18px] h-[18px] shrink-0 text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca]" />
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-charcoal dark:text-[#f0eeeb]">{{ pm.label }}</p>
                                        <p class="text-[10px] text-charcoal/50 dark:text-[#6a6a6e]">{{ pm.desc }}</p>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Info Pembayaran — muncul setelah dipilih -->
                        <Transition name="dropdown">
                            <div v-if="paymentMethod" class="mt-3">

                                <!-- Bank Transfer -->
                                <div v-if="paymentMethod === 'bank_transfer'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-4 space-y-3">
                                    <div v-if="availableBanks.length === 0" class="text-xs text-charcoal/40 dark:text-[#6a6a6e]">Belum ada rekening bank diatur.</div>
                                    <template v-else>
                                        <p class="text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e]">Pilih bank tujuan transfer:</p>
                                        <label v-for="(bank, i) in availableBanks" :key="i"
                                            class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                            :class="selectedBank === bank.bank_name
                                                ? 'border-maroon bg-maroon/5 dark:bg-maroon/10'
                                                : 'border-maroon-100 dark:border-[#303032] hover:border-maroon-200'">
                                            <input type="radio" :value="bank.bank_name" v-model="selectedBank" class="accent-maroon mt-0.5">
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 text-xs flex-1">
                                                <div>
                                                    <p class="text-charcoal/40 dark:text-[#6a6a6e]">Bank</p>
                                                    <p class="font-bold text-charcoal dark:text-[#f0eeeb]">{{ bank.bank_name }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-charcoal/40 dark:text-[#6a6a6e]">No. Rekening</p>
                                                    <p class="font-bold text-charcoal dark:text-[#f0eeeb] font-mono tracking-wider">{{ bank.account_no }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-charcoal/40 dark:text-[#6a6a6e]">Atas Nama</p>
                                                    <p class="font-bold text-charcoal dark:text-[#f0eeeb]">{{ bank.account_name }}</p>
                                                </div>
                                            </div>
                                        </label>
                                        <p class="text-[10px] text-charcoal/40 dark:text-[#6a6a6e] pt-1 border-t border-maroon-100 dark:border-[#303032]">Kirim bukti transfer via WhatsApp setelah pembayaran.</p>
                                    </template>
                                </div>

                                <!-- QRIS -->
                                <div v-else-if="paymentMethod === 'qris'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-4 text-center">
                                    <p class="text-xs font-bold text-charcoal dark:text-[#f0eeeb] mb-3">Scan QRIS untuk Pembayaran</p>
                                    <div v-if="get('payment_qris_image')" class="flex justify-center mb-3">
                                        <img :src="get('payment_qris_image')" alt="QRIS" class="w-32 h-32 sm:w-40 sm:h-40 object-contain rounded-xl border-2 border-maroon-100 dark:border-[#303032] bg-white dark:bg-[#1c1c1e] p-1" />
                                    </div>
                                    <div v-else class="w-40 h-40 mx-auto rounded-xl border-2 border-dashed border-maroon-200 dark:border-[#303032] flex items-center justify-center mb-3">
                                        <DevicePhoneMobileIcon class="w-10 h-10 text-maroon-200 dark:text-slate-600" />
                                    </div>
                                    <p v-if="get('payment_qris_name')" class="text-xs font-semibold text-charcoal dark:text-[#f0eeeb]">{{ get('payment_qris_name') }}</p>
                                    <p class="text-[10px] text-charcoal/40 dark:text-[#6a6a6e] mt-1">Scan dengan e-wallet atau m-banking manapun.</p>
                                </div>

                                <!-- COD -->
                                <div v-else-if="paymentMethod === 'cod'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-4">
                                    <p class="text-xs text-charcoal/60 dark:text-[#8a8a8e] leading-relaxed">Bayar langsung saat pesanan tiba di tanganmu. Kurir akan menagih saat pengiriman. Pastikan kamu ada di lokasi saat pengiriman tiba.</p>
                                </div>

                            </div>
                        </Transition>
                    </div>

                    <button type="submit" :disabled="!selectedShipping && selectedCity !== ''" class="w-full mt-6 px-8 py-3.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 dark:hover:bg-maroon/80 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:opacity-50 disabled:cursor-not-allowed">
                        Pesan Sekarang
                    </button>

                    <p class="mt-4 text-xs text-center text-charcoal/40 dark:text-[#6a6a6e] leading-relaxed">Dengan pesan, kamu setuju sama <router-link to="/terms" class="text-maroon dark:text-[#f0eeeb] hover:text-maroon-600 dark:hover:text-[#d0ceca] underline">syarat & ketentuan</router-link> kita.</p>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { BuildingLibraryIcon, DevicePhoneMobileIcon, CreditCardIcon } from '@heroicons/vue/24/outline'
import { useRouter } from 'vue-router'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'
import api from '../api'
import { useSettings } from '../useSettings'

const router = useRouter()
const { items, clear } = useCartStore()
const { fetchSettings, get } = useSettings()
const submitting = ref(false)
const error = ref('')

// Filter hanya item yang dipilih dari CartPage
const selectedIds = new Set(history.state?.selectedIds || items.value.map(i => i.product_id))
const checkoutItems = computed(() => items.value.filter(i => selectedIds.has(i.product_id)))

// Payment method
const paymentMethod = ref('bank_transfer')
const selectedBank = ref('')
const availableBanks = computed(() => {
    const banks = get('payment_banks', [])
    return Array.isArray(banks) ? banks : Object.values(banks || {})
})
const paymentMethods = [
    { value: 'bank_transfer', label: 'Transfer Bank', desc: 'BCA, BNI, Mandiri, dll', icon: BuildingLibraryIcon },
    { value: 'qris', label: 'QRIS', desc: 'Scan & Pay dari e-wallet manapun', icon: DevicePhoneMobileIcon },
    { value: 'cod', label: 'COD (Bayar di Tempat)', desc: 'Bayar langsung saat barang tiba', icon: CreditCardIcon },
]

// Form data
const form = reactive({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    shipping_address: '',
})

// RajaOngkir state
const selectedCity = ref('') // Stores selected destination ID
const shippingOptions = ref([])
const selectedShipping = ref(null)
const shippingCacheKey = ref(null) // Cache key dari backend (CRIT-2 fix)
const loadingShipping = ref(false)
const shippingError = ref('')
const manualShipping = ref(null) // { number, text } — saat semua provider gagal

// Autocomplete Direct Search state
const searchQuery = ref('')
const searchResults = ref([])
const loadingSearch = ref(false)
const showDropdown = ref(false)
const selectedDestination = ref(null)
const searchInputRef = ref(null)
const itemRefs = ref([])
const activeIndex = ref(-1)
let searchTimeout = null

const shippingCost = computed(() => {
    if (!selectedShipping.value) return 0
    return selectedShipping.value.cost || 0
})

// Coupon state — diarsipkan sementara
// const couponCode = ref('')
// const appliedCoupon = ref(null)
// const couponLoading = ref(false)
// const couponError = ref('')

const grandTotal = computed(() => {
    const subtotal = checkoutItems.value.reduce((sum, i) => sum + i.price * i.quantity, 0)
    return Math.max(0, subtotal + shippingCost.value)
})

// async function applyCoupon() {
//     if (!couponCode.value.trim()) return
//     couponLoading.value = true
//     couponError.value = ''
//     try {
//         const res = await api.post('/coupons/validate', {
//             code: couponCode.value.toUpperCase(),
//             order_total: total(),
//         })
//         appliedCoupon.value = res.data.coupon
//     } catch (e) {
//         couponError.value = e.response?.data?.message || 'Kode kupon tidak valid.'
//         appliedCoupon.value = null
//     } finally {
//         couponLoading.value = false
//     }
// }

// function removeCoupon() {
//     couponCode.value = ''
//     appliedCoupon.value = null
//     couponError.value = ''
// }

function onSearchInput() {
    selectedDestination.value = null
    selectedCity.value = ''
    shippingOptions.value = []
    selectedShipping.value = null
    shippingCacheKey.value = null
    manualShipping.value = null
    activeIndex.value = -1
    itemRefs.value = []

    if (searchTimeout) clearTimeout(searchTimeout)
    const query = searchQuery.value.trim()
    if (query.length < 3) {
        searchResults.value = []
        showDropdown.value = false
        return
    }

    showDropdown.value = true
    loadingSearch.value = true
    searchTimeout = setTimeout(async () => {
        try {
            const res = await api.get('/shipping/search', {
                params: { q: query }
            })
            searchResults.value = res.data.data || res.data
            activeIndex.value = -1
            itemRefs.value = []
        } catch {
            searchResults.value = []
        } finally {
            loadingSearch.value = false
        }
    }, 400)
}

function onArrowDown() {
    if (!showDropdown.value || !searchResults.value.length) return
    activeIndex.value = Math.min(activeIndex.value + 1, searchResults.value.length - 1)
    nextTick(() => {
        const el = itemRefs.value[activeIndex.value]
        if (el && typeof el.scrollIntoView === 'function') el.scrollIntoView({ block: 'nearest' })
    })
}

function onArrowUp() {
    if (!showDropdown.value || !searchResults.value.length) return
    activeIndex.value = Math.max(activeIndex.value - 1, 0)
    nextTick(() => {
        const el = itemRefs.value[activeIndex.value]
        if (el && typeof el.scrollIntoView === 'function') el.scrollIntoView({ block: 'nearest' })
    })
}

function onEnter() {
    if (showDropdown.value && activeIndex.value >= 0 && searchResults.value[activeIndex.value]) {
        selectDestination(searchResults.value[activeIndex.value])
    }
}

function selectDestination(destination) {
    selectedDestination.value = destination
    searchQuery.value = destination.label
    showDropdown.value = false
    selectedCity.value = destination.id
    searchResults.value = []
    selectedShipping.value = null
    activeIndex.value = -1
    itemRefs.value = []
    fetchShippingCost()
}

// Close search dropdown on click outside — cleanup saat component unmount
function handleClickOutside(e) {
    if (!e.target.closest('.location-search-container')) {
        showDropdown.value = false
    }
}
if (typeof window !== 'undefined') {
    window.addEventListener('click', handleClickOutside)
    onUnmounted(() => {
        window.removeEventListener('click', handleClickOutside)
        if (searchTimeout) clearTimeout(searchTimeout)
    })
}

onMounted(() => {
    fetchSettings()
})

async function fetchShippingCost() {
    if (!selectedCity.value) return
    loadingShipping.value = true
    shippingError.value = ''
    shippingOptions.value = []
    manualShipping.value = null
    try {
        const dest = selectedDestination.value
        const res = await api.post('/shipping/cost', {
            destination: selectedCity.value,
            weight: checkoutItems.value.reduce((sum, i) => sum + (i.weight || 300) * i.quantity, 0) || 500,
            area_id:     dest?.area_id     || undefined,
            postal_code: dest?.postal_code || undefined,
        })

        // Handle manual fallback — semua provider gagal
        if (res.data.manual) {
            manualShipping.value = res.data.whatsapp
            return
        }

        const results = res.data.data || res.data
        shippingCacheKey.value = res.data.cache_key || null
        shippingOptions.value = Array.isArray(results) ? results : []
        console.log('[shipping] raw response:', JSON.stringify(res.data, null, 2))
        console.log('[shipping] options:', shippingOptions.value.map(o => o.code + ' - ' + o.courier))
        if (!shippingOptions.value.length) {
            shippingError.value = 'Tidak ada layanan pengiriman tersedia untuk tujuan ini.'
        }
    } catch {
        shippingError.value = 'Gagal menghitung ongkir. Coba lagi.'
    } finally {
        loadingShipping.value = false
    }
}

async function submitOrder() {
    if (!checkoutItems.value.length) {
        error.value = 'Wah, keranjangmu kosong!'
        return
    }
    if (!shippingCacheKey.value || !selectedShipping.value) {
        error.value = 'Pilih layanan pengiriman terlebih dahulu.'
        return
    }
    if (paymentMethod.value === 'bank_transfer' && !selectedBank.value) {
        error.value = 'Pilih bank tujuan transfer terlebih dahulu.'
        return
    }
    submitting.value = true
    error.value = ''

    // Gabungkan alamat dengan label lokasi tujuan
    const fullAddress = [
        form.shipping_address,
        selectedDestination.value ? selectedDestination.value.label : '',
    ].filter(Boolean).join(', ')

    try {
        const payload = {
            customer_name: form.customer_name,
            customer_email: form.customer_email,
            customer_phone: form.customer_phone,
            shipping_address: fullAddress,
            shipping_cache_key: shippingCacheKey.value,
            shipping_courier: selectedShipping.value.courier,
            shipping_service: selectedShipping.value.service,
            payment_method: paymentMethod.value,
            selected_bank: paymentMethod.value === 'bank_transfer' ? selectedBank.value : null,
            coupon_code: null, // diarsipkan sementara — appliedCoupon.value?.code || null
            items: checkoutItems.value.map(i => ({
                product_id: i.product_id,
                quantity: i.quantity,
            })),
        }

        const res = await api.post('/orders', payload)
        const orderData = res.data.order
        const whatsappNumber = res.data.whatsapp_number
        const whatsappMessage = res.data.whatsapp_message

        clear()

        // Redirect ke WhatsApp dengan pesan otomatis
        if (whatsappNumber && whatsappMessage) {
            const waUrl = `https://wa.me/${whatsappNumber}?text=${whatsappMessage}`
            window.open(waUrl, '_blank')
        }

        router.push(`/order/${orderData.order_number}`)
    } catch (e) {
        const msg = e.response?.data?.message
        error.value = typeof msg === 'string' ? msg : 'Terjadi kesalahan. Coba lagi ya!'
        submitting.value = false
    }
}
</script>
