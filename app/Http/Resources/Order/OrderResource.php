<?php
// app/Http/Resources/Order/OrderResource.php
namespace App\Http\Resources\Order;

use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isStaff = $request->user()?->isEmployee();

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,

            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'shipping_amount' => $this->shipping_amount,
            'tax_amount' => $this->tax_amount,
            'gift_wrap_amount' => $this->gift_wrap_amount,
            'points_discount' => $this->points_discount,
            'total' => $this->total,

            'shipping_method' => $this->shipping_method,
            'shipping_company' => $this->shipping_company,
            'tracking_number' => $this->tracking_number,
            'tracking_url' => $this->tracking_url,

            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,

            'coupon_code' => $this->coupon_code,
            'is_gift' => $this->is_gift,
            'gift_message' => $this->gift_message,
            'customer_note' => $this->customer_note,
            'internal_note' => $this->when($isStaff, $this->internal_note),

            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_histories' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'user' => new UserResource($this->whenLoaded('user')),

            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
