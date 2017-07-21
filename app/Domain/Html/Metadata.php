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
        if ($key == 'og') {
            $key = 'openGraphCard';
        }

        if (method_exists($this, $method = 'get' . ucfirst($key))) {
            return $this->$key = $this->$method();
        }

        throw new UnexpectedValueException("No such key: {$key}");
    }

    public function __toString()
    {
        return (string) $this->getTwitterCard();
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

        foreach ($doc->getElementsByTagName('link') as $link) {
            unset($rel, $href);

            if ($link->hasAttribute('rel')) {
                $rel = $link->getAttribute('rel');
            }

            if ($link->hasAttribute('href')) {
                $href = $link->getAttribute('href');
            }

            if (isset($rel, $href)) {
                $this["link:{$rel}"] = compact('href');
            }
        }

        return $this;
    }

    public function getAuthor(): string
    {
        return array_get($this, 'auhtor.content')
            ?: array_get($this, 'twitter:creator.content')
            ?: array_get($this, 'twitter:site.content')
            ?: array_get($this, 'link:author.href')
            ?: array_get($this, 'article:author.content')
            ?: "";
    }

    public function getTitle(): string
    {
        return array_get($this, 'title.content')
            ?: array_get($this, 'og:title.content')
            ?: array_get($this, 'twitter:title.content')
            ?: "";
    }

    public function getDescription(): string
    {
        return array_get($this, 'description.content')
            ?: array_get($this, 'og:description.content')
            ?: array_get($this, 'twitter:description.content')
            ?: "";
    }

    public function getImage(): string
    {
        return array_get($this, 'og:image.content')
            ?: array_get($this, 'twitter:image')
            ?: array_get($this, 'twitter:image:src')
            ?: array_get($this, '')
            ?: "";
    }

    public function getUrl(): string
    {
        return array_get($this, 'link:canonical.href')
            ?: array_get($this, 'og:url.content')
            ?: array_get($this, 'al:web:url.content')
            ?: "";
    }

    public function getCard()
    {
        return new class($this)
        {
            public function __construct(Metadata $meta)
            {
                $this->author      = $meta->getAuthor();
                $this->title       = $meta->getTitle();
                $this->description = $meta->getDescription();
                $this->image       = $meta->getImage();
                $this->url         = $meta->getUrl();
            }

            public function __toString()
            {
                return (string) view('components.card', [
                    'title' => $this->title,
                    'slot'  => $this->description,
                    'image' => ['src' => $this->image, 'alt' => $this->title],
                    'links' => [['href' => $this->url, 'text' => parse_url($this->url, PHP_URL_HOST)]]
                ]);
            }
        };
    }

    public function getOpenGraphCard()
    {
        return new class($this)
        {
            /**
             * @see http://ogp.me/
             * @param Metadata $meta
             */
            public function __construct(Metadata $meta)
            {
                $this->title = array_get($this, 'og:title.content');
                $this->type  = array_get($this, 'og:type.content');
                $this->image = array_get($this, 'og:image.content');
                $this->url   = array_get($this, 'og:url.content');
            }

            public function __toString()
            {
                return (string) view('components.card', [
                    'title'     => $this->title,
                    'subtitle'  => $this->type,
                    'image'     => ['src' => $this->image, 'alt' => $this->title],
                    'links'     => [['href' => $this->url, 'text' => parse_url($this->url, PHP_URL_HOST)]]
                ]);
            }
        };
    }

    public function getTwitterCard()
    {
        return new class($this)
        {
            /**
             * @see https://dev.twitter.com/cards/markup
             * @param Metadata $meta
             */
            public function __construct(Metadata $meta)
            {
                $this->creator           = array_get($meta, 'twitter:creator.content');
                $this->creatorId         = array_get($meta, 'twitter:creator:id.content');
                $this->site              = array_get($meta, 'twitter:site.content');
                $this->title             = array_get($meta, 'twitter:title.content');
                $this->description       = array_get($meta, 'twitter:description.content');
                $this->image             = array_get($meta, 'twitter:image.content');
                $this->imageAlt          = array_get($meta, 'twitter:image:alt.content');
            }

            public function __toString()
            {
                return (string) view('components.card', [
                    'title'    => $this->title,
                    'subtitle' => $this->creator,
                    'slot'     => $this->description,
                    'image'    => ['src' => $this->image, 'alt' => $this->imageAlt ?? $this->title],
                    'links'    => [['href' => $this->url, 'text' => parse_url($this->url, PHP_URL_HOST)]],
                ]);
            }
        };
    }

    public function getAppLinkCard()
    {
        return new class($this)
        {
            public function __construct(Metadata $meta)
            {
                $this->ios = (object) [
                    'url'        => array_get($meta, 'al:ios:url.content'),
                    'appStoreId' => array_get($meta, 'al:ios:app_store_id.content'),
                    'appName'    => array_get($meta, 'al:ios:app_name.content'),
                ];

                $this->android = (object) [
                    'url'        => array_get($meta, 'al:android:url.content'),
                    'appName'    => array_get($meta, 'al:android:app_name.content'),
                    'package'    => array_get($meta, 'al:android:package.content'),
                ];

                $this->web = (object) [
                    'url'        => array_get($meta, 'al:web:url'),
                ];
            }

            public function __toString()
            {
                return (string) json_encode($this);
            }
        };
    }
}
