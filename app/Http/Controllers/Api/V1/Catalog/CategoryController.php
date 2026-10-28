<?php
// app/Http/Controllers/Api/V1/Catalog/CategoryController.php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::active()
            ->root()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show(string $slug)
    {
        $category = Category::active()
            ->where('slug', $slug)
            ->with('children')
            ->firstOrFail();

        return new CategoryResource($category);
    }
}
