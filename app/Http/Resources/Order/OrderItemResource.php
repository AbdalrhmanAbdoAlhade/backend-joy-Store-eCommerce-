<?php
// app/Http/Resources/Order/OrderItemResource.php
namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'variant_name' => $this->variant_name,
            'sku' => $this->sku,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'discount_amount' => $this->discount_amount,
            'total' => $this->total,
            'cost_price' => $this->when($request->user()?->isAdmin(), $this->cost_price),
        ];
    }
}
