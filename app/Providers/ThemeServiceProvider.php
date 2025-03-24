<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot(): void
    {
        $portalTheme = 'default';
        $clientTheme = 'default';

        $portalThemePath = resource_path("themes/portal/{$portalTheme}/theme.php");
        $clientThemePath = resource_path("themes/client/{$clientTheme}/theme.php");

        $portalThemeInfo = File::exists($portalThemePath) ? require $portalThemePath : [
            'name' => 'Default',
            'version' => '1.0.0',
            'author' => 'Billmora',
            'url' => 'https://billmora.com',
            'assets' => "/assets/themes/portal/{$portalTheme}",
        ];

        $clientThemeInfo = File::exists($clientThemePath) ? require $clientThemePath : [
            'name' => 'Default',
            'version' => '1.0.0',
            'author' => 'Billmora',
            'url' => 'https://billmora.com',
            'assets' => "/assets/themes/client/{$clientTheme}",
        ];

        View::share('portalTheme', $portalThemeInfo);
        View::share('clientTheme', $clientThemeInfo);

        View::addNamespace('portal', resource_path("themes/portal/{$portalTheme}"));
        View::addNamespace('client', resource_path("themes/client/{$clientTheme}"));
    }
}
