<?php
// 2024_01_01_000060_create_orders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('invoice_number')->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // للطلبات اليدوية

            // الحالة
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'ready_to_ship',
                'shipped', 'out_for_delivery', 'delivered',
                'cancelled', 'returned', 'refunded', 'failed'
            ])->default('pending');

            // المبالغ
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('gift_wrap_amount', 12, 2)->default(0);
            $table->decimal('points_discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            // الشحن
            $table->string('shipping_method')->nullable();
            $table->string('shipping_company')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();

            // العنوان (snapshot)
            $table->json('shipping_address');
            $table->json('billing_address')->nullable();

            // كوبون
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable();

            // هدايا
            $table->boolean('is_gift')->default(false);
            $table->text('gift_message')->nullable();

            // ملاحظات
            $table->text('customer_note')->nullable();
            $table->text('internal_note')->nullable();

            // الدفع
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])->default('pending');

            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
