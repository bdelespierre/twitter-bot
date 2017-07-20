<?php

namespace App\Console\Commands\Twitter;

use App\Console\Commands\Bliss;
use App\Models\Twitter\User as TwitterUser;
use Illuminate\Console\Command;

class Mute extends Command
{
    use Bliss;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:mute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mute everyone (except VIP)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (TwitterUser::exceptVip()->exceptMuted()->get() as $user) {
            $this->bliss(function() use ($user) {
                $this->info("muting @{$user->screen_name}");
                $user->mute();
            });
        }

        $this->report();
    }
}
