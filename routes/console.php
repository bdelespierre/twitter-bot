<?php

// REMOVEME
$vip = [
    'clientsfh'   , 'SarahCAndersen'  , 'yukaichou'       ,
    'ProductHunt' , 'iamlosion'       , 'newsycombinator' ,
    'paulg'       , 'verge'           , '_TheFamily'      ,
    'sensiolabs'  , 'elonmusk'        , 'BrianTracy'      ,
    'Medium'      , 'ThePracticalDev' , 'afilina'         ,
    'hackernoon'  , 'IonicFramework'  , 'polymer'         ,
    'reactjs'     , 'MongoDB'         , 'googledevs'      ,
    'Google'      , 'shenanigansen'   , 'Rozasalahshour'  ,
    'jlondiche'   , 'DelespierreB'    , 'matts2cant'      ,
];

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
    App\Journal::where('date', '>=', Carbon\Carbon::now()->subDays(3))->delete();
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

    libxml_use_internal_errors(true); // tgnb

    $doc = new DOMDocument;
    $doc->preserveWhiteSpace = false;
    $doc->load($url);

    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');
    $items = $xpath->query('/a:feed/a:entry');

    foreach ($items as $item) {
        $title = $xpath->query('./a:title', $item)->item(0)->nodeValue;
        $link  = $xpath->query('./a:link',  $item)->item(0)->getAttribute('href');
        $desc  = htmlspecialchars_decode($xpath->query('./a:content', $item)->item(0)->nodeValue);
        $urls  = [];

        $html = new DOMDocument;
        $html->loadHTML($desc);

        foreach ($html->getElementsByTagName('a') as $anchor) {
            if (!$anchor->hasAttribute('href')) {
                continue;
            }

            $url  = $anchor->getAttribute('href');
            $host = parse_url($url, PHP_URL_HOST);

            if ($host && false === stripos($host, 'reddit.com')) {
                $urls[] = $url;
            }
        }

        dd(App\Domain\Html\Meta::from($urls[0])->og);

        if (isset($metas['twitter:creator']) || isset($metas['twitter:site'])) {
            $by = $metas['twitter:creator']['content'] ?? $metas['twitter:site']['content'] ?? null;
        }

        ksort($metas);
        dd($metas);

        $metas = implode(',', array_keys($metas));
        dd(compact('title', 'link', 'urls', 'metas', 'by'));
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
            $user->follow();
        } catch (RuntimeException $exception) {
            App\Journal::error("[{$this->name}] $e");
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
            App\Journal::error("[{$this->name}] error with @{$user['screen_name']}: $e");
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

            App\Journal::error("[{$this->name}] $e");
            return;
        }
    }
})->describe("Mute everyone (except VIP)");
