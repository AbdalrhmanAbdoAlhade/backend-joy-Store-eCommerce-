<?php
// app/Models/BackInStockNotification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackInStockNotification extends Model
{
    protected $fillable = [
        'user_id', 'product_variant_id', 'notified', 'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'notified' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
