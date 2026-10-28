<?php
// 2024_01_01_000030_create_inventory_movements_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['purchase', 'sale', 'return', 'adjustment', 'damaged', 'reservation', 'release']);
            $table->integer('quantity'); // موجب أو سالب
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference_type')->nullable(); // Order, Return...
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['product_variant_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
