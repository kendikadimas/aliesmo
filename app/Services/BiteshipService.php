<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiteshipService
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $originPostal;
    protected ?string $originAreaId; // Biteship area_id Ulujami — akurasi tertinggi

    // Kurir yang ditampilkan ke pelanggan
    private const COURIERS = 'jne,jnt,pos';

    // Mapping courier_code Biteship → nama tampilan
    private const COURIER_NAMES = [
        'jne' => 'JNE',
        'jnt' => 'J&T Express',
        'pos' => 'POS Indonesia',
    ];

    public function __construct()
    {
        $this->apiKey       = config('services.biteship.api_key');
        $this->baseUrl      = config('services.biteship.base_url', 'https://api.biteship.com');
        $this->originPostal  = config('services.biteship.origin_postal', '52371');
        $this->originAreaId = config('services.biteship.origin_area_id') ?: null;
    }

    /**
     * Search area menggunakan Biteship Maps API.
     * Return array of { area_id, postal_code, label, province, city, district }
     */
    public function searchArea(string $query): array
    {
        if (!$this->isAvailable()) return [];

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->get("{$this->baseUrl}/v1/maps/areas", [
                    'countries'     => 'ID',
                    'input'         => $query,
                    'type'          => 'single',
                ]);

            if (!$response->successful()) return [];

            $areas = $response->json('areas') ?? [];

            return array_map(function ($area) {
                return [
                    'area_id'     => $area['id'] ?? '',
                    'postal_code' => $area['postal_code'] ?? '',
                    'label'       => $area['name'] ?? '',
                    'province'    => $area['administrative_division_level_1_name'] ?? '',
                    'city'        => $area['administrative_division_level_2_name'] ?? '',
                    'district'    => $area['administrative_division_level_3_name'] ?? '',
                ];
            }, array_slice($areas, 0, 20));

        } catch (\Exception $e) {
            Log::warning('Biteship searchArea gagal', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Return daftar kurir yang tersedia — format sama dengan RajaOngkirService::getAvailableCouriers().
     */
    public function getAvailableCouriers(): array
    {
        $couriers = [];
        foreach (self::COURIER_NAMES as $code => $name) {
            $couriers[] = [
                'code' => $code,
                'name' => $name,
            ];
        }
        return $couriers;
    }

    /**
     * Ambil ongkir menggunakan Biteship Area ID — akurasi tertinggi.
     *
     * @throws \RuntimeException jika API gagal
     */
    public function getAllShippingCostsByAreaId(string $destinationAreaId, int $weight, int $itemValue = 100000): array
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('Biteship API key tidak dikonfigurasi.');
        }

        // Gunakan origin_area_id jika tersedia (akurasi tertinggi), fallback ke postal code
        $originParams = $this->originAreaId
            ? ['origin_area_id'    => $this->originAreaId]
            : ['origin_postal_code' => (int) $this->originPostal];

        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/v1/rates/couriers", array_merge($originParams, [
                'destination_area_id' => $destinationAreaId,
                'couriers'            => self::COURIERS,
                'items'               => [
                    [
                        'name'     => 'Kemeja',
                        'category' => 'fashion',
                        'value'    => $itemValue,
                        'weight'   => $weight,
                        'quantity' => 1,
                        'height'   => 3,
                        'length'   => 30,
                        'width'    => 25,
                    ],
                ],
            ]));

        if (!$response->successful()) {
            Log::warning('Biteship getAllShippingCostsByAreaId error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Biteship API gagal: HTTP ' . $response->status());
        }

        $data = $response->json();

        Log::info('Biteship rates/area_id response', [
            'success'       => $data['success'] ?? null,
            'pricing_count' => count($data['pricing'] ?? []),
            'error'         => $data['error'] ?? null,
        ]);

        if (empty($data['success']) || empty($data['pricing'])) {
            throw new \RuntimeException('Biteship tidak mengembalikan data harga: ' . ($data['error'] ?? 'pricing kosong'));
        }

        return $this->normalizePricing($data['pricing']);
    }

    /**
     * Ambil ongkir menggunakan postal code.
     *
     * @param  string $destinationPostal  Kode pos tujuan
     * @param  int    $weight             Berat dalam gram
     * @param  int    $itemValue          Nilai barang untuk insurance (opsional)
     * @throws \RuntimeException jika API gagal
     */
    public function getAllShippingCosts(string $destinationPostal, int $weight, int $itemValue = 100000): array
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('Biteship API key tidak dikonfigurasi.');
        }

        // Gunakan origin_area_id jika tersedia (akurasi tertinggi), fallback ke postal code
        $originParams = $this->originAreaId
            ? ['origin_area_id'    => $this->originAreaId]
            : ['origin_postal_code' => (int) $this->originPostal];

        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/v1/rates/couriers", array_merge($originParams, [
                'destination_postal_code' => (int) $destinationPostal,
                'couriers'                => self::COURIERS,
                'items'                   => [
                    [
                        'name'     => 'Kemeja',
                        'category' => 'fashion',
                        'value'    => $itemValue,
                        'weight'   => $weight,
                        'quantity' => 1,
                    ],
                ],
            ]));

        if (!$response->successful()) {
            Log::warning('Biteship API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Biteship API gagal: HTTP ' . $response->status());
        }

        $data = $response->json();

        Log::info('Biteship rates/postal response', [
            'success'       => $data['success'] ?? null,
            'pricing_count' => count($data['pricing'] ?? []),
            'error'         => $data['error'] ?? null,
        ]);

        if (empty($data['success']) || empty($data['pricing'])) {
            throw new \RuntimeException('Biteship tidak mengembalikan data harga: ' . ($data['error'] ?? 'pricing kosong'));
        }

        return $this->normalizePricing($data['pricing']);
    }

    /**
     * Normalize response Biteship ke format yang sama dengan RajaOngkirService.
     * Output: array of ['code', 'courier', 'service', 'description', 'cost', 'etd']
     */
    private function normalizePricing(array $pricing): array
    {
        $results = [];

        foreach ($pricing as $item) {
            $code    = strtolower($item['courier_code'] ?? '');
            $service = strtoupper($item['courier_service_code'] ?? 'REG');
            $cost    = (int) ($item['price'] ?? $item['shipping_fee'] ?? 0);

            // Skip yang terlalu mahal (cargo/trucking)
            if ($cost > 500000 || $cost === 0) continue;

            // Skip instant courier (tidak relevan untuk toko kemeja)
            $serviceType = $item['service_type'] ?? '';
            if (in_array($serviceType, ['same_day', 'instant'])) continue;

            $etd = '-';
            if (!empty($item['shipment_duration_range']) && !empty($item['shipment_duration_unit'])) {
                $unit = str_replace('days', 'hari', $item['shipment_duration_unit']);
                $etd  = $item['shipment_duration_range'] . ' ' . $unit;
            }

            $results[] = [
                'code'        => $code,
                'courier'     => self::COURIER_NAMES[$code] ?? strtoupper($code),
                'service'     => $service,
                'description' => $item['courier_service_name'] ?? 'Layanan Reguler',
                'cost'        => $cost,
                'etd'         => $etd,
                'source'      => 'biteship', // untuk debugging
            ];
        }

        return $results;
    }

    /**
     * Buat order pengiriman di Biteship.
     * Dipanggil setelah order dibayar (paid) agar saldo Biteship tidak terpotong jika order dibatalkan.
     *
     * @param array $params [
     *     'order_number'       => string,
     *     'customer_name'      => string,
     *     'customer_phone'     => string,
     *     'customer_email'     => string,
     *     'shipping_address'   => string,
     *     'shipping_area_id'   => string (Biteship area_id),
     *     'courier_company'    => string (jne, jnt, pos),
     *     'courier_type'       => string (reg, express, dll),
     *     'items'              => array [['name', 'value', 'quantity', 'weight'], ...],
     * ]
     * @return array ['biteship_order_id', 'tracking_id', 'waybill_id', 'status', 'price']
     * @throws \RuntimeException jika gagal
     */
    public function createOrder(array $params): array
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('Biteship API key tidak dikonfigurasi.');
        }

        // Map courier code ke company name Biteship
        $courierMap = [
            'jne' => 'jne',
            'jnt' => 'jnt',
            'pos' => 'pos',
        ];
        $courierCode = strtolower($params['courier_company']);
        $courierCompany = $courierMap[$courierCode] ?? $courierCode;

        // Siapkan items untuk Biteship
        $items = [];
        foreach ($params['items'] as $item) {
            $items[] = [
                'name'     => $item['name'],
                'category' => 'fashion',
                'value'    => (int) $item['value'],
                'quantity' => (int) $item['quantity'],
                'weight'   => (int) $item['weight'],
                'height'   => 3,
                'length'   => 30,
                'width'    => 25,
            ];
        }

        // Origin dari config
        $originParams = $this->originAreaId
            ? ['origin_area_id' => $this->originAreaId]
            : ['origin_postal_code' => (int) $this->originPostal];

        $payload = array_merge($originParams, [
            'origin_contact_name'  => config('app.name', 'Aliesmo'),
            'origin_contact_phone' => config('services.biteship.origin_phone', '08138883345'),
            'origin_address'       => config('services.biteship.origin_address', 'Ulujami, Pemalang, Jawa Tengah'),
            'destination_contact_name'  => $params['customer_name'],
            'destination_contact_phone' => $params['customer_phone'],
            'destination_contact_email' => $params['customer_email'] ?? null,
            'destination_address'       => $params['shipping_address'],
            'destination_area_id'       => $params['shipping_area_id'],
            'courier_company'           => $courierCompany,
            'courier_type'              => $params['courier_type'] ?? 'reg',
            'delivery_type'             => 'now',
            'order_note'                => 'Order #' . $params['order_number'],
            'reference_id'              => $params['order_number'],
            'items'                     => $items,
        ]);

        Log::info('Biteship createOrder request', [
            'order_number' => $params['order_number'],
            'courier'      => $courierCompany . ' ' . ($params['courier_type'] ?? 'reg'),
            'destination'  => $params['shipping_address'],
        ]);

        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/v1/orders", $payload);

        $data = $response->json();

        Log::info('Biteship createOrder response', [
            'success' => $data['success'] ?? null,
            'id'      => $data['id'] ?? null,
            'status'  => $data['status'] ?? null,
            'error'   => $data['error'] ?? null,
        ]);

        if (!$response->successful() || empty($data['success'])) {
            $errorMsg = $data['error'] ?? $data['message'] ?? 'HTTP ' . $response->status();
            throw new \RuntimeException("Gagal membuat order Biteship: {$errorMsg}");
        }

        return [
            'biteship_order_id'    => $data['id'],
            'biteship_tracking_id' => $data['courier']['tracking_id'] ?? null,
            'biteship_waybill_id'  => $data['courier']['waybill_id'] ?? null,
            'biteship_status'      => $data['status'] ?? 'confirmed',
            'price'                => (int) ($data['price'] ?? 0),
        ];
    }
}
