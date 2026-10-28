<?php
// app/Models/ShippingZone.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = [
        'name', 'governorates', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'governorates' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
