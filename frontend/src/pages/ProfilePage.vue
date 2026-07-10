<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16">
        <h1 class="text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Akun Saya</h1>
        <p class="mt-2 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Kelola profil, keamanan, dan riwayat pesananmu</p>

        <div v-if="!isLoggedIn" class="py-24 text-center">
            <UserIcon class="w-16 h-16 mx-auto text-ink-20 dark:text-[#303032]" />
            <h2 class="mt-4 text-xl font-bold text-charcoal dark:text-[#f0eeeb]">Login dulu yuk!</h2>
            <router-link to="/login" class="inline-block mt-6 px-8 py-3 bg-ink dark:bg-[#f0eeeb] text-white dark:text-[#161618] text-sm font-semibold rounded-xl hover:bg-ink-60 dark:hover:bg-[#d0ceca] transition-all shadow-lg">
                Login Sekarang
            </router-link>
        </div>

        <div v-else-if="loadingProfile" class="mt-8 space-y-6">
            <!-- tab bar placeholder -->
            <div class="flex gap-2 border-b-2 border-maroon-50 dark:border-[#303032] pb-1">
                <SkeletonLoader v-for="t in 4" :key="t" :loading="true" :radius="8" height="32px" width="90px" />
            </div>
            <!-- profile card placeholder -->
            <SkeletonLoader :loading="true" :radius="16" height="280px" width="100%" class="max-w-2xl" />
            <!-- second card -->
            <SkeletonLoader :loading="true" :radius="16" height="180px" width="100%" class="max-w-2xl" />
        </div>

        <div v-else>
            <!-- Tab Navigation -->
            <div class="mt-8 flex gap-1 border-b-2 border-maroon-50 dark:border-[#303032] overflow-x-auto">
                <button v-for="tab in tabs" :key="tab.key"
                    @click="activeTab = tab.key"
                    class="relative px-5 py-3 text-xs font-bold tracking-wide uppercase whitespace-nowrap transition-all"
                    :class="activeTab === tab.key
                        ? 'text-maroon after:absolute after:bottom-[-2px] after:left-0 after:right-0 after:h-[2px] after:bg-maroon'
                        : 'text-charcoal/40 dark:text-[#6a6a6e] hover:text-charcoal dark:hover:text-[#f0eeeb] hover:text-charcoal/70 dark:text-[#d0ceca]/80 dark:hover:text-[#d0ceca]'">
                    {{ tab.label }}
                </button>
            </div>

            <!-- Tab: Profil -->
            <div v-if="activeTab === 'profil'" class="mt-6 max-w-2xl">
                <div class="bg-white dark:bg-[#1c1c1e] p-6 lg:p-8 rounded-2xl border-2 border-ink-10 dark:border-[#303032]">
                    <h2 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide mb-6">Data Diri</h2>
                    <form @submit.prevent="saveProfile" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Nama Lengkap</label>
                            <input v-model="profileForm.name" required
                                class="w-full border-2 border-ink-10 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] focus:border-ink dark:focus:border-[#f0eeeb] dark:border-[#f0eeeb] focus:outline-none transition-colors bg-white dark:bg-[#28282a]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Email</label>
                            <input v-model="profileForm.email" type="email" required
                                class="w-full border-2 border-ink-10 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] focus:border-ink dark:focus:border-[#f0eeeb] dark:border-[#f0eeeb] focus:outline-none transition-colors bg-white dark:bg-[#28282a]">
                        </div>

                        <div v-if="profileError" class="p-3 bg-ink-05 dark:bg-[#242426] dark:bg-ink-80/40 rounded-xl border border-ink-10 dark:border-ink-60 text-ink-60 dark:text-[#8a8a8e] dark:text-ink-20 dark:text-[#303032] text-sm">{{ profileError }}</div>
                        <div v-if="profileSuccess" class="p-3 bg-ink-05 dark:bg-[#242426] dark:bg-ink-80/40 rounded-xl border border-ink-10 dark:border-ink-60 text-ink dark:text-[#f0eeeb] dark:text-ink-05 text-sm font-medium">Profil berhasil diperbarui!</div>

                        <button type="submit" :disabled="profileSaving"
                            class="px-8 py-2.5 bg-ink dark:bg-[#f0eeeb] text-white dark:text-[#161618] text-sm font-semibold rounded-xl hover:bg-ink-60 dark:hover:bg-[#d0ceca] transition-all active:scale-[0.97] shadow-lg disabled:opacity-50">
                            {{ profileSaving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </form>
                </div>

                <!-- Logout -->
                <button @click="handleLogout" class="mt-4 w-full sm:w-auto px-6 py-3 border-2 border-ink-10 text-ink-40 text-sm font-semibold rounded-xl hover:border-ink-60 hover:text-ink-60 dark:text-[#8a8a8e] transition-all">
                    Keluar dari Akun
                </button>
            </div>

            <!-- Tab: Keamanan -->
            <div v-if="activeTab === 'keamanan'" class="mt-6 max-w-2xl">
                <div class="bg-white dark:bg-[#1c1c1e] p-6 lg:p-8 rounded-2xl border-2 border-ink-10 dark:border-[#303032]">
                    <h2 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] tracking-wide mb-6">Ganti Password</h2>
                    <form @submit.prevent="savePassword" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Password Lama</label>
                            <input v-model="passwordForm.current_password" type="password" required
                                class="w-full border-2 border-ink-10 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] focus:border-ink dark:focus:border-[#f0eeeb] dark:border-[#f0eeeb] focus:outline-none transition-colors bg-white dark:bg-[#28282a]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Password Baru</label>
                            <input v-model="passwordForm.password" type="password" required placeholder="Minimal 8 karakter"
                                class="w-full border-2 border-ink-10 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] focus:border-ink dark:focus:border-[#f0eeeb] dark:border-[#f0eeeb] focus:outline-none transition-colors bg-white dark:bg-[#28282a]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-charcoal/60 dark:text-[#8a8a8e] mb-1.5">Ulangi Password Baru</label>
                            <input v-model="passwordForm.password_confirmation" type="password" required
                                class="w-full border-2 border-ink-10 dark:border-[#303032] rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-[#f0eeeb] focus:border-ink dark:focus:border-[#f0eeeb] dark:border-[#f0eeeb] focus:outline-none transition-colors bg-white dark:bg-[#28282a]">
                        </div>

                        <div v-if="passwordError" class="p-3 bg-ink-05 dark:bg-[#242426] dark:bg-ink-80/40 rounded-xl border border-ink-10 dark:border-ink-60 text-ink-60 dark:text-[#8a8a8e] dark:text-ink-20 dark:text-[#303032] text-sm">{{ passwordError }}</div>
                        <div v-if="passwordSuccess" class="p-3 bg-ink-05 dark:bg-[#242426] dark:bg-ink-80/40 rounded-xl border border-ink-10 dark:border-ink-60 text-ink dark:text-[#f0eeeb] dark:text-ink-05 text-sm font-medium">Password berhasil diperbarui!</div>

                        <button type="submit" :disabled="passwordSaving"
                            class="px-8 py-2.5 bg-ink dark:bg-[#f0eeeb] text-white dark:text-[#161618] text-sm font-semibold rounded-xl hover:bg-ink-60 dark:hover:bg-[#d0ceca] transition-all active:scale-[0.97] shadow-lg disabled:opacity-50">
                            {{ passwordSaving ? 'Menyimpan...' : 'Ganti Password' }}
                        </button>
                    </form>
                </div>

                <!-- Lupa Password link -->
                <p class="mt-4 text-sm text-charcoal/50 dark:text-[#8a8a8e]">
                    Lupa password lama?
                    <router-link to="/forgot-password" class="text-maroon hover:text-maroon-600 font-semibold">Reset via email</router-link>
                </p>
            </div>

            <!-- Tab: Pesanan -->
            <div v-if="activeTab === 'pesanan'" class="mt-6">
                <!-- Loading orders -->
                <div v-if="loadingOrders" class="py-16 text-center">
                    <div class="inline-block w-10 h-10 border-4 border-maroon-100 border-t-maroon rounded-full animate-spin"></div>
                    <p class="mt-4 text-base text-charcoal/50 dark:text-[#8a8a8e]">Memuat pesanan...</p>
                </div>

                <!-- Empty state -->
                <div v-else-if="!orders.length" class="py-16 text-center">
                    <svg class="w-16 h-16 mx-auto text-maroon-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <h2 class="mt-4 text-xl font-bold text-charcoal dark:text-[#f0eeeb]">Belum ada pesanan nih</h2>
                    <p class="mt-2 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Yuk belanja sekarang!</p>
                    <router-link to="/?shop=1" class="inline-block mt-6 px-8 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
                        Mulai Belanja
                    </router-link>
                </div>

                <div v-else class="space-y-4">
                    <!-- Banner claim guest orders -->
                    <div v-if="claimableCount > 0" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border-2 border-blue-200 dark:border-blue-800 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Ada {{ claimableCount }} pesanan yang pernah kamu buat sebelum login!</p>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Klik tombol di samping untuk menambahkan pesanan tersebut ke riwayat akunmu.</p>
                        </div>
                        <button @click="claimOrders" :disabled="claiming"
                            class="shrink-0 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50">
                            {{ claiming ? 'Mengklaim...' : 'Klaim Pesanan' }}
                        </button>
                    </div>

                    <!-- Banner sukses claim -->
                    <div v-if="claimedMsg" class="p-4 bg-green-50 dark:bg-green-900/20 rounded-2xl border-2 border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-400 font-medium">
                        {{ claimedMsg }}
                    </div>

                    <!-- Order cards -->
                    <div v-for="order in orders" :key="order.id" class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border-2 border-maroon-50 dark:border-[#303032] hover:border-maroon-100 dark:hover:border-slate-600 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-base font-bold text-charcoal dark:text-[#f0eeeb]">{{ order.order_number }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                        :class="statusClass(order.status)">
                                        {{ statusLabel(order.status) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-charcoal/50 dark:text-[#8a8a8e]">{{ formatDate(order.created_at) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-charcoal/50 dark:text-[#8a8a8e]">Total Pembayaran</p>
                                <p class="text-lg font-bold text-maroon">Rp{{ formatPrice(order.total) }}</p>
                            </div>
                        </div>

                        <!-- Order items -->
                        <div class="border-t border-maroon-100 dark:border-[#303032] pt-4 space-y-2">
                            <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                                <span class="text-charcoal/70 dark:text-[#d0ceca]/80 dark:text-[#8a8a8e]">{{ item.product_name }} <span class="text-charcoal/40 dark:text-[#6a6a6e]">×{{ item.quantity }}</span></span>
                                <span class="font-medium text-charcoal dark:text-[#f0eeeb]">Rp{{ formatPrice(item.subtotal) }}</span>
                            </div>
                            <!-- Diskon kupon — diarsipkan sementara -->
                            <!--
                            <div v-if="order.coupon_discount > 0" class="flex justify-between text-sm text-green-600 dark:text-green-400 pt-1 border-t border-maroon-50 dark:border-[#303032]">
                                <span>Diskon Kupon <span v-if="order.coupon_code" class="font-mono text-xs bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-1.5 py-0.5 rounded ml-1">{{ order.coupon_code }}</span></span>
                                <span class="font-medium">-Rp{{ formatPrice(order.coupon_discount) }}</span>
                            </div>
                            -->
                        </div>

                        <!-- Info Pembayaran -->
                        <div v-if="order.payment_method" class="mt-4 pt-4 border-t border-maroon-100 dark:border-[#303032]">
                            <p class="text-xs font-bold text-charcoal/60 dark:text-[#8a8a8e] tracking-wide mb-2">Info Pembayaran</p>

                            <!-- Bank Transfer -->
                            <div v-if="order.payment_method === 'bank_transfer'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-4 space-y-1.5">
                                <div class="flex items-center gap-2 mb-2">
                                    <BuildingLibraryIcon class="w-4 h-4 text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] shrink-0" />
                                    <span class="text-xs font-bold text-charcoal dark:text-[#f0eeeb]">Transfer Bank</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <p class="text-charcoal/40 dark:text-[#6a6a6e]">Bank</p>
                                        <p class="font-semibold text-charcoal dark:text-[#f0eeeb]">{{ get('payment_bank_name', 'BCA') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-charcoal/40 dark:text-[#6a6a6e]">No. Rekening</p>
                                        <p class="font-semibold text-charcoal dark:text-[#f0eeeb] font-mono">{{ get('payment_bank_account_no', '-') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-charcoal/40 dark:text-[#6a6a6e]">Atas Nama</p>
                                        <p class="font-semibold text-charcoal dark:text-[#f0eeeb]">{{ get('payment_bank_account_name', '-') }}</p>
                                    </div>
                                </div>
                                <p class="text-[10px] text-charcoal/40 dark:text-[#6a6a6e] mt-1">Kirim bukti transfer via WhatsApp setelah pembayaran.</p>
                            </div>

                            <!-- QRIS -->
                            <div v-else-if="order.payment_method === 'qris'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <DevicePhoneMobileIcon class="w-4 h-4 text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] shrink-0" />
                                    <span class="text-xs font-bold text-charcoal dark:text-[#f0eeeb]">QRIS</span>
                                </div>
                                <div v-if="get('payment_qris_image')" class="flex justify-center mb-3">
                                    <img :src="get('payment_qris_image')" alt="QRIS" class="w-48 h-48 object-contain rounded-lg border border-maroon-100 dark:border-[#303032] bg-white dark:bg-[#1c1c1e] p-2">
                                </div>
                                <p class="text-xs text-center font-semibold text-charcoal dark:text-[#f0eeeb]">{{ get('payment_qris_name', 'Aliesmo') }}</p>
                                <p class="text-[10px] text-charcoal/40 dark:text-[#6a6a6e] text-center mt-1">Scan QRIS, lalu kirim bukti pembayaran via WhatsApp.</p>
                            </div>

                            <!-- COD -->
                            <div v-else-if="order.payment_method === 'cod'" class="bg-maroon-50/40 dark:bg-[#28282a]/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <CreditCardIcon class="w-4 h-4 text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] shrink-0" />
                                    <span class="text-xs font-bold text-charcoal dark:text-[#f0eeeb]">COD (Bayar di Tempat)</span>
                                </div>
                                <p class="text-xs text-charcoal/60 dark:text-[#8a8a8e]">Bayar langsung saat pesanan tiba. Hubungi via WhatsApp untuk penjadwalan.</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 pt-4 border-t border-maroon-100 dark:border-[#303032] flex flex-col sm:flex-row gap-3">
                            <router-link :to="`/order/${order.order_number}`" class="flex-1 text-center px-6 py-2.5 border-2 border-maroon text-maroon text-sm font-semibold rounded-xl hover:bg-maroon hover:text-white transition-all">
                                Lihat Detail
                            </router-link>
                            <a :href="waLink(order)" target="_blank" rel="noopener"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-2.5 bg-[#25D366] text-white text-sm font-semibold rounded-xl hover:bg-[#1ebe5d] transition-all">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Hubungi Admin
                            </a>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination && pagination.last_page > 1" class="flex justify-center gap-2 mt-8">
                        <button @click="loadOrders(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="px-4 py-2 border-2 border-maroon-100 dark:border-[#303032] text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] text-sm font-semibold rounded-xl hover:border-maroon dark:hover:border-maroon transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                            Prev
                        </button>
                        <span class="px-4 py-2 text-sm text-charcoal/60 dark:text-[#8a8a8e]">
                            Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
                        </span>
                        <button @click="loadOrders(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="px-4 py-2 border-2 border-maroon-100 dark:border-[#303032] text-charcoal dark:text-[#f0eeeb] dark:text-[#d0ceca] text-sm font-semibold rounded-xl hover:border-maroon dark:hover:border-maroon transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted, watch } from 'vue'
import { UserIcon, BuildingLibraryIcon, DevicePhoneMobileIcon, CreditCardIcon } from '@heroicons/vue/24/outline'
import { useRouter, useRoute } from 'vue-router'
import api, { clearToken } from '../api'
import { formatPrice } from '../mock-data'
import { useSettings } from '../useSettings'

const router = useRouter()
const route = useRoute()
const { fetchSettings, get } = useSettings()

const isLoggedIn = ref(false)
const loadingProfile = ref(true)

// Tab system
const tabs = [
    { key: 'profil', label: 'Profil' },
    { key: 'keamanan', label: 'Keamanan' },
    { key: 'pesanan', label: 'Pesanan' },
]
const activeTab = ref('profil')

// Profile form
const profileForm = reactive({ name: '', email: '' })
const profileSaving = ref(false)
const profileError = ref('')
const profileSuccess = ref(false)

// Password form
const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })
const passwordSaving = ref(false)
const passwordError = ref('')
const passwordSuccess = ref(false)

// Orders
const orders = ref([])
const pagination = ref(null)
const loadingOrders = ref(false)
const claimableCount = ref(0)
const claiming = ref(false)
const claimedMsg = ref('')
let ordersLoaded = false

// Watch tab changes — load orders lazily
watch(activeTab, (tab) => {
    // Update URL query
    const query = tab === 'profil' ? {} : { tab }
    router.replace({ query })

    if (tab === 'pesanan' && !ordersLoaded) {
        ordersLoaded = true
        loadOrders()
        checkClaimable()
    }
})

onMounted(async () => {
    isLoggedIn.value = !!localStorage.getItem('token')
    if (!isLoggedIn.value) { loadingProfile.value = false; return }

    // Fetch settings for payment info
    fetchSettings()

    try {
        const res = await api.get('/me/profile')
        const user = res.data.data || res.data
        profileForm.name = user.name
        profileForm.email = user.email
    } catch {
        isLoggedIn.value = false
    } finally {
        loadingProfile.value = false
    }

    // Check initial tab from query param
    const tabParam = route.query.tab
    if (tabParam && tabs.some(t => t.key === tabParam)) {
        activeTab.value = tabParam
    }
})

// Profile methods
async function saveProfile() {
    profileSaving.value = true
    profileError.value = ''
    profileSuccess.value = false
    try {
        await api.put('/me/profile', { name: profileForm.name, email: profileForm.email })
        profileSuccess.value = true
        setTimeout(() => { profileSuccess.value = false }, 3000)
    } catch (e) {
        const errors = e.response?.data?.errors
        profileError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Gagal menyimpan.')
    } finally {
        profileSaving.value = false
    }
}

async function savePassword() {
    if (passwordForm.password !== passwordForm.password_confirmation) {
        passwordError.value = 'Password baru dan konfirmasi tidak sama!'
        return
    }
    passwordSaving.value = true
    passwordError.value = ''
    passwordSuccess.value = false
    try {
        await api.put('/me/password', {
            current_password: passwordForm.current_password,
            password: passwordForm.password,
            password_confirmation: passwordForm.password_confirmation,
        })
        passwordSuccess.value = true
        passwordForm.current_password = ''
        passwordForm.password = ''
        passwordForm.password_confirmation = ''
        setTimeout(() => { passwordSuccess.value = false }, 3000)
    } catch (e) {
        passwordError.value = e.response?.data?.message || 'Gagal ganti password.'
    } finally {
        passwordSaving.value = false
    }
}

function waLink(order) {
    const number = import.meta.env.VITE_WHATSAPP_NUMBER || '6285196811722'
    const msg = `Halo admin, saya ingin menanyakan pesanan saya:\n\n*No. Pesanan:* ${order.order_number}\n*Status:* ${order.status}\n*Total:* Rp${formatPrice(order.total)}\n\nMohon bantuannya. Terima kasih!`
    return `https://wa.me/${number}?text=${encodeURIComponent(msg)}`
}

function handleLogout() {
    api.post('/auth/logout').catch(() => {})
    clearToken()
    localStorage.removeItem('user')
    router.push('/login')
}

// Order methods
async function loadOrders(page = 1) {
    loadingOrders.value = true
    try {
        const res = await api.get('/me/orders', { params: { page } })
        orders.value = res.data.data || res.data
        pagination.value = res.data.meta || res.data.pagination || null
    } catch (e) {
        if (e.response?.status === 401) {
            isLoggedIn.value = false
        }
        orders.value = []
    } finally {
        loadingOrders.value = false
    }
}

async function checkClaimable() {
    try {
        const res = await api.get('/me/orders/claimable-count')
        claimableCount.value = res.data.claimable_count || 0
    } catch {
        claimableCount.value = 0
    }
}

async function claimOrders() {
    claiming.value = true
    claimedMsg.value = ''
    try {
        const res = await api.post('/me/orders/claim')
        claimedMsg.value = res.data.message
        claimableCount.value = 0
        await loadOrders()
    } catch (e) {
        claimedMsg.value = e.response?.data?.message || 'Gagal mengklaim pesanan. Coba lagi.'
    } finally {
        claiming.value = false
    }
}

function statusLabel(status) {
    const labels = {
        pending: 'Menunggu Pembayaran',
        paid: 'Dibayar',
        processing: 'Diproses',
        shipped: 'Dikirim',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
        expired: 'Kadaluarsa',
    }
    return labels[status] || status
}

function statusClass(status) {
    const classes = {
        pending: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800/50',
        paid: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50',
        processing: 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/50',
        shipped: 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50',
        completed: 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/50',
        cancelled: 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/50',
        expired: 'bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-[#d0ceca] dark:text-gray-400 border border-gray-200 dark:border-gray-700',
    }
    return classes[status] || 'bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-[#d0ceca] dark:text-gray-400'
}

function formatDate(dateString) {
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date)
}
</script>
