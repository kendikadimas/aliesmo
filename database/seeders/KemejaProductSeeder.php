<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KemejaProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Formal (category_id: 1)
            [
                'category_id' => 1,
                'name'        => 'Kemeja Formal Slim Fit Putih',
                'sku'         => 'KMJ-FRM-001',
                'description' => 'Kemeja formal slim fit berbahan katun premium 100%. Cocok untuk acara resmi, interview kerja, dan hari-hari di kantor. Potongan slim fit yang modern memberikan tampilan rapi dan profesional.',
                'price'       => 185000,
                'stock'       => 50,
                'is_active'   => true,
                'seeds'       => ['kmj-frm-001-a', 'kmj-frm-001-b', 'kmj-frm-001-c'],
            ],
            [
                'category_id' => 1,
                'name'        => 'Kemeja Formal Regular Fit Navy',
                'sku'         => 'KMJ-FRM-002',
                'description' => 'Kemeja formal regular fit warna navy. Bahan twill premium anti-kusut, nyaman dipakai seharian. Tersedia dengan pilihan kancing mutiara yang elegan.',
                'price'       => 195000,
                'stock'       => 40,
                'is_active'   => true,
                'seeds'       => ['kmj-frm-002-a', 'kmj-frm-002-b', 'kmj-frm-002-c'],
            ],
            [
                'category_id' => 1,
                'name'        => 'Kemeja Formal Lengan Panjang Abu-Abu',
                'sku'         => 'KMJ-FRM-003',
                'description' => 'Kemeja formal warna abu-abu medium. Bahan katun Oxford premium yang breathable dan nyaman. Desain kerah spread collar yang modern dan timeless.',
                'price'       => 210000,
                'stock'       => 35,
                'is_active'   => true,
                'seeds'       => ['kmj-frm-003-a', 'kmj-frm-003-b', 'kmj-frm-003-c'],
            ],

            // Casual (category_id: 2)
            [
                'category_id' => 2,
                'name'        => 'Kemeja Casual Flannel Kotak-Kotak',
                'sku'         => 'KMJ-CSL-001',
                'description' => 'Kemeja flannel motif kotak-kotak klasik. Bahan tebal dan hangat, cocok untuk cuaca sejuk atau kegiatan outdoor santai. Tersedia dalam berbagai kombinasi warna.',
                'price'       => 165000,
                'stock'       => 60,
                'is_active'   => true,
                'seeds'       => ['kmj-csl-001-a', 'kmj-csl-001-b', 'kmj-csl-001-c'],
            ],
            [
                'category_id' => 2,
                'name'        => 'Kemeja Casual Linen Polos Cream',
                'sku'         => 'KMJ-CSL-002',
                'description' => 'Kemeja casual berbahan linen alami warna cream. Ringan dan breathable, perfect untuk hangout, jalan-jalan, atau acara semi-formal. Tampilan clean yang tidak pernah ketinggalan zaman.',
                'price'       => 175000,
                'stock'       => 45,
                'is_active'   => true,
                'seeds'       => ['kmj-csl-002-a', 'kmj-csl-002-b', 'kmj-csl-002-c'],
            ],
            [
                'category_id' => 2,
                'name'        => 'Kemeja Casual Oversize Vintage Wash',
                'sku'         => 'KMJ-CSL-003',
                'description' => 'Kemeja casual oversize dengan efek vintage wash. Bahan katun garment-dyed yang lembut dan nyaman. Cocok dipakai sebagai outer atau standalone piece untuk tampilan street style.',
                'price'       => 189000,
                'stock'       => 30,
                'is_active'   => true,
                'seeds'       => ['kmj-csl-003-a', 'kmj-csl-003-b', 'kmj-csl-003-c'],
            ],

            // Batik (category_id: 3)
            [
                'category_id' => 3,
                'name'        => 'Kemeja Batik Tulis Motif Parang',
                'sku'         => 'KMJ-BTK-001',
                'description' => 'Kemeja batik tulis tangan dengan motif parang klasik. Dibuat oleh pengrajin batik berpengalaman menggunakan malam asli. Cocok untuk acara formal, kondangan, maupun kegiatan adat.',
                'price'       => 385000,
                'stock'       => 20,
                'is_active'   => true,
                'seeds'       => ['kmj-btk-001-a', 'kmj-btk-001-b', 'kmj-btk-001-c'],
            ],
            [
                'category_id' => 3,
                'name'        => 'Kemeja Batik Cap Motif Kawung',
                'sku'         => 'KMJ-BTK-002',
                'description' => 'Kemeja batik cap dengan motif kawung yang elegan. Bahan katun halus yang nyaman dipakai di cuaca tropis. Tersedia dalam warna sogan cokelat dan hitam.',
                'price'       => 245000,
                'stock'       => 25,
                'is_active'   => true,
                'seeds'       => ['kmj-btk-002-a', 'kmj-btk-002-b', 'kmj-btk-002-c'],
            ],
            [
                'category_id' => 3,
                'name'        => 'Kemeja Batik Modern Motif Mega Mendung',
                'sku'         => 'KMJ-BTK-003',
                'description' => 'Kemeja batik modern dengan motif mega mendung khas Cirebon. Perpaduan warna biru dan putih yang segar. Cocok untuk acara semi-formal atau dipakai sehari-hari.',
                'price'       => 275000,
                'stock'       => 30,
                'is_active'   => true,
                'seeds'       => ['kmj-btk-003-a', 'kmj-btk-003-b', 'kmj-btk-003-c'],
            ],

            // Oxford (category_id: 4)
            [
                'category_id' => 4,
                'name'        => 'Kemeja Oxford Button-Down Biru Muda',
                'sku'         => 'KMJ-OXF-001',
                'description' => 'Kemeja oxford klasik dengan kerah button-down. Bahan Oxford weave 100% cotton yang breathable dan tahan lama. Warna biru muda yang versatile, cocok untuk smart casual maupun formal.',
                'price'       => 225000,
                'stock'       => 40,
                'is_active'   => true,
                'seeds'       => ['kmj-oxf-001-a', 'kmj-oxf-001-b', 'kmj-oxf-001-c'],
            ],
            [
                'category_id' => 4,
                'name'        => 'Kemeja Oxford Stripe Putih Biru',
                'sku'         => 'KMJ-OXF-002',
                'description' => 'Kemeja oxford dengan motif stripe halus putih-biru. Tekstur oxford yang khas memberikan tampilan premium. Potongan semi-slim yang flattering untuk berbagai tipe tubuh.',
                'price'       => 235000,
                'stock'       => 35,
                'is_active'   => true,
                'seeds'       => ['kmj-oxf-002-a', 'kmj-oxf-002-b', 'kmj-oxf-002-c'],
            ],
            [
                'category_id' => 4,
                'name'        => 'Kemeja Oxford Polos Putih Premium',
                'sku'         => 'KMJ-OXF-003',
                'description' => 'Kemeja oxford putih yang timeless. Bahan cotton oxford premium dengan finishing yang rapi. Investasi terbaik untuk lemari pakaian kamu — bisa dipadukan dengan apa saja.',
                'price'       => 219000,
                'stock'       => 55,
                'is_active'   => true,
                'seeds'       => ['kmj-oxf-003-a', 'kmj-oxf-003-b', 'kmj-oxf-003-c'],
            ],

            // Lengan Pendek (category_id: 5)
            [
                'category_id' => 5,
                'name'        => 'Kemeja Lengan Pendek Rayon Polos',
                'sku'         => 'KMJ-LPS-001',
                'description' => 'Kemeja lengan pendek berbahan rayon yang ringan dan adem. Perfect untuk cuaca panas Indonesia. Tersedia dalam berbagai pilihan warna pastel dan earth tone.',
                'price'       => 145000,
                'stock'       => 70,
                'is_active'   => true,
                'seeds'       => ['kmj-lps-001-a', 'kmj-lps-001-b', 'kmj-lps-001-c'],
            ],
            [
                'category_id' => 5,
                'name'        => 'Kemeja Lengan Pendek Motif Tropical',
                'sku'         => 'KMJ-LPS-002',
                'description' => 'Kemeja lengan pendek dengan motif daun tropical yang trendi. Bahan viscose yang lembut dan jatuh. Tampil stand out dengan motif yang eye-catching untuk liburan atau casual hangout.',
                'price'       => 159000,
                'stock'       => 45,
                'is_active'   => true,
                'seeds'       => ['kmj-lps-002-a', 'kmj-lps-002-b', 'kmj-lps-002-c'],
            ],
            [
                'category_id' => 5,
                'name'        => 'Kemeja Lengan Pendek Katun Chambray',
                'sku'         => 'KMJ-LPS-003',
                'description' => 'Kemeja lengan pendek bahan chambray denim-look. Ringan namun tetap memberikan kesan smart casual. Warna indigo yang khas chambray cocok dipadukan dengan chino atau jeans.',
                'price'       => 169000,
                'stock'       => 40,
                'is_active'   => true,
                'seeds'       => ['kmj-lps-003-a', 'kmj-lps-003-b', 'kmj-lps-003-c'],
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($products as $data) {
            $seeds = $data['seeds'];
            unset($data['seeds']);

            $data['slug'] = Str::slug($data['name']);

            // Pastikan slug unik
            $slug  = $data['slug'];
            $count = 1;
            while (Product::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $slug . '-' . $count++;
            }

            // Skip jika SKU sudah ada
            if (Product::where('sku', $data['sku'])->exists()) {
                // Update thumbnail & images saja jika produk sudah ada
                $product = Product::where('sku', $data['sku'])->first();
                if (!$product->thumbnail) {
                    $product->update([
                        'thumbnail' => "https://picsum.photos/seed/{$seeds[0]}/600/800",
                    ]);
                }
                if ($product->images()->count() === 0) {
                    foreach ($seeds as $i => $seed) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path'       => "https://picsum.photos/seed/{$seed}/600/800",
                            'sort_order' => $i,
                        ]);
                    }
                }
                $skipped++;
                continue;
            }

            // Set thumbnail dari seed pertama
            $data['thumbnail'] = "https://picsum.photos/seed/{$seeds[0]}/600/800";

            $product = Product::create($data);

            // Buat 3 product_images per produk
            foreach ($seeds as $i => $seed) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => "https://picsum.photos/seed/{$seed}/600/800",
                    'sort_order' => $i,
                ]);
            }

            $created++;
        }

        $this->command->info("Seeder selesai: {$created} produk dibuat, {$skipped} produk sudah ada (thumbnail & images di-update).");
    }
}
