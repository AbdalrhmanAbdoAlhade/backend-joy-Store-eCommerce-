<?php
// app/Models/ReturnRequest.php
namespace App\Models;

use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'return_number', 'order_id', 'user_id', 'status',
        'reason', 'description', 'images',
        'reviewed_by', 'admin_note', 'restock',
        'refund_amount', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReturnStatus::class,
            'images' => 'array',
            'restock' => 'boolean',
            'refund_amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
