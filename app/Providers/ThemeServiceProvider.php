<?php

namespace App\Providers;

use Filament\Facades\Filament;
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

        View::addNamespace('portal', resource_path("themes/portal/{$portalTheme}"));
        View::addNamespace('client', resource_path("themes/client/{$clientTheme}"));
    }
}
