<?php

namespace App\Domain\Feed;

use App\Domain\Html\Document;
use DateTime;
use UnexpectedValueException;

class Item
{
    protected $title;

    protected $description;

    protected $link;

    protected $date;

    public function __construct(string $title, string $description, string $link, DateTime $date = null)
    {
        $this->title       = $title;
        $this->description = $description;
        $this->link        = $link;
        $this->date        = $date ?: new DateTime;
    }

    public function __get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        if (method_exists($this, $method = 'get' . ucfirst($key))) {
            return $this->$key = $this->$method();
        }

        throw new UnexpectedValueException("No such key: {$key}");
    }

    public function getDocument()
    {
        return Document::fromUrl($this->link);
    }
}