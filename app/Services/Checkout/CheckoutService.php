<?php
// app/Services/Checkout/CheckoutService.php
namespace App\Services\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Coupon\CouponService;
use App\Services\Loyalty\LoyaltyService;
use App\Services\Pricing\PricingService;
use App\Services\Shipping\ShippingService;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        protected PricingService $pricing,
        protected StockService $stock,
        protected CouponService $couponService,
        protected ShippingService $shipping,
        protected LoyaltyService $loyalty,
    ) {}

    public function process(Cart $cart, array $data, ?User $user = null): Order
    {
        // Idempotency
        if (!empty($data['idempotency_key'])) {
            $existing = Order::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing;
            }
        }

        if ($cart->items->isEmpty()) {
            throw new RuntimeException('السلة فارغة');
        }

        return DB::transaction(function () use ($cart, $data, $user) {
            // 1. التحقق من المخزون + حجز
            $reservations = [];
            foreach ($cart->items as $item) {
                $variant = $item->variant ?? $item->product->variants()->first();
                if (!$variant) {
                    throw new RuntimeException("لا يوجد variant للمنتج: {$item->product->name}");
                }

                $reservation = $this->stock->reserve(
                    $variant,
                    $item->quantity,
                    $user,
                    $cart->session_id
                );
                $reservations[] = $reservation;
            }

            // 2. الكوبون
            $coupon = null;
            if (!empty($data['coupon_code'])) {
                $coupon = $this->couponService->validate($data['coupon_code'], $cart, $user);
            }

            // 3. الشحن
            $shippingResult = $this->shipping->calculate(
                $data['shipping_address']['governorate'],
                $cart->subtotal,
                0,
                $data['shipping_method'] ?? 'standard',
                $coupon
            );

            // 4. النقاط
            $pointsToRedeem = (int) ($data['points_to_redeem'] ?? 0);

            // 5. الحسابات
            $totals = $this->pricing->calculateCartTotals(
                $cart,
                $coupon,
                $shippingResult['amount'],
                $data['gift_wrap_amount'] ?? 0,
                $pointsToRedeem
            );

            // 6. إنشاء الطلب
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user?->id,
                'created_by' => $data['created_by'] ?? null,
                'status' => OrderStatus::Pending,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'shipping_amount' => $totals['shipping_amount'],
                'tax_amount' => $totals['tax_amount'],
                'gift_wrap_amount' => $totals['gift_wrap_amount'],
                'points_discount' => $totals['points_discount'],
                'total' => $totals['total'],
                'shipping_method' => $shippingResult['method']?->code,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? $data['shipping_address'],
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'is_gift' => $data['is_gift'] ?? false,
                'gift_message' => $data['gift_message'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'payment_method' => $data['payment_method'],
                'payment_status' => PaymentStatus::Pending,
                'idempotency_key' => $data['idempotency_key'] ?? Str::uuid()->toString(),
            ]);

            // 7. عناصر الطلب (Snapshot)
            foreach ($cart->items as $item) {
                $variant = $item->variant;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $item->product->name,
                    'variant_name' => $variant?->name,
                    'sku' => $variant?->sku ?? $item->product->sku,
                    'unit_price' => $item->price,
                    'quantity' => $item->quantity,
                    'total' => $item->price * $item->quantity,
                    'cost_price' => $variant?->cost_price ?? $item->product->cost_price,
                ]);
            }

            // 8. تأكيد الحجوزات (خصم فعلي من المخزون)
            foreach ($reservations as $reservation) {
                $this->stock->confirmReservation($reservation, $order);
            }

            // 9. تطبيق الكوبون
            if ($coupon && $user) {
                $this->couponService->apply($coupon, $user, $order, $totals['discount_amount']);
            }

            // 10. خصم النقاط
            if ($pointsToRedeem > 0 && $user) {
                $this->loyalty->redeem($user, $pointsToRedeem, $order);
            }

            // 11. تفريغ السلة
            $cart->items()->delete();

            return $order->load('items');
        });
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
