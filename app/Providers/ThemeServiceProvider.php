<?php

namespace App\Providers;

use App\Services\BillmoraService as Billmora;
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
        $portalTheme = Billmora::getGeneral('company_portal_theme', 'default');
        $clientTheme = Billmora::getGeneral('company_client_theme', 'default');;
        $emailTheme = Billmora::getGeneral('mail_template', 'default');;

        $portalThemePath = resource_path("themes/portal/{$portalTheme}/theme.php");
        $clientThemePath = resource_path("themes/client/{$clientTheme}/theme.php");
        $emailThemePath = resource_path("themes/email/{$clientTheme}/theme.php");

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

        $emailThemeInfo = File::exists($emailThemePath) ? require $emailThemePath : [
            'name' => 'Default',
            'version' => '1.0.0',
            'author' => 'Billmora',
            'url' => 'https://billmora.com',
        ];

        View::share('portalTheme', $portalThemeInfo);
        View::share('clientTheme', $clientThemeInfo);
        View::share('emailTheme', $emailThemeInfo);

        View::addNamespace('portal', resource_path("themes/portal/{$portalTheme}"));
        View::addNamespace('client', resource_path("themes/client/{$clientTheme}"));
        View::addNamespace('email', resource_path("themes/email/{$emailTheme}"));
    }
}
