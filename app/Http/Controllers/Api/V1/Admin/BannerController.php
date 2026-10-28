<?php
// app/Http/Controllers/Api/V1/Admin/BannerController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::orderBy('sort_order');

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:4096'],
            'link' => ['nullable', 'string', 'max:500'],
            'position' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);

        $data['image'] = $request->file('image')->store('banners', 'public');
        $data['is_active'] = $data['is_active'] ?? true;

        $banner = Banner::create($data);

        return response()->json(['message' => 'تم إنشاء البنر', 'banner' => $banner], 201);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
            'link' => ['nullable', 'string', 'max:500'],
            'position' => ['sometimes', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return response()->json(['message' => 'تم التحديث', 'banner' => $banner]);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return response()->json(['message' => 'تم الحذف']);
    }
}
