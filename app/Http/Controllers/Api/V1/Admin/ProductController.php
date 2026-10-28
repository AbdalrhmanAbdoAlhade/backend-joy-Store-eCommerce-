<?php
// app/Http/Controllers/Api/V1/Admin/ProductController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreProductRequest;
use App\Http\Requests\Catalog\UpdateProductRequest;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'categories', 'variants'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"));
        }

        return ProductResource::collection($query->paginate(20));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $product = Product::create($data);

        if (!empty($data['category_ids'])) {
            $product->categories()->sync($data['category_ids']);
        }
        if (!empty($data['tag_ids'])) {
            $product->tags()->sync($data['tag_ids']);
        }

        // Variants
        if (!empty($data['variants'])) {
            foreach ($data['variants'] as $v) {
                $product->variants()->create($v);
            }
        } else {
            // variant افتراضي
            $product->variants()->create([
                'sku' => $product->sku . '-DEFAULT',
                'stock' => 0,
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'message' => 'تم إنشاء المنتج',
            'product' => new ProductResource($product->load(['brand', 'categories', 'variants'])),
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $product->update($data);

        if (isset($data['category_ids'])) {
            $product->categories()->sync($data['category_ids']);
        }
        if (isset($data['tag_ids'])) {
            $product->tags()->sync($data['tag_ids']);
        }

        return response()->json([
            'message' => 'تم تحديث المنتج',
            'product' => new ProductResource($product->fresh()->load(['brand', 'categories', 'variants'])),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'تم حذف المنتج']);
    }
}
