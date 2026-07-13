# Aliesmo — Changelog & Panduan Deploy

## Stack
- **Backend**: Laravel 12, PHP 8.4, Filament v5, Livewire v4
- **Frontend**: Vue 3 + Vite + Tailwind CSS
- **Database**: MySQL
- **Deploy**: GitHub Actions → FTP ke cPanel → `deploy-runner.php`

---

## Panduan Deploy

### Cara Deploy (Normal)
Cukup push ke branch `main`. GitHub Actions akan otomatis:

1. Build frontend (`npm run build`) dengan env `VITE_API_URL` dari GitHub Secrets
2. Install Composer dependencies (`--no-dev --optimize-autoloader`)
3. Upload semua file ke cPanel via FTP (incremental — hanya file yang berubah)
4. Panggil `https://aliesmo.id/deploy-runner.php` dengan header `X-Deploy-Token` untuk:
   - `php artisan migrate --force`
   - `php artisan storage:link`
   - `php artisan optimize:clear`

```bash
git add .
git commit -m "feat/fix: deskripsi perubahan"
git push
```

### GitHub Secrets yang Dibutuhkan
| Secret | Keterangan |
|---|---|
| `FTP_SERVER` | Host FTP cPanel |
| `FTP_USERNAME` | Username FTP |
| `FTP_PASSWORD` | Password FTP |
| `VITE_API_URL` | URL API production (misal: `https://aliesmo.id/api`) |
| `DEPLOY_TOKEN` | Token untuk otentikasi `deploy-runner.php` — harus sama dengan `DEPLOY_TOKEN` di `.env` production |

### Jika Deploy Gagal / Perlu Rollback
Tidak ada akses SSH ke production. Opsi yang tersedia:
- **Rollback kode**: `git revert <hash>` lalu push ke main
- **Rollback database**: Tidak bisa otomatis — backup manual via cPanel phpMyAdmin sebelum perubahan skema
- **Cek hasil deploy**: Buka `https://aliesmo.id/deploy-runner.php?token=<DEPLOY_TOKEN>` di browser untuk lihat output migrate

### File Penting yang Tidak Di-deploy (di-exclude)
- `.env` dan `.env.*` — jangan pernah commit file ini
- `vendor/` — di-install ulang oleh Composer di GitHub Actions
- `node_modules/` dan `frontend/node_modules/`
- `storage/logs/`, `storage/framework/cache/sessions/views/`
- `*.md`, `package.json`, `vite.config.js`, `tailwind.config.js`

### Tambah Migration Baru
1. Buat migration: `php artisan make:migration nama_migration`
2. Edit file migration di `database/migrations/`
3. Test lokal: `php artisan migrate`
4. Commit dan push — production akan auto-migrate via `deploy-runner.php`

---

## Changelog

### 2026-07-13 — Dynamic Product Variant Selection (SKU Matrix)

**`app/Http/Resources/ProductVariantResource.php`**
- Tambah field `parsed_attributes` pada response API varian
- Parse `name` varian dengan separator ` / ` menjadi key-value atribut
  - Contoh: `"Navy / S"` → `{ "Warna": "Navy", "Ukuran": "S" }`
  - Flat name (`"S"`) → `{ "Varian": "S" }` (backward-compatible)
- Auto-detect label atribut berdasarkan keyword warna dan ukuran umum (Navy, Hitam, S, M, L, XL, XXL, dll)
- Fallback ke label posisional (`"Atribut 1"`, `"Atribut 2"`) jika keyword tidak dikenali

**`frontend/src/pages/ProductDetailPage.vue`**
- Tambah import `SkeletonLoader` yang sebelumnya hilang (bug fix — loading state tidak crash)
- Refactor state management varian:
  - `selectedOptions` — object `{ [label]: value }` untuk mencatat pilihan per atribut
  - `isSelectionComplete` — boolean, true jika semua atribut sudah dipilih
  - `isReadyToCart` — boolean, menggantikan `canAddToCart`
  - `displayPriceMax` — untuk rentang harga min–max saat belum ada varian dipilih
  - `maxQuantity` — batas quantity berdasarkan stok varian/produk yang aktif
- Implementasi 3 fase interaksi:
  - **Fase 1 (default):** tampil rentang harga min–max, total stok semua varian, quantity disabled
  - **Fase 2 (parsial):** stok ter-filter berdasarkan atribut yang sudah dipilih, gambar update otomatis
  - **Fase 3 (komplit):** harga & stok exact SKU, quantity enabled dengan max = stok varian, tombol aktif
- Implementasi mode matrix vs flat:
  - **Mode matrix** (nama varian mengandung `/`): tiap atribut tampil sebagai grup tombol terpisah
  - **Mode flat** (nama tunggal, misal `S`/`M`/`L`): tampil seperti sebelumnya, backward-compatible
- Disabled options: opsi dengan stok 0 dirender dengan `line-through + opacity rendah`, tidak bisa diklik
- Auto-correction quantity: saat ganti pilihan dan stok baru lebih kecil dari quantity saat ini, quantity otomatis turun
- Update gambar dinamis: saat atribut pertama (warna) dipilih, gambar utama otomatis ganti ke image yang sesuai posisi warna
- Tombol CTA dinamis: menampilkan nama atribut yang belum dipilih (`"Pilih Warna Dulu"`, `"Pilih Ukuran Dulu"`)

**`frontend/src/cart.js`**
- Tambah field `weight` pada cart item — diambil dari `selectedVariant.weight ?? product.weight ?? 300`
- Fix bug: sebelumnya `weight` tidak tersimpan di cart, menyebabkan ongkir selalu fallback ke 300g meskipun varian punya berat berbeda

---

### 2026-07-11 — Tracking Resi & Info Pengiriman

**`app/Filament/Resources/OrderResource.php`**
- Form modal "Input Resi" di admin: hapus `Select` kurir, ganti dengan `TextInput` disabled read-only
- Kurir otomatis terisi dari data pesanan (`$record->courier`), admin hanya input nomor resi
- `tracking_url` di-resolve dari `$record->courier` di action handler, bukan dari input form
- Tambah import `Filament\Forms\Components\Placeholder` (lalu diganti ke `TextInput` disabled)

**`frontend/src/pages/ProfilePage.vue`**
- Tambah section Info Pengiriman di tab Pesanan: nama kurir, nomor resi, tombol "Cek Resi"
- Tombol "Cek Resi" menggunakan `order.tracking_url` dari API (hanya muncul jika `tracking_url` dan `tracking_number` keduanya ada)
- Konsisten dengan tampilan di `MyOrdersPage.vue`

---

### 2026-07-11 — Logo, Social Media, Dark Mode

**`frontend/src/App.vue`**
- Ganti teks "ALIESMO" di header dan footer dengan SVG logo (`/aliesmo-logo.svg`)
- Logo header: `isDark ? 'brightness-0 invert' : ''` — putih di dark mode, normal di light
- Logo footer: selalu `brightness-0 invert` (selalu putih)
- Ukuran logo header: `h-24 sm:h-28 lg:h-32` | footer: `h-24`
- Tambah social media links di footer: Instagram, Facebook, TikTok, YouTube dari site settings

---

### 2026-07-11 — Order & Checkout Fixes

**`app/Services/OrderService.php`**
- Normalisasi `shipping_courier` dari format RajaOngkir (`jne`, `jnt`) ke display format (`JNE`, `JNT Express`)
- Auto-set `tracking_url` dari map kurir saat order dibuat
- Simpan ke kolom `courier` di tabel `orders`

**`app/Http/Controllers/Api/OrderController.php`**
- `store()`: tambah `selected_bank` ke `$request->only()` agar tersimpan ke database
- `myOrder()`: return `payment_info` dan `whatsapp_number`
- `claimGuestOrders()` dan `countClaimableOrders()`: endpoint klaim pesanan guest

**`app/Http/Resources/OrderItemResource.php`**
- Gunakan `relationLoaded()` (bukan `whenLoaded`) untuk cek relasi sebelum akses — cegah `MissingValue` di-pass ke `str_starts_with`
- Tambah `product_image` ke response

**`app/Http/Resources/PaymentResource.php`**
- Gunakan `whenLoaded` dengan closure untuk cegah 500 saat payment null

**`app/Http/Resources/OrderResource.php`**
- Expose field `courier`, `tracking_number`, `tracking_url`, `payment_info`

**`routes/api.php`**
- Pindahkan static routes `me/orders/claimable-count` dan `me/orders/claim` ke **sebelum** wildcard `{orderNumber}` — cegah route conflict 404

**`frontend/src/pages/CheckoutPage.vue`**
- `availableBanks` computed: handle format array dan object dari settings cache

**`frontend/src/pages/OrderConfirmationPage.vue`**
- Deteksi token: gunakan endpoint authenticated (`me/orders/{orderNumber}`) jika login, public jika guest

**`frontend/src/pages/ProfilePage.vue`**
- Auto-claim guest orders saat tab "Pesanan" dibuka (`checkAndAutoClaim()`)
- Tidak ada tombol manual — claim otomatis saat tab aktif

**`frontend/src/pages/MyOrdersPage.vue`**
- Tampilkan kurir, nomor resi, dan tombol "Cek Resi" di setiap order card

---

### 2026-07-11 — Auth & Navbar

**`routes/api.php`**
- Throttle login dan register: `5,1` → `10,1` (10 request per menit)

**`frontend/src/App.vue`**
- Dispatch custom event `auth:login` setelah `setToken()` — fix navbar tidak update di tab yang sama (event `storage` tidak trigger di tab yang sama)
- Redirect ke `/profile` setelah login/register berhasil

---

### 2026-07-10 — Produk & Kategori

**`frontend/src/pages/ProductDetailPage.vue`**
- Deskripsi produk tampil penuh (hapus `line-clamp`) di bawah harga
- Urutan layout: deskripsi → quantity → tombol keranjang
- `fetchRelated`: gunakan `categories[0].slug` bukan `category.slug`

---

### 2026-07-08 — Site Settings & Pembayaran

**`app/Http/Requests/Api/StoreOrderRequest.php`**
- Tambah validasi `selected_bank`

**`frontend/src/useSettings.js`**
- Module-level cache `_settings` — `fetchSettings(true)` untuk force re-fetch
- Cache stale bisa sembunyikan setting baru sampai hard refresh

---

## Struktur File Kunci

```
aliesmo/
├── .github/workflows/deploy.yml      # GitHub Actions CI/CD
├── app/
│   ├── Filament/Resources/
│   │   └── OrderResource.php         # Admin panel order management
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   └── OrderController.php   # API order endpoints
│   │   ├── Requests/Api/
│   │   │   └── StoreOrderRequest.php # Validasi order
│   │   └── Resources/
│   │       ├── OrderResource.php     # API resource order
│   │       ├── OrderItemResource.php # API resource item (include product_image)
│   │       └── PaymentResource.php   # API resource payment
│   └── Services/
│       └── OrderService.php          # Business logic: buat order, normalisasi kurir
├── database/migrations/              # Semua migration — jalankan php artisan migrate
├── frontend/src/
│   ├── App.vue                       # Navbar, footer, logo, social media
│   ├── pages/
│   │   ├── CheckoutPage.vue          # Checkout, bank selection
│   │   ├── OrderConfirmationPage.vue # Konfirmasi order
│   │   ├── ProfilePage.vue           # Profil, pesanan, info pengiriman
│   │   └── MyOrdersPage.vue          # Daftar pesanan
│   └── useSettings.js                # Settings cache
├── public/
│   ├── deploy-runner.php             # Post-deploy: migrate + storage:link + optimize:clear
│   └── aliesmo-logo.svg              # Logo SVG
└── routes/api.php                    # API routes (urutan penting: static sebelum wildcard)
```

---

## Kurir yang Didukung

| Nama di DB | Tracking URL |
|---|---|
| JNE | https://jne.co.id/tracking-package |
| JNT Express | https://jet.co.id/track |
| SiCepat | https://www.sicepat.com/ |
| Anteraja | https://anteraja.id/id/tracking |
| Ninja | https://www.ninjaxpress.co/en-id/tracking |
| Pos Indonesia | https://www.posindonesia.co.id/id/tracking |
| Lion Parcel | https://lionparcel.com/track |

Mapping ini ada di dua tempat:
- `app/Services/OrderService.php` — saat order dibuat
- `app/Filament/Resources/OrderResource.php` — saat admin input resi

---

## Catatan Penting

- **Jangan commit `.env`** — file ini tidak di-deploy via FTP (sudah di-exclude)
- **`deploy-runner.php` jangan dihapus** — dibutuhkan setiap deploy untuk migrate dan clear cache
- **Route order**: static routes (`claimable-count`, `claim`) harus di atas wildcard `{orderNumber}` di `routes/api.php`
- **Settings cache**: jika setting baru tidak muncul di frontend, lakukan hard refresh (Ctrl+Shift+R) atau tunggu cache expire
- Error `contentscript.js MaxListenersExceededWarning` di console adalah dari ekstensi browser (MetaMask) — bukan dari app
