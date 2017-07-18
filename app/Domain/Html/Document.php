<?php

namespace App\Domain\Html;

use Carbon\Carbon;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXpath;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use UnexpectedValueException;

class Document extends DOMDocument
{
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

    public static function fromUrl(string $url, bool $cache = true)
    {
        if ($cache && Cache::has($key = "domain.html.document." . str_slug($url))) {
            $html = Cache::get($key);
        } else {
            $response = (new Client())->get($url);
            $html = (string) $response->getBody();

            if ($cache && $html) {
                $expireAt = Carbon::now()->addHours(8);
                Cache::put($key, $html, $expireAt);
            }
        }

        $doc = new self;
        $doc->loadHTML($html);

        return $doc;
    }

    public static function fromHTML(string $html)
    {
        $doc = new self;
        $doc->loadHTML($html);

        return $doc;
    }

    public function getContent(): string
    {
        return $this->saveHTML();
    }

    public function getMetadata(): Metadata
    {
        return new Metadata($this);
    }

    public function getArticles(): array
    {
        foreach ($this->getElementsByTagName('article') as $article) {
            $articles[] = new Article($article, $this);
        }

        return $articles ?? [];
    }

    public function getArticle(): Article
    {
        return $this->getArticles()[0] ?? null;
    }

    public function getXPath(): DOMXpath
    {
        return new DOMXpath($this);
    }

    public function query(string $expression, DOMNode $context = null): DOMNodeList
    {
        return $this->xpath->query($expression, $context);
    }
}
