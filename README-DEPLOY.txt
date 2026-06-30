═══════════════════════════════════════════════════════════
 ALIESMO — DEPLOY MANUAL KE CPANEL (TANPA COMPOSER)
═══════════════════════════════════════════════════════════

A. PERSIAPAN FILE
──────────────────
1. File .env SUDAH diisi setting production:
   - APP_ENV=production
   - APP_DEBUG=false
   - APP_URL=https://domain-anda.com
   - DB_DATABASE=... (nama database yang dibuat di cPanel)
   - DB_USERNAME=... (user database dari cPanel)
   - DB_PASSWORD=... (password database)

2. Vendor folder SUDAH termasuk dalam zip
   (Composer sudah dijalankan di local)

B. UPLOAD KE CPANEL
────────────────────
1. Login cPanel → File Manager → masuk ke public_html/
2. Upload file: aliesmo.zip
3. Klik kanan → Extract
4. Hapus folder public/storage/ (jika ada)

C. SETUP DATABASE (pilih salah satu)
─────────────────────────────────────

─── CARA 1: Via Terminal cPanel (REKOMENDASI) ───
   Di cPanel → cari "Terminal" atau "SSH"
   Jalankan perintah berikut satu per satu:

   cd public_html
   php artisan migrate --force
   php artisan storage:link

─── CARA 2: Via phpMyAdmin ───
   a. Buka phpMyAdmin di cPanel
   b. Pilih database yang sudah dibuat
   c. Klik tab "SQL"
   d. Copy paste SQL dari file setup.sql
   e. Klik Go

   Lalu di Terminal / File Manager:
   php artisan storage:link
   (atau manual: buat folder public/storage dan isi dengan salinan storage/app/public)

─── CARA 3: Tidak ada Terminal sama sekali ───
   Upload, extract, lalu:
   1. Buka phpMyAdmin → import file setup.sql
   2. Jika gagal, buka satu per satu file migration di folder
      database/migrations/, copy SQL CREATE TABLE-nya,
      jalankan di phpMyAdmin satu per satu (urut sesuai nomor)
   3. Hapus folder public/storage
   4. Buat folder public/storage (kosong)

D. SETELAH INSTALL
───────────────────
   Akses: https://domain-anda.com

   Admin login: /admin
   Email: admin@example.com
   Password: password

   ⚠️ HAPUS atau ganti password admin SETELAH login pertama!

E. JIKA ERROR 500
───────────────────
   1. Cek file .env — pastikan DB settings benar
   2. Cek folder storage — harus writable
   3. Cek cPanel Error Log untuk detail error
   4. Pastikan PHP versi 8.2 atau lebih baru
   5. Pastikan ekstensi PHP: pdo_mysql, mbstring, openssl, tokenizer, json, fileinfo

F. JIKA ERROR 404 (HALAMAN PUTIH)
───────────────────────────────────
   Folder public_html harus berisi file .htaccess (dari root project)
   yang isinya: RewriteRule ^(.*)$ public/$1 [L]

   Jika masih error, coba akses langsung:
   https://domain-anda.com/public/
