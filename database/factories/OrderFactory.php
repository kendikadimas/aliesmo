<?php
namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50000, 5000000);
        $shipping = fake()->randomFloat(2, 0, 50000);

        return [
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) fake()->unique()->randomNumber(4), 4, '0', STR_PAD_LEFT) . '-' . strtoupper(fake()->bothify('???')),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->email(),
            'customer_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->address(),
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'total' => $subtotal + $shipping,
            'status' => fake()->randomElement(OrderStatus::cases()),
            'paid_at' => fake()->optional(0.7)->dateTimeThisMonth(),
        ];
    }
}
