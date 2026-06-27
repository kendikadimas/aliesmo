<?php
namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use App\Enums\StockMovementType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Sample customer
        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        // Categories
        $categories = Category::factory(5)->create();

        // Products
        $stockService = app(StockService::class);
        $products = Product::factory(30)
            ->sequence(fn($sequence) => ['category_id' => $categories->random()->id])
            ->create(['stock' => 0]);

        // Initial stock for all active products
        $stockLevels = [0, 2, 3, 5, 10, 15, 20, 25, 50, 100];
        foreach ($products as $product) {
            $qty = $stockLevels[array_rand($stockLevels)];
            if ($qty > 0) {
                $stockService->adjustStock(
                    $product->id,
                    $qty,
                    StockMovementType::Initial,
                    'Initial stock',
                    $admin
                );
            }
        }

        // Sample orders
        foreach (range(1, 10) as $i) {
            $status = fake()->randomElement([
                OrderStatus::Pending,
                OrderStatus::Paid,
                OrderStatus::Processing,
                OrderStatus::Completed,
                OrderStatus::Cancelled,
            ]);

            $items = $products->random(rand(1, 5))->map(function ($product) {
                $qty = rand(1, 3);
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $product->price * $qty,
                ];
            });

            $subtotal = $items->sum('subtotal');
            $shippingCost = rand(0, 50000);
            $total = $subtotal + $shippingCost;

            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'customer_name' => fake()->name(),
                'customer_email' => fake()->email(),
                'customer_phone' => fake()->phoneNumber(),
                'shipping_address' => fake()->address(),
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => $status,
                'paid_at' => in_array($status, [OrderStatus::Paid, OrderStatus::Completed, OrderStatus::Processing]) ? now()->subDays(rand(1, 30)) : null,
            ]);

            $order->items()->createMany($items->toArray());

            if (in_array($status, [OrderStatus::Paid, OrderStatus::Completed, OrderStatus::Processing])) {
                $paymentStatus = PaymentStatus::Success;

                $order->payment()->create([
                    'gateway' => 'midtrans',
                    'gateway_transaction_id' => 'TRX-' . fake()->uuid(),
                    'gateway_reference' => 'REF-' . fake()->uuid(),
                    'amount' => $total,
                    'status' => $paymentStatus,
                ]);
            }
        }
    }
}
