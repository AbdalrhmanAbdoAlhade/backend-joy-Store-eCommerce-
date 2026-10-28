<?php
// app/Http/Controllers/Api/V1/Catalog/BrandController.php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\BrandResource;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::active()->orderBy('sort_order')->get();

        return BrandResource::collection($brands);
    }
}
