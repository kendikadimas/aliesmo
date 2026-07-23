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
            'weight'      => 'required|integer|min:1|max:100000',
            'area_id'     => 'nullable|string',
            'postal_code' => 'nullable|string',
            // cod_amount: total order dalam rupiah — jika diisi, rates akan include COD fee
            'cod_amount'  => 'nullable|integer|min:0',
        ]);

        $destination = (int) $request->input('destination', 0);
        $weight      = (int) $request->weight;
        $areaId      = $request->input('area_id');
        $postalCode  = $request->input('postal_code');
        $codAmount   = $request->input('cod_amount') ? (int) $request->input('cod_amount') : null;

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
    private const CACHE_VERSION = 5; // bump: COD fee dibebankan ke customer, cache COD/non-COD dipisah

    private function generateShippingCacheKey(int $destination, int $weight, ?string $areaId = null, ?int $codAmount = null): string
    {
        $key = "v" . self::CACHE_VERSION . ":{$destination}:{$weight}";
        if ($areaId) $key .= ":{$areaId}";
        // Cache COD terpisah dari non-COD — harga berbeda karena ada COD fee
        if ($codAmount !== null && $codAmount > 0) $key .= ":cod{$codAmount}";
        return 'shipping:' . md5($key);
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
