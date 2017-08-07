<?php

namespace App\Console\Commands\Twitter;

use App\Console\Commands\Bliss;
use App\Domain\Generic\IntervalSynchronizer;
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
        $muted = 0;
        $mute  = new IntervalSynchronizer(6, function ($user) use (& $muted) {
            if ($this->output->isVerbose()) {
                $this->info("muting @{$user->screen_name}");
            }

            $user->mute();
            $muted++;
        });

        foreach (TwitterUser::exceptVip()->exceptMuted()->get() as $user) {
            $this->bliss($mute, $user);
        }

        $this->info(sprintf('%d users muted', $muted));
        $this->report();
    }
}
