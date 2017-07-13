<?php

namespace App\Domain\Atom;

use Carbon\Carbon;
use DOMDocument;
use DOMXpath;
use DOMNodeList;
use DOMNode;
use Illuminate\Support\Facades\Cache;
use UnexpectedValueException;
use App\Domain\Html\Document as HtmlDocument;

class Document extends DOMDocument
{
    public function __get($key)
    {
        if (method_exists($this, $method = 'get' . ucfirst($key))) {
            return $this->$key = $this->$method();
        }

        throw new UnexpectedValueException("No such key: {$key}");
    }

    public static function fromUrl(string $url, bool $cache = true): self
    {
        if ($cache && Cache::has($key = "domain.atom.document." . str_slug($url))) {
            $xml = Cache::get($key);
        } else {
            $xml = file_get_contents($url);

            if ($cache) {
                $expireAt = Carbon::now()->addMinutes(15);
                Cache::put($key, $xml, $expireAt);
            }
        }

        $doc = new self;
        $doc->loadXML($xml);

        return $doc;
    }

    public function getXPath(): DOMXPath
    {
        $xpath = new DOMXPath($this);
        $xpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');

        return $xpath;
    }

    public function query(string $expression, DOMNode $context = null): DOMNodeList
    {
        return $this->xpath->query($expression, $context);
    }

    public function getItems(): array
    {
        foreach ($this->query('/a:feed/a:entry') as $item) {
            $title = $this->query('./a:title', $item)->item(0)->nodeValue;
            $link  = $this->query('./a:link',  $item)->item(0)->getAttribute('href');
            $desc  = htmlspecialchars_decode($this->query('./a:content', $item)->item(0)->nodeValue);
            $html  = HtmlDocument::fromHtml($desc);
            $urls  = [];

            foreach ($html->getElementsByTagName('a') as $anchor) {
                if ($anchor->hasAttribute('href')) {
                    $urls[] = $anchor->getAttribute('href');
                }
            }

            $html    = (string) $html;
            $items[] = (object) compact('title', 'link', 'html', 'urls');
        }

        return $items ?? [];
    }
}
