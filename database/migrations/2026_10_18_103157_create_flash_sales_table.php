<?php
// 2024_01_01_000051_create_flash_sales_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('flash_sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('sale_price', 12, 2);
            $table->unsignedInteger('quantity_limit')->nullable();
            $table->unsignedInteger('sold_count')->default(0);
            $table->timestamps();

            $table->unique(
    ['flash_sale_id', 'product_id', 'product_variant_id'],
    'flash_sale_product_unique'   // اسم قصير
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sale_products');
        Schema::dropIfExists('flash_sales');
    }
};
