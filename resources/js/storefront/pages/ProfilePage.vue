<template>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 lg:py-16">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-8">
            <div>
                <p class="text-xs font-bold tracking-[0.22em] uppercase text-maroon mb-2">Akun Saya</p>
                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-charcoal">Profil pengguna</h1>
                <p class="mt-2 text-sm text-charcoal/60">Kelola informasi akun, password, dan lihat riwayat pesanan kamu.</p>
            </div>
            <button @click="handleLogout" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-charcoal text-white text-xs font-bold uppercase tracking-wider hover:bg-maroon transition-colors">
                Keluar akun
            </button>
        </div>

        <div class="grid lg:grid-cols-[240px_1fr] gap-6 lg:gap-8">
            <aside class="bg-white border border-maroon-100 rounded-2xl p-2 h-fit shadow-sm">
                <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold transition-colors" :class="activeTab === tab.key ? 'bg-maroon text-white' : 'text-charcoal/70 hover:bg-maroon-50 hover:text-maroon'">
                    <span>{{ tab.label }}</span>
                    <span v-if="tab.key === 'orders' && orders.length" class="text-[10px] rounded-full px-2 py-0.5" :class="activeTab === tab.key ? 'bg-white/20 text-white' : 'bg-maroon-50 text-maroon'">{{ orders.length }}</span>
                </button>
            </aside>

            <section class="min-h-[420px]">
                <div v-if="loading" class="bg-white border border-maroon-100 rounded-2xl p-8 text-sm text-charcoal/60">
                    Memuat data akun...
                </div>

                <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-2xl p-6 text-sm text-red-700">
                    {{ error }}
                </div>

                <div v-else class="space-y-6">
                    <div v-if="activeTab === 'profile'" class="bg-white border border-maroon-100 rounded-2xl p-5 sm:p-7 shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-14 h-14 rounded-full bg-maroon text-white flex items-center justify-center text-xl font-bold">
                                {{ profileInitial }}
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-charcoal">Info profil</h2>
                                <p class="text-sm text-charcoal/50">Data utama akun Aliesmo kamu.</p>
                            </div>
                        </div>

                        <form @submit.prevent="updateProfile" class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Nama Lengkap</label>
                                <input v-model="profileForm.name" required class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal bg-white focus:border-maroon focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Email</label>
                                <input v-model="profileForm.email" type="email" required class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm text-charcoal bg-white focus:border-maroon focus:outline-none transition-colors">
                            </div>
                            <div class="sm:col-span-2 flex justify-end pt-2">
                                <button :disabled="savingProfile" class="w-full sm:w-auto px-6 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all disabled:opacity-50">
                                    {{ savingProfile ? 'Menyimpan...' : 'Simpan Profil' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-if="activeTab === 'password'" class="grid lg:grid-cols-2 gap-6">
                        <div class="bg-white border border-maroon-100 rounded-2xl p-5 sm:p-7 shadow-sm">
                            <h2 class="text-xl font-bold text-charcoal">Ganti password</h2>
                            <p class="mt-1 text-sm text-charcoal/50">Gunakan jika kamu masih ingat password saat ini.</p>

                            <form @submit.prevent="updatePassword" class="mt-6 space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Password Saat Ini</label>
                                    <input v-model="passwordForm.current_password" type="password" required class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm focus:border-maroon focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Password Baru</label>
                                    <input v-model="passwordForm.password" type="password" required minlength="8" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm focus:border-maroon focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-charcoal/60 mb-1.5">Konfirmasi Password Baru</label>
                                    <input v-model="passwordForm.password_confirmation" type="password" required minlength="8" class="w-full border-2 border-maroon-100 rounded-xl px-4 py-2.5 text-sm focus:border-maroon focus:outline-none">
                                </div>
                                <button :disabled="savingPassword" class="w-full px-6 py-3 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all disabled:opacity-50">
                                    {{ savingPassword ? 'Memproses...' : 'Update Password' }}
                                </button>
                            </form>
                        </div>

                        <div class="bg-charcoal text-white rounded-2xl p-5 sm:p-7 shadow-sm">
                            <h2 class="text-xl font-bold">Lupa password?</h2>
                            <p class="mt-1 text-sm text-white/60">Kami akan kirim link reset password ke email kamu.</p>

                            <form @submit.prevent="sendForgotPassword" class="mt-6 space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-white/60 mb-1.5">Email</label>
                                    <input v-model="forgotEmail" type="email" required class="w-full border-2 border-white/10 rounded-xl px-4 py-2.5 text-sm text-white bg-white/10 placeholder:text-white/30 focus:border-white/40 focus:outline-none">
                                </div>
                                <button :disabled="sendingForgot" class="w-full px-6 py-3 bg-white text-charcoal text-sm font-semibold rounded-xl hover:bg-maroon-50 transition-all disabled:opacity-50">
                                    {{ sendingForgot ? 'Mengirim...' : 'Kirim Link Reset' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div v-if="activeTab === 'orders'" class="bg-white border border-maroon-100 rounded-2xl p-5 sm:p-7 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                            <div>
                                <h2 class="text-xl font-bold text-charcoal">Riwayat pesanan</h2>
                                <p class="text-sm text-charcoal/50">Daftar pesanan yang terhubung dengan akun ini.</p>
                            </div>
                            <button @click="loadOrders" :disabled="loadingOrders" class="px-4 py-2 rounded-xl border border-maroon-100 text-xs font-bold text-maroon hover:bg-maroon-50 disabled:opacity-50">
                                {{ loadingOrders ? 'Memuat...' : 'Refresh' }}
                            </button>
                        </div>

                        <div v-if="orders.length" class="flex flex-col lg:flex-row lg:items-center gap-2 mb-5 rounded-2xl bg-maroon-50/35 border border-maroon-100 p-2.5">
                            <input v-model="orderSearch" type="search" placeholder="Cari order / produk" class="min-w-0 flex-1 border border-maroon-100 rounded-xl px-3 py-2 text-xs text-charcoal bg-white focus:border-maroon focus:outline-none transition-colors">
                            <div class="grid grid-cols-[1fr_1fr_auto] gap-2 lg:flex lg:items-center">
                                <select v-model="orderStatusFilter" class="min-w-0 lg:w-36 border border-maroon-100 rounded-xl px-3 py-2 text-xs text-charcoal bg-white focus:border-maroon focus:outline-none transition-colors">
                                    <option value="all">Semua status</option>
                                    <option v-for="status in availableStatuses" :key="status" :value="status">{{ statusLabel(status) }}</option>
                                </select>
                                <select v-model="orderSort" class="min-w-0 lg:w-40 border border-maroon-100 rounded-xl px-3 py-2 text-xs text-charcoal bg-white focus:border-maroon focus:outline-none transition-colors">
                                    <option value="newest">Terbaru</option>
                                    <option value="oldest">Terlama</option>
                                    <option value="total_desc">Total terbesar</option>
                                    <option value="total_asc">Total terkecil</option>
                                </select>
                                <button @click="resetOrderFilters" class="px-3 py-2 rounded-xl border border-maroon-100 bg-white text-xs font-bold text-charcoal/60 hover:text-maroon transition-colors">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <div v-if="!orders.length" class="py-14 text-center border border-dashed border-maroon-100 rounded-2xl">
                            <p class="text-sm font-semibold text-charcoal">Belum ada pesanan</p>
                            <p class="mt-1 text-xs text-charcoal/50">Pesanan kamu akan muncul di sini setelah checkout.</p>
                            <router-link to="/catalog" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-maroon text-white text-xs font-bold uppercase tracking-wider">Belanja Sekarang</router-link>
                        </div>

                        <div v-else-if="!filteredOrders.length" class="py-12 text-center border border-dashed border-maroon-100 rounded-2xl">
                            <p class="text-sm font-semibold text-charcoal">Tidak ada pesanan yang cocok</p>
                            <p class="mt-1 text-xs text-charcoal/50">Coba ubah kata kunci, status, atau urutan filter.</p>
                            <button @click="resetOrderFilters" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-maroon text-white text-xs font-bold uppercase tracking-wider">Reset Filter</button>
                        </div>

                        <div v-else class="space-y-4">
                            <p class="text-xs font-semibold text-charcoal/50">Menampilkan {{ filteredOrders.length }} dari {{ orders.length }} pesanan</p>
                            <article v-for="order in filteredOrders" :key="order.id || order.order_number" class="border border-maroon-100 rounded-2xl p-4 sm:p-5">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-charcoal">#{{ order.order_number }}</p>
                                        <p class="text-xs text-charcoal/50 mt-1">{{ formatDate(order.created_at) }}</p>
                                    </div>
                                    <div class="flex sm:flex-col sm:items-end gap-2 sm:gap-1">
                                        <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider" :class="statusClass(order.status)">{{ statusLabel(order.status) }}</span>
                                        <span class="text-sm font-bold text-charcoal">{{ formatCurrency(order.total) }}</span>
                                    </div>
                                </div>

                                <div v-if="order.tracking_number" class="mt-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-700">
                                    <span>Resi</span>
                                    <span>{{ order.tracking_number }}</span>
                                </div>

                                <div class="mt-4 divide-y divide-maroon-50">
                                    <div v-for="item in order.items || []" :key="item.id" class="py-2 flex justify-between gap-4 text-sm">
                                        <span class="text-charcoal/70">{{ item.product_name }}<span v-if="item.variant_name" class="text-charcoal/40"> / {{ item.variant_name }}</span> x{{ item.quantity }}</span>
                                        <span class="font-semibold text-charcoal whitespace-nowrap">{{ formatCurrency(item.subtotal) }}</span>
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button @click="openOrderDetail(order)" class="px-4 py-2 rounded-xl bg-charcoal text-white text-xs font-bold uppercase tracking-wider hover:bg-maroon transition-colors">
                                        Lihat Detail
                                    </button>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <Transition name="modal-fade">
            <div v-if="detailOpen" class="fixed inset-0 z-[120] bg-charcoal/55 backdrop-blur-sm px-4 py-6 overflow-y-auto" @click.self="closeOrderDetail">
                <div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <div class="p-5 sm:p-7 border-b border-maroon-100 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold tracking-[0.22em] uppercase text-maroon mb-2">Detail Pesanan</p>
                            <h2 class="text-2xl font-bold text-charcoal">#{{ selectedOrder?.order_number }}</h2>
                            <p class="mt-1 text-sm text-charcoal/50">{{ formatDate(selectedOrder?.created_at) }}</p>
                        </div>
                        <button @click="closeOrderDetail" class="w-10 h-10 rounded-full bg-maroon-50 text-charcoal/60 hover:text-maroon flex items-center justify-center transition-colors" aria-label="Tutup detail pesanan">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div v-if="loadingOrderDetail" class="p-7 text-sm text-charcoal/60">
                        Memuat detail pesanan...
                    </div>

                    <div v-else-if="selectedOrder" class="p-5 sm:p-7 space-y-6">
                        <div class="grid sm:grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-maroon-50/50 border border-maroon-100 p-4">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal/40">Status</p>
                                <span class="mt-2 inline-flex px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider" :class="statusClass(selectedOrder.status)">{{ statusLabel(selectedOrder.status) }}</span>
                            </div>
                            <div class="rounded-2xl bg-maroon-50/50 border border-maroon-100 p-4">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal/40">Pembayaran</p>
                                <p class="mt-2 text-sm font-bold text-charcoal">{{ paymentLabel(selectedOrder.payment_method) }}</p>
                            </div>
                            <div class="rounded-2xl bg-maroon-50/50 border border-maroon-100 p-4">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal/40">Total</p>
                                <p class="mt-2 text-sm font-bold text-charcoal">{{ formatCurrency(selectedOrder.total) }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-maroon-100 p-4 sm:p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                                <div>
                                    <h3 class="text-sm font-bold text-charcoal">Informasi Pengiriman</h3>
                                    <p class="text-xs text-charcoal/50">Data penerima dan alamat pengiriman.</p>
                                </div>
                                <div v-if="selectedOrder.tracking_number" class="flex flex-wrap gap-2">
                                    <button @click="copyTrackingNumber(selectedOrder.tracking_number)" class="px-3 py-2 rounded-xl bg-charcoal text-white text-xs font-bold hover:bg-maroon transition-colors">
                                        Copy Resi
                                    </button>
                                    <a v-if="selectedOrder.tracking_url" :href="selectedOrder.tracking_url" target="_blank" rel="noopener noreferrer" class="px-3 py-2 rounded-xl border border-maroon-100 text-xs font-bold text-maroon hover:bg-maroon-50 transition-colors">
                                        Lacak di Kurir
                                    </a>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-xs font-semibold text-charcoal/40">Nama</p>
                                    <p class="font-semibold text-charcoal">{{ selectedOrder.customer_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-charcoal/40">Email</p>
                                    <p class="font-semibold text-charcoal break-all">{{ selectedOrder.customer_email }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-charcoal/40">Telepon</p>
                                    <p class="font-semibold text-charcoal">{{ selectedOrder.customer_phone || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-charcoal/40">No. Resi</p>
                                    <p class="font-semibold text-charcoal break-all">{{ selectedOrder.tracking_number || 'Belum tersedia' }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-xs font-semibold text-charcoal/40">Alamat</p>
                                    <p class="font-semibold text-charcoal leading-relaxed">{{ selectedOrder.shipping_address }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-maroon-100 p-4 sm:p-5">
                            <h3 class="text-sm font-bold text-charcoal mb-4">Detail Produk</h3>
                            <div class="divide-y divide-maroon-50">
                                <div v-for="item in selectedOrder.items || []" :key="item.id" class="py-3 first:pt-0 last:pb-0 flex justify-between gap-4 text-sm">
                                    <div>
                                        <p class="font-semibold text-charcoal">{{ item.product_name }}</p>
                                        <p class="text-xs text-charcoal/50">{{ item.variant_name || 'Default' }} x{{ item.quantity }} - {{ formatCurrency(item.price) }}</p>
                                    </div>
                                    <p class="font-bold text-charcoal whitespace-nowrap">{{ formatCurrency(item.subtotal) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-zinc-50 border border-zinc-100 p-4 sm:p-5 space-y-2 text-sm">
                            <div class="flex justify-between gap-4"><span class="text-charcoal/55">Subtotal</span><span class="font-semibold text-charcoal">{{ formatCurrency(selectedOrder.subtotal) }}</span></div>
                            <div class="flex justify-between gap-4"><span class="text-charcoal/55">Ongkir</span><span class="font-semibold text-charcoal">{{ formatCurrency(selectedOrder.shipping_cost) }}</span></div>
                            <div v-if="selectedOrder.coupon_discount" class="flex justify-between gap-4"><span class="text-charcoal/55">Diskon<span v-if="selectedOrder.coupon_code"> ({{ selectedOrder.coupon_code }})</span></span><span class="font-semibold text-emerald-700">-{{ formatCurrency(selectedOrder.coupon_discount) }}</span></div>
                            <div class="pt-2 border-t border-zinc-200 flex justify-between gap-4 text-base"><span class="font-bold text-charcoal">Total</span><span class="font-bold text-charcoal">{{ formatCurrency(selectedOrder.total) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api, { clearToken } from '../api'
import { useToast } from '../toast'

const router = useRouter()
const { show: showToast } = useToast()

const tabs = [
    { key: 'profile', label: 'Info Profil' },
    { key: 'password', label: 'Password' },
    { key: 'orders', label: 'Riwayat Pesanan' },
]

const activeTab = ref('profile')
const loading = ref(true)
const loadingOrders = ref(false)
const savingProfile = ref(false)
const savingPassword = ref(false)
const sendingForgot = ref(false)
const error = ref('')
const orders = ref([])
const forgotEmail = ref('')
const orderSearch = ref('')
const orderStatusFilter = ref('all')
const orderSort = ref('newest')
const detailOpen = ref(false)
const selectedOrder = ref(null)
const loadingOrderDetail = ref(false)

const profileForm = reactive({ name: '', email: '' })
const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })

const profileInitial = computed(() => (profileForm.name || profileForm.email || 'A').charAt(0).toUpperCase())
const availableStatuses = computed(() => [...new Set(orders.value.map(order => order.status).filter(Boolean))])
const filteredOrders = computed(() => {
    const query = orderSearch.value.trim().toLowerCase()
    const status = orderStatusFilter.value

    return orders.value
        .filter(order => {
            const matchesStatus = status === 'all' || order.status === status
            const searchable = [
                order.order_number,
                order.customer_name,
                order.customer_email,
                ...(order.items || []).flatMap(item => [item.product_name, item.variant_name]),
            ].filter(Boolean).join(' ').toLowerCase()
            const matchesQuery = !query || searchable.includes(query)
            return matchesStatus && matchesQuery
        })
        .slice()
        .sort((a, b) => {
            if (orderSort.value === 'oldest') return new Date(a.created_at || 0) - new Date(b.created_at || 0)
            if (orderSort.value === 'total_desc') return Number(b.total || 0) - Number(a.total || 0)
            if (orderSort.value === 'total_asc') return Number(a.total || 0) - Number(b.total || 0)
            return new Date(b.created_at || 0) - new Date(a.created_at || 0)
        })
})

onMounted(async () => {
    if (!localStorage.getItem('token')) {
        router.replace('/login')
        return
    }

    await loadProfile()
    await claimGuestOrders()
    await loadOrders()
    loading.value = false
})

async function loadProfile() {
    try {
        const res = await api.get('/me/profile')
        const data = res.data.data || {}
        profileForm.name = data.name || ''
        profileForm.email = data.email || ''
        forgotEmail.value = data.email || ''
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal memuat profil.'
    }
}

async function loadOrders() {
    loadingOrders.value = true
    try {
        const res = await api.get('/me/orders')
        orders.value = res.data.data || []
    } catch (e) {
        showToast(e.response?.data?.message || 'Gagal memuat riwayat pesanan.', 'error')
    } finally {
        loadingOrders.value = false
    }
}

async function claimGuestOrders() {
    try {
        const res = await api.post('/me/orders/claim')
        if (res.data.claimed_count > 0) {
            showToast(res.data.message || 'Pesanan guest berhasil diklaim ke akun kamu.')
        }
    } catch (e) {
        // Riwayat tetap dimuat walaupun claim gagal.
    }
}

async function updateProfile() {
    savingProfile.value = true
    try {
        const res = await api.put('/me/profile', profileForm)
        const data = res.data.data || {}
        profileForm.name = data.name || profileForm.name
        profileForm.email = data.email || profileForm.email
        forgotEmail.value = profileForm.email
        showToast(res.data.message || 'Profil berhasil diperbarui.')
    } catch (e) {
        showToast(e.response?.data?.message || 'Gagal memperbarui profil.', 'error')
    } finally {
        savingProfile.value = false
    }
}

async function updatePassword() {
    savingPassword.value = true
    try {
        const res = await api.put('/me/password', passwordForm)
        passwordForm.current_password = ''
        passwordForm.password = ''
        passwordForm.password_confirmation = ''
        showToast(res.data.message || 'Password berhasil diperbarui.')
    } catch (e) {
        showToast(e.response?.data?.message || 'Gagal memperbarui password.', 'error')
    } finally {
        savingPassword.value = false
    }
}

async function sendForgotPassword() {
    sendingForgot.value = true
    try {
        const res = await api.post('/auth/forgot-password', { email: forgotEmail.value })
        showToast(res.data.message || 'Link reset password sudah dikirim.')
    } catch (e) {
        showToast(e.response?.data?.message || 'Gagal mengirim link reset password.', 'error')
    } finally {
        sendingForgot.value = false
    }
}

async function handleLogout() {
    try { await api.post('/auth/logout') } catch (e) {}
    clearToken()
    showToast('Berhasil keluar dari akun.')
    router.replace('/')
}

function resetOrderFilters() {
    orderSearch.value = ''
    orderStatusFilter.value = 'all'
    orderSort.value = 'newest'
}

async function openOrderDetail(order) {
    detailOpen.value = true
    selectedOrder.value = order
    loadingOrderDetail.value = true
    try {
        const res = await api.get(`/me/orders/${order.order_number}`)
        selectedOrder.value = res.data.data || order
    } catch (e) {
        showToast(e.response?.data?.message || 'Gagal memuat detail pesanan.', 'error')
    } finally {
        loadingOrderDetail.value = false
    }
}

function closeOrderDetail() {
    detailOpen.value = false
    selectedOrder.value = null
}

async function copyTrackingNumber(trackingNumber) {
    try {
        await navigator.clipboard.writeText(trackingNumber)
        showToast('No. resi berhasil disalin.')
    } catch (e) {
        showToast('Gagal menyalin no. resi.', 'error')
    }
}

function paymentLabel(method) {
    return {
        bank_transfer: 'Transfer Bank',
        qris: 'QRIS',
        cod: 'COD',
    }[method] || method || '-'
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0)
}

function formatDate(value) {
    if (!value) return '-'
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value))
}

function statusLabel(status) {
    const labels = {
        pending: 'Menunggu',
        processing: 'Diproses',
        shipped: 'Dikirim',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
    }
    return labels[status] || status || 'Pesanan'
}

function statusClass(status) {
    return {
        pending: 'bg-amber-50 text-amber-700',
        processing: 'bg-blue-50 text-blue-700',
        shipped: 'bg-indigo-50 text-indigo-700',
        completed: 'bg-emerald-50 text-emerald-700',
        cancelled: 'bg-red-50 text-red-700',
    }[status] || 'bg-zinc-50 text-zinc-700'
}
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.18s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>
