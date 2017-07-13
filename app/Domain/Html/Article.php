<?php

namespace App\Domain\Html;

use DOMDocument;
use DOMNode;
use danielme85\ForceUTF8\Encoding;

class Article
{
    public function __construct(DOMNode $node, DOMDocument $doc)
    {
        $this->node = $node;
        $this->doc = $doc;
    }

    public function __get($key)
    {
        if (method_exists($this, $method = 'get' . ucfirst($key))) {
            return $this->$key = $this->$method();
        }

        throw new UnexpectedValueException("No such key: {$key}");
    }

    public function __toString()
    {
        return $this->getRawContent();
    }

    public function getContent(): string
    {
        return (string) $this->doc->saveHTML($this->node);
    }

    public function getRawContent(): string
    {
        $content = $this->getContent();
        $content = self::remove($content, ['script', 'iframe', 'embeed', 'style']);
        $content = strip_tags($content);
        $content = preg_replace("/\s{2,}/", " ", $content);
        $content = str_replace(["\n", "&nbsp;"], " ", $content);
        $content = str_replace(['.', ',', ':', ';'], '', $content);
        $content = trim($content);

        return $content;
    }

    public function getWords()
    {
        $words = [];
        foreach (explode(' ', strtolower($this->getRawContent())) as $word) {
            if (strlen($word) < 2) {
                continue;
            }

            if (preg_match('/^[0-9]$/', $word)) {
                continue;
            }

            if (substr($word, -2) == "'s") {
                $word = substr($word, 0, -2);
            }

            if (!preg_match('/^\w+$/', $word)) {
                continue;
            }

            if (!isset($words[$word])) {
                $words[$word] = 0;
            }

            $words[$word]++;
        }

        arsort($words);
        return $words;
    }

    public function getTitle()
    {
        //
    }

    public function getParagraphs()
    {
        //
    }

    public function getKeywords()
    {
        // ['keyword' => (float)strength]
    }

    protected static function remove(string $html, array $tags): string
    {
        $doc = Document::fromHtml($html);

        foreach ($tags as $tag) {
            foreach($doc->getElementsByTagName($tag) as $item) {
                $remove[] = $item;
            }

            foreach ($remove ?? [] as $item) {
                if ($item->parentNode) {
                    $item->parentNode->removeChild($item);
                }
            }
        }

        return $doc->saveHTML();
    }
}
