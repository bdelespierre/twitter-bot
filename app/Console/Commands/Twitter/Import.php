<?php

namespace App\Console\Commands\Twitter;

use App\Console\Commands\Bliss;
use App\Domain\Twitter\CursoredCollection;
use App\Models\Twitter\User as TwitterUser;
use Exception;
use Illuminate\Console\Command;
use UnexpectedValueException;

class Import extends Command
{
    use Bliss;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:import {relationship} {--cursor=} {--no-cache} {--throttle=60}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports friends or followers from Twitter account';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!in_array($relationship = $this->argument('relationship'), ['friends', 'followers'])) {
            return $this->error("Relationship should be 'friends' or 'followers'");
        }

        if ($this->option('cursor')) {
            $cursor = $this->option('cursor');
        }

        if (!$this->option('no-cache')) {
            $cache = ['key' => "twitter.import.{$relationship}.cursor", 'ttl' => 180];
        }

        if ($this->option('throttle')) {
            $throttle = (int) $this->option('throttle');
        }

        $options = compact('cursor', 'cache', 'throttle') + [
            'args' => ['count' => 200],
            'tap'  => function ($collection, $cursor) {
                if ($this->output->isVerbose()) {
                    $this->comment("sleep before cursor {$cursor}");
                }
            },
        ];

        $updated = 0;
        foreach (new CursoredCollection('get'.ucfirst($relationship), 'users', $options) as $data) {
            $this->bliss(function () use ($data, $relationship, & $updated) {
                if ($this->output->isVerbose()) {
                    $this->info(sprintf('#%s @%s', str_pad($data['id'], 25, '.'), $data['screen_name']));
                }

                $user = TwitterUser::updateOrCreate(
                    ['id'          => $data['id']],
                    ['screen_name' => $data['screen_name']] + compact('data')
                );

                if (in_array($user->screen_name, config('twitter.vip', []))) {
                    $user->vip = true;
                }

                $user->{substr($relationship, 0, -1)} = true; // 'friend' or 'follower'
                $updated += (int) $user->updateAttributes()->save();
            });
        }

        $this->info(sprintf('%d users updated', $updated));
        $this->report();
    }
}
