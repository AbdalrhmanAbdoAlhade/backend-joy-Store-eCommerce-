<?php
// app/Http/Controllers/Api/V1/ProductQuestionController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\ProductAnswer;
use Illuminate\Http\Request;

class ProductQuestionController extends Controller
{
    public function index(Product $product)
    {
        $questions = $product->questions()
            ->where('status', 'answered')
            ->with(['user:id,name', 'answers.user:id,name'])
            ->latest()
            ->paginate(10);

        return response()->json($questions);
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        $question = $product->questions()->create([
            'user_id' => $request->user()->id,
            'question' => $request->question,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'تم إرسال السؤال بنجاح',
            'question' => $question,
        ], 201);
    }

    public function answer(Request $request, ProductQuestion $question)
    {
        // للموظف/الأدمن فقط
        if (!$request->user()->can('products.manage') && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate([
            'answer' => ['required', 'string', 'max:2000'],
        ]);

        $answer = $question->answers()->create([
            'user_id' => $request->user()->id,
            'answer' => $request->answer,
            'is_official' => true,
        ]);

        $question->update(['status' => 'answered']);

        return response()->json([
            'message' => 'تم إضافة الإجابة',
            'answer' => $answer,
        ], 201);
    }
}
