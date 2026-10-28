<?php
// 2024_01_01_000001_create_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->enum('status', ['active', 'banned', 'pending'])->default('active');
            $table->boolean('is_admin')->default(false);
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_admin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
