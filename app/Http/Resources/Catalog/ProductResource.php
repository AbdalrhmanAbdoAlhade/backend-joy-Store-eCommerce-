<?php
// app/Http/Resources/Catalog/ProductResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user()?->isAdmin();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'age_group' => new AgeGroupResource($this->whenLoaded('ageGroup')),
            'gender' => $this->gender,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            // Toy specific
            'material' => $this->material,
            'dimensions' => $this->dimensions,
            'weight' => $this->weight,
            'battery_required' => $this->battery_required,
            'batteries_included' => $this->batteries_included,
            'safety_warnings' => $this->safety_warnings,
            'choking_hazard' => $this->choking_hazard,
            'certifications' => $this->certifications,
            'pieces_count' => $this->pieces_count,
            'educational' => $this->educational,
            'how_to_play' => $this->how_to_play,

            // Pricing (لا نعرض cost_price للعامة)
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'final_price' => $this->final_price,
            'is_on_sale' => $this->is_on_sale,
            'discount_percentage' => $this->discount_percentage,
            'cost_price' => $this->when($isAdmin, $this->cost_price),
            'tax_rate' => $this->tax_rate,

            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_new_arrival' => $this->is_new_arrival,

            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'primary_image' => $this->whenLoaded('primaryImage', fn () => $this->primaryImage?->path ? asset('storage/' . $this->primaryImage->path) : null),
            'videos' => ProductVideoResource::collection($this->whenLoaded('videos')),

            'avg_rating' => $this->avg_rating,
            'reviews_count' => $this->reviews_count,
            'sold_count' => $this->sold_count,
            'views_count' => $this->views_count,

            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
