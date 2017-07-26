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
    $this->call('twitter:import', ['relationship' => 'friends']);
    $this->call('twitter:import', ['relationship' => 'followers']);
    $this->call('twitter:follow');
    $this->call('twitter:unfollow');
    $this->call('twitter:mute');
    //$this->call('twitter:purge');
});

/*
|--------------------------------------------------------------------------
| Import
|--------------------------------------------------------------------------
|
*/

/**
 * learnprogramming
 * programming
 */
Artisan::command('import:reddit {subreddit}', function ($subreddit) {
    foreach (App\Domain\Feed\Reddit\Document::fromSubreddit($subreddit) as $item) {
        dump($item);
    }
});

/**
 * https://hackernoon.com/feed
 * https://news.ycombinator.com/rss
 */
Artisan::command('import:feed {--type=rss} {url}', function ($url) {
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

    foreach ($feed as $item) {
        dump($item);
    }
});
