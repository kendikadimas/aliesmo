<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\StockService;
use App\Enums\StockMovementType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private array $categories = [
        ['name' => 'Formal', 'slug' => 'kemeja-formal'],
        ['name' => 'Casual', 'slug' => 'kemeja-casual'],
        ['name' => 'Batik', 'slug' => 'kemeja-batik'],
        ['name' => 'Oxford', 'slug' => 'kemeja-oxford'],
        ['name' => 'Lengan Pendek', 'slug' => 'kemeja-lengan-pendek'],
    ];

    private array $products = [
        'kemeja-formal' => [
            [
                'name' => 'Kemeja Putih Oxford Premium',
                'description' => 'Kemeja putih favorit semua orang! Bahan oxford premium yang adem dan tidak mudah kusut, cocok dipakai ke kantor, kondangan, atau acara formal lainnya. Dilengkapi dengan kerah kaku yang tetap rapi seharian. Dijamin kamu bakal pede dan tampil maksimal!',
                'price' => 149000,
                'stock' => 15,
                'images' => ['kemeja-putih-1', 'kemeja-putih-1a', 'kemeja-putih-1b'],
            ],
            [
                'name' => 'Kemeja Navy Slim Fit',
                'description' => 'Buat kamu yang suka tampil rapi dan modern. Potongan slim fit navy ini pas banget di badan, memberikan siluet yang lebih ramping tanpa membuat kamu merasa sesak. Cocok untuk interview, meeting, atau acara formal lainnya.',
                'price' => 169000,
                'stock' => 23,
                'images' => ['kemeja-navy-2', 'kemeja-navy-2a', 'kemeja-navy-2b'],
            ],
            [
                'name' => 'Kemeja White French Cuff',
                'description' => 'Mau tampil beda dan elegan? Kemeja french cuff ini pilihan tepat! Tambahkan cufflink favoritmu biar makin berkelas. Cocok buat acara spesial seperti wedding, gala dinner, atau anniversary. Dijamin kamu bakal jadi pusat perhatian!',
                'price' => 199000,
                'stock' => 8,
                'images' => ['kemeja-french-7', 'kemeja-french-7a', 'kemeja-french-7b'],
            ],
            [
                'name' => 'Kemeja Grey Slim Fit',
                'description' => 'Warna grey yang versatile dan mudah dipadukan dengan apapun! Potongan slim fit bikin kamu tampil lebih rapi dan profesional. Cocok untuk daily office wear atau acara semi-formal. Bahan katun premium yang adem dan nyaman dipakai seharian.',
                'price' => 159000,
                'stock' => 20,
                'images' => ['kemeja-grey-11', 'kemeja-grey-11a', 'kemeja-grey-11b'],
            ],
            [
                'name' => 'Kemeja Dusty Pink Regular Fit',
                'description' => 'Tampil beda dengan warna dusty pink yang kalem dan sophisticated. Potongan regular fit yang nyaman di badan, cocok untuk kamu yang ingin tampil stylish tanpa ribet. Padukan dengan celana chino atau slacks untuk look yang sempurna.',
                'price' => 154000,
                'stock' => 12,
                'images' => ['kemeja-pink-dusty', 'kemeja-pink-dusty-a', 'kemeja-pink-dusty-b'],
            ],
            [
                'name' => 'Kemeja Putih Klasik Lengan Panjang',
                'description' => 'Kemeja putih klasik yang wajib ada di lemari setiap pria. Dibuat dari bahan katun premium dengan jahitan presisi. Cocok untuk segala acara formal maupun semi-formal. Investasi fashion yang gak bakal rugi!',
                'price' => 139000,
                'stock' => 30,
                'images' => ['kemeja-putih-klasik', 'kemeja-putih-klasik-a', 'kemeja-putih-klasik-b'],
            ],
        ],
        'kemeja-casual' => [
            [
                'name' => 'Kemeja Casual Linen Beige',
                'description' => 'Cuaca panas? Tenang, kemeja linen ini solusinya! Bahannya ringan, adem, dan menyerap keringat dengan baik. Cocok buat hangout santai, jalan-jalan weekend, atau liburan ke pantai. Warna beige yang timeless dan gampang dipadukan.',
                'price' => 139000,
                'stock' => 30,
                'images' => ['kemeja-beige-3', 'kemeja-beige-3a', 'kemeja-beige-3b'],
            ],
            [
                'name' => 'Kemeja Denim Casual Light Wash',
                'description' => 'Buat kamu yang suka gaya maskulin dan santai. Bahan denim ringan yang nyaman dipakai seharian. Cocok buat daily wear, hangout bareng teman, atau date santai. Tambah kece dengan digulung sedikit di bagian lengan!',
                'price' => 149000,
                'stock' => 12,
                'images' => ['kemeja-denim-6', 'kemeja-denim-6a', 'kemeja-denim-6b'],
            ],
            [
                'name' => 'Kemeja Plaid Merah Flannel',
                'description' => 'Buat kamu yang suka gaya flannel khas anak muda! Motif plaid merah yang keren dan eye-catching. Cocok buat hangout santai, nonton konser, atau date di akhir pekan. Dipadukan dengan jeans favoritmu auto keren!',
                'price' => 139000,
                'stock' => 0,
                'images' => ['kemeja-plaid-12', 'kemeja-plaid-12a', 'kemeja-plaid-12b'],
            ],
            [
                'name' => 'Kemeja Chambray Biru Muda',
                'description' => 'Kemeja chambray dengan bahan ringan yang nyaman dipakai sepanjang hari. Warna biru muda yang segar dan cocok untuk tampilan smart casual sehari-hari. Bisa dipakai ke kantor casual atau hangout bareng teman.',
                'price' => 129000,
                'stock' => 18,
                'images' => ['kemeja-chambray-blue', 'kemeja-chambray-blue-a', 'kemeja-chambray-blue-b'],
            ],
            [
                'name' => 'Kemeja Flanel Kotak-kotak Hijau',
                'description' => 'Tampil casual dengan motif flanel kotak-kotak hijau yang khas. Bahannya tebal dan hangat, cocok buat cuaca dingin atau ruangan ber-AC. Padukan dengan jacket kulit atau jeans untuk look yang maskulin.',
                'price' => 149000,
                'stock' => 7,
                'images' => ['kemeja-flanel-hijau', 'kemeja-flanel-hijau-a', 'kemeja-flanel-hijau-b'],
            ],
        ],
        'kemeja-batik' => [
            [
                'name' => 'Kemeja Batik Modern Cirebon',
                'description' => 'Tampil kece dengan sentuhan tradisional yang modern! Motif batik Cirebon yang keren dengan warna-warna kontemporer. Cocok buat acara semi-formal, kondangan, atau office wear yang ingin tampil beda. Dijamin kamu bakal standout!',
                'price' => 189000,
                'stock' => 0,
                'images' => ['kemeja-batik-4', 'kemeja-batik-4a', 'kemeja-batik-4b'],
            ],
            [
                'name' => 'Kemeja Batik Parang Solo',
                'description' => 'Motif batik Parang yang ikonik dengan warna hangat dan elegan. Melambangkan semangat yang tak pernah padam. Cocok buat kamu yang mau tampil tradisional tapi tetap stylish. Dibuat dari bahan katun premium yang nyaman.',
                'price' => 199000,
                'stock' => 10,
                'images' => ['kemeja-batik-9', 'kemeja-batik-9a', 'kemeja-batik-9b'],
            ],
            [
                'name' => 'Kemeja Batik Mega Mendung',
                'description' => 'Motif Mega Mendung khas Cirebon dengan awan-awan yang artistik. Warna biru dan putih yang segar cocok untuk acara formal maupun semi-formal. Bahan katun premium yang adem dan nyaman dipakai seharian.',
                'price' => 179000,
                'stock' => 5,
                'images' => ['kemeja-batik-mega', 'kemeja-batik-mega-a', 'kemeja-batik-mega-b'],
            ],
            [
                'name' => 'Kemeja Batik Kontemporer Hitam',
                'description' => 'Batik dengan sentuhan kontemporer warna hitam yang elegan. Cocok untuk kamu yang ingin tampil formal dengan nuansa tradisional. Motif abstrak yang modern dan tidak pasaran. Wajib punya buat koleksi batik kamu!',
                'price' => 209000,
                'stock' => 14,
                'images' => ['kemeja-batik-hitam', 'kemeja-batik-hitam-a', 'kemeja-batik-hitam-b'],
            ],
        ],
        'kemeja-oxford' => [
            [
                'name' => 'Kemeja Oxford Biru Stripe',
                'description' => 'Motif stripe biru yang klasik dan gak pernah ketinggalan zaman. Bahan oxford yang tebal tapi tetap nyaman. Cocok dipaduin sama celana apapun, dari chino sampai jeans, dijamin anti mainstream!',
                'price' => 159000,
                'stock' => 18,
                'images' => ['kemeja-stripe-5', 'kemeja-stripe-5a', 'kemeja-stripe-5b'],
            ],
            [
                'name' => 'Kemeja Oxford Pink Klasik',
                'description' => 'Berani tampil beda? Pink klasik ini sophisticated banget! Warna pink yang tidak norak dan tetap maskulin. Dijamin kamu bakal jadi pusat perhatian dengan gaya berani dan percaya diri. Bahan oxford premium yang adem.',
                'price' => 159000,
                'stock' => 14,
                'images' => ['kemeja-pink-10', 'kemeja-pink-10a', 'kemeja-pink-10b'],
            ],
            [
                'name' => 'Kemeja Oxford Putih Navy Stripe',
                'description' => 'Kombinasi putih dan navy stripe yang elegan. Cocok untuk tampilan smart casual yang rapi tapi tetap santai. Bisa dipakai ke kantor, brunch, atau hangout. Bahan oxford tebal yang awet dan tahan lama.',
                'price' => 164000,
                'stock' => 22,
                'images' => ['kemeja-oxford-navy', 'kemeja-oxford-navy-a', 'kemeja-oxford-navy-b'],
            ],
            [
                'name' => 'Kemeja Oxford Hitam Polos',
                'description' => 'Kemeja oxford hitam yang wajib ada di lemari! Warna hitam yang sleek dan mudah dipadukan dengan apapun. Cocok untuk acara semi-formal, dinner, atau daily wear yang ingin tampil keren dan misterius.',
                'price' => 154000,
                'stock' => 16,
                'images' => ['kemeja-oxford-hitam', 'kemeja-oxford-hitam-a', 'kemeja-oxford-hitam-b'],
            ],
        ],
        'kemeja-lengan-pendek' => [
            [
                'name' => 'Kemeja Lengan Pendek Chambray',
                'description' => 'Pas buat iklim tropis! Lengan pendek bahan chambray yang ringan dan adem. Tetap gaya walau gerah, cocok buat daily wear atau hangout. Wajib punya buat koleksi kamu, apalagi buat yang tinggal di kota panas!',
                'price' => 119000,
                'stock' => 25,
                'images' => ['kemeja-chambray-8', 'kemeja-chambray-8a', 'kemeja-chambray-8b'],
            ],
            [
                'name' => 'Kemeja Lengan Pendek Putih Casual',
                'description' => 'Kemeja putih lengan pendek yang simpel tapi tetap stylish. Cocok untuk hangout santai, jalan-jalan, atau brunch bareng teman. Bahannya ringan dan adem, dijamin nyaman dipakai seharian!',
                'price' => 109000,
                'stock' => 35,
                'images' => ['kemeja-lp-putih', 'kemeja-lp-putih-a', 'kemeja-lp-putih-b'],
            ],
            [
                'name' => 'Kemeja Lengan Pendek Motif Tropis',
                'description' => 'Liburan ke pantai? Pakai ini auto makin liburan! Motif tropis yang colorful dan ceria. Bahan katun ringan yang cepat kering dan nyaman. Cocok buat vacation, staycation, atau hangout santai di akhir pekan.',
                'price' => 129000,
                'stock' => 11,
                'images' => ['kemeja-tropis', 'kemeja-tropis-a', 'kemeja-tropis-b'],
            ],
            [
                'name' => 'Kemeja Lengan Pendek Denim Biru',
                'description' => 'Kemeja denim lengan pendek yang casual dan maskulin. Dibuat dari bahan denim ringan yang nyaman. Cocok dipadukan dengan celana putih atau chino untuk look yang fresh. Wajib punya buat koleksi casual kamu!',
                'price' => 139000,
                'stock' => 9,
                'images' => ['kemeja-lp-denim', 'kemeja-lp-denim-a', 'kemeja-lp-denim-b'],
            ],
        ],
    ];

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $admin = \App\Models\User::where('role', 'admin')->first();
        $stockService = app(StockService::class);

        foreach ($this->categories as $catData) {
            Category::updateOrCreate(
                ['slug' => $catData['slug']],
                ['name' => $catData['name']]
            );
        }

        $categoryMap = Category::pluck('id', 'slug');

        foreach ($this->products as $catSlug => $products) {
            $categoryId = $categoryMap[$catSlug] ?? null;
            if (!$categoryId) continue;

            foreach ($products as $data) {
                $slug = Str::slug($data['name']);
                $sku = 'SKU-' . strtoupper(Str::random(6));

                $product = Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $categoryId,
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'price' => $data['price'],
                        'stock' => 0,
                        'sku' => $sku,
                        'is_active' => true,
                        'thumbnail' => "https://picsum.photos/seed/{$data['images'][0]}/600/800",
                    ]
                );

                ProductImage::where('product_id', $product->id)->delete();
                foreach ($data['images'] as $i => $seed) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => "https://picsum.photos/seed/{$seed}/600/800",
                        'sort_order' => $i,
                    ]);
                }

                if ($data['stock'] > 0 && $admin) {
                    try {
                        $stockService->adjustStock(
                            $product->id,
                            $data['stock'],
                            StockMovementType::Initial,
                            'Initial stock from seeder',
                            $admin
                        );
                    } catch (\Exception $e) {
                        $product->update(['stock' => $data['stock']]);
                    }
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
