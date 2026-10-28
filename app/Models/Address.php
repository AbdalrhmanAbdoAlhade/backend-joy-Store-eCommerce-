<?php
// app/Models/Address.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'label', 'governorate', 'city', 'area',
        'street', 'building', 'floor', 'apartment', 'landmark',
        'phone', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullAddress(): string
    {
        return collect([
            $this->street,
            $this->building ? "مبنى {$this->building}" : null,
            $this->floor ? "دور {$this->floor}" : null,
            $this->apartment ? "شقة {$this->apartment}" : null,
            $this->area,
            $this->city,
            $this->governorate,
            $this->landmark ? "علامة: {$this->landmark}" : null,
        ])->filter()->implode('، ');
    }
}
