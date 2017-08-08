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
    $this->call('twitter:import', ['relationship' => 'friends']);
    $this->call('twitter:import', ['relationship' => 'followers']);
})->describe("Warms up the cache");

Artisan::command('twitter:sync', function () {
    $this->call('cache:warmup');
    $this->call('twitter:follow');
    $this->call('twitter:unfollow');
    $this->call('twitter:mute');
});

/*
|--------------------------------------------------------------------------
| Import
|--------------------------------------------------------------------------
|
*/

Artisan::command('import', function () {
    $subs = [
        'learnprogramming',
        'programming'
    ];

    foreach ($subs as $subreddit) {
        $this->call('import:reddit', compact('subreddit'));
    }

    $feeds = [
        'rss' => [
            'https://hackernoon.com/feed',
            'https://news.ycombinator.com/rss',
            'http://www.prototypr.io/feed/',
            'https://medium.freecodecamp.org/feed',
            'https://uxplanet.org/feed',
            'https://m.signalvnoise.com/feed',
        ]
    ];

    foreach ($feeds as $type => $urls) {
        foreach ($urls as $url) {
            $this->call('import:feed', ['--type' => $type, 'url' => $url]);
        }
    }

    $this->call('update:scores');
});

Artisan::command('import:reddit {subreddit}', function ($subreddit) {
    $this->info('Import from subreddit: ' . $subreddit);

    $items = 0;
    foreach (App\Domain\Feed\Reddit\Document::fromSubreddit($subreddit) as $item) {
        try {
            $this->comment("{$item->title} ({$item->link})");
            App\Models\Pool\Item::fromFeed($item);
            $items++;
        } catch (Exception $e) {
            //
        }
    }

    $this->info(sprintf('%d items imported', $items));
});

Artisan::command('import:feed {--type=rss} {url}', function ($url) {
    $this->info('Import from feed: ' . $url);

    switch (strtolower($this->option('type'))) {
        case 'rss':
            $feed = App\Domain\Feed\Rss\Document::fromUrl($url);
            break;

        case 'atom':
            $feed = App\Domain\Feed\Atom\Document::fromUrl($url);
            break;

        default:
            throw new InvalidArgumentException("Invalid type: " . $this->option('type'));
    }

    $items = 0;
    foreach ($feed as $item) {
        try {
            $this->comment("{$item->title} ({$item->link})");
            App\Models\Pool\Item::fromFeed($item);
            $items++;
        } catch (Exception $e) {
            //
        }
    }

    $this->info(sprintf('%d items imported', $items));
});

Artisan::command('update:scores', function() {
    foreach (App\Models\Pool\Item::all() as $item) {
        $item->updateScore(config('twitter.hashtags', []))->save();
    }
});
