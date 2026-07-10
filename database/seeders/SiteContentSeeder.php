<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        // Banners
        Banner::truncate();
        $banners = [
            [
                'title'       => "Kemeja Keren\nMulai Rp 149.000",
                'subtitle'    => 'Kualitas premium, harga merakyat. Cocok buat semua momen!',
                'badge_text'  => 'Koleksi Baru',
                'image_url'   => 'https://picsum.photos/seed/banner-kemeja-1/1400/600',
                'button_text' => 'Belanja Yuk',
                'button_link' => '/?shop=1',
                'order'       => 1,
                'is_active'   => true,
            ],
            [
                'title'       => "Gratis Ongkir!\nMin. Rp 200.000",
                'subtitle'    => 'Berlaku untuk JNE, J&T, dan SiCepat ke seluruh Indonesia.',
                'badge_text'  => 'Promo Spesial',
                'image_url'   => 'https://picsum.photos/seed/banner-kemeja-2/1400/600',
                'button_text' => 'Belanja Sekarang',
                'button_link' => '/?shop=1',
                'order'       => 2,
                'is_active'   => true,
            ],
            [
                'title'       => "Tidak Cocok?\nGanti Baru!",
                'subtitle'    => 'Belanja tanpa risau dengan garansi return 30 hari.',
                'badge_text'  => 'Garansi 30 Hari',
                'image_url'   => 'https://picsum.photos/seed/banner-kemeja-3/1400/600',
                'button_text' => 'Mulai Belanja',
                'button_link' => '/?shop=1',
                'order'       => 3,
                'is_active'   => true,
            ],
        ];
        foreach ($banners as $banner) {
            Banner::create($banner);
        }

        // Site Settings — Announcements
        $announcements = [
            [
                'key'   => 'announcement_1',
                'label' => 'Announcement Bar 1',
                'value' => 'Gratis Ongkir Seluruh Indonesia | Min. Belanja Rp 200rb',
                'type'  => 'text',
                'group' => 'announcement',
            ],
            [
                'key'   => 'announcement_1_link',
                'label' => 'Link Announcement 1',
                'value' => '/?shop=1',
                'type'  => 'text',
                'group' => 'announcement',
            ],
            [
                'key'   => 'announcement_2',
                'label' => 'Announcement Bar 2',
                'value' => 'Bahan Premium Oxford & Linen | Garansi 30 Hari',
                'type'  => 'text',
                'group' => 'announcement',
            ],
            [
                'key'   => 'announcement_2_link',
                'label' => 'Link Announcement 2',
                'value' => '/?shop=1',
                'type'  => 'text',
                'group' => 'announcement',
            ],
            [
                'key'   => 'announcement_3',
                'label' => 'Announcement Bar 3',
                'value' => 'Diskon 10% First Order | Kode: ALIESNEW10',
                'type'  => 'text',
                'group' => 'announcement',
            ],
            [
                'key'   => 'announcement_3_link',
                'label' => 'Link Announcement 3',
                'value' => '/login',
                'type'  => 'text',
                'group' => 'announcement',
            ],
        ];

        // Site Settings — Stats
        $stats = [
            [
                'key'   => 'stat_kemeja_terjual',
                'label' => 'Statistik: Kemeja Terjual (Angka)',
                'value' => '12.000+',
                'type'  => 'text',
                'group' => 'stats',
            ],
            [
                'key'   => 'stat_kemeja_terjual_label',
                'label' => 'Statistik: Kemeja Terjual (Teks)',
                'value' => 'Kemeja Sudah Terjual',
                'type'  => 'text',
                'group' => 'stats',
            ],
            [
                'key'   => 'stat_kota',
                'label' => 'Statistik: Kota (Angka)',
                'value' => '15+',
                'type'  => 'text',
                'group' => 'stats',
            ],
            [
                'key'   => 'stat_kota_label',
                'label' => 'Statistik: Kota (Teks)',
                'value' => 'Kota Dikirim',
                'type'  => 'text',
                'group' => 'stats',
            ],
            [
                'key'   => 'stat_kualitas',
                'label' => 'Statistik: Kualitas (Angka)',
                'value' => '100%',
                'type'  => 'text',
                'group' => 'stats',
            ],
            [
                'key'   => 'stat_kualitas_label',
                'label' => 'Statistik: Kualitas (Teks)',
                'value' => 'Bahan Original',
                'type'  => 'text',
                'group' => 'stats',
            ],
            [
                'key'   => 'stat_garansi',
                'label' => 'Statistik: Garansi (Angka)',
                'value' => '30',
                'type'  => 'text',
                'group' => 'stats',
            ],
            [
                'key'   => 'stat_garansi_label',
                'label' => 'Statistik: Garansi (Teks)',
                'value' => 'Hari Garansi Retur',
                'type'  => 'text',
                'group' => 'stats',
            ],
        ];

        foreach ([...$announcements, ...$stats] as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // Site Settings — General (contact info, WA number)
        $general = [
            [
                'key'   => 'contact_email',
                'label' => 'Email Kontak',
                'value' => 'hello@aliesmo.com',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'contact_phone',
                'label' => 'Nomor Telepon',
                'value' => '+62 851-9681-1722',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'contact_address',
                'label' => 'Alamat Toko',
                'value' => 'Ulujami, Pemalang, Jawa Tengah',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'whatsapp_number',
                'label' => 'Nomor WhatsApp Admin',
                'value' => '6285196811722',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'store_description',
                'label' => 'Deskripsi Toko',
                'value' => 'Kemeja batik dan casual berkualitas. Nyaman dipakai, bangga dengan budaya Indonesia.',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'terms_title',
                'label' => 'Judul Syarat & Ketentuan',
                'value' => 'Syarat & Ketentuan',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'terms_updated_at',
                'label' => 'Tanggal Update Syarat & Ketentuan',
                'value' => 'Juli 2026',
                'type'  => 'text',
                'group' => 'general',
            ],
            [
                'key'   => 'terms_sections',
                'label' => 'Isi Syarat & Ketentuan',
                'value' => json_encode([
                    ['title' => 'Pemesanan', 'body' => 'Dengan melakukan pemesanan di Aliesmo, kamu menyetujui bahwa data yang kamu berikan adalah benar dan lengkap. Pesanan dianggap sah setelah dikonfirmasi oleh admin via WhatsApp.'],
                    ['title' => 'Pembayaran', 'body' => 'Pembayaran dilakukan melalui transfer bank, QRIS, COD, atau metode lain yang disepakati bersama admin. Pesanan akan diproses setelah pembayaran dikonfirmasi.'],
                    ['title' => 'Pengiriman', 'body' => 'Ongkos kirim dihitung berdasarkan berat dan tujuan pengiriman. Estimasi waktu pengiriman bergantung pada kurir yang dipilih dan kondisi di lapangan.'],
                    ['title' => 'Pengembalian Barang', 'body' => 'Pengembalian barang dapat dilakukan dalam 3 hari setelah barang diterima, dengan syarat barang masih dalam kondisi asli, belum dipakai, dan tag masih terpasang. Hubungi admin via WhatsApp untuk proses retur.'],
                    ['title' => 'Privasi Data', 'body' => 'Data pribadi kamu (nama, email, nomor telepon, alamat) hanya digunakan untuk keperluan pemrosesan pesanan dan tidak akan dibagikan ke pihak ketiga tanpa persetujuanmu.'],
                ], JSON_UNESCAPED_UNICODE),
                'type'  => 'json',
                'group' => 'general',
            ],
            // Payment settings
            [
                'key'   => 'payment_banks',
                'label' => 'Daftar Rekening Bank',
                'value' => json_encode([
                    ['bank_name' => 'BCA', 'account_no' => '1234567890', 'account_name' => 'Aliesmo Store'],
                ]),
                'type'  => 'json',
                'group' => 'payment',
            ],
            [
                'key'   => 'payment_qris_image',
                'label' => 'URL Gambar QRIS',
                'value' => '',
                'type'  => 'text',
                'group' => 'payment',
            ],
            [
                'key'   => 'payment_qris_name',
                'label' => 'Nama QRIS',
                'value' => 'Aliesmo Store',
                'type'  => 'text',
                'group' => 'payment',
            ],
            [
                'key'   => 'payment_cod_enabled',
                'label' => 'COD Aktif',
                'value' => '1',
                'type'  => 'boolean',
                'group' => 'payment',
            ],
        ];

        foreach ($general as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
