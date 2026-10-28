<?php
// app/Models/InventoryMovement.php
namespace App\Models;

use App\Enums\InventoryMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_variant_id', 'type', 'quantity',
        'quantity_before', 'quantity_after',
        'reference_type', 'reference_id', 'reason', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => InventoryMovementType::class,
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
