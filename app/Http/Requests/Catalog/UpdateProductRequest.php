<?php
// app/Http/Requests/Catalog/UpdateProductRequest.php
namespace App\Http\Requests\Catalog;

use App\Enums\Gender;
use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.manage');
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('products')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'age_group_id' => ['nullable', 'exists:age_groups,id'],
            'gender' => ['sometimes', Rule::enum(Gender::class)],
            'category_ids' => ['sometimes', 'array', 'min:1'],
            'category_ids.*' => ['exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],

            'price' => ['sometimes', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::enum(ProductStatus::class)],
            'is_featured' => ['boolean'],
            'is_new_arrival' => ['boolean'],
        ];
    }
}
