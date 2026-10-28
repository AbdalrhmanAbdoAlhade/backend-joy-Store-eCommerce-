<?php
// app/Http/Resources/Catalog/CategoryResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'parent_id' => $this->parent_id,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
