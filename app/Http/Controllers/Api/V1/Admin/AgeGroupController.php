<?php
// app/Http/Controllers/Api/V1/Admin/AgeGroupController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\AgeGroupResource;
use App\Models\AgeGroup;
use Illuminate\Http\Request;

class AgeGroupController extends Controller
{
    public function index()
    {
        return AgeGroupResource::collection(
            AgeGroup::orderBy('sort_order')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'min_age' => ['nullable', 'integer', 'min:0'],
            'max_age' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $ageGroup = AgeGroup::create($data);

        return response()->json([
            'message' => 'تم إنشاء الفئة العمرية',
            'age_group' => new AgeGroupResource($ageGroup),
        ], 201);
    }

    public function update(Request $request, AgeGroup $ageGroup)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'min_age' => ['nullable', 'integer', 'min:0'],
            'max_age' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $ageGroup->update($data);

        return response()->json([
            'message' => 'تم التحديث',
            'age_group' => new AgeGroupResource($ageGroup),
        ]);
    }

    public function destroy(AgeGroup $ageGroup)
    {
        $ageGroup->delete();
        return response()->json(['message' => 'تم الحذف']);
    }
}
