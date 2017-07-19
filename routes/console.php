<?php

/*
|--------------------------------------------------------------------------
| Heroku
|--------------------------------------------------------------------------
|
*/

Artisan::command('heroku:bash', function () {
    passthru("heroku run bash -a " . env('HEROKU_APP_NAME'));
});

Artisan::command('heroku:logs {--t|tail}', function () {
    passthru("heroku logs " . ($this->option('tail') ? '--tail' : '') . " -a " . env('HEROKU_APP_NAME'));
});

Artisan::command('heroku:tinker', function () {
    passthru("heroku run php artisan tinker -a " . env('HEROKU_APP_NAME'));
});

/*
|--------------------------------------------------------------------------
| Generic
|--------------------------------------------------------------------------
|
*/

Artisan::command('cache:warmup', function () {
    $this->call('import:twitter', ['relationship' => 'friends']);
    $this->call('import:twitter', ['relationship' => 'followers']);
})->describe("Warms up the cache");

Artisan::command('purge:logs {--d|days=3 : Number of days}', function () {
    App\Journal::notice("[{$this->name}] started");
    App\Journal::where('date', '<=', Carbon\Carbon::now()->subDays(3))->delete();
})->describe("Destroy old journal entries");

/*
|--------------------------------------------------------------------------
| Import
|--------------------------------------------------------------------------
|
*/

Artisan::command('import:twitter {relationship} {--throttle=60} {--cursor=}', function () {
    if (!in_array($relationship = $this->argument('relationship'), ['friends', 'followers'])) {
        $msg = "Relationship is expected to be 'friends' or 'followers', {$relationship} given";
        throw new UnexpectedValueException($msg);
    }

    $cmd = "[{$this->name}] [{$relationship}]";
    App\Journal::notice("{$cmd} started");

    if ($this->option('cursor')) {
        $cursor = $this->option('cursor');
        App\Journal::notice("{$cmd} resuming from cursor {$cursor}");
    }

    if (Cache::has($cacheKey = "twitter.import.{$relationship}.cursor")) {
        $cursor = Cache::pull($cacheKey);
        App\Journal::notice("{$cmd} resuming from cursor {$cursor}");
    }

    do {
        try {
            $following = Twitter::{'get'.ucfirst($relationship)}(['format' => 'array'] + compact('cursor'));
            list('users' => $users, 'next_cursor_str' => $cursor) = $following;
        } catch (RuntimeException $e) {
            App\Journal::error("{$cmd} $e");
            return;
        }

        foreach ($users as $data) {
            App\Journal::info("{$cmd} @{$data['screen_name']} #{$data['id']}");

            $user = App\Models\Twitter\User::updateOrCreate(
                ['id' => $data['id']],
                ['screen_name' => $data['screen_name']] + compact('data')
            );

            $user->{substr($relationship, 0, -1)} = true; // 'friend' or 'follower'
            $user->updateAttributes()->save();
        }

        if ($cursor) {
            $expireAt = Carbon\Carbon::now()->addMinutes(180); // 3h
            Cache::put($cacheKey, $cursor, $expireAt);
        }

        if ($this->option('throttle') && $cursor) {
            App\Journal::debug("{$cmd} sleep before cursor {$cursor}");
            sleep($this->option('throttle')); // seconds
        }
    } while ($cursor);
})->describe("Imports friends or followers from Twitter account");

/**
 * learnprogramming
 * programming
 */
Artisan::command('import:reddit {subreddit}', function ($subreddit) {
    App\Journal::notice("[{$this->name}] started", compact('subreddit'));

    $url = "https://www.reddit.com/r/{$subreddit}/hot/.rss?sort=hot";
    App\Journal::debug("[{$this->name}] reading from {$url}");

    foreach (App\Domain\Atom\Document::fromUrl($url)->items as $item) {
        $url = array_first($item->urls, function($url) {
            return parse_url($url, PHP_URL_HOST) != 'www.reddit.com';
        });

        if (!$articles = App\Domain\Html\Document::fromUrl($url)->articles) {
            App\Journal::debug("[{$this->name}] ignoring {$url} : no article found");
            continue;
        }

        App\Journal::debug("[{$this->name}] " . count($articles) . " found for {$url}");

        // ...
    }
});

/*
|--------------------------------------------------------------------------
| Bot
|--------------------------------------------------------------------------
|
*/

Artisan::command('bot:follow', function () {
    App\Journal::notice("[{$this->name}] started");

    foreach (App\Models\Twitter\User::fans()->get() as $user) {
        try {
            App\Journal::info("[{$this->name}] following {$user->id}");
            $user->follow()->mute();
        } catch (RuntimeException $exception) {
            return;
        }
    }
})->describe('Follow back fans');

Artisan::command('bot:unfollow', function () {
    App\Journal::notice("[{$this->name}] started");

    $following = App\Models\Twitter\User::friends()->exceptVip()->pluck('id')->toArray();
    $unfollow  = [];

    while ($bulk = array_slice($following, 0, 100)) {
        try {
            $users = Twitter::getFriendshipsLookup(['format' => 'array', 'user_id' => $bulk]);
        } catch (RuntimeException $e) {
            App\Journal::error("[{$this->name}] $e");
            return;
        }

        foreach ($users as $user) {
            if (!in_array('followed_by', $user['connections'])) {
                $unfollow[] = $user;
            }
        }

        $following = array_slice($following, 100);
    }

    if (empty($unfollow)) {
        App\Journal::info("[{$this->name}] no one to unfollow");
        return;
    }

    foreach ($unfollow as $user) {
        App\Journal::info("[{$this->name}] unfollowing @{$user['screen_name']}");

        try {
            App\Models\Twitter\User::findOrFail($user['id'])->unfollow();
        } catch (Exception $e) {
            return;
        }
    }
})->describe('Unfollow people that don\'t follow me');

Artisan::command('bot:mute', function () {
    App\Journal::notice("[{$this->name}] started");

    foreach (App\Models\Twitter\User::exceptVip()->exceptMuted()->get() as $user) {
        try {
            App\Journal::info("[{$this->name}] muting @{$user->screen_name}");
            $user->mute();
        } catch (RuntimeException $e) {
            if (strpos($e->getMessage(), 'does not exist') !== false) {
                App\Journal::error("[{$this->name}] Propably doesn't exists anymore");
                continue;
            }

            return;
        }
    }
})->describe("Mute everyone (except VIP)");

Artisan::command('bot:tweet', function () {
    App\Journal::notice("[{$this->name}] started");

    $item = App\Models\BufferItem::orderBy('created_at', 'desc')->first();
    $res  = (new GuzzleHttp\Client)->post('https://www.googleapis.com/urlshortener/v1/url', [
        'query' => ['key' => 'AIzaSyAqnT8WwK6HL5rX61R5lc_WL4kZca4VYtc'],
        'json'  => ['longUrl' => $item->url]
    ]);

    $url   = substr(json_decode((string) $res->getBody(), true)['id'], 8); // len = 13
    $max   = 140 - strlen($url) - 1;
    $title = $item->metadata->title;

    // truncate long titles
    if (strlen($title) > $max) {
        $title = substr($title, 0, $max - 1) . "…";
    }

    $hashtags = [
        'tech', 'javascript', 'php', 'startup', 'ux', 'devops', 'laravel',
        'symfony', 'chatbot', 'devel', 'bitcoin', 'blockchain', 'angular',
        'react', 'frontend', 'backend', 'code', 'coding', 'gamification',
        'programming', 'ai', 'node', 'nodejs', 'firebase', 'google', 'chrome',
        'android', 'webapp', 'ui'
    ];

    // if there is room, add hashtags
    if (strlen($title) -1 < $max) {
        foreach ($hashtags as $i => $hashtag) {
            $new = preg_replace("/ ({$hashtag})/i", ' #$1', $title, 1, $count);

            if (!$count) {
                continue;
            }

            if (strlen($new) > $max) {
                break;
            }

            //  no more than 3 hashtags
            if (substr_count($title, '#') >= 3) {
                break;
            }

            $title = $new;
        }
    }

    $icons = ['😎','😃','😊','😍','😺','😻','😄','😆','💯','👍','🔥'];

    // if there is room, add icons
    if (strlen($title) -2 < $max) {
        $title .= " " . $icons[array_rand($icons)];

        for ($i=1; $i<3; $i++) {
            $new = $title . $icons[array_rand($icons)];

            if (strlen($new) > $max) {
                break;
            }

            $title = $new;
        }
    }

    // Ok, let's tweet that
    $result = Twitter::postTweet(['status' => "{$title} {$url}"]);

    if (!empty($result->id)) {
        $item->delete();
    }
})->describe("Tweet something");