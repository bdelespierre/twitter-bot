<?php

/*
|--------------------------------------------------------------------------
| Very Important People
|--------------------------------------------------------------------------
|
| Never mute, unfollow or block these people no matter if they follow me or
| not: I want them on my timeline and the bot should not handle them.
|
*/

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
| Cache Warmup
|--------------------------------------------------------------------------
|
*/

Artisan::command('cache:warmup', function () {
    $this->call('twitter:import', ['relationship' => 'friends']);
    $this->call('twitter:import', ['relationship' => 'followers']);
})->describe("Warms up the cache");

/*
|--------------------------------------------------------------------------
| Twitter Import
|--------------------------------------------------------------------------
|
*/

Artisan::command('twitter:import {relationship} {--throttle=60} {--cursor=}', function () {
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
        $following = Twitter::{'get'.ucfirst($relationship)}(['format' => 'array'] + compact('cursor'));
        list('users' => $users, 'next_cursor_str' => $cursor) = $following;

        foreach ($users as $data) {
            App\Journal::info("{$cmd} @{$data['screen_name']} #{$data['id']}");

            $user = App\Models\Twitter\User::updateOrCreate(
                ['id' => $data['id']],
                ['screen_name' => $data['screen_name']] + compact('data')
            );

            $user->{substr($relationship, -1)} = true; // 'friend' or 'follower'
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
})->describe("Imports the friends or the follower of current Twitter account");

/*
|--------------------------------------------------------------------------
| Bot Follow
|--------------------------------------------------------------------------
|
| Automatically follow-back everyone that follows me and mute them so they
| don't pollute my timeline
|
*/

Artisan::command('bot:follow', function () {
    App\Journal::notice("[{$this->name}] started");

    foreach (App\Models\Twitter\User::fans()->get() as $user) {
        try {
            App\Journal::info("[{$this->name}] following {$id}");
            $user->follow();
        } catch (RuntimeException $exception) {
            App\Journal::error("[{$this->name}] " . $exception->getMessage(), compact('exception'));
        }
    }
})->describe('Automatically follow back fans');

/*
|--------------------------------------------------------------------------
| Bot Unfollow
|--------------------------------------------------------------------------
|
| Unfollow everyone that no longer follow me (except VIP)
|
*/

Artisan::command('bot:unfollow', function () use ($vip) {
    App\Journal::notice("[{$this->name}] started");

    $following = array_pluck(App\Models\Twitter\User::friends()->exceptVip()->get(), 'id');
    $unfollow  = [];

    while ($bulk = array_slice($following, 0, 100)) {
        foreach (Twitter::getFriendshipsLookup(['format' => 'array', 'user_id' => $bulk]) as $user) {
            if (!in_array('followed_by', $user['connections']) && !in_array($user['screen_name'], $vip)) {
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
            App\Journal::error("[{$this->name}] error with @{$user['screen_name']}", ['exception' => (string) $e]);
            continue;
        }
    }
})->describe('Automatically unfollow people that don\'t follow me');

/*
|--------------------------------------------------------------------------
| Bot Silence
|--------------------------------------------------------------------------
|
| Mute everyone (except VIP)
|
*/

Artisan::command('bot:mute', function () use ($vip) {
    App\Journal::notice("[{$this->name}] started");

    foreach (App\Models\Twitter\User::exceptVip()->exceptMuted()->get() as $user) {
        App\Journal::info("[{$this->name}] muting @{$user->screen_name}");
        $user->mute();
    }
});

/*
|--------------------------------------------------------------------------
| Logs Purge
|--------------------------------------------------------------------------
|
*/

Artisan::command('purge:logs', function () {
    App\Journal::notice("[{$this->name}] started");
    App\Journal::where('date', '>=', Carbon\Carbon::now()->subDays(3))->delete();
});

/*
|--------------------------------------------------------------------------
| Reddit Import
|--------------------------------------------------------------------------
|
*/

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

        $article = new DOMDocument;
        $article->loadHTMLFile($urls[0]);

        foreach ($article->getElementsByTagName('meta') as $meta) {
            unset($name, $property, $content);

            if ($meta->hasAttribute('name')) {
                $name = $meta->getAttribute('name');
            }

            if ($meta->hasAttribute('property')) {
                $property = $meta->getAttribute('property');
            }

            if ($meta->hasAttribute('content')) {
                $content = $meta->getAttribute('content');
            }

            if ($meta = compact('name', 'property', 'content')) {
                $key = $name ?? $property ?? uniqid('generic:');
                $metas[$key] = $meta;
            }
        }

        if (isset($meta['twitter:creator']) || isset($meta['twitter:site'])) {
            $by = $meta['twitter:creator'] ?? $meta['twitter:site'];
        }

        dd(compact('title', 'link', 'urls', 'metas', 'by'));
    }
});
