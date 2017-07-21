<?php

namespace App\Domain\Generic;

class Str
{
    /**
     * @see    https://stackoverflow.com/a/2934602
     *
     * @param  string $str
     * @param  string $format (or 'UTF-16BE')
     *
     * @return string
     */
    public static function decodeUnicode(string $str, string $format = 'UCS-2BE'): string
    {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', $format);
        }, $str);
    }
}
