<?php

namespace App\Support\Facades;

use DetectLanguage\DetectLanguage;
use Illuminate\Support\Facades\Facade;

class Language
{
    protected static function getFacadeAccessor()
    {
        return DetectLanguage::class;
    }

    public static function detect($text)
    {
        return DetectLanguage::detect($text);
    }

    public static function simpleDetect($text)
    {
        return DetectLanguage::simpleDetect($text);
    }
}
