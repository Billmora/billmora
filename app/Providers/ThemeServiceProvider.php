<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        $defaultThemes = [
            'admin' => 'moraine',
            'portal' => 'moraine',
            'client' => 'moraine',
            'email' => 'moraine',
        ];

        foreach ($defaultThemes as $role => $themeName) {
            $basePath = resource_path("themes/{$role}/{$themeName}");

            $themeInfo = File::exists("{$basePath}/theme.php")
                ? require "{$basePath}/theme.php"
                : [
                    'name' => ucfirst($themeName),
                    'version' => '1.0.0',
                    'author' => 'Billmora',
                    'url' => 'https://billmora.com',
                    'assets' => "/themes/{$role}/{$themeName}",
                ];

            View::share("{$role}Theme", $themeInfo);

            View::addNamespace($role, $basePath);

            $componentPath = "{$basePath}/views/components";
            Blade::anonymousComponentPath($componentPath, $role);

            if (File::exists($componentPath)) {
                collect(File::allFiles($componentPath))->each(function ($file) use ($componentPath, $role) {
                    $relativePath = Str::of($file->getRealPath())
                        ->replace('\\', '/')
                        ->after(Str::of($componentPath)->replace('\\', '/') . '/')
                        ->before('.blade.php')
                        ->replace('/', '.');

                    $view  = "{$role}::components.{$relativePath}";
                    $alias = "{$role}.{$relativePath}";

                    Blade::component($view, $alias);
                });
            }
        }
    }
}
