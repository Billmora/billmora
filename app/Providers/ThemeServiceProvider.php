<?php

namespace App\Providers;

use App\Helpers\Config;
use App\Helpers\Theme;
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
        $theme = Theme::getActive();
        $themeName = session('theme', Config::setting('company_theme', 'Default'));
        $themePath = resource_path("themes/$themeName");

        if (File::isDirectory($themePath)) {
            View::addLocation($themePath);
        }

        View::share('theme', $theme);
    }
}
