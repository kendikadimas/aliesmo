<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\RajaOngkirService;
use App\Services\BiteshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    public function __construct(
        private RajaOngkirService $rajaOngkir,
        private BiteshipService   $biteship,
    ) {}

    public function provinces()
    {
        return response()->json([
            'data' => $this->rajaOngkir->getProvinces(),
        ]);
    }

    public function cities(Request $request, int $provinceId)
    {
        return response()->json([
            'data' => $this->rajaOngkir->getCities($provinceId),
        ]);
    }

    public function cost(Request $request)
    {
        $request->validate([
            'destination' => 'required|integer',
            'weight'      => 'required|integer|min:1|max:100000',
            'area_id'     => 'nullable|string',   // Biteship area_id (opsional, dari autocomplete)
            'postal_code' => 'nullable|string',   // Biteship postal code (opsional)
        ]);

        $destination = (int) $request->destination;
        $weight      = (int) $request->weight;
        $areaId      = $request->input('area_id');
        $postalCode  = $request->input('postal_code');

        // Cache key mencakup area_id jika ada untuk akurasi lebih tinggi
        $cacheKey = $this->generateShippingCacheKey($destination, $weight, $areaId);

        // 1. Cek cache dulu — return langsung jika ada
        $cached = cache()->get($cacheKey);
        if ($cached !== null) {
            return response()->json([
                'data'      => $cached,
                'cache_key' => $cacheKey,
                'source'    => 'cache',
            ]);
        }

        // 2. Biteship sebagai primary provider
        $costs  = null;
        $source = null;

        try {
            if ($areaId) {
                Log::info('Biteship primary via area_id', ['area_id' => $areaId]);
                $costs  = $this->biteship->getAllShippingCostsByAreaId($areaId, $weight);
                $source = 'biteship';
            } elseif ($postalCode) {
                Log::info('Biteship primary via postal_code', ['postal_code' => $postalCode]);
                $costs  = $this->biteship->getAllShippingCosts($postalCode, $weight);
                $source = 'biteship';
            } else {
                $resolvedPostal = $this->getPostalCodeByCityId($destination);
                Log::info('Biteship primary via cities.json', ['resolved_postal' => $resolvedPostal]);
                if ($resolvedPostal) {
                    $costs  = $this->biteship->getAllShippingCosts($resolvedPostal, $weight);
                    $source = 'biteship';
                }
            }
        } catch (\RuntimeException $e) {
            Log::warning('Biteship gagal', ['reason' => $e->getMessage()]);
        }

        // 3. Jika Biteship gagal, arahkan ke admin WA toko
        if (empty($costs)) {
            Log::error('Semua provider ongkir gagal', [
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
     * Search destinasi — Biteship Maps API sebagai primary.
     * Komerce digunakan untuk enrich city_id (agar kompatibel dengan parameter ?destination=).
     * Setiap hasil mengandung area_id + postal_code (Biteship) + city_id (Komerce, opsional).
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->q;

        // Primary: Biteship Maps API
        $biteshipAreas  = $this->biteship->searchArea($query);

        // Enrichment: Komerce untuk mendapatkan city_id (integer) yang dipakai cache key
        // Juga dipakai sebagai fallback jika Biteship tidak tersedia
        $komerceResults = $this->rajaOngkir->searchDestinations($query);

        // Jika Biteship kosong (API key tidak ada atau gagal), fallback ke Komerce saja
        if (empty($biteshipAreas)) {
            $results = array_map(function ($item) {
                return [
                    'id'          => (int) $item['id'],
                    'label'       => $item['label'] ?? strtoupper(($item['city_name'] ?? '') . ', ' . ($item['province_name'] ?? '')),
                    'city_name'   => $item['city_name'] ?? '',
                    'province'    => $item['province_name'] ?? '',
                    'zip_code'    => $item['zip_code'] ?? '',
                    'area_id'     => null,
                    'postal_code' => $item['zip_code'] ?? null,
                ];
            }, $komerceResults);

            return response()->json(['data' => array_values($results)]);
        }

        // Buat lookup Komerce berdasarkan nama kota/kecamatan (lowercase)
        $komerceByCity = [];
        foreach ($komerceResults as $item) {
            $key = strtolower(trim($item['city_name'] ?? ''));
            if ($key && !isset($komerceByCity[$key])) {
                $komerceByCity[$key] = $item;
            }
        }

        // Bangun hasil dari Biteship, enrich dengan city_id dari Komerce
        $results = array_map(function ($area) use ($komerceByCity) {
            $cityKey = strtolower(trim($area['district'] ?: $area['city']));
            $match   = $komerceByCity[$cityKey] ?? null;

            // Fallback city_id: pakai 0 jika tidak ada match Komerce
            // cost() akan resolve via area_id atau postal_code jika city_id = 0
            $cityId = $match ? (int) $match['id'] : 0;

            $label = trim(implode(', ', array_filter([
                $area['district'],
                $area['city'],
                $area['province'],
            ])));

            return [
                'id'          => $cityId,
                'label'       => $label ?: ($area['label'] ?? ''),
                'city_name'   => $area['city'] ?? '',
                'province'    => $area['province'] ?? '',
                'zip_code'    => $area['postal_code'] ?? '',
                'area_id'     => $area['area_id'],          // Biteship area_id — primary untuk cost()
                'postal_code' => $area['postal_code'] ?? null,
            ];
        }, $biteshipAreas);

        // Hapus duplikat berdasarkan area_id
        $seen    = [];
        $results = array_values(array_filter($results, function ($r) use (&$seen) {
            if (empty($r['area_id']) || isset($seen[$r['area_id']])) return false;
            $seen[$r['area_id']] = true;
            return true;
        }));

        return response()->json([
            'data' => $results,
        ]);
    }

    // Naikkan versi ini setiap kali kurir whitelist berubah agar cache lama invalid
    private const CACHE_VERSION = 3;

    private function generateShippingCacheKey(int $destination, int $weight, ?string $areaId = null): string
    {
        $key = "v" . self::CACHE_VERSION . ":{$destination}:{$weight}";
        if ($areaId) $key .= ":{$areaId}";
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
