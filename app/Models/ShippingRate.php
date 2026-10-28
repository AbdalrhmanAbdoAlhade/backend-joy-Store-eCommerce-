<?php
// app/Models/ShippingRate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_zone_id', 'shipping_method_id',
        'min_order_amount', 'max_order_amount',
        'min_weight', 'max_weight', 'price',
        'is_free_above', 'free_above_amount',
    ];

    protected function casts(): array
    {
        return [
            'min_order_amount' => 'decimal:2',
            'max_order_amount' => 'decimal:2',
            'min_weight' => 'decimal:3',
            'max_weight' => 'decimal:3',
            'price' => 'decimal:2',
            'is_free_above' => 'boolean',
            'free_above_amount' => 'decimal:2',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }
}
