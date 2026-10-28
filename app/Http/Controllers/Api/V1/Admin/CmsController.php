<?php
// app/Http/Controllers/Api/V1/Admin/CmsController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    // ===== Static Pages =====
    public function pages()
    {
        return response()->json(StaticPage::orderBy('title')->get());
    }

    public function storePage(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'unique:static_pages,slug'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $page = StaticPage::create($data);

        return response()->json(['message' => 'تم إنشاء الصفحة', 'page' => $page], 201);
    }

    public function updatePage(Request $request, StaticPage $page)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'unique:static_pages,slug,' . $page->id],
            'content' => ['sometimes', 'string'],
            'meta_title' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $page->update($data);
        return response()->json(['message' => 'تم التحديث', 'page' => $page]);
    }

    public function destroyPage(StaticPage $page)
    {
        $page->delete();
        return response()->json(['message' => 'تم الحذف']);
    }

    // ===== FAQs =====
    public function faqs()
    {
        return response()->json(Faq::orderBy('sort_order')->get());
    }

    public function storeFaq(Request $request)
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $faq = Faq::create($data);
        return response()->json(['message' => 'تم الإنشاء', 'faq' => $faq], 201);
    }

    public function updateFaq(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'question' => ['sometimes', 'string'],
            'answer' => ['sometimes', 'string'],
            'category' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $faq->update($data);
        return response()->json(['message' => 'تم التحديث', 'faq' => $faq]);
    }

    public function destroyFaq(Faq $faq)
    {
        $faq->delete();
        return response()->json(['message' => 'تم الحذف']);
    }
}
