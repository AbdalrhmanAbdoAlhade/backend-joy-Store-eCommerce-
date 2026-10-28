<?php
// 2024_01_01_000014_create_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();

            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('age_group_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('gender', ['boys', 'girls', 'unisex'])->default('unisex');

            // حقول خاصة بالألعاب
            $table->string('material')->nullable();
            $table->string('dimensions')->nullable(); // LxWxH
            $table->decimal('weight', 8, 3)->nullable(); // كجم
            $table->boolean('battery_required')->default(false);
            $table->boolean('batteries_included')->default(false);
            $table->text('safety_warnings')->nullable();
            $table->boolean('choking_hazard')->default(false);
            $table->json('certifications')->nullable(); // CE, EN71...
            $table->unsignedInteger('pieces_count')->nullable();
            $table->boolean('educational')->default(false);
            $table->text('how_to_play')->nullable();

            // الأسعار
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable(); // للأدمن فقط
            $table->decimal('tax_rate', 5, 2)->default(0);

            // الحالة
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(false);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured', 'is_new_arrival']);
            $table->index(['brand_id', 'age_group_id', 'gender']);
            $table->index(['price', 'sale_price']);
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
