<?php

namespace App\Domain\Html;

use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\Cache;
use stdClass;
use UnexpectedValueException;

class Meta
{
    protected $data;

    public function __construct(string $content)
    {
        $this->setContent($content);
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

    public static function from(string $url): self
    {
        if (Cache::has($key = "document.url." . str_slug($url))) {
            return new self(Cache::get($key));
        }

        $contents  = file_get_contents($url);
        Cache::put($key, $contents, Carbon::now()->addHours(8));
        return new self($contents);
    }

    public function setContent(string $content): self
    {
        $doc = new DOMDocument;
        $doc->loadHTML($content);

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
                $this->data[$name ?? $property ?? uniqid('generic:')] = $meta;
            }
        }

        return $this;
    }

    public function getOpenGraph(): stdClass
    {
        return (object) [
            'title' => array_get($this->data, 'og:title.content'),
            'type'  => array_get($this->data, 'og:type.content'),
            'image' => array_get($this->data, 'og:image.content'),
            'url'   => array_get($this->data, 'og:url.content'),
        ];
    }
}