<?php

namespace App\Helpers;

use App\Helpers\Config;

class Theme
{
    public static function getActive()
    {
        $theme = session('theme', Config::setting('company_theme', 'Default'));

        $themePath = resource_path("themes/$theme/theme.php");

        if (file_exists($themePath)) {
            return require $themePath;
        }

        return [
            'name' => 'Default',
            'version' => '1.0.0',
            'author' => 'Billmora',
            'url' => 'https://billmora.com',
            'assets' => '/assets/themes/default',
        ];
    }
}
