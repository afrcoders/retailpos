<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####')),
            'barcode' => $this->faker->unique()->ean13(),
            'description' => $this->faker->sentence(),
            'category_id' => Category::factory(),
            'cost_price' => $this->faker->randomFloat(2, 100, 5000),
            'retail_price' => $this->faker->randomFloat(2, 150, 7500),
            'stock_type' => 'stock',
            'reorder_level' => $this->faker->numberBetween(5, 20),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
