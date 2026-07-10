<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected ?string $apiKey;
    protected ?string $baseUrl;
    protected ?int    $originId;   // subdistrict ID Ulujami dari search API (akurasi tinggi)
    protected int     $originCity; // city ID Pemalang (fallback jika originId tidak di-set)

    public function __construct()
    {
        $this->apiKey     = config('services.rajaongkir.api_key');
        $this->baseUrl    = config('services.rajaongkir.base_url');
        $this->originId   = config('services.rajaongkir.origin_id') ? (int) config('services.rajaongkir.origin_id') : null;
        $this->originCity = (int) config('services.rajaongkir.origin_city', 570);
    }

    public function getProvinces(): array
    {
        if ($this->apiKey && $this->baseUrl) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['key' => $this->apiKey])
                    ->get("{$this->baseUrl}/destination/province");

                if ($response->successful()) {
                    $results = $response->json()['data'] ?? [];
                    if (!empty($results)) {
                        return array_map(function ($p) {
                            return [
                                'province_id' => (string) $p['id'],
                                'province'    => $p['name'],
                            ];
                        }, $results);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('RajaOngkir getProvinces failed, using static fallback', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->getStaticProvinces();
    }

    public function getCities(int $provinceId): array
    {
        if ($this->apiKey && $this->baseUrl) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['key' => $this->apiKey])
                    ->get("{$this->baseUrl}/destination/city/{$provinceId}");

                if ($response->successful()) {
                    $results = $response->json()['data'] ?? [];
                    if (!empty($results)) {
                        return array_map(function ($c) use ($provinceId) {
                            return [
                                'city_id'     => (string) $c['id'],
                                'province_id' => (string) $provinceId,
                                'province'    => '',
                                'type'        => '',
                                'city_name'   => $c['name'],
                                'postal_code' => '',
                            ];
                        }, $results);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('RajaOngkir getCities failed, using static fallback', [
                    'province_id' => $provinceId,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        return $this->getStaticCities($provinceId);
    }

    // Kurir yang ditampilkan ke pelanggan
    private const ALL_COURIERS = 'jne:jnt:sicepat:ninja:anteraja:pos:lion';

    // Whitelist kurir — hanya ini yang tampil di hasil cek ongkir
    private const ALLOWED_COURIERS = ['jne', 'jnt', 'sicepat', 'anteraja', 'ninja', 'pos', 'lion'];

    // Human-readable name map — hanya kurir yang diizinkan
    private const COURIER_NAMES = [
        'jne'      => 'JNE',
        'jnt'      => 'J&T Express',
        'sicepat'  => 'SiCepat',
        'anteraja' => 'AnterAja',
        'ninja'    => 'Ninja Express',
        'pos'      => 'POS Indonesia',
        'lion'     => 'Lion Parcel',
    ];

    public function getAllShippingCosts(int $originCity, int $destinationCity, int $weight): array
    {
        if (!$this->apiKey || !$this->baseUrl) {
            throw new \RuntimeException('RajaOngkir API key tidak dikonfigurasi.');
        }

        // Cek daily hit counter — jika sudah mendekati limit, langsung throw
        $dailyLimit = (int) config('services.rajaongkir.daily_limit', 95);
        if ($this->getDailyHits() >= $dailyLimit) {
            throw new \RuntimeException('RajaOngkir daily limit tercapai (' . $dailyLimit . ' hits).');
        }

        // Pakai subdistrict ID (origin_id) jika dikonfigurasi — akurasi lebih tinggi per docs Komerce.
        // Jika tidak ada, fallback ke city ID (origin_city).
        $origin = $this->originId ?? $originCity;

        $response = Http::timeout(10)
            ->withHeaders(['key' => $this->apiKey])
            ->asForm()
            ->post("{$this->baseUrl}/calculate/domestic-cost", [
                'origin'      => $origin,
                'destination' => $destinationCity,
                'weight'      => $weight,
                'courier'     => self::ALL_COURIERS,
                'price'       => 'lowest',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('RajaOngkir API error: HTTP ' . $response->status());
        }

        $results = $response->json()['data'] ?? [];

        if (empty($results)) {
            throw new \RuntimeException('RajaOngkir tidak mengembalikan data harga.');
        }

        // Increment hit counter setelah request sukses
        $this->incrementDailyHits();

        $mapped = array_map(function ($cost) {
            $code = strtolower($cost['code'] ?? '');
            return [
                'code'        => $code,
                'courier'     => self::COURIER_NAMES[$code] ?? strtoupper($code),
                'service'     => $cost['service'],
                'description' => $cost['description'],
                'cost'        => (int) $cost['cost'],
                'etd'         => $cost['etd'] ?? '-',
                'source'      => 'komerce',
            ];
        }, $results);

        // Filter out heavy cargo/trucking services
        $cargoKeywords = ['trucking', 'cargo', 'motor', 'freight'];
        $filtered = array_filter($mapped, function ($item) use ($cargoKeywords) {
            if ($item['cost'] > 500000) return false;
            $desc = strtolower($item['description']);
            foreach ($cargoKeywords as $kw) {
                if (str_contains($desc, $kw)) return false;
            }
            return true;
        });

        // Hanya tampilkan kurir yang diizinkan
        $filtered = array_filter($filtered, function ($item) {
            return in_array($item['code'], self::ALLOWED_COURIERS, true);
        });

        if (empty($filtered)) {
            throw new \RuntimeException('RajaOngkir tidak ada hasil setelah filter.');
        }

        return array_values($filtered);
    }

    /**
     * Ambil jumlah hit hari ini dari cache.
     */
    public function getDailyHits(): int
    {
        return (int) cache()->get($this->dailyHitKey(), 0);
    }

    /**
     * Increment daily hit counter, TTL sampai tengah malam.
     */
    private function incrementDailyHits(): void
    {
        $key     = $this->dailyHitKey();
        $current = (int) cache()->get($key, 0);
        $ttl     = now()->endOfDay();
        cache()->put($key, $current + 1, $ttl);
    }

    /**
     * Cache key untuk hit counter harian — reset otomatis tiap hari.
     */
    private function dailyHitKey(): string
    {
        return 'rajaongkir:daily_hits:' . now()->format('Y-m-d');
    }

    public function getAvailableCouriers(): array
    {
        return array_map(fn($code, $name) => ['code' => $code, 'name' => $name],
            array_keys(self::COURIER_NAMES), array_values(self::COURIER_NAMES));
    }

    private function getStaticProvinces(): array
    {
        $path = app_path('Services/data/provinces.json');
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $provinces = json_decode($content, true);

        if (!is_array($provinces)) {
            return [];
        }

        return array_map(function ($p) {
            return [
                'province_id' => (string) $p['id'],
                'province'    => $p['name'],
            ];
        }, $provinces);
    }

    private function getStaticCities(int $provinceId): array
    {
        $path = app_path('Services/data/cities.json');
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $cities = json_decode($content, true);

        if (!is_array($cities)) {
            return [];
        }

        $filtered = array_filter($cities, function ($c) use ($provinceId) {
            return (int) ($c['province_id'] ?? 0) === $provinceId;
        });

        return array_map(function ($c) {
            return [
                'city_id'     => (string) $c['id'],
                'province_id' => (string) $c['province_id'],
                'province'    => $c['province_name'] ?? '',
                'type'        => $c['type'] ?? '',
                'city_name'   => $c['city_name'] ?? '',
                'postal_code' => (string) ($c['postal_code'] ?? ''),
            ];
        }, array_values($filtered));
    }

    public function searchDestinations(string $query): array
    {
        if ($this->apiKey && $this->baseUrl) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['key' => $this->apiKey])
                    ->get("{$this->baseUrl}/destination/domestic-destination", [
                        'search' => $query,
                        'limit'  => 30,
                    ]);

                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }
            } catch (\Exception $e) {
                // Fail silently and fallback
            }
        }

        return $this->getStaticDestinations($query);
    }

    private function getStaticDestinations(string $query): array
    {
        $path = app_path('Services/data/cities.json');
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $cities = json_decode($content, true);

        if (!is_array($cities)) {
            return [];
        }

        $queryLower = strtolower($query);
        $filtered = array_filter($cities, function ($c) use ($queryLower) {
            $cityName = strtolower($c['city_name'] ?? '');
            $provinceName = strtolower($c['province_name'] ?? '');
            return str_contains($cityName, $queryLower) || str_contains($provinceName, $queryLower);
        });

        // Limit results to 30
        $filtered = array_slice($filtered, 0, 30);

        return array_map(function ($c) {
            $label = $c['city_name'];
            if (!empty($c['province_name'])) {
                $label .= ', ' . $c['province_name'];
            }
            return [
                'id'               => (int) $c['id'],
                'label'            => strtoupper($label),
                'province_name'    => $c['province_name'] ?? '',
                'city_name'        => $c['city_name'] ?? '',
                'district_name'    => '',
                'subdistrict_name' => '',
                'zip_code'         => $c['postal_code'] ?? '',
            ];
        }, array_values($filtered));
    }
}
