<?php

use Illuminate\Foundation\Inspiring;


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
            Twitter::postFollow(['user_id' => $following[] = $id]);
            Twitter::muteUser(['user_id' => $id]);
        } catch (RuntimeException $e) {
            continue;
        }
    }
})->describe('Automatically follow back fans');

Artisan::command('bot:unfollow', function () use ($vip) {
    $this->info('Running bot:unfollow...');

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

    foreach ($unfollow as $user) {
        $this->info("Unfollowing @{$user['screen_name']}");
        Twitter::postUnfollow(['user_id' => $user['id']]);
    }
})->describe('Automatically unfollow people that don\'t follow me');

Artisan::command('bot:silence', function () use ($vip) {
    $this->info('Running bot:silence...');

    $muted = Twitter::mutedUserIds(['format' => 'array'])['ids'];

    do {
        $following = Twitter::getFriends(['format' => 'array'] + compact('cursor'));
        list('users' => $users, 'next_cursor_str' => $cursor) = $following;

        foreach ($users as $user) {
            if (in_array($user['screen_name'], $vip) || in_array($user['id'], $muted)) {
                continue;
            }

            $this->info("Muting @{$user['screen_name']}");
            Twitter::muteUser(['user_id' => $user['id']]);
        }
    } while ($cursor);
});

/**
 * learnprogramming
 * programming
 */
Artisan::command('reddit:import {subreddit}', function ($subreddit) {
    $url = "https://www.reddit.com/r/{$subreddit}/hot/.rss?sort=hot";

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
