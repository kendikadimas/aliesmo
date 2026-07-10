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

        $origin      = (int) config('services.rajaongkir.origin_city', 39);
        $destination = (int) $request->destination;
        $weight      = (int) $request->weight;
        $areaId      = $request->input('area_id');
        $postalCode  = $request->input('postal_code');

        // Cache key mencakup area_id jika ada untuk akurasi lebih tinggi
        $cacheKey = $this->generateShippingCacheKey($origin, $destination, $weight, $areaId);

        // 1. Cek cache dulu — return langsung jika ada
        $cached = cache()->get($cacheKey);
        if ($cached !== null) {
            return response()->json([
                'data'      => $cached,
                'cache_key' => $cacheKey,
                'source'    => 'cache',
            ]);
        }

        // 2. Coba RajaOngkir (Komerce) — primary provider
        $costs  = null;
        $source = null;

        try {
            $costs  = $this->rajaOngkir->getAllShippingCosts($origin, $destination, $weight);
            $source = 'komerce';
        } catch (\RuntimeException $e) {
            Log::info('RajaOngkir tidak tersedia, coba Biteship fallback', [
                'reason'      => $e->getMessage(),
                'origin'      => $origin,
                'destination' => $destination,
                'weight'      => $weight,
            ]);

            // 3. Fallback ke Biteship — gunakan area_id (akurat) atau postal_code
            try {
                if ($areaId) {
                    Log::info('Biteship fallback via area_id', ['area_id' => $areaId]);
                    $costs  = $this->biteship->getAllShippingCostsByAreaId($areaId, $weight);
                    $source = 'biteship';
                } elseif ($postalCode) {
                    Log::info('Biteship fallback via postal_code', ['postal_code' => $postalCode]);
                    $costs  = $this->biteship->getAllShippingCosts($postalCode, $weight);
                    $source = 'biteship';
                } else {
                    $resolvedPostal = $this->getPostalCodeByCityId($destination);
                    Log::info('Biteship fallback via cities.json', ['resolved_postal' => $resolvedPostal]);
                    if ($resolvedPostal) {
                        $costs  = $this->biteship->getAllShippingCosts($resolvedPostal, $weight);
                        $source = 'biteship';
                    }
                }
            } catch (\RuntimeException $be) {
                Log::warning('Biteship fallback gagal', ['reason' => $be->getMessage()]);
            }
        }

        // 4. Jika keduanya gagal, arahkan ke admin WA toko
        if (empty($costs)) {
            Log::error('Semua provider ongkir gagal', [
                'destination' => $destination,
                'weight'      => $weight,
            ]);

            $cityName = $this->getCityNameById($destination) ?? 'kota tujuan';
            $waNumber = (string) SiteSetting::get('whatsapp_number', config('services.whatsapp.number', '6285196811722'));
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
     * Search destinasi — gabungkan hasil Komerce + Biteship Maps API.
     * Setiap hasil mengandung city_id (Komerce) + area_id + postal_code (Biteship).
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->q;

        // Fetch dari Komerce dan Biteship secara paralel
        $komerceResults  = $this->rajaOngkir->searchDestinations($query);
        $biteshipAreas   = $this->biteship->searchArea($query);

        // Buat lookup Biteship berdasarkan nama kota (lowercase) untuk matching
        $biteshipByCity = [];
        foreach ($biteshipAreas as $area) {
            $key = strtolower(trim($area['district'] ?: $area['city']));
            if ($key && !isset($biteshipByCity[$key])) {
                $biteshipByCity[$key] = $area;
            }
        }

        // Enrich hasil Komerce dengan data Biteship
        $enriched = array_map(function ($item) use ($biteshipByCity) {
            $cityKey  = strtolower(trim($item['city_name'] ?? ''));
            $match    = $biteshipByCity[$cityKey] ?? null;

            return [
                'id'          => (int) $item['id'],
                'label'       => $item['label'] ?? strtoupper(($item['city_name'] ?? '') . ', ' . ($item['province_name'] ?? '')),
                'city_name'   => $item['city_name'] ?? '',
                'province'    => $item['province_name'] ?? '',
                'zip_code'    => $item['zip_code'] ?? ($match['postal_code'] ?? ''),
                'area_id'     => $match['area_id'] ?? null,      // Biteship area_id untuk akurasi tinggi
                'postal_code' => $match['postal_code'] ?? ($item['zip_code'] ?? null), // untuk Biteship fallback
            ];
        }, $komerceResults);

        return response()->json([
            'data' => $enriched,
        ]);
    }

    // Naikkan versi ini setiap kali kurir whitelist berubah agar cache lama invalid
    private const CACHE_VERSION = 2;

    private function generateShippingCacheKey(int $origin, int $destination, int $weight, ?string $areaId = null): string
    {
        $key = "v" . self::CACHE_VERSION . ":{$origin}:{$destination}:{$weight}";
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
            'data' => $this->rajaOngkir->getAvailableCouriers(),
        ]);
    }
}
