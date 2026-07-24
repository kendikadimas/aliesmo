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

    /**
     * Mapping courier_code → courier_service_code (type) yang benar per Biteship docs.
     * JNE  : reg  (reguler)
     * J&T  : ez   (bukan reg — bug umum)
     * POS  : sps  (surat pos biasa)
     */
    public const COURIER_SERVICE_MAP = [
        'jne' => 'reg',
        'jnt' => 'ez',
        'pos' => 'sps',
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
                    'latitude'    => $area['latitude'] ?? null,
                    'longitude'   => $area['longitude'] ?? null,
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
    public function getAllShippingCostsByAreaId(string $destinationAreaId, int $weight, int $itemValue = 100000, ?int $codAmount = null): array
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('Biteship API key tidak dikonfigurasi.');
        }

        $originParams = $this->originAreaId
            ? ['origin_area_id'    => $this->originAreaId]
            : ['origin_postal_code' => (int) $this->originPostal];

        $rateParams = array_merge($originParams, [
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
        ]);

        // Kirim cod_amount agar Biteship hitung COD fee — dibebankan ke customer
        if ($codAmount !== null && $codAmount > 0) {
            $rateParams['cash_on_delivery'] = $codAmount;
        }

        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/v1/rates/couriers", $rateParams);

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
    public function getAllShippingCosts(string $destinationPostal, int $weight, ?int $codAmount = null, int $itemValue = 100000): array
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('Biteship API key tidak dikonfigurasi.');
        }

        $originParams = $this->originAreaId
            ? ['origin_area_id'    => $this->originAreaId]
            : ['origin_postal_code' => (int) $this->originPostal];

        $rateParams = array_merge($originParams, [
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
        ]);

        // Kirim cod_amount agar Biteship hitung COD fee — dibebankan ke customer
        if ($codAmount !== null && $codAmount > 0) {
            $rateParams['cash_on_delivery'] = $codAmount;
        }

        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/v1/rates/couriers", $rateParams);

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
                'code'         => $code,
                'courier'      => self::COURIER_NAMES[$code] ?? strtoupper($code),
                'service'      => $service,
                'description'  => $item['courier_service_name'] ?? 'Layanan Reguler',
                'cost'         => $cost,
                'etd'          => $etd,
                'source'       => 'biteship',
                // Field COD dari Biteship rates response — dipakai untuk filter kurir saat COD dipilih
                'supports_cod' => (bool) ($item['available_for_cash_on_delivery'] ?? false),
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

        // Normalisasi courier code
        $courierCode    = strtolower($params['courier_company']);
        $courierCompany = $courierCode; // Biteship pakai kode langsung (jne, jnt, pos)

        // Courier type: pakai dari params jika ada (berasal dari cache ongkir),
        // fallback ke COURIER_SERVICE_MAP agar setiap kurir dapat type yang benar.
        // BUG SEBELUMNYA: semua kurir hardcode 'reg' — J&T harus 'ez', POS harus 'sps'.
        $courierType = !empty($params['courier_type'])
            ? $params['courier_type']
            : (self::COURIER_SERVICE_MAP[$courierCode] ?? 'reg');

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
            'origin_contact_name'       => config('app.name', 'Aliesmo'),
            'origin_contact_phone'      => config('services.biteship.origin_phone', '08138883345'),
            'origin_address'            => config('services.biteship.origin_address', 'Ulujami, Pemalang, Jawa Tengah'),
            'origin_coordinate'         => [
                'latitude'  => (float) config('services.biteship.origin_latitude', -6.909194),
                'longitude' => (float) config('services.biteship.origin_longitude', 109.555889),
            ],
            'destination_contact_name'  => $params['customer_name'],
            'destination_contact_phone' => $params['customer_phone'],
            'destination_contact_email' => $params['customer_email'] ?? null,
            'destination_address'       => $params['shipping_address'],
            'destination_area_id'       => $params['shipping_area_id'],
            'courier_company'           => $courierCompany,
            'courier_type'              => $courierType,
            // delivery_type wajib diisi — 'now' = pengambilan segera (sandbox & production)
            'delivery_type'             => 'now',
            'order_note'                => 'Order #' . $params['order_number'],
            'reference_id'              => $params['order_number'],
            'items'                     => $items,
        ]);

        // destination_coordinate hanya dikirim jika lat/lng tersedia — null akan reject oleh Biteship
        $lat = $params['destination_lat'] ?? null;
        $lng = $params['destination_lng'] ?? null;
        if ($lat !== null && $lng !== null) {
            $payload['destination_coordinate'] = [
                'latitude'  => (float) $lat,
                'longitude' => (float) $lng,
            ];
        }

        // COD: kirim destination_cash_on_delivery sesuai docs resmi Biteship
        // Format: destination_cash_on_delivery = number (nominal), destination_cash_on_delivery_type = string
        // type '7_days' = remittance 7 hari kerja (default untuk reg/standard courier)
        if (!empty($params['is_cod']) && !empty($params['cod_amount'])) {
            $payload['destination_cash_on_delivery']      = (int) $params['cod_amount'];
            $payload['destination_cash_on_delivery_type'] = '7_days';
        }

        Log::info('Biteship createOrder request', [
            'order_number'   => $params['order_number'],
            'courier'        => $courierCompany . ' ' . $courierType,
            'destination'    => $params['shipping_address'],
            'area_id'        => $params['shipping_area_id'],
            'item_count'     => count($items),
            'has_coordinate' => isset($payload['destination_coordinate']),
            'insurance'      => $payload['courier_insurance'] ?? 0,
            'is_cod'         => !empty($params['is_cod']),
            'cod_amount'     => $params['cod_amount'] ?? null,
        ]);

        Log::debug('Biteship createOrder payload', [
            'order_number' => $params['order_number'],
            'payload_keys' => array_keys($payload),
            'origin' => $originParams,
            'destination_area_id' => $params['shipping_area_id'],
        ]);

        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/v1/orders", $payload);

        $data = $response->json();

        Log::info('Biteship createOrder response', [
            'order_number' => $params['order_number'],
            'http_status'  => $response->status(),
            'success'      => $data['success'] ?? null,
            'id'           => $data['id'] ?? null,
            'status'       => $data['status'] ?? null,
            'price'        => $data['price'] ?? null,
            'error'        => $data['error'] ?? null,
            'message'      => $data['message'] ?? null,
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
            'tracking_url'         => $data['courier']['link'] ?? null,
            'price'                => (int) ($data['price'] ?? 0),
        ];
    }

    /**
     * Batalkan order di Biteship.
     * Docs: POST /v1/orders/:id/cancel
     *
     * @throws \RuntimeException jika API gagal (kecuali sudah cancelled)
     */
    public function cancelOrder(string $biteshipOrderId, string $reason = 'Dibatalkan dari admin Aliesmo'): array
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('BITESHIP_API_KEY belum dikonfigurasi.');
        }

        $payload = [
            'cancellation_reason_code' => 'others',
            'cancellation_reason'      => $reason,
        ];

        Log::info('Biteship cancelOrder request', [
            'biteship_order_id' => $biteshipOrderId,
            'reason'            => $reason,
        ]);

        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/v1/orders/{$biteshipOrderId}/cancel", $payload);

        $data = $response->json() ?? [];

        Log::info('Biteship cancelOrder response', [
            'biteship_order_id' => $biteshipOrderId,
            'http_status'       => $response->status(),
            'success'           => $data['success'] ?? null,
            'status'            => $data['status'] ?? null,
            'message'           => $data['message'] ?? null,
            'error'             => $data['error'] ?? null,
        ]);

        // Sudah cancelled di Biteship → anggap sukses
        $status = strtolower((string) ($data['status'] ?? ''));
        if ($status === 'cancelled' || ($data['success'] ?? false)) {
            return [
                'success' => true,
                'status'  => $data['status'] ?? 'cancelled',
                'message' => $data['message'] ?? 'Order successfully cancelled',
            ];
        }

        $errorMsg = $data['error'] ?? $data['message'] ?? 'HTTP ' . $response->status();
        // Pesan "already cancelled" dari API juga ok
        if (stripos((string) $errorMsg, 'cancel') !== false && stripos((string) $errorMsg, 'already') !== false) {
            return ['success' => true, 'status' => 'cancelled', 'message' => $errorMsg];
        }

        throw new \RuntimeException("Gagal batalkan order Biteship: {$errorMsg}");
    }
}
