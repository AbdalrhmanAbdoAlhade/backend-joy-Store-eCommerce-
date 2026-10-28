<?php
// app/Services/Loyalty/LoyaltyService.php
namespace App\Services\Loyalty;

use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public function earnFromOrder(Order $order): void
    {
        if (!$order->user_id) {
            return;
        }

        $rate = (float) setting('points_earn_rate', 1); // كل جنيه = 1 نقطة
        $points = (int) floor($order->total * $rate);

        if ($points <= 0) {
            return;
        }

        $this->addPoints($order->user, $points, 'earn', "نقاط من الطلب #{$order->order_number}", $order);
    }

    public function redeem(User $user, int $points, $reference = null): float
    {
        if ($user->loyalty_points < $points) {
            throw new \RuntimeException('رصيد النقاط غير كافٍ');
        }

        $rate = (float) setting('points_to_currency_rate', 0.1);
        $discount = round($points * $rate, 2);

        $this->deductPoints($user, $points, 'redeem', 'استبدال نقاط', $reference);

        return $discount;
    }

    public function addPoints(User $user, int $points, string $type, ?string $description = null, $reference = null): void
    {
        DB::transaction(function () use ($user, $points, $type, $description, $reference) {
            $user->increment('loyalty_points', $points);

            LoyaltyPointTransaction::create([
                'user_id' => $user->id,
                'points' => $points,
                'type' => $type,
                'description' => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);
        });
    }

    public function deductPoints(User $user, int $points, string $type, ?string $description = null, $reference = null): void
    {
        DB::transaction(function () use ($user, $points, $type, $description, $reference) {
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            if ($user->loyalty_points < $points) {
                throw new \RuntimeException('رصيد النقاط غير كافٍ');
            }

            $user->decrement('loyalty_points', $points);

            LoyaltyPointTransaction::create([
                'user_id' => $user->id,
                'points' => -$points,
                'type' => $type,
                'description' => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);
        });
    }
}
