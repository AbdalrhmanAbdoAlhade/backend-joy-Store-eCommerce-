<?php
// app/Events/BackInStock.php
namespace App\Events;

use App\Models\ProductVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackInStock
{
    use Dispatchable, SerializesModels;

    public function __construct(public ProductVariant $variant) {}
}
