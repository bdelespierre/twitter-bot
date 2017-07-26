<?php

namespace App\Console\Commands\Twitter;

use App\Models\Buffer\Item;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Thujohn\Twitter\Facades\Twitter;
use RuntimeException;

class Tweet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:tweet {--dry} {--item=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tweet something';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $item  = $this->hasOption('item')
            ? Item::findOrFail($this->option('item'))
            : Item::pickOneAtRandom();

        $url   = self::shorten($item->url);
        $max   = 140 - strlen($url) - 1;
        $title = $item->metadata->title;

        // truncate long titles
        if (strlen($title) > $max) {
            $title = substr($title, 0, $max - 1) . "…";
        }

        self::addHashtags($title, $max);
        self::addEmojis($title, $max);

        $status = "{$title} {$url}";

        if ($this->hasOption('dry')) {
            return $this->line($status);
        }

        // Ok, let's tweet that
        $result = Twitter::postTweet(compact('status'));

        if (!empty($result->id)) {
            $item->delete();
        }
    }

    protected static function shorten($url)
    {
        $res  = (new Client)->post('https://www.googleapis.com/urlshortener/v1/url', [
            'query' => ['key' => env('GOOGLE_URLSHORTENER_API_KEY')],
            'json'  => ['longUrl' => $url]
        ]);

        if (!$data = json_decode((string) $res->getBody(), true)) {
            throw new RuntimeException("Unable to shorten: $url");
        }

        // removes the 'https://' prefix
        return substr($data['id'], 8); // length = 13
    }

    protected static function addHashtags(& $title, $max)
    {
        if (!$hashtags = config('twitter.hashtags')) {
            return;
        }

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
    }

    protected static function addEmojis(& $title, $max)
    {
        if (!$icons = config('twitter.emojis', [])) {
            return;
        }

        // if there is room, add icons
        if ($icons && strlen($title) -2 < $max) {
            $title .= " " . $icons[array_rand($icons)];

            for ($i=1; $i<3; $i++) {
                $new = $title . $icons[array_rand($icons)];

                if (strlen($new) > $max) {
                    break;
                }

                $title = $new;
            }
        }
    }
}
