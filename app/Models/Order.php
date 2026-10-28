<?php
// app/Models/Order.php
namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'order_number', 'invoice_number', 'user_id', 'created_by',
        'status', 'subtotal', 'discount_amount', 'shipping_amount',
        'tax_amount', 'gift_wrap_amount', 'points_discount', 'total',
        'shipping_method', 'shipping_company', 'tracking_number', 'tracking_url',
        'shipping_address', 'billing_address',
        'coupon_id', 'coupon_code',
        'is_gift', 'gift_message',
        'customer_note', 'internal_note',
        'payment_method', 'payment_status',
        'idempotency_key',
        'confirmed_at', 'shipped_at', 'delivered_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'gift_wrap_amount' => 'decimal:2',
            'points_discount' => 'decimal:2',
            'total' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'is_gift' => 'boolean',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'total', 'internal_note'])
            ->logOnlyDirty();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, [
            OrderStatus::Pending,
            OrderStatus::Confirmed,
            OrderStatus::Processing,
        ]);
    }
}
