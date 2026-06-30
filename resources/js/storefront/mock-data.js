export const categories = [
    { id: 1, name: 'Formal', slug: 'kemeja-formal', products_count: 6 },
    { id: 2, name: 'Casual', slug: 'kemeja-casual', products_count: 8 },
    { id: 3, name: 'Batik', slug: 'kemeja-batik', products_count: 4 },
    { id: 4, name: 'Oxford', slug: 'kemeja-oxford', products_count: 5 },
    { id: 5, name: 'Lengan Pendek', slug: 'kemeja-lengan-pendek', products_count: 7 },
]

export const products = [
    { id: 1, name: 'Kemeja Putih Oxford Premium', slug: 'oxford-premium-white', description: 'Kemeja putih favorit semua orang! Bahan oxford premium yang adem, cocok dipakai ke kantor, kondangan, atau acara formal lainnya. Dijamin kamu bakal pede seharian!', price: 149000, stock: 15, thumbnail: 'https://picsum.photos/seed/kemeja-putih-1/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-putih-1a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-putih-1b/600/800' }], category: { id: 1, name: 'Formal', slug: 'kemeja-formal' } },
    { id: 2, name: 'Kemeja Navy Slim Fit', slug: 'slim-fit-navy', description: 'Buat kamu yang suka tampil rapi dan modern. Potongan slim fit navy ini pas banget di badan, bikin penampilan makin kece tanpa ribet.', price: 169000, stock: 23, thumbnail: 'https://picsum.photos/seed/kemeja-navy-2/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-navy-2a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-navy-2b/600/800' }], category: { id: 1, name: 'Formal', slug: 'kemeja-formal' } },
    { id: 3, name: 'Kemeja Casual Linen', slug: 'casual-linen-beige', description: 'Cuaca panas? Tenang, kemeja linen ini solusinya! Bahannya ringan dan adem banget, cocok buat hangout santai atau jalan-jalan.', price: 139000, stock: 30, thumbnail: 'https://picsum.photos/seed/kemeja-beige-3/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-beige-3a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-beige-3b/600/800' }], category: { id: 2, name: 'Casual', slug: 'kemeja-casual' } },
    { id: 4, name: 'Kemeja Batik Modern', slug: 'batik-modern-cirebon', description: 'Tampil kece dengan sentuhan tradisional? Kemeja batik modern ini jawabannya! Motifnya keren, cocok buat acara semi-formal atau kondangan.', price: 189000, stock: 0, thumbnail: 'https://picsum.photos/seed/kemeja-batik-4/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-batik-4a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-batik-4b/600/800' }], category: { id: 3, name: 'Batik', slug: 'kemeja-batik' } },
    { id: 5, name: 'Kemeja Oxford Biru Stripe', slug: 'oxford-blue-stripe', description: 'Motif stripe biru yang klasik dan gak pernah ketinggalan zaman. Cocok dipaduin sama celana apapun, dijamin anti mainstream!', price: 159000, stock: 18, thumbnail: 'https://picsum.photos/seed/kemeja-stripe-5/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-stripe-5a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-stripe-5b/600/800' }], category: { id: 4, name: 'Oxford', slug: 'kemeja-oxford' } },
    { id: 6, name: 'Kemeja Denim Casual', slug: 'casual-denim-light', description: 'Buat kamu yang suka gaya maskulin dan santai. Bahan denim ringan yang nyaman, cocok buat daily wear atau hangout bareng teman.', price: 149000, stock: 12, thumbnail: 'https://picsum.photos/seed/kemeja-denim-6/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-denim-6a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-denim-6b/600/800' }], category: { id: 2, name: 'Casual', slug: 'kemeja-casual' } },
    { id: 7, name: 'Kemeja White French Cuff', slug: 'formal-white-french-cuff', description: 'Mau tampil beda? Kemeja french cuff ini pilihan tepat! Tambah cufflink biar makin elegan, cocok buat acara spesial.', price: 199000, stock: 8, thumbnail: 'https://picsum.photos/seed/kemeja-french-7/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-french-7a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-french-7b/600/800' }], category: { id: 1, name: 'Formal', slug: 'kemeja-formal' } },
    { id: 8, name: 'Kemeja Lengan Pendek Chambray', slug: 'lengan-pendek-chambray', description: 'Pas buat iklim tropis! Lengan pendek bahan chambray yang ringan, tetap gaya walau gerah. Wajib punya buat koleksi kamu!', price: 119000, stock: 25, thumbnail: 'https://picsum.photos/seed/kemeja-chambray-8/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-chambray-8a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-chambray-8b/600/800' }], category: { id: 5, name: 'Lengan Pendek', slug: 'kemeja-lengan-pendek' } },
    { id: 9, name: 'Kemeja Batik Parang Solo', slug: 'batik-solo-parang', description: 'Motif batik Parang yang ikonik dengan warna hangat. Cocok buat kamu yang mau tampil tradisional tapi tetap stylish.', price: 199000, stock: 10, thumbnail: 'https://picsum.photos/seed/kemeja-batik-9/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-batik-9a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-batik-9b/600/800' }], category: { id: 3, name: 'Batik', slug: 'kemeja-batik' } },
    { id: 10, name: 'Kemeja Oxford Pink', slug: 'oxford-pink-classic', description: 'Berani tampil beda? Pink klasik ini sophisticated banget! Dijamin kamu bakal jadi pusat perhatian dengan gaya berani dan percaya diri.', price: 159000, stock: 14, thumbnail: 'https://picsum.photos/seed/kemeja-pink-10/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-pink-10a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-pink-10b/600/800' }], category: { id: 4, name: 'Oxford', slug: 'kemeja-oxford' } },
    { id: 11, name: 'Kemeja Grey Slim Fit', slug: 'slim-fit-light-grey', description: 'Warna grey yang versatile, bisa dipadu apapun! Potongan slim fit bikin kamu tampil lebih rapi dan profesional.', price: 159000, stock: 20, thumbnail: 'https://picsum.photos/seed/kemeja-grey-11/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-grey-11a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-grey-11b/600/800' }], category: { id: 1, name: 'Formal', slug: 'kemeja-formal' } },
    { id: 12, name: 'Kemeja Plaid Merah', slug: 'casual-plaid-red', description: 'Buat kamu yang suka gaya flannel khas anak muda! Motif plaid merah yang keren, cocok buat hangout santai atau date.', price: 139000, stock: 0, thumbnail: 'https://picsum.photos/seed/kemeja-plaid-12/600/800', images: [{ path: 'https://picsum.photos/seed/kemeja-plaid-12a/600/800' }, { path: 'https://picsum.photos/seed/kemeja-plaid-12b/600/800' }], category: { id: 2, name: 'Casual', slug: 'kemeja-casual' } },
]

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

export function getProductBySlug(slug) {
    return products.find(p => p.slug === slug) || null
}

export function getCategoryBySlug(slug) {
    return categories.find(c => c.slug === slug) || null
}
