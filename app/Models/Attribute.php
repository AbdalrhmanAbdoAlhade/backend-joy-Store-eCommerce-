<?php
// app/Models/Attribute.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $fillable = [
        'name', 'slug', 'type', 'is_filterable', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }
}
