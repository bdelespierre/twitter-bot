<?php

namespace App\Domain\Html;

use Carbon\Carbon;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXpath;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use UnexpectedValueException;

class Document extends DOMDocument
{
    protected const CACHE_KEY = "domain.html.document.";

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

    public static function fromUrl(string $url)
    {
        $html = Cache::remember(static::CACHE_KEY.str_slug($url), Carbon::now()->addHours(8), function () use ($url) {
            $response = (new Client)->get($url);
            return (string) $response->getBody();
        });

        return static::fromHtml($html);
    }

    public static function fromHtml(string $html)
    {
        $doc = new static;
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

    public function getArticle(): ?Article
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
