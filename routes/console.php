<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\ReleaseExpiredStockReservations;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new ReleaseExpiredStockReservations)->everyFiveMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
