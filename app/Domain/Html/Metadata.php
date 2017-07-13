<?php

namespace App\Domain\Html;

use ArrayObject;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\Cache;
use stdClass;
use UnexpectedValueException;

class Metadata extends ArrayObject
{
    protected $doc;

    public function __construct(DOMDocument $doc)
    {
        $this->setDocument($doc);
    }

    public function __get($key)
    {
        switch ($key) {
            case 'og':
            case 'openGraph':
                return $this->getOpenGraph();

            default:
                throw new UnexpectedValueException("No such key: {$key}");
        }
    }

    public static function fromUrl(string $url): self
    {
        return new self(Document::fromUrl($url));
    }

    public function setDocument(DOMDocument $doc): self
    {
        $this->doc = $doc;

        foreach ($doc->getElementsByTagName('meta') as $meta) {
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
                $this[$name ?? $property ?? uniqid('generic:')] = $meta;
            }
        }

        return $this;
    }

    public function getOpenGraph(): stdClass
    {
        return (object) [
            'title' => array_get($this, 'og:title.content'),
            'type'  => array_get($this, 'og:type.content'),
            'image' => array_get($this, 'og:image.content'),
            'url'   => array_get($this, 'og:url.content'),
        ];
    }

    public function getTwitterAuthor(): string
    {
        return array_get($this, 'twitter:creator.content')
            ?: array_get($this, 'twitter:site.content');
    }
}
