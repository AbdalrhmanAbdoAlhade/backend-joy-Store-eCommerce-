<?php
// app/Services/Coupon/CouponService.php
namespace App\Services\Coupon;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use RuntimeException;

class CouponService
{
    public function validate(string $code, Cart $cart, ?User $user = null): Coupon
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            throw new RuntimeException('كود الخصم غير موجود');
        }

        if (!$coupon->isValid()) {
            throw new RuntimeException('كود الخصم غير صالح أو منتهي');
        }

        if ($user && $coupon->usage_limit_per_user) {
            $used = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->count();

            if ($used >= $coupon->usage_limit_per_user) {
                throw new RuntimeException('لقد استخدمت هذا الكود من قبل بالحد الأقصى');
            }
        }

        $subtotal = $cart->subtotal;

        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            throw new RuntimeException("الحد الأدنى للطلب لاستخدام هذا الكود هو {$coupon->min_order_amount} ج.م");
        }

        // التحقق من المنتجات/الفئات المخصصة
        if ($coupon->applicable_products || $coupon->applicable_categories) {
            $this->validateApplicability($coupon, $cart);
        }

        return $coupon;
    }

    protected function validateApplicability(Coupon $coupon, Cart $cart): void
    {
        $productIds = $cart->items->pluck('product_id')->toArray();

        if ($coupon->applicable_products) {
            $intersection = array_intersect($productIds, $coupon->applicable_products);
            if (empty($intersection)) {
                throw new RuntimeException('كود الخصم غير صالح للمنتجات في السلة');
            }
        }

        // يمكن إضافة تحقق الفئات بنفس الطريقة
    }

    public function apply(Coupon $coupon, User $user, $order, float $discountAmount): void
    {
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => $discountAmount,
        ]);

        $coupon->increment('used_count');
    }
}
