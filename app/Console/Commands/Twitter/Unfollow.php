<?php

namespace App\Console\Commands\Twitter;

use App\Console\Commands\Bliss;
use App\Domain\Generic\IntervalSynchronizer;
use App\Models\Twitter\User as TwitterUser;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Thujohn\Twitter\Facades\Twitter;

class Unfollow extends Command
{
    use Bliss;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:unfollow {--throttle=60}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unfollow users that don\'t follow me';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $friends = TwitterUser::friends()->exceptVip()->pluck('id')->toArray();

        $getFriendshipsLookup = function ($chunk) {
            return Twitter::getFriendshipsLookup(['format' => 'array', 'user_id' => $chunk]);
        };

        if ($this->option('throttle')) {
            $getFriendshipsLookup = new IntervalSynchronizer($this->option('throttle'), $getFriendshipsLookup);
        }

        $unfollow = new IntervalSynchronizer(6, function ($user) {
            $this->info("unfollowing @{$user['screen_name']}");
            TwitterUser::findOrFail($user['id'])->unfollow();
        });

        foreach (array_chunk($friends, 100) as $chunk) {
            $friendships = $this->bliss($getFriendshipsLookup, $chunk);

            foreach ($friendships as $user) {
                if (// does this user follows me?
                    !in_array('followed_by', $user['connections']) ||

                    // de we speak the same language(s)?
                    !in_array(($user = TwitterUser::findOrFail($user['id']))->lang, ['en', 'fr'])
                ) {
                    $this->bliss($unfollow, $user);
                }
            }
        }

        $this->report();
    }
}
