<?php
namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(rand(2, 4), true);

        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory()->create()->id,
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'sku' => 'SKU-' . strtoupper(fake()->unique()->bothify('???####')),
            'description' => fake()->paragraphs(rand(2, 4), true),
            'price' => fake()->randomFloat(2, 5000, 5000000),
            'stock' => fake()->randomElement([0, 2, 3, 5, 10, 15, 20, 25, 50, 100]),
            'is_active' => fake()->boolean(90),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => ['is_active' => false]);
    }

    public function lowStock(): static
    {
        return $this->state(fn(array $attributes) => ['stock' => fake()->numberBetween(0, 5)]);
    }
}
