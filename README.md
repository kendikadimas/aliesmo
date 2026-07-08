# Aliesmo

E-commerce kemeja pria. Backend Laravel 12, frontend Vue 3, admin panel Filament v5. Order flow berbasis WhatsApp tanpa payment gateway.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12, PHP 8.4 |
| Admin Panel | Filament v5 |
| Frontend (storefront) | Vue 3, Vue Router 4, Tailwind CSS v4 |
| Database | MySQL |
| Auth | Laravel Sanctum + Google OAuth (Socialite) |
| Shipping primary | RajaOngkir / Komerce |
| Shipping fallback | Biteship Maps API |
| Order flow | WhatsApp (tanpa Midtrans) |
| Testing backend | PHPUnit 11 |
| Testing frontend | Vitest 3 + Vue Test Utils |

---

## Struktur Direktori

`
aliesmo/
app/
  Filament/Resources/     # Admin panel: Banner, Category, Coupon, Order, Product, Review, SiteSetting, StockMovement, User
  Http/Controllers/Api/   # AuthController, BannerController, CategoryController, CouponController
                          # OrderController, ProductController, ProfileController, ReviewController
                          # ShippingController, SiteSettingController
  Models/                 # Banner, Category, Coupon, Order, OrderItem, Payment, Product, ProductImage
                          # Review, SiteSetting, StockMovement, User
  Services/               # BiteshipService, RajaOngkirService, OrderService, StockService
  Enums/                  # OrderStatus, PaymentStatus, StockMovementType, UserRole
database/
  migrations/             # 20 migration files
  seeders/                # DatabaseSeeder, KemejaProductSeeder, SiteContentSeeder
resources/js/storefront/ # Vue SPA (storefront)
  App.vue                 # Layout utama: header, footer, navbar, cart icon, toast
  pages/
    HomePage.vue          # Hero banner, kategori, produk per kategori
    CatalogPage.vue       # Grid produk, filter, search, sort, load more
    ProductDetailPage.vue # Detail produk, galeri, tambah ke keranjang
    CartPage.vue          # Keranjang belanja
    CheckoutPage.vue      # Form order + autocomplete lokasi + pilih kurir + kupon
    OrderConfirmationPage.vue # Ringkasan order + tombol konfirmasi WA
    LoginPage.vue           # Login manual + Google OAuth
    RegisterPage.vue        # Register manual + Google OAuth
    AuthCallbackPage.vue    # Callback SPA untuk token Google OAuth
    ProfilePage.vue         # Profil, password, lupa password, riwayat pesanan
    TermsPage.vue
routes/
  api.php                 # Semua API endpoint /api/v1/...
  web.php                 # Google OAuth routes + SPA catch-all
tests/
  Feature/                # OrderTest, ProductApiTest, SecurityTest, ShippingTest, StockTest
frontend/                 # Standalone Vue SPA (development/referensi)
  src/tests/             # CheckoutPage.test.js (Vitest)
`

---

## Setup & Instalasi

### Prasyarat

- PHP 8.4+
- MySQL 8+
- Node.js 20+
- Composer 2

### Langkah Instalasi

1. Clone repo dan masuk ke direktori

2. Install dependencies
   composer install
   npm install

3. Copy dan isi file environment
   cp .env.example .env
   php artisan key:generate

4. Buat database MySQL dengan nama aliesmo, lalu jalankan migrasi
   php artisan migrate
   php artisan db:seed

5. Buat symlink storage
   php artisan storage:link

6. Build frontend
   npm run build

7. Jalankan server
   php artisan serve

### Environment Penting

```env
# Database
DB_DATABASE=aliesmo
DB_USERNAME=root
DB_PASSWORD=

# WhatsApp admin toko
WHATSAPP_NUMBER=6285196811722

# Google OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
VITE_BACKEND_URL="${APP_URL}"

# RajaOngkir/Komerce (cek ongkir - primary)
RAJAONGKIR_API_KEY=
RAJAONGKIR_BASE_URL=https://api.rajaongkir.com/starter
RAJAONGKIR_ORIGIN_CITY=570
RAJAONGKIR_DAILY_LIMIT=95

# Biteship (cek ongkir - fallback)
BITESHIP_API_KEY=
BITESHIP_ORIGIN_POSTAL=52371
```

RAJAONGKIR_ORIGIN_CITY=570 adalah Pemalang, Jawa Tengah.
BITESHIP_ORIGIN_POSTAL=52371 adalah kode pos Ulujami, Pemalang.
`WHATSAPP_NUMBER` adalah fallback env. Nilai runtime utama bisa diubah dari admin melalui Site Settings key `whatsapp_number`.

Untuk local development dengan `php artisan serve`, gunakan nilai berikut agar callback Google tidak masuk ke host yang salah:

```env
APP_URL=http://localhost:8000
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
VITE_BACKEND_URL=http://localhost:8000
```

Authorized redirect URI di Google Cloud Console harus sama persis dengan `GOOGLE_REDIRECT_URI`.

---

## Admin Panel

URL: /admin

Fitur yang tersedia:

| Resource | Keterangan |
|---|---|
| Produk | CRUD produk, upload gambar, field berat (gram), adjust stock |
| Kategori | CRUD kategori kemeja |
| Order | Lihat semua pesanan, ubah status, export |
| Kupon | Buat kode diskon, set persentase/nominal, batas pemakaian |
| Banner | Kelola hero banner di homepage |
| Ulasan | Moderasi ulasan produk dari pembeli |
| Pengguna | Manajemen akun user |
| Stock Movement | Riwayat pergerakan stok |
| Site Settings | Konfigurasi teks, stats, announcement bar, kontak |
| Sales Report | Laporan penjualan dengan export Excel |

Widgets dashboard: Pending Orders, Low Stock, Revenue Chart, Top Selling Products, Sales Overview.

Batas produk: maksimal 30 produk aktif (enforced di CreateProduct).

Admin dapat menginput informasi pengiriman pada order melalui action `Update Resi`:
- No. resi (`tracking_number`)
- Link website tracking kurir (`tracking_url`)

Data ini muncul di halaman profil user pada detail riwayat pesanan. User bisa copy no. resi dan membuka link tracking kurir di tab baru.

Nomor WhatsApp untuk redirect order, fallback tanya ongkir, floating WhatsApp, dan halaman konfirmasi diambil dari Site Settings key `whatsapp_number`. Ubah via Admin Panel > Pengaturan Situs > `Nomor WhatsApp Admin`.

---

## API Endpoints

Base URL: /api/v1

### Public

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | /banners | Daftar banner aktif |
| GET | /settings | Semua site settings |
| GET | /settings/group/{group} | Settings per grup |
| GET | /products | Daftar produk (filter: category, search, min_price, max_price, sort, per_page) |
| GET | /products/{slug} | Detail produk |
| GET | /categories | Daftar kategori |
| POST | /orders | Buat pesanan baru |
| GET | /orders/{orderNumber}/status | Status pesanan |
| POST | /orders/track | Lacak pesanan |
| GET | /orders/token/{token} | Lacak pesanan by token |
| POST | /coupons/validate | Validasi kode kupon |
| GET | /products/{slug}/reviews | Ulasan produk |
| GET | /shipping/provinces | Daftar provinsi |
| GET | /shipping/cities/{provinceId} | Daftar kota per provinsi |
| GET | /shipping/search?q={query} | Autocomplete kota/kecamatan tujuan |
| POST | /shipping/cost | Cek ongkos kirim |
| GET | /shipping/couriers | Daftar kurir tersedia |

### Auth

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | /auth/register | Daftar akun |
| POST | /auth/login | Login |
| POST | /auth/forgot-password | Lupa password |
| POST | /auth/reset-password | Reset password |

Google OAuth memakai route web, bukan `/api/v1`:

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | /auth/google/redirect | Mulai login/register dengan Google |
| GET | /auth/google/callback | Callback dari Google, issue Sanctum token |
| GET | /auth/callback | Halaman SPA yang menerima token OAuth |

### Authenticated (Bearer Token)

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | /auth/logout | Logout |
| POST | /auth/logout-all | Logout dari semua perangkat |
| GET | /me/orders | Pesanan milik user |
| GET | /me/orders/{orderNumber} | Detail pesanan milik user |
| DELETE | /me/orders/{orderNumber} | Batalkan pesanan |
| POST | /me/orders/claim | Klaim pesanan guest |
| GET | /me/orders/claimable-count | Jumlah pesanan guest yang bisa diklaim |
| GET | /me/profile | Profil user |
| PUT | /me/profile | Update profil |
| PUT | /me/password | Ubah password |
| POST | /products/{slug}/reviews | Tulis ulasan |

---

## Auth, Profile, dan Riwayat Pesanan

### Login/Register Manual

- Register manual membuat user, issue Sanctum token, lalu frontend menyimpan token di `localStorage`.
- Login manual menghapus token lama user, issue token baru, lalu menjalankan auto-claim guest orders berdasarkan email user.
- Setelah register manual, sistem juga mencoba auto-claim guest orders dengan email yang sama.

### Login/Register Google

- Tombol Google di `LoginPage.vue` dan `RegisterPage.vue` memakai `VITE_BACKEND_URL` agar OAuth selalu dimulai dari backend Laravel.
- Backend route `/auth/google/redirect` mengarah ke Google consent screen via Laravel Socialite.
- Callback `/auth/google/callback` membuat atau menghubungkan user berdasarkan `google_id` atau email.
- Backend membuat Sanctum token, menjalankan auto-claim guest orders berdasarkan email Google, lalu redirect ke `/auth/callback?token=...`.
- Route `/auth/callback` harus mengembalikan SPA `welcome.blade.php`, karena Vue Router yang memproses token melalui `AuthCallbackPage.vue`.

### User Profile

Halaman `/profile` tersedia untuk user yang sudah login.

Fitur:
- Info profil: lihat dan update nama/email.
- Ganti password: membutuhkan password lama.
- Lupa password: kirim link reset password ke email.
- Riwayat pesanan: menampilkan data dari `/api/v1/me/orders`.
- Detail riwayat pesanan: menampilkan item produk, alamat, kontak, pembayaran, total, no. resi, dan link tracking kurir.
- Filter dan sort riwayat pesanan:
  - Cari berdasarkan nomor order, nama/email customer, nama produk, atau varian.
  - Filter berdasarkan status order yang tersedia.
  - Sort terbaru, terlama, total terbesar, atau total terkecil.
- Logout akun.

Catatan untuk user Google: jika user dibuat dari Google dan belum punya password lokal, gunakan fitur lupa password untuk membuat password lokal pertama kali.

### Auto-Claim Guest Orders

Order mendukung guest checkout. Field `orders.user_id` nullable.

Auto-claim dilakukan pada beberapa titik:
- Saat login manual berhasil.
- Saat register manual berhasil.
- Saat login/register Google berhasil.
- Saat user membuka halaman `/profile`, frontend memanggil `/api/v1/me/orders/claim` sebelum memuat `/api/v1/me/orders`.

Syarat order guest bisa diklaim:
- `orders.user_id` masih `null`.
- `orders.customer_email` sama dengan email user yang login.

Jika user checkout dalam keadaan login, endpoint order publik tetap menerima Bearer token. `OrderService` membaca user opsional dari Sanctum token dan langsung menyimpan `user_id`, sehingga order langsung muncul di riwayat tanpa perlu claim.

### Detail Pesanan dan Tracking

Setiap pesanan di halaman `/profile` memiliki tombol `Lihat Detail`.

Detail yang ditampilkan:
- Nomor order dan tanggal order.
- Status order.
- Metode pembayaran.
- Data penerima: nama, email, nomor telepon.
- Alamat pengiriman.
- Detail produk: nama produk, varian, quantity, harga satuan, subtotal item.
- Ringkasan pembayaran: subtotal, ongkir, diskon kupon, total.
- No. resi jika sudah diinput admin.
- Link tracking kurir jika sudah diinput admin.

Admin mengisi no. resi dan link tracking dari Admin Panel > Order > action `Update Resi`.

Field database terkait:
- `orders.tracking_number`
- `orders.tracking_url`

User bisa:
- Copy no. resi dengan tombol `Copy Resi`.
- Membuka website tracking kurir lewat tombol `Lacak di Kurir`.

---

## Varian Produk

Produk dapat memiliki varian yang dikelola dari Admin Panel > Produk.

Data varian yang didukung:
- Nama varian.
- SKU varian.
- Harga varian.
- Stok varian.
- Berat varian.
- Status aktif/nonaktif.
- Urutan tampil.

API produk memuat varian aktif pada endpoint:
- `GET /api/v1/products`
- `GET /api/v1/products/{slug}`

Frontend storefront memakai varian sebagai berikut:
- Halaman detail produk menampilkan pilihan varian jika produk memiliki varian aktif.
- Harga di detail produk berubah mengikuti varian yang dipilih.
- Stok di detail produk berubah mengikuti varian yang dipilih.
- Quantity dibatasi berdasarkan stok varian.
- Tombol tambah ke keranjang menyimpan varian yang dipilih.
- Cart membedakan produk yang sama dengan varian berbeda sebagai item terpisah.
- Cart dan checkout menampilkan nama varian.
- Checkout mengirim `variant_id` ke backend.
- Backend memvalidasi bahwa `variant_id` benar-benar milik `product_id` yang dikirim.
- Order item menyimpan `variant_id`, `variant_name`, harga varian, quantity, dan subtotal.

Payload checkout item mendukung:

```json
{
  "product_id": 1,
  "variant_id": 10,
  "quantity": 2
}
```

Jika produk tidak memiliki varian, `variant_id` boleh `null` atau tidak dikirim.

Catatan: UI varian hanya muncul jika admin sudah membuat varian aktif. Jika belum ada varian aktif, produk tetap memakai harga, stok, dan berat utama produk.

---

## Konfigurasi WhatsApp

Nomor WhatsApp utama toko disimpan sebagai Site Setting dengan key `whatsapp_number`.

Digunakan untuk:
- Redirect WhatsApp otomatis setelah checkout.
- Tombol konfirmasi WhatsApp di halaman order confirmation.
- Floating WhatsApp button di storefront.
- Fallback manual saat cek ongkir otomatis gagal.

Cara mengubah nomor:
1. Buka `/admin`.
2. Masuk ke `Pengaturan Situs`.
3. Edit setting `Nomor WhatsApp Admin` atau key `whatsapp_number`.
4. Isi nomor dalam format internasional tanpa tanda plus, contoh `6285196811722`.

Jika setting tidak ada, backend fallback ke env `WHATSAPP_NUMBER`.

---

## Branding dan Favicon

Favicon storefront dan halaman error memakai file:

```text
public/aliesmo.svg
```

Blade yang memakai favicon ini:
- `resources/views/welcome.blade.php`
- `resources/views/errors/404.blade.php`
- `resources/views/errors/500.blade.php`

Tag yang digunakan:

```html
<link rel="icon" type="image/svg+xml" href="/aliesmo.svg">
```

Browser sering cache favicon. Jika logo belum berubah, lakukan hard refresh atau clear browser cache.

---

## Konten Dinamis dan Fallback Data

Storefront production tidak lagi memakai fallback `mock-data` untuk produk/kategori saat API gagal.

Perilaku saat API gagal:
- Homepage menampilkan empty/error state, bukan produk dummy.
- Catalog menampilkan empty/error state, bukan produk dummy.
- Product detail menampilkan pesan gagal/tidak ditemukan, bukan produk dummy.

`mock-data` masih dipakai hanya untuk helper seperti `formatPrice` atau referensi development, bukan sebagai data publik fallback.

Konten Syarat & Ketentuan dipindahkan ke Site Settings group `general`:
- `terms_title`
- `terms_updated_at`
- `terms_sections` berupa JSON array dengan format `[{ "title": "...", "body": "..." }]`

Admin bisa mengubahnya via Admin Panel > Pengaturan Situs. Jika setting belum tersedia atau API gagal, `TermsPage.vue` memakai fallback teks default agar halaman tetap bisa dibaca.

---

## Alur Pembelian

### 1. Browse Produk

User mengunjungi homepage, melihat banner promo, kategori kemeja, dan daftar produk.
Bisa filter per kategori, search by nama, atau sort by harga.

### 2. Detail Produk

User klik produk, lihat foto (hover untuk gambar kedua), deskripsi, harga, stok.
Tombol Tambah ke Keranjang akan trigger animasi fly-to-cart.

Jika produk memiliki varian aktif:
- User memilih varian terlebih dahulu.
- Harga dan stok berubah sesuai varian.
- Keranjang menyimpan varian sebagai item terpisah.

### 3. Keranjang

User lihat ringkasan item, bisa ubah jumlah atau hapus.
Tombol Lanjut Pesan mengarah ke halaman checkout.

Produk yang sama dengan varian berbeda ditampilkan sebagai baris berbeda di keranjang.

### 4. Checkout

User mengisi:
- Nama lengkap, email, nomor telepon
- Alamat lengkap
- Kota/kecamatan tujuan (autocomplete search, debounce 400ms)
  - Mengetik minimal 3 karakter
  - Hasil muncul dari Komerce API yang di-enrich dengan Biteship area_id
  - Navigasi dengan keyboard (ArrowUp/Down/Enter/Escape)
- Layanan pengiriman (otomatis dimuat setelah pilih kota)
- Kode kupon (opsional)
- Metode pembayaran: transfer bank, QRIS, atau COD

### 5. Cek Ongkir (Backend)

POST /api/v1/shipping/cost dipanggil dengan:
- destination: city_id (dari Komerce)
- weight: total berat item (gram)
- area_id: Biteship area ID (jika tersedia, akurasi tinggi)
- postal_code: kode pos (fallback)

Logika backend:
1. Cek cache - jika ada return langsung
2. Coba RajaOngkir/Komerce (primary)
3. Jika Komerce gagal/limit, fallback ke Biteship (pakai area_id atau postal_code)
4. Jika semua gagal, return manual:true + link WhatsApp tanya ongkir
5. Simpan ke cache 24 jam

### 6. Submit Order

POST /api/v1/orders dengan payload:
- Data pelanggan (nama, email, telepon, alamat)
- shipping_cache_key (dari response cek ongkir)
- shipping_courier + shipping_service
- coupon_code (opsional)
- payment_method: bank_transfer, qris, atau cod
- items: array product_id + variant_id + quantity

Backend melakukan:
- Validasi stok setiap item
- Ambil harga ongkir dari cache menggunakan shipping_cache_key
- Hitung subtotal, diskon kupon, dan total
- Buat record Order + OrderItems
- Simpan payment_method dengan fallback `bank_transfer`
- Jika Bearer token valid dikirim saat checkout, simpan user_id dari Sanctum token
- Jika item memakai varian, simpan variant_id, variant_name, harga varian, dan stok divalidasi terhadap varian tersebut
- Return order_number + whatsapp_number + whatsapp_message

### 7. Konfirmasi via WhatsApp

Setelah order dibuat, browser otomatis membuka WhatsApp dengan pesan berisi:
- Nomor pesanan
- Daftar item + jumlah + subtotal
- Total yang harus dibayar
- Alamat pengiriman

User diarahkan ke halaman OrderConfirmation dengan ringkasan pesanan.
Ada tombol Konfirmasi Pesanan via WhatsApp jika user belum membuka WA.

### 8. Proses oleh Admin

Admin menerima pesan WA, konfirmasi pembayaran, lalu update status order di panel /admin.
Status order: pending -> paid -> processing -> shipped -> completed.

---

## Shipping Provider

| Provider | Peran | Kondisi |
|---|---|---|
| RajaOngkir/Komerce | Primary | Dipakai pertama kali, ada daily limit (default 95 hit/hari) |
| Biteship | Fallback | Dipakai jika Komerce limit atau gagal |
| Manual WA | Last resort | Tampil jika semua provider gagal |

Biteship juga dipakai di /shipping/search untuk enrich hasil Komerce dengan area_id.
area_id Biteship memberikan akurasi ongkir lebih tinggi dibanding hanya city_id atau postal_code.

---

## Testing

### PHPUnit (Backend)

vendor/bin/phpunit

| File | Coverage |
|---|---|
| OrderTest.php | Buat order, validasi stok, status, WA flow |
| ShippingTest.php | Search, cek ongkir, Biteship fallback, manual WA, cache |
| ProductApiTest.php | List produk, detail, filter |
| SecurityTest.php | Rate limiting, auth protection, input sanitization |
| StockTest.php | Adjust stock, low stock detection |

### Vitest (Frontend)

Jalankan dari direktori frontend/:
npm test

| File | Coverage |
|---|---|
| CheckoutPage.test.js | Debounce, dropdown, loading state, selectDestination, area_id dikirim, kurir muncul, manual WA, keyboard nav, reset state |

---

## Model Relationships

`
User
  hasMany: Order, Review

Product
  belongsTo: Category
  hasMany: ProductImage, ProductVariant, OrderItem, StockMovement, Review
  fields: name, slug, sku, price, stock, weight (gram), is_active, thumbnail

ProductVariant
  belongsTo: Product
  fields: name, sku, price, stock, weight, is_active, sort_order

Order
  belongsTo: User (nullable, guest checkout didukung)
  belongsTo: Coupon (nullable)
  hasMany: OrderItem
  hasOne: Payment
  fields: order_number, customer_name, customer_email, customer_phone
          shipping_address, subtotal, coupon_discount, shipping_cost
          total, status, payment_method, tracking_number
          tracking_url, lookup_token

OrderItem
  belongsTo: Order, Product
  fields: product_name, variant_id, variant_name, price, quantity, subtotal

Coupon
  hasMany: Order
  fields: code, type (percent/fixed), value, min_order, max_discount
          usage_limit, used_count, is_active, expires_at

StockMovement
  belongsTo: Product, User
  fields: type (in/out/adjustment), quantity, note
`

---

## Development

### Jalankan dev server (hot reload)

npm run dev

Akses storefront di http://aliesmo.test atau http://localhost:8000
Akses admin panel di /admin

### Build production

npm run build

### Cache management

php artisan cache:clear
php artisan config:cache
php artisan route:cache

---

## Notes

- Payment gateway (Midtrans) dikonfigurasi tapi dinonaktifkan - order flow sepenuhnya via WhatsApp
- Stok tidak berkurang saat order dibuat, hanya berkurang setelah status berubah ke paid
- Guest checkout didukung penuh - user tidak perlu login untuk pesan
- User yang login bisa klaim pesanan guest ke akun mereka berdasarkan email yang sama
- Order yang dibuat saat user login langsung tersambung ke akun lewat Sanctum token meskipun endpoint `/orders` bersifat publik
- Halaman `/profile` memuat profil, password, lupa password, logout, dan riwayat pesanan
- Toast/notifikasi global tampil di atas tengah layar agar terlihat pada desktop dan mobile
- Varian produk didukung end-to-end, tetapi UI varian hanya tampil jika admin sudah membuat varian aktif pada produk
- Ulasan hanya bisa ditulis untuk order yang sudah completed
- Shipping cache berlaku 24 jam dengan key berbasis origin+destination+weight+area_id
- RajaOngkir daily hit counter auto-reset setiap tengah malam

---

## Outstanding Follow-ups

Hal yang masih perlu ditindaklanjuti:
- Update `tests/Feature/OrderTest.php` agar sesuai flow checkout terbaru yang mewajibkan `shipping_cache_key`, `shipping_courier`, dan `shipping_service`.
- Hapus atau update ekspektasi test payment callback lama yang masih mengharapkan status `410`.
- Tambahkan data varian aktif dari admin atau seeder untuk menguji flow varian secara manual.
- Pastikan semua environment production sudah menjalankan seeder/settings terbaru agar `terms_sections` tersedia di Site Settings.
- Jika admin panel juga perlu favicon/logo custom, tambahkan konfigurasi favicon pada Filament panel.
