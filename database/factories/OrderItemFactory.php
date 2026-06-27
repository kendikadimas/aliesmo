<?php
namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5000, 5000000);
        $qty = fake()->numberBetween(1, 5);

        return [
            'product_name' => fake()->words(3, true),
            'price' => $price,
            'quantity' => $qty,
            'subtotal' => $price * $qty,
        ];
    }
}
