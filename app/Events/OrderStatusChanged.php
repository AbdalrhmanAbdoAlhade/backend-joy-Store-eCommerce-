<?php
// app/Events/OrderStatusChanged.php
namespace App\Events;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public OrderStatus $fromStatus,
        public OrderStatus $toStatus
    ) {}
}
