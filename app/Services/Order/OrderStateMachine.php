<?php
// app/Services/Order/OrderStateMachine.php
namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderStateMachine
{
    public function transition(Order $order, OrderStatus $newStatus, ?User $changedBy = null, ?string $note = null): Order
    {
        $current = $order->status;

        if (!$current->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Cannot transition order #{$order->order_number} from {$current->value} to {$newStatus->value}"
            );
        }

        return DB::transaction(function () use ($order, $current, $newStatus, $changedBy, $note) {
            $order->status = $newStatus;

            match ($newStatus) {
                OrderStatus::Confirmed => $order->confirmed_at = now(),
                OrderStatus::Shipped => $order->shipped_at = now(),
                OrderStatus::Delivered => $order->delivered_at = now(),
                OrderStatus::Cancelled => $order->cancelled_at = now(),
                default => null,
            };

            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $current->value,
                'to_status' => $newStatus->value,
                'changed_by' => $changedBy?->id,
                'note' => $note,
            ]);

            return $order->fresh();
        });
    }
}
