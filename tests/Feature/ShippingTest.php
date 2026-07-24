<?php

namespace Tests\Feature;

use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_search_returns_results_from_biteship(): void
    {
        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('searchArea')
                ->andReturn([
                    [
                        'area_id'     => 'IDNP6IDNC152IDND1001',
                        'postal_code' => '10110',
                        'label'       => 'Jakarta Pusat',
                        'province'    => 'DKI Jakarta',
                        'city'        => 'Jakarta Pusat',
                        'district'    => 'Gambir',
                        'latitude'    => -6.175,
                        'longitude'   => 106.827,
                    ],
                ]);
        });

        $response = $this->getJson('/api/v1/shipping/search?q=jakarta');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'label', 'city_name', 'province', 'zip_code', 'area_id', 'postal_code'],
            ],
        ]);
        $this->assertNotEmpty($response->json('data'));
        $this->assertEquals('IDNP6IDNC152IDND1001', $response->json('data.0.area_id'));
    }

    public function test_search_returns_empty_when_no_results(): void
    {
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

    public function test_cost_requires_weight_or_items(): void
    {
        $response = $this->postJson('/api/v1/shipping/cost', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['weight']);
    }

    public function test_cost_returns_results_from_biteship(): void
    {
        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getAllShippingCostsByAreaId')
                ->andReturn([
                    [
                        'code'        => 'jne',
                        'courier'     => 'JNE',
                        'service'     => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost'        => 15000,
                        'etd'         => '2-3 hari',
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
        $response->assertJsonStructure([
            'data' => [
                '*' => ['code', 'courier', 'service', 'description', 'cost', 'etd'],
            ],
            'cache_key',
            'source',
        ]);
    }

    public function test_cost_via_postal_code_when_no_area_id(): void
    {
        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getAllShippingCosts')
                ->andReturn([
                    [
                        'code'        => 'sicepat',
                        'courier'     => 'SiCepat',
                        'service'     => 'REG',
                        'description' => 'SiCepat Reguler',
                        'cost'        => 12000,
                        'etd'         => '2-3 hari',
                        'source'      => 'biteship',
                    ],
                ]);
        });

        $response = $this->postJson('/api/v1/shipping/cost', [
            'destination' => 152,
            'weight'      => 500,
            'postal_code' => '10110',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('source', 'biteship');
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_cost_returns_manual_whatsapp_when_biteship_fails(): void
    {
        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getAllShippingCostsByAreaId')
                ->andThrow(new \RuntimeException('Biteship gagal.'));
            $mock->shouldReceive('getAllShippingCosts')
                ->andThrow(new \RuntimeException('Biteship postal gagal.'));
        });

        $response = $this->postJson('/api/v1/shipping/cost', [
            'destination' => 9999,
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

        $this->mock(BiteshipService::class, function ($mock) use (&$callCount) {
            $mock->shouldReceive('getAllShippingCostsByAreaId')
                ->andReturnUsing(function () use (&$callCount) {
                    $callCount++;
                    return [
                        [
                            'code'        => 'jne',
                            'courier'     => 'JNE',
                            'service'     => 'REG',
                            'description' => 'Layanan Reguler',
                            'cost'        => 15000,
                            'etd'         => '2-3 hari',
                            'source'      => 'biteship',
                        ],
                    ];
                });
        });

        $this->postJson('/api/v1/shipping/cost', ['destination' => 152, 'weight' => 500, 'area_id' => 'IDNP6IDNC152IDND1001']);
        $this->postJson('/api/v1/shipping/cost', ['destination' => 152, 'weight' => 500, 'area_id' => 'IDNP6IDNC152IDND1001']);

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

    public function test_cost_computes_weight_from_items(): void
    {
        $product = \App\Models\Product::factory()->create(['weight' => 400, 'stock' => 10]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getAllShippingCostsByAreaId')
                ->withArgs(function ($areaId, $weight) {
                    return $weight === 800; // 400g * 2
                })
                ->andReturn([
                    [
                        'code' => 'jne', 'courier' => 'JNE', 'service' => 'REG',
                        'description' => 'Reg', 'cost' => 15000, 'etd' => '2-3', 'source' => 'biteship',
                    ],
                ]);
        });

        $response = $this->postJson('/api/v1/shipping/cost', [
            'area_id' => 'IDNP6IDNC152IDND1001',
            'items'   => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('source', 'biteship');
    }
}
