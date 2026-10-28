<?php
// 2024_01_01_000101_create_product_questions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->enum('status', ['pending', 'answered', 'rejected'])->default('pending');
            $table->timestamps();
        });

        Schema::create('product_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // موظف أو أدمن
            $table->text('answer');
            $table->boolean('is_official')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_answers');
        Schema::dropIfExists('product_questions');
    }
};
