<?php
// app/Services/Stock/StockService.php
namespace App\Services\Stock;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    public function decrease(ProductVariant $variant, int $quantity, InventoryMovementType $type, $reference = null, ?User $user = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($variant, $quantity, $type, $reference, $user, $reason) {
            $variant = ProductVariant::where('id', $variant->id)->lockForUpdate()->first();

            if ($variant->available_stock < $quantity) {
                throw new RuntimeException("Insufficient stock for SKU: {$variant->sku}");
            }

            $before = $variant->stock;
            $variant->stock -= $quantity;
            $variant->save();

            $this->logMovement($variant, $type, -$quantity, $before, $variant->stock, $reference, $user, $reason);
        });
    }

    public function increase(ProductVariant $variant, int $quantity, InventoryMovementType $type, $reference = null, ?User $user = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($variant, $quantity, $type, $reference, $user, $reason) {
            $variant = ProductVariant::where('id', $variant->id)->lockForUpdate()->first();

            $before = $variant->stock;
            $variant->stock += $quantity;
            $variant->save();

            $this->logMovement($variant, $type, $quantity, $before, $variant->stock, $reference, $user, $reason);
        });
    }

    public function reserve(ProductVariant $variant, int $quantity, ?User $user = null, ?string $sessionId = null, int $minutes = 15): StockReservation
    {
        return DB::transaction(function () use ($variant, $quantity, $user, $sessionId, $minutes) {
            $variant = ProductVariant::where('id', $variant->id)->lockForUpdate()->first();

            if ($variant->available_stock < $quantity) {
                throw new RuntimeException("Insufficient stock to reserve for SKU: {$variant->sku}");
            }

            $variant->reserved_stock += $quantity;
            $variant->save();

            return StockReservation::create([
                'product_variant_id' => $variant->id,
                'user_id' => $user?->id,
                'session_id' => $sessionId,
                'quantity' => $quantity,
                'expires_at' => now()->addMinutes($minutes),
            ]);
        });
    }

    public function releaseReservation(StockReservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $variant = ProductVariant::where('id', $reservation->product_variant_id)->lockForUpdate()->first();

            $variant->reserved_stock = max(0, $variant->reserved_stock - $reservation->quantity);
            $variant->save();

            $reservation->delete();
        });
    }

    public function releaseExpiredReservations(): int
    {
        $expired = StockReservation::expired()->get();
        $count = 0;

        foreach ($expired as $reservation) {
            $this->releaseReservation($reservation);
            $count++;
        }

        return $count;
    }

    public function confirmReservation(StockReservation $reservation, $order = null): void
    {
        DB::transaction(function () use ($reservation, $order) {
            $variant = ProductVariant::where('id', $reservation->product_variant_id)->lockForUpdate()->first();

            // تحويل الحجز إلى بيع فعلي
            $variant->reserved_stock = max(0, $variant->reserved_stock - $reservation->quantity);
            $variant->stock -= $reservation->quantity;
            $variant->save();

            $this->logMovement(
                $variant,
                InventoryMovementType::Sale,
                -$reservation->quantity,
                $variant->stock + $reservation->quantity,
                $variant->stock,
                $order
            );

            $reservation->delete();
        });
    }

    protected function logMovement(
        ProductVariant $variant,
        InventoryMovementType $type,
        int $quantity,
        int $before,
        int $after,
        $reference = null,
        ?User $user = null,
        ?string $reason = null
    ): void {
        InventoryMovement::create([
            'product_variant_id' => $variant->id,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'reason' => $reason,
            'user_id' => $user?->id,
        ]);
    }
}
