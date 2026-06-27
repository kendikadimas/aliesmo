export const categories = [
    { id: 1, name: 'Kemeja Formal', slug: 'kemeja-formal', products_count: 6 },
    { id: 2, name: 'Kemeja Casual', slug: 'kemeja-casual', products_count: 8 },
    { id: 3, name: 'Kemeja Batik', slug: 'kemeja-batik', products_count: 4 },
    { id: 4, name: 'Kemeja Oxford', slug: 'kemeja-oxford', products_count: 5 },
    { id: 5, name: 'Kemeja Lengan Pendek', slug: 'kemeja-lengan-pendek', products_count: 7 },
]

export const products = [
    { id: 1, name: 'Oxford Premium White', slug: 'oxford-premium-white', sku: 'ALM-001', description: 'Kemeja Oxford putih premium dengan bahan katun twill 100% berkualitas tinggi. Cocok untuk acara formal maupun semi-formal. Dilengkapi dengan fused collar yang kokoh dan jahitan presisi.', price: 249000, stock: 15, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-putih-1/600/800', category: { id: 1, name: 'Kemeja Formal', slug: 'kemeja-formal' }, images: [], created_at: '2025-01-15' },
    { id: 2, name: 'Slim Fit Navy', slug: 'slim-fit-navy', sku: 'ALM-002', description: 'Kemeja navy slim fit dengan potongan modern yang pas di badan. Bahan katun premium yang adem dan nyaman dipakai seharian.', price: 279000, stock: 23, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-navy-2/600/800', category: { id: 1, name: 'Kemeja Formal', slug: 'kemeja-formal' }, images: [], created_at: '2025-02-10' },
    { id: 3, name: 'Casual Linen Beige', slug: 'casual-linen-beige', sku: 'ALM-003', description: 'Kemeja casual bahan linen warna beige yang ringan dan adem. Cocok untuk hangout santai atau acara semi-formal di siang hari.', price: 219000, stock: 30, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-beige-3/600/800', category: { id: 2, name: 'Kemeja Casual', slug: 'kemeja-casual' }, images: [], created_at: '2025-02-20' },
    { id: 4, name: 'Batik Modern Cirebon', slug: 'batik-modern-cirebon', sku: 'ALM-004', description: 'Kemeja batik modern dengan motif Cirebon yang elegan. Perpaduan tradisi dan gaya kontemporer untuk pria masa kini.', price: 299000, stock: 0, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-batik-4/600/800', category: { id: 3, name: 'Kemeja Batik', slug: 'kemeja-batik' }, images: [], created_at: '2025-03-01' },
    { id: 5, name: 'Oxford Blue Stripe', slug: 'oxford-blue-stripe', sku: 'ALM-005', description: 'Kemeja Oxford dengan motif stripe biru navy. Klasik dan mudah dipadankan dengan berbagai warna celana.', price: 259000, stock: 18, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-stripe-5/600/800', category: { id: 4, name: 'Kemeja Oxford', slug: 'kemeja-oxford' }, images: [], created_at: '2025-03-10' },
    { id: 6, name: 'Casual Denim Light', slug: 'casual-denim-light', sku: 'ALM-006', description: 'Kemeja casual berbahan denim ringan warna light blue. Tampilan maskulin yang cocok untuk daily wear.', price: 239000, stock: 12, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-denim-6/600/800', category: { id: 2, name: 'Kemeja Casual', slug: 'kemeja-casual' }, images: [], created_at: '2025-03-15' },
    { id: 7, name: 'Formal White French Cuff', slug: 'formal-white-french-cuff', sku: 'ALM-007', description: 'Kemeja formal putih dengan french cuff (manset lipat) yang elegan. Dilengkapi dengan cufflink untuk tampilan yang lebih mewah.', price: 349000, stock: 8, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-french-7/600/800', category: { id: 1, name: 'Kemeja Formal', slug: 'kemeja-formal' }, images: [], created_at: '2025-04-01' },
    { id: 8, name: 'Lengan Pendek Chambray', slug: 'lengan-pendek-chambray', sku: 'ALM-008', description: 'Kemeja lengan pendek bahan chambray ringan. Cocok untuk iklim tropis, tetap gaya tanpa gerah.', price: 199000, stock: 25, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-chambray-8/600/800', category: { id: 5, name: 'Kemeja Lengan Pendek', slug: 'kemeja-lengan-pendek' }, images: [], created_at: '2025-04-05' },
    { id: 9, name: 'Batik Solo Parang', slug: 'batik-solo-parang', sku: 'ALM-009', description: 'Kemeja batik Solo dengan motif Parang Rusak yang ikonik. Warna coklat dan krem yang hangat dan maskulin.', price: 319000, stock: 10, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-batik-9/600/800', category: { id: 3, name: 'Kemeja Batik', slug: 'kemeja-batik' }, images: [], created_at: '2025-04-10' },
    { id: 10, name: 'Oxford Pink Classic', slug: 'oxford-pink-classic', sku: 'ALM-010', description: 'Kemeja Oxford warna pink klasik yang sophisticated. Warna yang berani namun tetap elegan untuk pria percaya diri.', price: 269000, stock: 14, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-pink-10/600/800', category: { id: 4, name: 'Kemeja Oxford', slug: 'kemeja-oxford' }, images: [], created_at: '2025-04-15' },
    { id: 11, name: 'Slim Fit Light Grey', slug: 'slim-fit-light-grey', sku: 'ALM-011', description: 'Kemeja slim fit warna light grey yang versatile. Bisa dipakai ke kantor maupun acara santai dengan paduan yang tepat.', price: 259000, stock: 20, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-grey-11/600/800', category: { id: 1, name: 'Kemeja Formal', slug: 'kemeja-formal' }, images: [], created_at: '2025-04-20' },
    { id: 12, name: 'Casual Plaid Red', slug: 'casual-plaid-red', sku: 'ALM-012', description: 'Kemeja casual motif plaid merah khas flannel. Cocok untuk tampilan santai yang hangat dan penuh karakter.', price: 229000, stock: 0, is_active: true, thumbnail: 'https://picsum.photos/seed/kemeja-plaid-12/600/800', category: { id: 2, name: 'Kemeja Casual', slug: 'kemeja-casual' }, images: [], created_at: '2025-05-01' },
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
