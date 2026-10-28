<?php
// app/Http/Controllers/Api/V1/Catalog/ProductController.php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()
            ->with(['brand', 'ageGroup', 'categories', 'primaryImage', 'variants']);

        // Filters
        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $request->category));
        }
        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $request->brand));
        }
        if ($request->filled('age_group')) {
            $query->where('age_group_id', $request->age_group);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->boolean('on_sale')) {
            $query->onSale();
        }
        if ($request->boolean('in_stock')) {
            $query->inStock();
        }
        if ($request->boolean('featured')) {
            $query->featured();
        }
        if ($request->boolean('new_arrival')) {
            $query->newArrival();
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'best_selling' => $query->orderByDesc('sold_count'),
            'rating' => $query->orderByDesc('avg_rating'),
            default => $query->latest(),
        };

        $products = $query->paginate($request->get('per_page', 20));

        return ProductResource::collection($products);
    }

    public function show(string $slug)
    {
        $product = Product::active()
            ->where('slug', $slug)
            ->with([
                'brand', 'ageGroup', 'categories', 'tags',
                'variants' => fn ($q) => $q->active(),
                'images', 'videos', 'relatedProducts.primaryImage',
            ])
            ->firstOrFail();

        // زيادة عداد المشاهدات
        $product->increment('views_count');

        return new ProductResource($product);
    }
}
