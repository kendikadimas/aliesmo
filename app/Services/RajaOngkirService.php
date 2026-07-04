<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
    protected ?string $apiKey;
    protected ?string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
        $this->baseUrl = config('services.rajaongkir.base_url');
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

    // All supported courier codes for a single Komerce request
    private const ALL_COURIERS = 'jne:jnt:sicepat:ninja:anteraja:tiki:pos:lion:sap:ide:ncs:rex:rpx:sentral:star:wahana:dse';

    // Human-readable name map for each courier code
    private const COURIER_NAMES = [
        'jne'      => 'JNE',
        'jnt'      => 'J&T Express',
        'sicepat'  => 'SiCepat',
        'ninja'    => 'Ninja Express',
        'anteraja' => 'AnterAja',
        'tiki'     => 'TIKI',
        'pos'      => 'POS Indonesia',
        'lion'     => 'Lion Parcel',
        'sap'      => 'SAP Express',
        'ide'      => 'ID Express',
        'ncs'      => 'NCS',
        'rex'      => 'REX',
        'rpx'      => 'RPX',
        'sentral'  => 'Sentral Cargo',
        'star'     => 'Star Cargo',
        'wahana'   => 'Wahana',
        'dse'      => 'DSE',
    ];

    public function getAllShippingCosts(int $originCity, int $destinationCity, int $weight): array
    {
        if ($this->apiKey && $this->baseUrl) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['key' => $this->apiKey])
                    ->asForm()
                    ->post("{$this->baseUrl}/calculate/domestic-cost", [
                        'origin'      => $originCity,
                        'destination' => $destinationCity,
                        'weight'      => $weight,
                        'courier'     => self::ALL_COURIERS,
                        'price'       => 'lowest',
                    ]);

                if ($response->successful()) {
                    $results = $response->json()['data'] ?? [];
                    if (!empty($results)) {
                        $mapped = array_map(function ($cost) {
                            $code = strtolower($cost['code'] ?? '');
                            return [
                                'code'        => $code,
                                'courier'     => self::COURIER_NAMES[$code] ?? strtoupper($code),
                                'service'     => $cost['service'],
                                'description' => $cost['description'],
                                'cost'        => (int) $cost['cost'],
                                'etd'         => $cost['etd'] ?? '-',
                            ];
                        }, $results);

                        // Filter out heavy cargo/trucking services not suitable for small parcels
                        // (cost > Rp500.000 or service codes that are clearly cargo/motor tiers)
                        $cargoKeywords = ['trucking', 'cargo', 'motor', 'freight'];
                        $filtered = array_filter($mapped, function ($item) use ($cargoKeywords) {
                            if ($item['cost'] > 500000) return false;
                            $desc = strtolower($item['description']);
                            foreach ($cargoKeywords as $kw) {
                                if (str_contains($desc, $kw)) return false;
                            }
                            return true;
                        });

                        if (!empty($filtered)) {
                            return array_values($filtered);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fail silently and fallback
            }
        }

        return $this->getStaticAllShippingCosts($originCity, $destinationCity, $weight);
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

    private function getStaticAllShippingCosts(int $originCity, int $destinationCity, int $weight): array
    {
        $rates = [
            'jne'      => ['rate' => 15000, 'etd' => '3-5'],
            'jnt'      => ['rate' => 15000, 'etd' => '2-4'],
            'sicepat'  => ['rate' => 14000, 'etd' => '2-3'],
            'ninja'    => ['rate' => 13000, 'etd' => '3-5'],
            'anteraja' => ['rate' => 13000, 'etd' => '2-5'],
            'tiki'     => ['rate' => 14000, 'etd' => '3-5'],
            'pos'      => ['rate' => 12000, 'etd' => '5-7'],
        ];

        $kg = max(1, ceil($weight / 1000));
        $results = [];
        foreach ($rates as $code => $info) {
            $results[] = [
                'code'        => $code,
                'courier'     => self::COURIER_NAMES[$code] ?? strtoupper($code),
                'service'     => 'REG',
                'description' => 'Layanan Reguler',
                'cost'        => $info['rate'] * $kg,
                'etd'         => $info['etd'],
            ];
        }
        return $results;
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
