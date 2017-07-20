<?php

namespace App\Console\Commands\Twitter;

use App\Models\Twitter\User as TwitterUser;
use Illuminate\Console\Command;
use RuntimeException;

class Follow extends Command
{
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
        foreach (TwitterUser::fans()->get() as $user) {
            if (!in_array($user->lang, ['en', 'fr'])) {
                return;
            }

            $this->info("following {$user->id}");
            $user->follow();

            if ($this->hasOption('mute')) {
                $user->mute();
            }
        }
    }
}
