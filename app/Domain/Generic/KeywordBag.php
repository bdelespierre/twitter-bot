<?php

namespace App\Domain\Generic;

use ArrayObject;

class KeywordBag extends ArrayObject
{
    public function __construct(array $keywords = [])
    {
        parent::__construct($keywords, ArrayObject::ARRAY_AS_PROPS);
    }

    public function match(array $words): float
    {
        return array_reduce($words, function($memo, $word) {
            return $memo += $this[$word] ?? 0;
        }, 0);
    }
}
