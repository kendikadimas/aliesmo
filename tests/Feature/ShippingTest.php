<?php

namespace Tests\Feature;

use App\Services\BiteshipService;
use App\Services\RajaOngkirService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShippingTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // /api/v1/shipping/search
    // ──────────────────────────────────────────────

    public function test_search_requires_minimum_2_characters(): void
    {
        $response = $this->getJson('/api/v1/shipping/search?q=a');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['q']);
    }

    public function test_search_returns_results_from_static_fallback(): void
    {
        // Mock Komerce — gagal agar pakai static fallback
        $this->mock(RajaOngkirService::class, function ($mock) {
            $mock->shouldReceive('searchDestinations')
                ->with('jakarta')
                ->andReturn([
                    [
                        'id'            => 152,
                        'label'         => 'JAKARTA PUSAT, DKI JAKARTA',
                        'city_name'     => 'Jakarta Pusat',
                        'province_name' => 'DKI Jakarta',
                        'zip_code'      => '10110',
                    ],
                ]);
        });

        // Mock Biteship — return empty agar tidak perlu API key
        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('searchArea')
                ->andReturn([]);
        });

        $response = $this->getJson('/api/v1/shipping/search?q=jakarta');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'label', 'city_name', 'province', 'zip_code', 'area_id', 'postal_code'],
            ],
        ]);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_search_enriches_results_with_biteship_area_id(): void
    {
        $this->mock(RajaOngkirService::class, function ($mock) {
            $mock->shouldReceive('searchDestinations')
                ->andReturn([
                    [
                        'id'            => 152,
                        'label'         => 'JAKARTA PUSAT, DKI JAKARTA',
                        'city_name'     => 'jakarta pusat',
                        'province_name' => 'DKI Jakarta',
                        'zip_code'      => '',
                    ],
                ]);
        });

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('searchArea')
                ->andReturn([
                    [
                        'area_id'     => 'IDNP6IDNC152IDND1001',
                        'postal_code' => '10110',
                        'label'       => 'Jakarta Pusat',
                        'province'    => 'DKI Jakarta',
                        'city'        => 'Jakarta Pusat',
                        'district'    => 'jakarta pusat',
                    ],
                ]);
        });

        $response = $this->getJson('/api/v1/shipping/search?q=jakarta');

        $response->assertStatus(200);
        $first = $response->json('data.0');
        $this->assertEquals('IDNP6IDNC152IDND1001', $first['area_id']);
        $this->assertEquals('10110', $first['postal_code']);
    }

    public function test_search_returns_empty_when_no_results(): void
    {
        $this->mock(RajaOngkirService::class, function ($mock) {
            $mock->shouldReceive('searchDestinations')->andReturn([]);
        });

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('searchArea')->andReturn([]);
        });

        $response = $this->getJson('/api/v1/shipping/search?q=xyznotexist');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }

    // ──────────────────────────────────────────────
    // /api/v1/shipping/cost
    // ──────────────────────────────────────────────

    public function test_cost_requires_destination_and_weight(): void
    {
        $response = $this->postJson('/api/v1/shipping/cost', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['destination', 'weight']);
    }

    public function test_cost_returns_results_from_komerce(): void
    {
        $this->mock(RajaOngkirService::class, function ($mock) {
            $mock->shouldReceive('getAllShippingCosts')
                ->andReturn([
                    [
                        'code'        => 'jne',
                        'courier'     => 'JNE',
                        'service'     => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost'        => 15000,
                        'etd'         => '2-3',
                        'source'      => 'komerce',
                    ],
                ]);
        });

        $response = $this->postJson('/api/v1/shipping/cost', [
            'destination' => 152,
            'weight'      => 500,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('source', 'komerce');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['code', 'courier', 'service', 'description', 'cost', 'etd'],
            ],
            'cache_key',
            'source',
        ]);
    }

    public function test_cost_falls_back_to_biteship_when_komerce_fails(): void
    {
        $this->mock(RajaOngkirService::class, function ($mock) {
            $mock->shouldReceive('getAllShippingCosts')
                ->andThrow(new \RuntimeException('RajaOngkir daily limit tercapai.'));
        });

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getAllShippingCostsByAreaId')
                ->andReturn([
                    [
                        'code'        => 'jnt',
                        'courier'     => 'J&T Express',
                        'service'     => 'EZ',
                        'description' => 'J&T EZ',
                        'cost'        => 18000,
                        'etd'         => '2-4 hari',
                        'source'      => 'biteship',
                    ],
                ]);
        });

        $response = $this->postJson('/api/v1/shipping/cost', [
            'destination' => 152,
            'weight'      => 500,
            'area_id'     => 'IDNP6IDNC152IDND1001',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('source', 'biteship');
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_cost_returns_manual_whatsapp_when_all_providers_fail(): void
    {
        $this->mock(RajaOngkirService::class, function ($mock) {
            $mock->shouldReceive('getAllShippingCosts')
                ->andThrow(new \RuntimeException('Komerce gagal.'));
        });

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getAllShippingCostsByAreaId')
                ->andThrow(new \RuntimeException('Biteship gagal.'));
            $mock->shouldReceive('getAllShippingCosts')
                ->andThrow(new \RuntimeException('Biteship postal gagal.'));
        });

        $response = $this->postJson('/api/v1/shipping/cost', [
            'destination' => 9999, // ID tidak ada di cities.json
            'weight'      => 500,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('manual', true);
        $response->assertJsonStructure([
            'manual',
            'message',
            'whatsapp' => ['number', 'text'],
        ]);
    }

    public function test_cost_uses_cache_on_second_request(): void
    {
        $callCount = 0;

        $this->mock(RajaOngkirService::class, function ($mock) use (&$callCount) {
            $mock->shouldReceive('getAllShippingCosts')
                ->andReturnUsing(function () use (&$callCount) {
                    $callCount++;
                    return [
                        [
                            'code'        => 'jne',
                            'courier'     => 'JNE',
                            'service'     => 'REG',
                            'description' => 'Layanan Reguler',
                            'cost'        => 15000,
                            'etd'         => '2-3',
                            'source'      => 'komerce',
                        ],
                    ];
                });
        });

        // Request pertama — hit API
        $this->postJson('/api/v1/shipping/cost', ['destination' => 152, 'weight' => 500]);
        // Request kedua — harusnya dari cache
        $this->postJson('/api/v1/shipping/cost', ['destination' => 152, 'weight' => 500]);

        $this->assertEquals(1, $callCount, 'API seharusnya hanya dipanggil sekali — request kedua dari cache.');
    }

    public function test_cost_validates_weight_minimum(): void
    {
        $response = $this->postJson('/api/v1/shipping/cost', [
            'destination' => 152,
            'weight'      => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['weight']);
    }

    // ──────────────────────────────────────────────
    // /api/v1/shipping/provinces & cities
    // ──────────────────────────────────────────────

    public function test_provinces_returns_list(): void
    {
        $this->mock(RajaOngkirService::class, function ($mock) {
            $mock->shouldReceive('getProvinces')->andReturn([
                ['province_id' => '12', 'province' => 'Jawa Tengah'],
            ]);
        });

        $response = $this->getJson('/api/v1/shipping/provinces');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    public function test_cities_returns_list_by_province(): void
    {
        $this->mock(RajaOngkirService::class, function ($mock) {
            $mock->shouldReceive('getCities')->with(12)->andReturn([
                ['city_id' => '570', 'city_name' => 'Pemalang', 'province_id' => '12'],
            ]);
        });

        $response = $this->getJson('/api/v1/shipping/cities/12');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }
}
