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
| Bot Follow
|--------------------------------------------------------------------------
|
| Automatically follow-back everyone that follows me and mute them so they
| don't pollute my timeline
|
*/

Artisan::command('bot:follow', function () {
    App\Journal::notice("[bot:follow] started");

    $followers = Twitter::getFollowersIds(['format' => 'array'])['ids'];
    $following = Twitter::getFriendsIds(['format' => 'array'])['ids'];

    if (!$ids = array_values(array_diff($followers, $following))) {
        App\Journal::info("[bot:follow] no one to follow");
        return;
    }

    shuffle($ids);

    while ((count($following) <= count($followers)) && ($id = array_pop($ids))) {
        try {
            App\Journal::info("[bot:follow] following {$id}");
            Twitter::postFollow(['user_id' => $following[] = $id]);
            Twitter::muteUser(['user_id' => $id]);
        } catch (RuntimeException $exception) {
            App\Journal::error("[bot:follow] " . $exception->getMessage(), compact('exception'));
            continue;
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
    App\Journal::notice("[bot:unfollow] started");

    $following = Twitter::getFriendsIds(['format' => 'array'])['ids'];
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
        App\Journal::info("[bot:unfollow] no one to unfollow");
        return;
    }

    foreach ($unfollow as $user) {
        App\Journal::info("[bot:unfollow] unfollowing @{$user['screen_name']}");
        Twitter::postUnfollow(['user_id' => $user['id']]);
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
    App\Journal::notice("[bot:mute] started");

    foreach (App\TwitterUser::unmuted()->get() as $user) {
        if (in_array($user->screen_name, $vip)) {
            continue;
        }

        App\Journal::info("[bot:mute] muting @{$user->screen_name}");
        Twitter::muteUser(['user_id' => $user->id]);
    }
});

/*
|--------------------------------------------------------------------------
| Cache Warmup
|--------------------------------------------------------------------------
|
*/

Artisan::command('cache:warmup {--throttle}', function () {
    do {
        $following = Twitter::getFriends(['format' => 'array'] + compact('cursor'));
        list('users' => $users, 'next_cursor_str' => $cursor) = $following;

        foreach ($users as $data) {
            App\Journal::info("[cache:warmup] @{$data['id']}");
            $user = App\TwitterUser::updateOrCreate(
                array_only($data, ['id', 'screen_name']),
                compact('data')
            );

            $user->mask |= App\TwitterUser::FOLLOWING;
            $user->save();
        }

        if ($this->options('throttle')) {
            App\Journal::info("[cache:warmup] sleep before cursor {$cursor}");
            sleep(65); // seconds
        }
    } while ($cursor);
})->describe("Warms up the cache");

/*
|--------------------------------------------------------------------------
| Logs Purge
|--------------------------------------------------------------------------
|
*/

Artisan::command('logs:purge', function () {
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
Artisan::command('reddit:import {subreddit}', function ($subreddit) {
    App\Journal::notice("[reddit:import] started", compact('subreddit'));

    $url = "https://www.reddit.com/r/{$subreddit}/hot/.rss?sort=hot";
    App\Journal::debug("[reddit:import] reading from {$url}");

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
