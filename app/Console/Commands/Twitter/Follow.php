<?php

namespace App\Console\Commands\Twitter;

use App\Console\Commands\Bliss;
use App\Domain\Generic\IntervalSynchronizer;
use App\Models\Twitter\User as TwitterUser;
use Illuminate\Console\Command;
use RuntimeException;

class Follow extends Command
{
    use Bliss;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:follow {--mute}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Follow back fans';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $follow = new IntervalSynchronizer(6, function (TwitterUser $user) {
            return $user->follow();
        });

        $mute = new IntervalSynchronizer(6, function (TwitterUser $user) {
            return $user->mute();
        });

        foreach (TwitterUser::fans()->get() as $user) {
            if (!in_array($user->lang, ['en', 'fr'])) {
                return;
            }

            $this->info("following {$user->id}");
            $this->bliss($follow, $user);

            if ($this->hasOption('mute')) {
                $this->bliss($mute, $user);
            }
        }

        $this->report();
    }
}
