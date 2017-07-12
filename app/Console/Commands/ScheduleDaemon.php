<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * worker: php -d memory_limit=512M artisan schedule:daemon
 */
class ScheduleDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the scheduler without having to use a cron';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        do {
            $wait = 60 - date('s');
            $this->info("Waiting {$wait}s until next run...");
            sleep($wait);
            $this->call('schedule:run');
        } while (true);
    }
}
