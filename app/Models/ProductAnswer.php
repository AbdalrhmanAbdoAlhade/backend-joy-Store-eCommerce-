<?php
// app/Models/ProductAnswer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAnswer extends Model
{
    protected $fillable = [
        'product_question_id', 'user_id', 'answer', 'is_official',
    ];

    protected function casts(): array
    {
        return [
            'is_official' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ProductQuestion::class, 'product_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
