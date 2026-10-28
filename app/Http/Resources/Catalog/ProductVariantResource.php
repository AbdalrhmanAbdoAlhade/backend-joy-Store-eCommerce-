<?php
// app/Http/Resources/Catalog/ProductVariantResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user()?->isAdmin();

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'attributes' => $this->attributes,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'final_price' => $this->final_price,
            'cost_price' => $this->when($isAdmin, $this->cost_price),
            'stock' => $this->when($isAdmin || $request->user()?->can('inventory.manage'), $this->stock),
            'available_stock' => $this->available_stock,
            'is_in_stock' => $this->available_stock > 0,
            'is_low_stock' => $this->isLowStock(),
            'is_active' => $this->is_active,
            'barcode' => $this->barcode,
        ];
    }
}
