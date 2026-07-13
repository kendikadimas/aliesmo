import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'

// ── Mocks ──────────────────────────────────────────────────────────────────

// Mock api module
vi.mock('../api', () => ({
    default: {
        get: vi.fn(),
        post: vi.fn(),
    },
}))

// Mock cart store — isi dengan 1 produk default
vi.mock('../cart', () => ({
    useCartStore: () => ({
        items: { value: [{ product_id: 1, name: 'Kemeja Test', price: 150000, quantity: 1, weight: 300 }] },
        total: () => 150000,
        clear: vi.fn(),
    }),
}))

// Mock mock-data formatPrice
vi.mock('../mock-data', () => ({
    formatPrice: (n) => new Intl.NumberFormat('id-ID').format(n),
}))

import api from '../api'
import CheckoutPage from '../pages/CheckoutPage.vue'

// ── Router stub ────────────────────────────────────────────────────────────

const router = createRouter({
    history: createMemoryHistory(),
    routes: [
        { path: '/', component: { template: '<div/>' } },
        { path: '/order/:id', component: { template: '<div/>' } },
        { path: '/terms', component: { template: '<div/>' } },
    ],
})

// ── Helpers ────────────────────────────────────────────────────────────────

function mountCheckout() {
    return mount(CheckoutPage, {
        global: { plugins: [router] },
        attachTo: document.body,
    })
}

const mockSearchResults = [
    { id: 152, label: 'JAKARTA PUSAT, DKI JAKARTA', city_name: 'Jakarta Pusat', province: 'DKI Jakarta', zip_code: '10110', area_id: 'AREA123', postal_code: '10110' },
    { id: 153, label: 'JAKARTA SELATAN, DKI JAKARTA', city_name: 'Jakarta Selatan', province: 'DKI Jakarta', zip_code: '12190', area_id: 'AREA456', postal_code: '12190' },
]

// ── Tests ──────────────────────────────────────────────────────────────────

describe('CheckoutPage — Autocomplete Search', () => {
    beforeEach(() => {
        vi.useFakeTimers()
        vi.clearAllMocks()
    })

    afterEach(() => {
        vi.useRealTimers()
    })

    it('tidak memanggil API jika query kurang dari 3 karakter', async () => {
        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('ja')
        await input.trigger('input')
        vi.runAllTimers()
        await flushPromises()

        expect(api.get).not.toHaveBeenCalled()
        wrapper.unmount()
    })

    it('memanggil API dengan debounce 400ms setelah query >= 3 karakter', async () => {
        api.get.mockResolvedValue({ data: { data: mockSearchResults } })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('jakarta')
        await input.trigger('input')

        // Belum dipanggil sebelum timer habis
        expect(api.get).not.toHaveBeenCalled()

        vi.advanceTimersByTime(400)
        await flushPromises()

        expect(api.get).toHaveBeenCalledWith('/shipping/search', { params: { q: 'jakarta' } })
        wrapper.unmount()
    })

    it('menampilkan dropdown hasil setelah API return data', async () => {
        api.get.mockResolvedValue({ data: { data: mockSearchResults } })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('jakarta')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        const buttons = wrapper.findAll('button[type="button"]').filter(b =>
            b.text().includes('JAKARTA')
        )
        expect(buttons.length).toBe(2)
        wrapper.unmount()
    })

    it('menampilkan loading state di dalam dropdown saat fetch', async () => {
        // API lambat — tidak resolve dulu
        api.get.mockReturnValue(new Promise(() => {}))

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('jakarta')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        expect(wrapper.text()).toContain('Mencari lokasi...')
        wrapper.unmount()
    })

    it('menampilkan pesan tidak ditemukan jika results kosong', async () => {
        api.get.mockResolvedValue({ data: { data: [] } })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('xyznotexist')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        expect(wrapper.text()).toContain('Lokasi tidak ditemukan')
        wrapper.unmount()
    })

    it('selectDestination mengisi input dan menutup dropdown', async () => {
        api.get.mockResolvedValue({ data: { data: mockSearchResults } })
        api.post.mockResolvedValue({ data: { data: [], cache_key: null } })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('jakarta')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        // Klik hasil pertama
        const firstResult = wrapper.findAll('button[type="button"]').find(b =>
            b.text().includes('JAKARTA PUSAT')
        )
        await firstResult.trigger('click')
        await flushPromises()

        expect(input.element.value).toBe('JAKARTA PUSAT, DKI JAKARTA')
        wrapper.unmount()
    })

    it('fetchShippingCost mengirim area_id dan postal_code setelah select', async () => {
        api.get.mockResolvedValue({ data: { data: mockSearchResults } })
        api.post.mockResolvedValue({
            data: {
                data: [{ code: 'jne', courier: 'JNE', service: 'REG', description: 'Reguler', cost: 15000, etd: '2-3' }],
                cache_key: 'shipping:abc123',
                source: 'komerce',
            },
        })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('jakarta')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        const firstResult = wrapper.findAll('button[type="button"]').find(b =>
            b.text().includes('JAKARTA PUSAT')
        )
        await firstResult.trigger('click')
        await flushPromises()

        expect(api.post).toHaveBeenCalledWith('/shipping/cost', expect.objectContaining({
            destination: 152,
            area_id: 'AREA123',
            postal_code: '10110',
        }))
        wrapper.unmount()
    })

    it('menampilkan opsi kurir setelah fetchShippingCost berhasil', async () => {
        api.get.mockResolvedValue({ data: { data: mockSearchResults } })
        api.post.mockResolvedValue({
            data: {
                data: [{ code: 'jne', courier: 'JNE', service: 'REG', description: 'Reguler', cost: 15000, etd: '2-3' }],
                cache_key: 'shipping:abc123',
                source: 'komerce',
            },
        })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('jakarta')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        const firstResult = wrapper.findAll('button[type="button"]').find(b =>
            b.text().includes('JAKARTA PUSAT')
        )
        await firstResult.trigger('click')
        await flushPromises()

        expect(wrapper.text()).toContain('JNE')
        expect(wrapper.text()).toContain('REG')
        wrapper.unmount()
    })

    it('menampilkan tombol WhatsApp saat manual:true dari backend', async () => {
        api.get.mockResolvedValue({ data: { data: mockSearchResults } })
        api.post.mockResolvedValue({
            data: {
                manual: true,
                message: 'Cek ongkir otomatis tidak tersedia.',
                whatsapp: { number: '628138883345', text: 'Halo Admin, tanya ongkir...' },
            },
        })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('jakarta')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        const firstResult = wrapper.findAll('button[type="button"]').find(b =>
            b.text().includes('JAKARTA PUSAT')
        )
        await firstResult.trigger('click')
        await flushPromises()

        expect(wrapper.text()).toContain('Cek ongkir otomatis tidak tersedia')
        expect(wrapper.text()).toContain('Tanya Ongkir via WhatsApp')
        const waLink = wrapper.find('a[href*="wa.me"]')
        expect(waLink.exists()).toBe(true)
        expect(waLink.attributes('href')).toContain('628138883345')
        wrapper.unmount()
    })

    it('keyboard ArrowDown memindahkan activeIndex ke bawah', async () => {
        api.get.mockResolvedValue({ data: { data: mockSearchResults } })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('jakarta')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        await input.trigger('keydown', { key: 'ArrowDown', code: 'ArrowDown' })
        await flushPromises()

        // Item pertama (index 0) harusnya aktif — cek class highlight
        const buttons = wrapper.findAll('button[type="button"]').filter(b =>
            b.text().includes('JAKARTA')
        )
        expect(buttons[0].classes().join(' ')).toMatch(/maroon|slate-600/)
        wrapper.unmount()
    })

    it('keyboard Escape menutup dropdown', async () => {
        api.get.mockResolvedValue({ data: { data: mockSearchResults } })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        await input.setValue('jakarta')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        // Dropdown terbuka
        expect(wrapper.text()).toContain('JAKARTA PUSAT')

        await input.trigger('keydown', { key: 'Escape', code: 'Escape' })
        await flushPromises()

        // Dropdown tertutup
        const buttons = wrapper.findAll('button[type="button"]').filter(b =>
            b.text().includes('JAKARTA PUSAT')
        )
        expect(buttons.length).toBe(0)
        wrapper.unmount()
    })

    it('reset state saat user mengetik ulang setelah pilih destinasi', async () => {
        api.get.mockResolvedValue({ data: { data: mockSearchResults } })
        api.post.mockResolvedValue({
            data: {
                data: [{ code: 'jne', courier: 'JNE', service: 'REG', description: 'Reguler', cost: 15000, etd: '2-3' }],
                cache_key: 'shipping:abc123',
            },
        })

        const wrapper = mountCheckout()
        const input = wrapper.find('input[placeholder*="kota atau kecamatan"]')

        // Pilih destinasi dulu
        await input.setValue('jakarta')
        await input.trigger('input')
        vi.advanceTimersByTime(400)
        await flushPromises()

        const firstResult = wrapper.findAll('button[type="button"]').find(b =>
            b.text().includes('JAKARTA PUSAT')
        )
        await firstResult.trigger('click')
        await flushPromises()

        // Ketik ulang — shipping options harus di-reset
        await input.setValue('su')
        await input.trigger('input')
        await flushPromises()

        expect(wrapper.text()).not.toContain('JNE')
        wrapper.unmount()
    })
})
