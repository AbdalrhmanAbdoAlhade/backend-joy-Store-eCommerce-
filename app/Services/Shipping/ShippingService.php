<?php
// app/Services/Shipping/ShippingService.php
namespace App\Services\Shipping;

use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\Coupon;

class ShippingService
{
    public function calculate(string $governorate, float $subtotal, float $weight = 0, ?string $methodCode = 'standard', ?Coupon $coupon = null): array
    {
        $zone = ShippingZone::active()
            ->whereJsonContains('governorates', $governorate)
            ->first();

        if (!$zone) {
            return [
                'amount' => 0,
                'method' => null,
                'error' => 'لا توجد منطقة شحن لهذه المحافظة',
            ];
        }

        $method = ShippingMethod::active()->where('code', $methodCode)->first();

        if (!$method) {
            $method = ShippingMethod::active()->orderBy('sort_order')->first();
        }

        $rate = ShippingRate::where('shipping_zone_id', $zone->id)
            ->where('shipping_method_id', $method->id)
            ->where('min_order_amount', '<=', $subtotal)
            ->where(function ($q) use ($subtotal) {
                $q->whereNull('max_order_amount')->orWhere('max_order_amount', '>=', $subtotal);
            })
            ->where('min_weight', '<=', $weight)
            ->where(function ($q) use ($weight) {
                $q->whereNull('max_weight')->orWhere('max_weight', '>=', $weight);
            })
            ->first();

        if (!$rate) {
            return [
                'amount' => 0,
                'method' => $method,
                'error' => 'لا توجد تعريفة شحن مناسبة',
            ];
        }

        $amount = $rate->price;

        // شحن مجاني فوق حد معين
        $freeAbove = (float) setting('free_shipping_above', 500);
        if ($subtotal >= $freeAbove || ($rate->is_free_above && $rate->free_above_amount && $subtotal >= $rate->free_above_amount)) {
            $amount = 0;
        }

        // كوبون شحن مجاني
        if ($coupon && $coupon->type === \App\Enums\CouponType::FreeShipping) {
            $amount = 0;
        }

        return [
            'amount' => round($amount, 2),
            'method' => $method,
            'zone' => $zone,
            'rate' => $rate,
        ];
    }

    public function getAvailableMethods(string $governorate, float $subtotal): array
    {
        $zone = ShippingZone::active()
            ->whereJsonContains('governorates', $governorate)
            ->first();

        if (!$zone) {
            return [];
        }

        $methods = ShippingMethod::active()->orderBy('sort_order')->get();
        $result = [];

        foreach ($methods as $method) {
            $calc = $this->calculate($governorate, $subtotal, 0, $method->code);
            if (!isset($calc['error'])) {
                $result[] = [
                    'method' => $method,
                    'amount' => $calc['amount'],
                ];
            }
        }

        return $result;
    }
}
