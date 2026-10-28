<?php
// tests/Feature/Cart/CartTest.php
namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_item_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/cart', [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 2,
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'تمت الإضافة إلى السلة');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 10]);

        $this->actingAs($user)->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $item = $user->cart->items()->first();

        $response = $this->actingAs($user)
            ->putJson("/api/v1/cart/items/{$item->id}", ['quantity' => 3]);

        $response->assertOk();
        $this->assertEquals(3, $item->fresh()->quantity);
    }
}
