<?php

namespace App\Console;

use App\Http\Controllers\Api\OrderController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('ratings:calculate')->dailyAt('03:00'); // Каждый день в 03:00 ночи

        // $schedule->command('orders:process-pending')->everyThirtyMinutes();

        $schedule->command('database:backup')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
