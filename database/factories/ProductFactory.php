<?php
// database/factories/ProductFactory.php
namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'sku' => 'SKU-' . fake()->unique()->numerify('######'),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 50, 500),
            'status' => ProductStatus::Active,
            'gender' => Gender::Unisex,
        ];
    }
}
