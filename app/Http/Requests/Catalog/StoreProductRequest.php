<?php
// app/Http/Requests/Catalog/StoreProductRequest.php
namespace App\Http\Requests\Catalog;

use App\Enums\Gender;
use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'age_group_id' => ['nullable', 'exists:age_groups,id'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],

            'material' => ['nullable', 'string', 'max:100'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'battery_required' => ['boolean'],
            'batteries_included' => ['boolean'],
            'safety_warnings' => ['nullable', 'string'],
            'choking_hazard' => ['boolean'],
            'certifications' => ['nullable', 'array'],
            'pieces_count' => ['nullable', 'integer', 'min:0'],
            'educational' => ['boolean'],
            'how_to_play' => ['nullable', 'string'],

            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'status' => ['required', Rule::enum(ProductStatus::class)],
            'is_featured' => ['boolean'],
            'is_new_arrival' => ['boolean'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],

            // Variants
            'variants' => ['nullable', 'array'],
            'variants.*.sku' => ['required_with:variants', 'string', 'distinct'],
            'variants.*.name' => ['nullable', 'string'],
            'variants.*.attributes' => ['nullable', 'array'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
