<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendDailyLeaveEmail;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    // ✅ Register command explicitly
    protected $commands = [
        SendDailyLeaveEmail::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('email:daily-leave-report')->dailyAt('12:38');
        $schedule->command('email:daily-leave-report')->everyMinute();
        
        // $schedule->command('email:daily-leave-report')->everyFiveMinutes();
        // $schedule->command('email:daily-leave-report')->dailyAt('12:25');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
