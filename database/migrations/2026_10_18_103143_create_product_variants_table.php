<?php
// 2024_01_01_000017_create_product_variants_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('name')->nullable(); // أحمر / كبير...
            $table->json('attributes')->nullable(); // {"color":"red","size":"L"}
            $table->decimal('price', 12, 2)->nullable(); // لو null → سعر المنتج
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('reserved_stock')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->boolean('is_active')->default(true);
            $table->string('barcode')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
            $table->index('stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
