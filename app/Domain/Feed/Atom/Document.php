<?php

namespace App\Domain\Feed\Atom;

use App\Domain\Feed\Document as XmlDocument;
use App\Domain\Feed\Item;
use App\Domain\Html\Document as HtmlDocument;
use DateTime;
use DOMXPath;

class Document extends XmlDocument
{
    protected const CACHE_KEY = "domain.feed.atom.document.";

    public function getIterator()
    {
        foreach ($this->query('/a:feed/a:entry') as $item) {
            $title  = $this->firstNodeValue('./a:title',   $item);
            $desc   = [];
            $desc[] = $this->firstNodeValue('./a:summary', $item, '');
            $desc[] = $this->firstNodeValue('./a:content', $item, '');
            $desc   = implode(' ', $desc);
            $link   = $this->query('./a:link', $item)->item(0)->getAttribute('href');
            $date   = new DateTime($this->firstNodeValue('./a:updated', $item, ''));

            yield new Item($title, $desc, $link, $date);
        }
    }

    public function count()
    {
        return $this->query('/a:feed/a:entry')->length;
    }

    public function getXPath(): DOMXPath
    {
        $xpath = parent::getXPath();
        $xpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');

        return $xpath;
    }
}
