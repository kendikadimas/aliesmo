<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantImportController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate(['tsv_data' => 'required|string']);

        $lines      = preg_split('/\r?\n/', $request->tsv_data);
        $variantMap = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $cols = str_contains($line, "\t")
                ? explode("\t", $line)
                : str_getcsv($line);
            $cols = array_map('trim', $cols);

            // auto-skip kolom NO (nomor urut) di awal
            if (count($cols) >= 6 && is_numeric($cols[0]) && !is_numeric($cols[1])) {
                array_shift($cols);
            }

            if (count($cols) < 5) continue;
            [$sku, $warna, $lengan, $ukuran, $stok] = $cols;
            if (!is_numeric($stok)) continue; // skip header

            $variantKey = $warna . ' - ' . $lengan;
            $variantMap[$variantKey][] = compact('sku', 'ukuran', 'stok');
        }

        if (empty($variantMap)) {
            return back()->with('error', 'Tidak ada data valid yang bisa diparse.');
        }

        DB::transaction(function () use ($product, $variantMap) {
            foreach ($variantMap as $variantName => $sizes) {
                // upsert variant berdasarkan nama — tidak duplikat kalau re-import
                $variant = $product->variants()->firstOrCreate(
                    ['name' => $variantName],
                    ['price' => $product->price, 'stock' => 0, 'is_active' => true, 'sort_order' => 0]
                );

                foreach ($sizes as $size) {
                    $variant->sizes()->updateOrCreate(
                        ['sku' => $size['sku']],
                        ['name' => $size['ukuran'], 'stock' => (int) $size['stok'], 'is_active' => true, 'sort_order' => 0]
                    );
                }
            }
        });

        $variantCount = count($variantMap);
        $skuCount     = array_sum(array_map('count', $variantMap));

        return redirect()
            ->to("/admin/products/{$product->id}/edit")
            ->with('success', "{$variantCount} varian, {$skuCount} SKU berhasil diimport.");
    }
}
