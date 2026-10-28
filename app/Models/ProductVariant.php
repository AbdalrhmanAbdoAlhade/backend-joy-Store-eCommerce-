<?php
// app/Models/ProductVariant.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'name', 'attributes',
        'price', 'sale_price', 'cost_price',
        'stock', 'reserved_stock', 'low_stock_threshold',
        'is_active', 'barcode',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->stock - $this->reserved_stock);
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->sale_price ?? $this->price ?? $this->product->final_price;
    }

    public function isLowStock(): bool
    {
        return $this->available_stock <= $this->low_stock_threshold;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->whereRaw('(stock - reserved_stock) > 0');
    }
}
