<?php
// tests/Feature/Coupon/CouponTest.php
namespace Tests\Feature\Coupon;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Services\Coupon\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_coupon_applies_discount(): void
    {
        $user = User::factory()->create();
        $coupon = Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        // أضف items للسلة...

        $service = app(CouponService::class);
        $validated = $service->validate('SAVE10', $cart, $user);

        $this->assertEquals('SAVE10', $validated->code);
    }

    public function test_expired_coupon_fails(): void
    {
        $this->expectException(\RuntimeException::class);

        $coupon = Coupon::create([
            'code' => 'OLD',
            'type' => 'fixed',
            'value' => 50,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);

        app(CouponService::class)->validate('OLD', $cart, $user);
    }
}
