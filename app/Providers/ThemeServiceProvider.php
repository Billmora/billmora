<?php

namespace App\Providers;

use App\Helpers\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $theme = session('theme',  Config::setting('company_theme', 'default'));

        $themePath = resource_path("themes/$theme");

        if (File::isDirectory($themePath)) {
            View::addLocation($themePath);
        }
    }
}
