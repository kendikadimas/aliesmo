# Panduan Deploy ke cPanel

## Prasyarat
- Hosting cPanel dengan PHP 8.2+
- Akses SSH terminal di cPanel
- Database MySQL sudah dibuat di cPanel
- Domain/subdomain sudah diarahkan ke hosting

---

## Langkah 1 — Persiapan di Lokal

### 1.1 Build frontend
```powershell
cd D:\laragon\www\aliesmo\frontend
npm run build
```
Hasil build masuk ke `public/build/` — pastikan folder ini ada sebelum upload.

### 1.2 Install dependencies production
```powershell
cd D:\laragon\www\aliesmo
composer install --no-dev --optimize-autoloader
```

### 1.3 Salin dan edit `.env` untuk production
Duplikat `.env` lokal, simpan sebagai `.env.production`, lalu sesuaikan:

```env
APP_NAME="Aliesmo"
APP_ENV=production
APP_KEY=             # salin dari .env lokal, JANGAN kosongkan
APP_DEBUG=false
APP_URL=https://domainmu.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

SESSION_DOMAIN=.domainmu.com
SANCTUM_STATEFUL_DOMAINS=domainmu.com

MAIL_MAILER=smtp
MAIL_HOST=mail.domainmu.com
MAIL_PORT=465
MAIL_USERNAME=noreply@domainmu.com
MAIL_PASSWORD=password_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@domainmu.com
MAIL_FROM_NAME="Aliesmo"

RAJAONGKIR_API_KEY=isi_api_key_disini
RAJAONGKIR_ORIGIN_CITY=153
RAJAONGKIR_BASE_URL=https://api.rajaongkir.com/starter

WHATSAPP_NUMBER=628xxxxxxxxxx

VITE_API_URL=https://domainmu.com/api/v1
```

---

## Langkah 2 — Upload ke cPanel

### 2.1 Struktur folder di server

```
/home/username/
├── public_html/          ← isi folder public/ Laravel
│   ├── index.php         ← diedit (lihat langkah 2.3)
│   ├── .htaccess
│   ├── build/            ← hasil npm run build
│   └── storage/          ← symlink (dibuat via artisan)
└── aliesmo/              ← seluruh project Laravel
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── .env              ← isi dari .env.production
    └── ...
```

### 2.2 Upload file

Gunakan File Manager cPanel atau FTP:

1. Upload seluruh project ke `/home/username/aliesmo/` — **kecuali** folder `frontend/node_modules/` dan `.git/`
2. Upload isi folder `public/` ke `/home/username/public_html/`
3. Upload file `.env.production` ke `/home/username/aliesmo/.env`

> Tips: Zip dulu di lokal, upload zip, lalu extract via File Manager cPanel untuk lebih cepat.

### 2.3 Edit `public_html/index.php`

Buka `/home/username/public_html/index.php` via File Manager, ubah path:

```php
// Baris require autoload — ubah dari:
require __DIR__.'/../vendor/autoload.php';
// Menjadi:
require __DIR__.'/../aliesmo/vendor/autoload.php';

// Baris bootstrap app — ubah dari:
$app = require_once __DIR__.'/../bootstrap/app.php';
// Menjadi:
$app = require_once __DIR__.'/../aliesmo/bootstrap/app.php';
```

---

## Langkah 3 — Konfigurasi di Server (via SSH)

Buka SSH terminal di cPanel (menu "Terminal").

### 3.1 Set permission folder storage dan cache
```bash
chmod -R 775 ~/aliesmo/storage
chmod -R 775 ~/aliesmo/bootstrap/cache
```

### 3.2 Buat symlink storage
```bash
cd ~/aliesmo
php artisan storage:link
```
> Ini membuat `public_html/storage` → `aliesmo/storage/app/public`

### 3.3 Jalankan migrasi database
```bash
cd ~/aliesmo
php artisan migrate --force
```

### 3.4 Seed data awal (jika perlu)
```bash
php artisan db:seed --force
```

### 3.5 Cache konfigurasi dan route untuk performa
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## Langkah 4 — Konfigurasi PHP di cPanel

Di cPanel > "Select PHP Version" atau "PHP Configuration":

- PHP versi: **8.2** atau **8.4**
- Extension yang harus aktif:
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `tokenizer`
  - `xml`
  - `ctype`
  - `json`
  - `fileinfo`
  - `curl`
  - `bcmath`

---

## Langkah 5 — Ganti Credentials Default Admin

Setelah deploy, **segera** ganti password admin default via SSH:

```bash
cd ~/aliesmo
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'admin@example.com')->first();
$user->update([
    'email'    => 'admin@domainmu.com',
    'password' => \Illuminate\Support\Facades\Hash::make('PasswordBaruYangKuat123!'),
]);
exit
```

---

## Langkah 6 — Verifikasi

Cek endpoint-endpoint berikut berfungsi:

| URL | Expected |
|-----|----------|
| `https://domainmu.com` | Frontend Vue tampil |
| `https://domainmu.com/api/v1/products` | JSON list produk |
| `https://domainmu.com/api/v1/categories` | JSON list kategori |
| `https://domainmu.com/admin` | Login Filament admin panel |

---

## Troubleshooting

### Error 500 setelah deploy
```bash
cd ~/aliesmo
php artisan config:clear
php artisan cache:clear
cat storage/logs/laravel.log | tail -50
```

### Halaman putih / CSS tidak muncul
- Pastikan `npm run build` sudah dijalankan di lokal dan folder `public/build/` ikut di-upload
- Cek `APP_URL` di `.env` sudah pakai `https://`

### Storage symlink tidak bekerja
```bash
rm ~/public_html/storage          # hapus symlink lama jika ada
cd ~/aliesmo && php artisan storage:link
```

### Session/Auth tidak persistent
- Pastikan `SESSION_DOMAIN=.domainmu.com` di `.env` (dengan titik di depan)
- Pastikan `SANCTUM_STATEFUL_DOMAINS=domainmu.com` tanpa `https://`

### `.env` tidak terbaca
- Pastikan file `.env` ada di `~/aliesmo/` (bukan di `public_html/`)
- Jalankan `php artisan config:cache` ulang setelah edit `.env`

---

## Checklist Deploy

- [ ] `npm run build` selesai, folder `public/build/` ada
- [ ] `composer install --no-dev` selesai, folder `vendor/` ada
- [ ] `.env` production sudah diisi lengkap (`APP_KEY`, `DB_*`, `WHATSAPP_NUMBER`)
- [ ] Seluruh project di-upload ke `/home/username/aliesmo/`
- [ ] Isi `public/` di-upload ke `public_html/`
- [ ] `public_html/index.php` path sudah diedit
- [ ] Permission `storage/` dan `bootstrap/cache/` sudah 775
- [ ] `php artisan storage:link` sudah dijalankan
- [ ] `php artisan migrate --force` sudah dijalankan
- [ ] `php artisan config:cache` sudah dijalankan
- [ ] Admin credentials default sudah diganti
- [ ] Semua endpoint verifikasi berfungsi
- [ ] `APP_DEBUG=false` di `.env` production
