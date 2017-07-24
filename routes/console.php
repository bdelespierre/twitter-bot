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
    $url = "https://www.reddit.com/r/{$subreddit}/hot/.rss?sort=hot";
    $this->comment("reading from {$url}");

    foreach (App\Domain\Atom\Document::fromUrl($url)->items as $item) {
        $url = array_first($item->urls, function($url) {
            return parse_url($url, PHP_URL_HOST) != 'www.reddit.com';
        });

        if (!$articles = App\Domain\Html\Document::fromUrl($url)->articles) {
            $this->comment("ignoring {$url} : no article found");
            continue;
        }

        $this->comment("" . count($articles) . " found for {$url}");

        // ...
    }
});

/*
|--------------------------------------------------------------------------
| Bot
|--------------------------------------------------------------------------
|
*/

Artisan::command('bot:tweet', function () {
    $item = App\Models\BufferItem::orderBy(DB::raw('random()'))->first();
    $res  = (new GuzzleHttp\Client)->post('https://www.googleapis.com/urlshortener/v1/url', [
        'query' => ['key' => env('GOOGLE_URLSHORTENER_API_KEY')],
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
        'android', 'webapp', 'ui', 'linux'
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