<?php
// tests/Feature/Checkout/CheckoutTest.php
namespace Tests\Feature\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // بيانات شحن أساسية
        $zone = ShippingZone::create([
            'name' => 'القاهرة',
            'governorates' => ['القاهرة'],
            'is_active' => true,
        ]);
        $method = ShippingMethod::create([
            'name' => 'عادي',
            'code' => 'standard',
            'is_active' => true,
        ]);
        ShippingRate::create([
            'shipping_zone_id' => $zone->id,
            'shipping_method_id' => $method->id,
            'price' => 40,
            'is_free_above' => true,
            'free_above_amount' => 500,
        ]);
    }

    public function test_checkout_creates_order_and_decreases_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'price' => 100]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => 5,
            'is_active' => true,
            'price' => 100,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 100,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout', [
            'payment_method' => 'cod',
            'shipping_method' => 'standard',
            'shipping_address' => [
                'governorate' => 'القاهرة',
                'city' => 'مدينة نصر',
                'street' => 'شارع تجريبي',
            ],
            'idempotency_key' => 'test-key-123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'order', 'payment']);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'cod',
        ]);

        // المخزون انخفض
        $this->assertEquals(3, $variant->fresh()->stock);
    }

    public function test_checkout_fails_when_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => 1,
            'is_active' => true,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 5,
            'price' => 100,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout', [
            'payment_method' => 'cod',
            'shipping_method' => 'standard',
            'shipping_address' => [
                'governorate' => 'القاهرة',
                'city' => 'مدينة نصر',
                'street' => 'شارع تجريبي',
            ],
        ]);

        $response->assertStatus(500); // أو 422 حسب معالجة الأخطاء
    }

    public function test_idempotency_prevents_duplicate_orders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'price' => 50]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
            'price' => 50,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 50,
        ]);

        $payload = [
            'payment_method' => 'cod',
            'shipping_method' => 'standard',
            'shipping_address' => [
                'governorate' => 'القاهرة',
                'city' => 'مدينة نصر',
                'street' => 'شارع تجريبي',
            ],
            'idempotency_key' => 'unique-key-999',
        ];

        $this->actingAs($user)->postJson('/api/v1/checkout', $payload);
        $response = $this->actingAs($user)->postJson('/api/v1/checkout', $payload);

        $response->assertStatus(201);
        $this->assertEquals(1, \App\Models\Order::where('idempotency_key', 'unique-key-999')->count());
    }
}
