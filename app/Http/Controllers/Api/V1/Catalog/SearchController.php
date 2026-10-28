<?php
// app/Http/Controllers/Api/V1/Catalog/SearchController.php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = $request->get('q');

        if (!$q || strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $products = Product::active()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%")
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', "%{$q}%"));
            })
            ->with(['brand', 'primaryImage', 'variants'])
            ->limit(20)
            ->get();

        return ProductResource::collection($products);
    }
}
