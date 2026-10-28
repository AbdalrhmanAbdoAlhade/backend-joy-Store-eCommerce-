<?php
// app/Policies/OrderPolicy.php
namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('orders.view') || $user->hasRole('admin');
    }

    public function view(User $user, Order $order): bool
    {
        // العميل يشوف طلباته فقط
        if ($user->id === $order->user_id) {
            return true;
        }

        return $user->can('orders.view');
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->can('orders.update_status');
    }

    public function createManual(User $user): bool
    {
        return $user->can('orders.create_manual');
    }

    public function cancel(User $user, Order $order): bool
    {
        // العميل يلغي قبل الشحن فقط
        if ($user->id === $order->user_id) {
            return $order->canBeCancelledByCustomer();
        }

        return $user->can('orders.update_status');
    }
}
