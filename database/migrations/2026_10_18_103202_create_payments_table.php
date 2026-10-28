<?php
// 2024_01_01_000070_create_payments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway'); // cod, paymob, fawry, wallet...
            $table->string('transaction_id')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('EGP');
            $table->enum('status', ['pending', 'success', 'failed', 'refunded', 'partially_refunded'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // charge, refund, partial_refund
            $table->decimal('amount', 12, 2);
            $table->string('gateway_transaction_id')->nullable();
            $table->enum('status', ['pending', 'success', 'failed']);
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');
    }
};
