<?php
// database/factories/ProductVariantFactory.php
namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => 'VAR-' . fake()->unique()->numerify('######'),
            'stock' => fake()->numberBetween(0, 50),
            'is_active' => true,
            'price' => fake()->randomFloat(2, 50, 500),
        ];
    }
}
