<?php
// app/Http/Controllers/Api/V1/Staff/ProductController.php
namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'variants', 'categories'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return ProductResource::collection($query->paginate(20));
    }

    public function show(Product $product)
    {
        return new ProductResource(
            $product->load(['brand', 'ageGroup', 'categories', 'tags', 'variants', 'images'])
        );
    }
}
