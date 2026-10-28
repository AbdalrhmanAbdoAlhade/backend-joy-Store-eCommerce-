<?php
// app/Http/Resources/Catalog/ProductVideoResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'provider' => $this->provider,
            'sort_order' => $this->sort_order,
        ];
    }
}
