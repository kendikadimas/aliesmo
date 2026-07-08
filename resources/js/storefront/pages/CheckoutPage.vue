<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16 overflow-x-hidden">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-charcoal tracking-tight">Checkout</h1>

        <div v-if="submitting" class="py-24 text-center">
            <div class="inline-block w-10 h-10 border-4 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
            <p class="mt-4 text-base text-charcoal/50">Bentar ya, kami lagi proses pesananmu...</p>
        </div>

        <form v-else @submit.prevent="submitOrder" class="mt-6 lg:mt-10 grid md:grid-cols-5 gap-6 md:gap-10 lg:gap-12">
            <div class="md:col-span-3 space-y-6 min-w-0">

                <!-- Data Diri -->
                <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50">
                    <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Data Diri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Nama Lengkap</label>
                            <input v-model="form.customer_name" required placeholder="Masukkan nama kamu" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors">
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Email</label>
                                <input v-model="form.customer_email" type="email" required placeholder="kamu@email.com" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Telepon</label>
                                <input v-model="form.customer_phone" placeholder="0812-xxxx-xxxx" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alamat & Pengiriman -->
                <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50">
                    <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Alamat & Pengiriman</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Alamat Lengkap</label>
                            <textarea v-model="form.shipping_address" required placeholder="Contoh: Jl. Merdeka No. 123, RT 01/RW 02, Kelurahan, Kecamatan" rows="2" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors resize-none"></textarea>
                        </div>

                        <!-- Lokasi Pengiriman (Autocomplete Direct Search) -->
                        <div class="relative location-search-container">
                            <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Kota atau Kecamatan Tujuan</label>
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
                                    class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors"
                                    autocomplete="off"
                                    required
                                >
                                <div v-if="loadingSearch" class="absolute right-3 top-3">
                                    <div class="w-4 h-4 border-2 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
                                </div>
                            </div>

                            <!-- Search Results Dropdown -->
                            <div v-if="showDropdown && (loadingSearch || searchResults.length || searchQuery.trim().length >= 3)"
                                class="absolute left-0 right-0 z-50 w-full mt-1 bg-white border-2 border-maroon-50 rounded-xl shadow-lg max-h-60 overflow-y-auto">

                                <!-- Loading state di dalam dropdown -->
                                <div v-if="loadingSearch" class="flex items-center gap-2 px-4 py-3 text-xs text-charcoal/50">
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
                                        class="w-full text-left px-4 py-3 text-xs border-b border-maroon-50 last:border-0 transition-colors"
                                        :class="activeIndex === index
                                            ? 'bg-maroon-50/70 text-charcoal'
                                            : 'text-charcoal hover:bg-maroon-50/50'"
                                    >
                                        <span class="font-semibold">{{ item.label }}</span>
                                    </button>
                                </template>

                                <!-- Empty state -->
                                <div v-else-if="!loadingSearch && searchQuery.trim().length >= 3"
                                    class="px-4 py-4 text-xs text-charcoal/50 text-center">
                                    Lokasi tidak ditemukan. Coba ketik nama kota/kecamatan lain.
                                </div>
                            </div>
                        </div>

                        <!-- Layanan Pengiriman - tampil otomatis setelah lokasi dipilih -->
                        <div v-if="selectedCity">
                            <div v-if="loadingShipping" class="flex items-center gap-2 text-xs text-charcoal/50 py-3">
                                <div class="w-4 h-4 border-2 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
                                Mencari layanan pengiriman tersedia...
                            </div>

                            <div v-else-if="shippingOptions.length">
                                <label class="block text-xs font-semibold text-charcoal/60 mb-2">Pilih Layanan Pengiriman</label>
                                <div class="space-y-2">
                                    <label v-for="opt in shippingOptions" :key="opt.code + '-' + opt.service"
                                        class="flex items-center justify-between p-3 rounded-xl border-2 cursor-pointer transition-all"
                                        :class="selectedShipping?.code === opt.code && selectedShipping?.service === opt.service
                                            ? 'border-maroon bg-maroon-50/30'
                                            : 'border-maroon-100 hover:border-maroon-200'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" :value="opt" v-model="selectedShipping" class="accent-maroon">
                                            <div>
                                                <p class="text-xs font-bold text-charcoal">{{ opt.courier }}</p>
                                                <p class="text-xs text-charcoal/60">{{ opt.service }} · {{ opt.description }}</p>
                                                <p class="text-xs text-charcoal/40">Estimasi {{ opt.etd || '-' }} hari</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-bold text-maroon shrink-0 ml-2">Rp{{ formatPrice(opt.cost || 0) }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Manual fallback — tampil tombol WA jika semua provider gagal -->
                            <div v-else-if="manualShipping" class="p-4 bg-amber-50 rounded-xl border-2 border-amber-200">
                                <p class="text-xs font-semibold text-amber-800 mb-1">Cek ongkir otomatis tidak tersedia</p>
                                <p class="text-xs text-amber-700 mb-3">Hubungi admin untuk konfirmasi ongkir ke tujuanmu.</p>
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
            <div class="md:col-span-2 min-w-0">
                <div class="bg-white p-6 lg:p-8 rounded-2xl border-2 border-maroon-50 md:sticky md:top-28">
                    <h2 class="text-sm font-bold text-charcoal tracking-wide mb-6">Ringkasan Pesanan</h2>
                    <div class="space-y-3">
                        <div v-for="item in selectedItems" :key="item.cart_key || item.product_id" class="rounded-xl border border-maroon-100 bg-maroon-50/20 p-3">
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-[72px] shrink-0 rounded-lg bg-white border border-maroon-100 overflow-hidden">
                                    <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-maroon-200/70 text-lg font-bold">A</div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-charcoal truncate">{{ item.name }}</p>
                                    <p v-if="item.variant_name" class="mt-0.5 text-xs font-semibold text-charcoal/45">Varian: {{ item.variant_name }}</p>
                                    <p class="mt-1 text-xs text-charcoal/50">
                                        {{ item.quantity }} produk × Rp{{ formatPrice(item.price) }} / pcs
                                    </p>
                                </div>
                                <span class="ml-auto shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-bold text-maroon border border-maroon-100">Qty {{ item.quantity }}</span>
                            </div>
                            <div class="mt-3 flex justify-between border-t border-maroon-100 pt-2 text-sm">
                                <span class="text-charcoal/50">Subtotal item</span>
                                <span class="font-bold text-charcoal">Rp{{ formatPrice(item.price * item.quantity) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kupon dinonaktifkan sementara -->
                    <div v-if="couponsEnabled" class="mt-4 pt-4 border-t-2 border-maroon-100">
                        <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Kode Kupon</label>
                        <div class="flex gap-2">
                            <input
                                v-model="couponCode"
                                :disabled="!!appliedCoupon || couponLoading"
                                placeholder="Masukkan kode kupon"
                                class="flex-1 border-2 border-maroon-100 rounded-xl px-3 py-2 text-xs text-charcoal placeholder:text-charcoal/30 bg-white focus:border-maroon focus:outline-none transition-colors disabled:opacity-50 uppercase"
                                @keyup.enter="applyCoupon"
                            >
                            <button
                                v-if="!appliedCoupon"
                                type="button"
                                @click="applyCoupon"
                                :disabled="!couponCode || couponLoading"
                                class="px-3 py-2 text-xs font-semibold bg-maroon text-white rounded-xl hover:bg-maroon-600 transition-all disabled:opacity-50"
                            >
                                {{ couponLoading ? '...' : 'Pakai' }}
                            </button>
                            <button
                                v-else
                                type="button"
                                @click="removeCoupon"
                                class="px-3 py-2 text-xs font-semibold border-2 border-maroon-100 text-charcoal/60 rounded-xl hover:border-maroon transition-all"
                            >
                                Hapus
                            </button>
                        </div>
                        <p v-if="couponError" class="text-xs text-red-500 mt-1.5">{{ couponError }}</p>
                        <p v-if="appliedCoupon" class="text-xs text-green-600 mt-1.5 font-medium">
                            Kupon <span class="font-bold">{{ appliedCoupon.code }}</span> berhasil dipakai!
                        </p>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="mt-4 pt-4 border-t-2 border-maroon-100">
                        <label class="block text-xs font-semibold text-charcoal/60 mb-3">Metode Pembayaran</label>
                        <div class="space-y-2">
                            <!-- Transfer Bank -->
                            <label
                                class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                :class="paymentMethod === 'bank_transfer' ? 'border-maroon bg-maroon-50/30' : 'border-maroon-100 hover:border-maroon/40'"
                            >
                                <input type="radio" v-model="paymentMethod" value="bank_transfer" class="accent-maroon">
                                <div class="flex-1">
                                    <span class="text-xs font-semibold text-charcoal">Transfer Bank</span>
                                    <p class="text-xs text-charcoal/50 mt-0.5">Konfirmasi & kirim bukti via WhatsApp</p>
                                </div>
                                <svg class="w-5 h-5 text-charcoal/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                            </label>
                            <!-- QRIS -->
                            <label
                                class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                :class="paymentMethod === 'qris' ? 'border-maroon bg-maroon-50/30' : 'border-maroon-100 hover:border-maroon/40'"
                            >
                                <input type="radio" v-model="paymentMethod" value="qris" class="accent-maroon">
                                <div class="flex-1">
                                    <span class="text-xs font-semibold text-charcoal">QRIS</span>
                                    <p class="text-xs text-charcoal/50 mt-0.5">Scan QR, kirim bukti via WhatsApp</p>
                                </div>
                                <svg class="w-5 h-5 text-charcoal/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 18.75h.75v.75h-.75v-.75zM18.75 13.5h.75v.75h-.75v-.75zM18.75 18.75h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/></svg>
                            </label>
                            <!-- COD -->
                            <label
                                class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                :class="paymentMethod === 'cod' ? 'border-maroon bg-maroon-50/30' : 'border-maroon-100 hover:border-maroon/40'"
                            >
                                <input type="radio" v-model="paymentMethod" value="cod" class="accent-maroon">
                                <div class="flex-1">
                                    <span class="text-xs font-semibold text-charcoal">COD (Bayar di Tempat)</span>
                                    <p class="text-xs text-charcoal/50 mt-0.5">Bayar saat pesanan tiba</p>
                                </div>
                                <svg class="w-5 h-5 text-charcoal/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                            </label>
                        </div>

                        <div class="mt-3 overflow-hidden rounded-xl border-2 border-maroon-100 bg-maroon-50/25">
                            <div class="px-4 py-3 border-b border-maroon-100 flex items-center justify-between gap-3">
                                <span class="text-xs font-bold text-charcoal">Info {{ activePaymentInfo.label }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-maroon">Dipilih</span>
                            </div>

                            <div v-if="paymentMethod === 'bank_transfer'" class="p-4 space-y-3 text-sm">
                                <div class="grid grid-cols-[96px_1fr] gap-3">
                                    <span class="text-xs font-semibold text-charcoal/45">Bank</span>
                                    <span class="font-bold text-charcoal">{{ paymentSettings.payment_bank_name || 'BCA' }}</span>
                                    <span class="text-xs font-semibold text-charcoal/45">No. Rekening</span>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="font-bold text-charcoal break-all">{{ paymentSettings.payment_bank_account_no || '-' }}</span>
                                        <button v-if="paymentSettings.payment_bank_account_no" type="button" @click="copyText(paymentSettings.payment_bank_account_no, 'Nomor rekening disalin.')" class="shrink-0 px-2 py-1 rounded-lg bg-white border border-maroon-100 text-[10px] font-bold text-maroon hover:bg-maroon-50">Copy</button>
                                    </div>
                                    <span class="text-xs font-semibold text-charcoal/45">Atas Nama</span>
                                    <span class="font-bold text-charcoal">{{ paymentSettings.payment_bank_account_name || '-' }}</span>
                                </div>
                                <p class="text-xs text-charcoal/55 leading-relaxed">Transfer sesuai total pesanan, lalu kirim bukti pembayaran lewat WhatsApp setelah order dibuat.</p>
                            </div>

                            <div v-else-if="paymentMethod === 'qris'" class="p-4 space-y-3 text-sm">
                                <div class="flex gap-3">
                                    <div v-if="paymentSettings.payment_qris_image" class="w-24 h-24 rounded-xl bg-white border border-maroon-100 p-2 shrink-0">
                                        <img :src="paymentSettings.payment_qris_image" alt="QRIS" class="w-full h-full object-contain">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-charcoal/45">Nama QRIS</p>
                                        <p class="font-bold text-charcoal">{{ paymentSettings.payment_qris_name || 'Aliesmo Store' }}</p>
                                        <p class="mt-2 text-xs text-charcoal/55 leading-relaxed">Scan QRIS, bayar sesuai total pesanan, lalu kirim bukti pembayaran lewat WhatsApp.</p>
                                        <a v-if="paymentSettings.payment_qris_image" :href="paymentSettings.payment_qris_image" target="_blank" rel="noopener noreferrer" class="inline-block mt-2 text-xs font-bold text-maroon hover:underline">Buka QRIS</a>
                                    </div>
                                </div>
                                <p v-if="!paymentSettings.payment_qris_image" class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2">Gambar QRIS belum diatur admin. Kamu tetap bisa lanjut order dan konfirmasi via WhatsApp.</p>
                            </div>

                            <div v-else class="p-4 text-sm">
                                <p class="font-bold text-charcoal">Bayar saat pesanan tiba</p>
                                <p class="mt-1 text-xs text-charcoal/55 leading-relaxed">Admin akan menghubungi kamu via WhatsApp untuk konfirmasi alamat dan jadwal pengiriman COD.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t-2 border-maroon-100 space-y-2">
                        <div class="flex justify-between text-sm text-charcoal/60">
                            <span>Subtotal</span>
                            <span class="font-medium">Rp{{ formatPrice(selectedTotal()) }}</span>
                        </div>
                        <div v-if="appliedCoupon" class="flex justify-between text-sm text-green-600">
                            <span>Diskon ({{ appliedCoupon.code }})</span>
                            <span class="font-medium">-Rp{{ formatPrice(appliedCoupon.discount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-charcoal/60">
                            <span>Ongkir</span>
                            <span v-if="shippingCost === 0 && !selectedShipping" class="text-charcoal/40 italic text-xs">Pilih kurir dulu</span>
                            <span v-else-if="shippingCost === 0" class="text-green-600 font-medium">Gratis!</span>
                            <span v-else class="font-medium">Rp{{ formatPrice(shippingCost) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-charcoal pt-3 border-t-2 border-maroon-100">
                            <span>Total</span>
                            <span class="text-maroon">Rp{{ formatPrice(grandTotal) }}</span>
                        </div>
                    </div>

                    <div v-if="error" class="mt-4 p-3 bg-red-50 rounded-xl border border-red-200 text-red-700 text-sm">{{ error }}</div>

                    <button type="submit" :disabled="!selectedShipping && selectedCity !== ''" class="w-full mt-6 px-8 py-3.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25 disabled:opacity-50 disabled:cursor-not-allowed">
                        Pesan Sekarang
                    </button>

                    <p class="mt-4 text-xs text-center text-charcoal/40 leading-relaxed">Dengan pesan, kamu setuju sama <router-link to="/terms" class="text-maroon hover:text-maroon-600 underline">syarat & ketentuan</router-link> kita.</p>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../cart'
import { formatPrice } from '../mock-data'
import api from '../api'

const router = useRouter()
const { items, selectedItems, total, selectedTotal, clear } = useCartStore()
const submitting = ref(false)
const error = ref('')

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

// Coupon state
const couponsEnabled = false
const couponCode = ref('')
const appliedCoupon = ref(null)
const couponLoading = ref(false)
const couponError = ref('')

// Metode pembayaran
const paymentMethod = ref('bank_transfer')
const paymentSettings = ref({})
const paymentLabels = {
    bank_transfer: { label: 'Transfer Bank' },
    qris: { label: 'QRIS' },
    cod: { label: 'COD' },
}
const activePaymentInfo = computed(() => paymentLabels[paymentMethod.value] || { label: 'Pembayaran' })

const grandTotal = computed(() => {
    const subtotal = selectedTotal()
    const discount = appliedCoupon.value?.discount || 0
    return Math.max(0, subtotal - discount + shippingCost.value)
})

onMounted(() => {
    loadPaymentSettings()
})

async function loadPaymentSettings() {
    try {
        const res = await api.get('/settings/group/payment')
        paymentSettings.value = res.data.data || {}
    } catch (e) {
        paymentSettings.value = {}
    }
}

async function copyText(text, message) {
    try {
        await navigator.clipboard.writeText(text)
        error.value = ''
        window.__showToast?.(message)
    } catch (e) {
        error.value = 'Gagal menyalin teks.'
    }
}

async function applyCoupon() {
    if (!couponCode.value.trim()) return
    couponLoading.value = true
    couponError.value = ''
    try {
        const res = await api.post('/coupons/validate', {
            code: couponCode.value.toUpperCase(),
            order_total: selectedTotal(),
        })
        appliedCoupon.value = res.data.coupon
    } catch (e) {
        couponError.value = e.response?.data?.message || 'Kode kupon tidak valid.'
        appliedCoupon.value = null
    } finally {
        couponLoading.value = false
    }
}

function removeCoupon() {
    couponCode.value = ''
    appliedCoupon.value = null
    couponError.value = ''
}

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
            console.log('[Checkout] Searching:', query)
            const res = await api.get('/shipping/search', {
                params: { q: query }
            })
            console.log('[Checkout] Search response:', res.data)
            searchResults.value = res.data.data || res.data
            activeIndex.value = -1
            itemRefs.value = []
        } catch (err) {
            console.error('[Checkout] Search error:', err?.response?.status, err?.response?.data || err?.message)
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

async function fetchShippingCost() {
    if (!selectedCity.value) return
    loadingShipping.value = true
    shippingError.value = ''
    shippingOptions.value = []
    manualShipping.value = null
    try {
        const dest = selectedDestination.value
        const payload = {
            destination: selectedCity.value,
            weight: selectedItems.value.reduce((sum, i) => sum + (i.weight || 300) * i.quantity, 0) || 500,
            area_id:     dest?.area_id     || undefined,
            postal_code: dest?.postal_code || undefined,
        }
        console.log('[Checkout] fetchShippingCost payload:', JSON.stringify(payload))
        const res = await api.post('/shipping/cost', payload)
        console.log('[Checkout] fetchShippingCost response:', JSON.stringify(res.data))

        // Handle manual fallback — semua provider gagal
        if (res.data.manual) {
            console.warn('[Checkout] Manual fallback aktif:', res.data.whatsapp)
            manualShipping.value = res.data.whatsapp
            return
        }

        const results = res.data.data || res.data
        shippingCacheKey.value = res.data.cache_key || null
        shippingOptions.value = Array.isArray(results) ? results : []
        console.log('[Checkout] shippingOptions:', shippingOptions.value.length, 'items, cache_key:', shippingCacheKey.value)
        if (!shippingOptions.value.length) {
            shippingError.value = 'Tidak ada layanan pengiriman tersedia untuk tujuan ini.'
        }
    } catch (err) {
        console.error('[Checkout] fetchShippingCost error:', err?.response?.status, err?.response?.data || err?.message)
        shippingError.value = 'Gagal menghitung ongkir. Coba lagi.'
    } finally {
        loadingShipping.value = false
    }
}

async function submitOrder() {
    if (!selectedItems.value.length) {
        error.value = 'Pilih produk yang ingin dipesan dulu ya!'
        return
    }
    if (!shippingCacheKey.value || !selectedShipping.value) {
        error.value = 'Pilih layanan pengiriman terlebih dahulu.'
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
            coupon_code: appliedCoupon.value?.code || null,
            payment_method: paymentMethod.value,
            items: selectedItems.value.map(i => ({
                product_id: i.product_id,
                variant_id: i.variant_id || null,
                quantity: i.quantity,
            })),
        }

        const res = await api.post('/orders', payload)
        const orderData = res.data.order
        const whatsappNumber = res.data.whatsapp_number
        const whatsappMessage = res.data.whatsapp_message

        // Simpan payment_info ke sessionStorage untuk ditampilkan di halaman konfirmasi
        if (res.data.payment_info) {
            sessionStorage.setItem('payment_info', JSON.stringify(res.data.payment_info))
        }

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
