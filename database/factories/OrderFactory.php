<?php
// database/factories/OrderFactory.php
namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => User::factory(),
            'status' => OrderStatus::Pending,
            'subtotal' => 100,
            'total' => 140,
            'shipping_amount' => 40,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'shipping_address' => [
                'governorate' => 'القاهرة',
                'city' => 'مدينة نصر',
                'street' => 'شارع تجريبي',
            ],
            'payment_method' => 'cod',
            'payment_status' => PaymentStatus::Pending,
        ];
    }
}
