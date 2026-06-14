<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $purchase = fake()->randomFloat(2, 5, 500);
        $selling = $purchase + fake()->randomFloat(2, 1, 100);

        return [
            'category_id'         => Category::query()->inRandomOrder()->value('id') ?? Category::factory(),
            'name'                => fake()->unique()->words(3, true),
            'sku'                 => 'SKU-' . strtoupper(fake()->unique()->bothify('??##??')),
            'barcode'             => fake()->unique()->numerify('############'),
            'description'         => fake()->paragraph(),
            'purchase_price'      => $purchase,
            'selling_price'       => $selling,
            'tax_rate'            => fake()->randomElement([0, 5, 12, 18]),
            'stock_quantity'      => fake()->numberBetween(0, 100),
            'low_stock_threshold' => 10,
            'type'                => 'simple',
            'is_active'           => true,
        ];
    }
}
