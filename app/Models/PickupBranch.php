<?php
// app/Models/PickupBranch.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupBranch extends Model
{
    protected $fillable = [
        'name', 'governorate', 'city', 'address', 'phone',
        'lat', 'lng', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
