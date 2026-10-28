<?php
// app/Jobs/ReleaseExpiredStockReservations.php
namespace App\Jobs;

use App\Services\Stock\StockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredStockReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(StockService $stockService): void
    {
        $count = $stockService->releaseExpiredReservations();

        if ($count > 0) {
            Log::info("Released {$count} expired stock reservations");
        }
    }
}
