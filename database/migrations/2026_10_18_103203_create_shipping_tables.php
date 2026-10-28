<?php
// 2024_01_01_000080_create_shipping_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // القاهرة، الجيزة...
            $table->json('governorates'); // ["القاهرة","الجيزة"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // عادي، سريع، استلام من الفرع
            $table->string('code')->unique(); // standard, express, pickup
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->decimal('min_order_amount', 12, 2)->default(0);
            $table->decimal('max_order_amount', 12, 2)->nullable();
            $table->decimal('min_weight', 8, 3)->default(0);
            $table->decimal('max_weight', 8, 3)->nullable();
            $table->decimal('price', 12, 2);
            $table->boolean('is_free_above')->default(false);
            $table->decimal('free_above_amount', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['shipping_zone_id', 'shipping_method_id']);
        });

        Schema::create('pickup_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('governorate');
            $table->string('city');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_branches');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
    }
};
