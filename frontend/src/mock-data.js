// Mock arrays removed — data now comes from API
// GET /api/v1/categories  →  categoriesList
// GET /api/v1/products    →  products
// GET /api/v1/banners     →  banners
// GET /api/v1/settings    →  site settings (announcements, stats, WA number, etc.)

export const ORDER_STATUS_LABELS = {
    pending: 'Menunggu Pembayaran',
    paid: 'Lunas',
    processing: 'Diproses',
    shipped: 'Dikirim',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    expired: 'Kadaluarsa',
}

export function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price)
}
