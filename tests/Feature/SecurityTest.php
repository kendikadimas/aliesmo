<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // VULN 1: Mass Assignment — user tidak bisa daftar sebagai Admin
    // ─────────────────────────────────────────────────────────────
    public function test_register_cannot_assign_admin_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Evil Admin',
            'email'                 => 'evil@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'admin', // attempt privilege escalation
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'evil@example.com')->first();
        $this->assertNotNull($user);
        // Role harus tetap customer, bukan admin
        $this->assertEquals(UserRole::Customer, $user->role);
    }

    // ─────────────────────────────────────────────────────────────
    // VULN 2: IDOR — user tidak bisa lihat order milik user lain
    // ─────────────────────────────────────────────────────────────
    public function test_idor_user_cannot_view_other_users_order(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $order = Order::factory()->create([
            'user_id'  => $owner->id,
            'subtotal' => 100000,
            'total'    => 100000,
        ]);

        $attackerToken = $attacker->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$attackerToken}")
            ->getJson("/api/v1/orders/{$order->order_number}/status");

        $response->assertStatus(403);
    }

    public function test_idor_guest_cannot_view_user_owned_order(): void
    {
        $owner = User::factory()->create();
        $order = Order::factory()->create([
            'user_id'  => $owner->id,
            'subtotal' => 100000,
            'total'    => 100000,
        ]);

        // Request tanpa token
        $response = $this->getJson("/api/v1/orders/{$order->order_number}/status");

        $response->assertStatus(403);
    }

    public function test_guest_order_is_publicly_accessible(): void
    {
        // Order tanpa user_id (guest checkout) boleh diakses publik
        $order = Order::factory()->create([
            'user_id'  => null,
            'subtotal' => 100000,
            'total'    => 100000,
        ]);

        $response = $this->getJson("/api/v1/orders/{$order->order_number}/status");

        $response->assertStatus(200);
    }

    public function test_owner_can_view_own_order(): void
    {
        $owner = User::factory()->create();
        $order = Order::factory()->create([
            'user_id'  => $owner->id,
            'subtotal' => 100000,
            'total'    => 100000,
        ]);

        $token = $owner->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/orders/{$order->order_number}/status");

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────
    // VULN 3: DoS — per_page dibatasi max 100
    // ─────────────────────────────────────────────────────────────
    public function test_product_list_per_page_is_capped_at_100(): void
    {
        Product::factory()->count(5)->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/products?per_page=999999');

        $response->assertStatus(200);
        // per_page tidak boleh lebih dari 100
        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }

    // ─────────────────────────────────────────────────────────────
    // VULN 4: Shipping weight max validation
    // ─────────────────────────────────────────────────────────────
    public function test_shipping_cost_rejects_excessive_weight(): void
    {
        $response = $this->postJson('/api/v1/shipping/cost', [
            'destination' => 501,
            'weight'      => 99999999, // jauh melebihi batas 100kg
            'courier'     => 'jne',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['weight']);
    }

    public function test_shipping_cost_accepts_valid_weight(): void
    {
        $response = $this->postJson('/api/v1/shipping/cost', [
            'destination' => 501,
            'weight'      => 1000, // 1kg, valid
            'courier'     => 'jne',
        ]);

        // 422 dari RajaOngkir (API key kosong di test env) atau 200 — keduanya ok
        // Yang penting bukan 422 karena validasi weight
        $errors = $response->json('errors');
        $this->assertArrayNotHasKey('weight', $errors ?? []);
    }

    // ─────────────────────────────────────────────────────────────
    // VULN 5: Security headers tersedia di response
    // ─────────────────────────────────────────────────────────────
    public function test_security_headers_are_present(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    // ─────────────────────────────────────────────────────────────
    // VULN 6: Unauthenticated access ke protected endpoints
    // ─────────────────────────────────────────────────────────────
    public function test_my_orders_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/me/orders');
        $response->assertStatus(401);
    }

    public function test_profile_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/me/profile');
        $response->assertStatus(401);
    }

    public function test_update_password_requires_authentication(): void
    {
        $response = $this->putJson('/api/v1/me/password', [
            'current_password' => 'old',
            'password'         => 'new12345',
            'password_confirmation' => 'new12345',
        ]);
        $response->assertStatus(401);
    }

    // ─────────────────────────────────────────────────────────────
    // VULN 7: SQL Injection via search — Laravel binding harus aman
    // ─────────────────────────────────────────────────────────────
    public function test_search_is_safe_against_sql_injection(): void
    {
        $response = $this->getJson("/api/v1/products?search=' OR '1'='1");
        // Harus return 200 (kosong), bukan 500
        $response->assertStatus(200);
    }

    public function test_price_filter_is_safe_against_non_numeric(): void
    {
        $response = $this->getJson("/api/v1/products?min_price='; DROP TABLE products;--");
        // Laravel cast ke numeric, query aman
        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────
    // VULN 8: Password brute force — current_password tidak leak info
    // ─────────────────────────────────────────────────────────────
    public function test_wrong_current_password_returns_422_not_401(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/me/password', [
                'current_password'      => 'wrongpassword',
                'password'              => 'newpass123',
                'password_confirmation' => 'newpass123',
            ]);

        // 422 bukan 401 — jangan leak info "authenticated but wrong password"
        $response->assertStatus(422);
    }
}
