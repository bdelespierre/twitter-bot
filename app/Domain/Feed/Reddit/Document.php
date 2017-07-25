<?php

namespace App\Domain\Feed\Reddit;

use App\Domain\Feed\Atom\Document as AtomDocument;
use App\Domain\Feed\Item;
use App\Domain\Html\Document as HtmlDocument;
use GuzzleHttp\Client;

class Document extends AtomDocument
{
    protected static function getClient(): Client
    {
        return new Client([
            'headers' => [
                'User-Agent' => str_slug(config('app.name')) . ' ' . config('app.version', '1.0'),
            ],
        ]);
    }

    public static function fromSubreddit($subreddit, string $sort = "hot")
    {
        return static::fromUrl("https://www.reddit.com/r/{$subreddit}/hot/.rss?sort={$sort}");
    }

    public function getIterator()
    {
        foreach (parent::getIterator() as $item) {
            $link  = $item->link;
            $desc  = htmlspecialchars_decode($item->description);
            $html  = HtmlDocument::fromHtml($desc);

            foreach ($html->getElementsByTagName('a') as $anchor) {
                if ($anchor->hasAttribute('href')) {
                    $urls[] = $anchor->getAttribute('href');
                }
            }

            if (!empty($urls)) {
                $link = array_first($urls, function($url) {
                    return parse_url($url, PHP_URL_HOST) != 'www.reddit.com';
                }, $link);
            }

            yield new Item($item->title, $desc, $link, $item->date);
        }
    }
}
