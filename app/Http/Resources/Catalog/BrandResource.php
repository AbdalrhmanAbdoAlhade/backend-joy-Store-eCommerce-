<?php
// app/Http/Resources/Catalog/BrandResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
            'description' => $this->description,
        ];
    }
}
