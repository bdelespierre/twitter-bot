<?php

namespace App\Providers;

use DetectLanguage\DetectLanguage;
use Illuminate\Support\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        DetectLanguage::setApiKey(env('DETECT_LANGUAGE_API_KEY'));
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
