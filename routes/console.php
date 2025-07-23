<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RatingController;
use App\Console\Commands\BackupDatabase;
use App\Console\Commands\CalculateMasterRatings;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    app(OrderController::class)->QuickOrdersUpdates();
})->hourly();

Schedule::call(function () {
    app(BackupDatabase::class)->handle();
})->daily();

Schedule::call(function () {
    app(CalculateMasterRatings::class)->handle();
})->everyMinute();

Schedule::command('telescope:prune --hours=168')->daily();