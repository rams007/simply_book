<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\WatchBookingStatus',
        'App\Console\Commands\DeleteUpaidBooking',
        'App\Console\Commands\updateAvaliableDates',
        'App\Console\Commands\updateAdditionalFields',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('WatchBookingStatus')->daily()->sendOutputTo(storage_path() . '/logs/cron/WatchBookingStatus.log');
        $schedule->command('updateAdditionalFields')->twiceDaily()->sendOutputTo(storage_path() . '/logs/cron/updateAdditionalFields.log');
        $schedule->command('DeleteUpaidBooking')->everyMinute()->sendOutputTo(storage_path() . '/logs/cron/DeleteUpaidBooking.log');
        $schedule->command('updateAvaliableDates')->everyFifteenMinutes()->sendOutputTo(storage_path() . '/logs/cron/updateAvaliableDates.log');
    }


}
