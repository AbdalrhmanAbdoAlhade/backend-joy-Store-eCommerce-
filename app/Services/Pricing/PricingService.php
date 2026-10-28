<?php
// app/Services/Pricing/PricingService.php
namespace App\Services\Pricing;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

class PricingService
{
    public function getProductPrice(Product $product, ?ProductVariant $variant = null): float
    {
        if ($variant) {
            return $variant->final_price;
        }

        return $product->final_price;
    }

    public function calculateCartTotals(Cart $cart, ?Coupon $coupon = null, float $shipping = 0, float $giftWrap = 0, int $pointsToRedeem = 0): array
    {
        $subtotal = 0;

        foreach ($cart->items as $item) {
            $subtotal += $item->price * $item->quantity;
        }

        $discount = 0;
        if ($coupon && $coupon->isValid()) {
            $discount = $this->calculateCouponDiscount($coupon, $subtotal, $cart);
        }

        $pointsDiscount = 0;
        if ($pointsToRedeem > 0 && $cart->user) {
            $pointsDiscount = $this->calculatePointsDiscount($cart->user, $pointsToRedeem);
        }

        $taxable = max(0, $subtotal - $discount - $pointsDiscount);
        $taxRate = (float) setting('tax_rate', 14); // ضريبة القيمة المضافة مصر
        $tax = round($taxable * ($taxRate / 100), 2);

        $total = max(0, $subtotal - $discount - $pointsDiscount + $shipping + $giftWrap + $tax);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discount, 2),
            'points_discount' => round($pointsDiscount, 2),
            'shipping_amount' => round($shipping, 2),
            'gift_wrap_amount' => round($giftWrap, 2),
            'tax_amount' => $tax,
            'total' => round($total, 2),
        ];
    }

    public function calculateCouponDiscount(Coupon $coupon, float $subtotal, Cart $cart): float
    {
        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            return 0;
        }

        $discount = match ($coupon->type) {
            \App\Enums\CouponType::Percentage => $subtotal * ($coupon->value / 100),
            \App\Enums\CouponType::Fixed => $coupon->value,
            \App\Enums\CouponType::FreeShipping => 0, // يتم التعامل معه في الشحن
            default => 0,
        };

        if ($coupon->max_discount) {
            $discount = min($discount, $coupon->max_discount);
        }

        return min($discount, $subtotal);
    }

    public function calculatePointsDiscount(User $user, int $points): float
    {
        $available = $user->loyalty_points;
        $points = min($points, $available);

        $rate = (float) setting('points_to_currency_rate', 0.1); // كل نقطة = 0.1 جنيه

        return round($points * $rate, 2);
    }
}
