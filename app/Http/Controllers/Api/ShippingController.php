<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\BiteshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    public function __construct(
        private BiteshipService $biteship,
    ) {}

    public function cost(Request $request)
    {
        $request->validate([
            'destination' => 'nullable|integer',
            // items (server weight) ATAU weight fallback — salah satu wajib
            'weight'      => 'required_without:items|nullable|integer|min:1|max:100000',
            'area_id'     => 'nullable|string',
            'postal_code' => 'nullable|string',
            // cod_amount: total order dalam rupiah — jika diisi, rates akan include COD fee
            'cod_amount'  => 'nullable|integer|min:0',
            'items'                  => 'required_without:weight|nullable|array|max:20',
            'items.*.product_id'     => 'required_with:items|integer|exists:products,id',
            'items.*.variant_id'     => 'nullable|integer|exists:product_variants,id',
            'items.*.size_id'        => 'nullable|integer|exists:product_variant_sizes,id',
            'items.*.quantity'       => 'required_with:items|integer|min:1|max:100',
        ]);

        $destination = (int) $request->input('destination', 0);
        $areaId      = $request->input('area_id');
        $postalCode  = $request->input('postal_code');
        $codAmount   = $request->input('cod_amount') ? (int) $request->input('cod_amount') : null;

        if (!$areaId && !$postalCode && $destination <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'destination' => ['Tujuan pengiriman wajib diisi (area_id, postal_code, atau destination).'],
            ]);
        }

        // Server-side weight dari items — client weight hanya fallback tanpa items
        $weight = !empty($request->items)
            ? $this->computeWeightFromItems($request->items)
            : max(1, (int) $request->input('weight', 300));

        // Cache key berbeda untuk COD vs non-COD — harga berbeda karena ada COD fee
        $cacheKey = $this->generateShippingCacheKey($destination, $weight, $areaId, $codAmount);

        // 1. Cek cache dulu — return langsung jika ada
        $cached = cache()->get($cacheKey);
        if ($cached !== null) {
            return response()->json([
                'data'      => $cached,
                'cache_key' => $cacheKey,
                'source'    => 'cache',
            ]);
        }

        // 2. Biteship — satu-satunya provider
        $costs  = null;
        $source = null;

        try {
            if ($areaId) {
                Log::info('Biteship cost via area_id', ['area_id' => $areaId, 'cod_amount' => $codAmount]);
                $costs  = $this->biteship->getAllShippingCostsByAreaId($areaId, $weight, 100000, $codAmount);
                $source = 'biteship';
            } elseif ($postalCode) {
                Log::info('Biteship cost via postal_code', ['postal_code' => $postalCode, 'cod_amount' => $codAmount]);
                $costs  = $this->biteship->getAllShippingCosts($postalCode, $weight, $codAmount);
                $source = 'biteship';
            } else {
                $resolvedPostal = $this->getPostalCodeByCityId($destination);
                Log::info('Biteship cost via cities.json', ['resolved_postal' => $resolvedPostal]);
                if ($resolvedPostal) {
                    $costs  = $this->biteship->getAllShippingCosts($resolvedPostal, $weight, $codAmount);
                    $source = 'biteship';
                }
            }
        } catch (\RuntimeException $e) {
            Log::warning('Biteship cost gagal', ['reason' => $e->getMessage()]);
        }

        // 3. Jika Biteship tidak tersedia, arahkan ke admin WA toko
        if (empty($costs)) {
            Log::error('Biteship tidak dapat menghitung ongkir', [
                'destination' => $destination,
                'weight'      => $weight,
            ]);

            $cityName = $this->getCityNameById($destination) ?? 'kota tujuan';
            $waNumber = (string) SiteSetting::get('whatsapp_number', config('services.whatsapp.number', '628138883345'));
            $waText   = "Halo Admin Aliesmo, saya ingin tanya ongkir pengiriman ke {$cityName} dengan berat {$weight} gram. Mohon infonya, terima kasih!";

            return response()->json([
                'manual'   => true,
                'message'  => 'Cek ongkir otomatis tidak tersedia saat ini.',
                'whatsapp' => [
                    'number' => $waNumber,
                    'text'   => $waText,
                ],
            ], 200);
        }

        // Simpan ke cache 24 jam
        cache()->put($cacheKey, $costs, now()->addHours(24));

        return response()->json([
            'data'      => $costs,
            'cache_key' => $cacheKey,
            'source'    => $source,
        ]);
    }

    /**
     * Search destinasi via Biteship Maps API.
     * Hasil mengandung area_id + postal_code + label lengkap dari Biteship.
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $biteshipAreas = $this->biteship->searchArea($request->q);

        if (empty($biteshipAreas)) {
            return response()->json(['data' => []]);
        }

        // Bangun hasil dari Biteship, hapus duplikat berdasarkan area_id
        $seen    = [];
        $results = [];

        foreach ($biteshipAreas as $area) {
            $areaId = $area['area_id'] ?? null;
            if (empty($areaId) || isset($seen[$areaId])) continue;
            $seen[$areaId] = true;

            $label = trim(implode(', ', array_filter([
                $area['district'] ?? '',
                $area['city'] ?? '',
                $area['province'] ?? '',
            ])));

            $results[] = [
                'id'          => 0,                          // city_id tidak digunakan, cost() pakai area_id
                'label'       => $label ?: ($area['label'] ?? ''),
                'city_name'   => $area['city'] ?? '',
                'district'    => $area['district'] ?? '',
                'province'    => $area['province'] ?? '',
                'zip_code'    => $area['postal_code'] ?? '',
                'area_id'     => $areaId,
                'postal_code' => $area['postal_code'] ?? null,
                'latitude'    => $area['latitude'] ?? null,
                'longitude'   => $area['longitude'] ?? null,
            ];
        }

        return response()->json(['data' => $results]);
    }

    // Naikkan versi ini setiap kali kurir whitelist berubah agar cache lama invalid
    private const CACHE_VERSION = 6; // bump: server-side weight recompute

    private function generateShippingCacheKey(int $destination, int $weight, ?string $areaId = null, ?int $codAmount = null): string
    {
        $key = "v" . self::CACHE_VERSION . ":{$destination}:{$weight}";
        if ($areaId) $key .= ":{$areaId}";
        // Cache COD terpisah dari non-COD — harga berbeda karena ada COD fee
        if ($codAmount !== null && $codAmount > 0) $key .= ":cod{$codAmount}";
        return 'shipping:' . md5($key);
    }

    /**
     * Hitung berat total (gram) dari product/variant/size di DB — bukan input client.
     */
    private function computeWeightFromItems(array $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $weight = 300;

            if (!empty($item['size_id'])) {
                $size = \App\Models\ProductVariantSize::with('variant.product')->find($item['size_id']);
                $weight = $size?->weight
                    ?? $size?->variant?->weight
                    ?? $size?->variant?->product?->weight
                    ?? 300;
            } elseif (!empty($item['variant_id'])) {
                $variant = \App\Models\ProductVariant::with('product')->find($item['variant_id']);
                $weight = $variant?->weight ?? $variant?->product?->weight ?? 300;
            } elseif (!empty($item['product_id'])) {
                $product = \App\Models\Product::find($item['product_id']);
                $weight = $product?->weight ?? 300;
            }

            $total += max(1, (int) $weight) * $qty;
        }

        return max(1, $total);
    }

    /**
     * Lookup postal code dari cities.json berdasarkan city_id.
     */
    private function getPostalCodeByCityId(int $cityId): ?string
    {
        $path = app_path('Services/data/cities.json');
        if (!file_exists($path)) return null;

        $cities = json_decode(file_get_contents($path), true);
        if (!is_array($cities)) return null;

        foreach ($cities as $city) {
            if ((int) ($city['id'] ?? 0) === $cityId) {
                $postal = trim((string) ($city['postal_code'] ?? ''));
                return $postal !== '' ? $postal : null;
            }
        }

        return null;
    }

    /**
     * Lookup nama kota dari cities.json berdasarkan city_id.
     */
    private function getCityNameById(int $cityId): ?string
    {
        $path = app_path('Services/data/cities.json');
        if (!file_exists($path)) return null;

        $cities = json_decode(file_get_contents($path), true);
        if (!is_array($cities)) return null;

        foreach ($cities as $city) {
            if ((int) ($city['id'] ?? 0) === $cityId) {
                $name = trim((string) ($city['city_name'] ?? ''));
                $prov = trim((string) ($city['province_name'] ?? ''));
                if ($name === '') return null;
                return $prov ? "{$name}, {$prov}" : $name;
            }
        }

        return null;
    }

    public function couriers()
    {
        return response()->json([
            'data' => $this->biteship->getAvailableCouriers(),
        ]);
    }
}
