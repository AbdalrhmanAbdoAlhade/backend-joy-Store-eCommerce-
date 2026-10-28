<?php
// 2024_01_01_000012_create_age_groups_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('age_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 0-12 شهر، 1-3 سنين...
            $table->unsignedTinyInteger('min_age')->nullable(); // بالشهور
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('age_groups');
    }
};
