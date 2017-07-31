<?php

namespace App\Domain\Feed\Rss;

use App\Domain\Feed\Document as XmlDocument;
use App\Domain\Feed\Item;
use DateTime;

class Document extends XmlDocument
{
    protected const CACHE_KEY = "domain.feed.rss.document.";

    public function getIterator()
    {
        foreach ($this->query('/rss/channel/item') as $item) {
            yield new Item(
                $this->firstNodeValue('./title',       $item),
                $this->firstNodeValue('./description', $item, ''),
                $this->firstNodeValue('./link',        $item),
                new DateTime($this->firstNodeValue('./pubDate', $item, ''))
            );
        }
    }

    public function count()
    {
        return $this->query('/rss/channel/item')->length;
    }
}
