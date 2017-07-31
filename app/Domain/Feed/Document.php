<?php

namespace App\Domain\Feed;

use Carbon\Carbon;
use Countable;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use IteratorAggregate;
use UnexpectedValueException;

abstract class Document extends DOMDocument implements IteratorAggregate, Countable
{
    protected const CACHE_KEY = "domain.feed.document.";

    public function __toString()
    {
        return (string) $this->getContent();
    }

    public function __get($key)
    {
        if (method_exists($this, $method = 'get' . ucfirst($key))) {
            return $this->$key = $this->$method();
        }

        throw new UnexpectedValueException("No such key: {$key}");
    }

    protected static function getClient(): Client
    {
        return new Client;
    }

    public static function fromUrl(string $url)
    {
        $xml = Cache::remember(static::CACHE_KEY.str_slug($url), Carbon::now()->addHours(8), function () use ($url) {
            $response = static::getClient()->get($url);
            return (string) $response->getBody();
        });

        return static::fromXml($xml);
    }

    public static function fromXml($xml)
    {
        $doc = new static;
        $doc->loadXML($xml);

        return $doc;
    }

    public function getXPath(): DOMXPath
    {
        return new DOMXPath($this);
    }

    public function query(string $expression, DOMNode $context = null): DOMNodeList
    {
        return $this->xpath->query($expression, $context);
    }

    protected function firstNodeValue(string $query, DOMNode $context = null, $default = null)
    {
        $list = $this->query($query, $context);
        $node = $list->item(0);

        return $node ? $node->nodeValue : $default;
    }
}
