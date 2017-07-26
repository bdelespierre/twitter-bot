<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ScheduleDaemon::class,
        Commands\Twitter\Follow::class,
        Commands\Twitter\Import::class,
        Commands\Twitter\Mute::class,
        Commands\Twitter\Unfollow::class,
        Commands\Twitter\Tweet::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('twitter:sync')
            ->hourly()
            ->withoutOverlapping()
            ->emailOutputTo('benjamin.delespierre@gmail.com');

        $schedule->command('twitter:tweet')
            ->cron('0 10,13,18,20 * * *');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
