<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('bot:follow', function () {
    $this->info("Running bot:follow...");

    $followers = Twitter::getFollowersIds(['format' => 'array'])['ids'];
    $following = Twitter::getFriendsIds(['format' => 'array'])['ids'];

    if (!$ids = array_values(array_diff($followers, $following))) {
        return;
    }

    shuffle($ids);

    while ((count($following) <= count($followers)) && ($id = array_pop($ids))) {
        try {
            $this->info("Following {$id}");
            Twitter::postFollow(['user_id' => $id]);
            $following[] = $id;
        } catch (RuntimeException $e) {
            continue;
        }
    }
})->describe('Automatically follow back fans in random order');
