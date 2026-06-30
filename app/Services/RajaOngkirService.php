<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
        $this->baseUrl = config('services.rajaongkir.base_url');
    }

    public function getProvinces(): array
    {
        $response = Http::withHeaders(['key' => $this->apiKey])
            ->get("{$this->baseUrl}/province");

        return $response->successful()
            ? ($response->json()['rajaongkir']['results'] ?? [])
            : [];
    }

    public function getCities(int $provinceId): array
    {
        $response = Http::withHeaders(['key' => $this->apiKey])
            ->get("{$this->baseUrl}/city", ['province' => $provinceId]);

        return $response->successful()
            ? ($response->json()['rajaongkir']['results'] ?? [])
            : [];
    }

    public function getShippingCost(int $originCity, int $destinationCity, int $weight, string $courier): array
    {
        $response = Http::withHeaders(['key' => $this->apiKey])
            ->post("{$this->baseUrl}/cost", [
                'origin' => $originCity,
                'destination' => $destinationCity,
                'weight' => $weight,
                'courier' => $courier,
            ]);

        if (!$response->successful()) return [];

        $results = $response->json()['rajaongkir']['results'] ?? [];
        if (empty($results)) return [];

        $costs = [];
        foreach ($results[0]['costs'] as $cost) {
            $costs[] = [
                'service' => $cost['service'],
                'description' => $cost['description'],
                'cost' => $cost['cost'][0]['value'],
                'etd' => $cost['cost'][0]['etd'],
            ];
        }

        return $costs;
    }

    public function getAvailableCouriers(): array
    {
        return [
            ['code' => 'jne', 'name' => 'JNE'],
            ['code' => 'tiki', 'name' => 'TIKI'],
            ['code' => 'pos', 'name' => 'POS Indonesia'],
        ];
    }
}
