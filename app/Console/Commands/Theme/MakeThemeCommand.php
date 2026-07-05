<?php

namespace App\Console\Commands\Theme;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\Prompts;

class MakeThemeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billmora:theme:make {name?} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Billmora theme';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Billmora Theme Creator');
        $this->newLine();

        $name = $this->argument('name');
        if (!$name) {
            $name = Prompts\text(
                label: 'Theme Name (e.g. MyTheme)',
                required: true,
                validate: fn ($value) => preg_match('/^[a-zA-Z0-9_\-\s]+$/', $value) ? null : 'Name can only contain alphanumeric characters, spaces, dashes, and underscores.'
            );
        } else {
            if (!preg_match('/^[a-zA-Z0-9_\-\s]+$/', $name)) {
                $this->error('Name can only contain alphanumeric characters, spaces, dashes, and underscores.');
                return self::FAILURE;
            }
        }

        $type = $this->option('type');
        $allowedTypes = ['client', 'admin', 'portal', 'email', 'invoice'];
        
        if (!$type || !in_array($type, $allowedTypes)) {
            $type = Prompts\select(
                label: 'Theme Type',
                options: [
                    'client' => 'Client Area',
                    'admin' => 'Admin Panel',
                    'portal' => 'Portal',
                    'email' => 'Email Templates',
                    'invoice' => 'Invoice Templates',
                ],
                default: 'client',
                required: true
            );
        }

        $provider = Str::slug($name);
        
        $themeDir = resource_path("themes/{$type}/{$provider}");

        if (File::exists($themeDir)) {
            $this->error("Theme directory already exists at: {$themeDir}");
            return self::FAILURE;
        }

        File::ensureDirectoryExists($themeDir);

        $themeJson = [
            'provider' => $provider,
            'name' => $name,
            'type' => $type,
            'version' => '1.0.0',
            'author' => 'Your Name',
            'url' => 'https://yourwebsite.com',
            'assets' => "/themes/{$type}/{$provider}",
            'preview' => '',
        ];
        File::put($themeDir . '/theme.json', json_encode($themeJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        File::ensureDirectoryExists($themeDir . '/css');
        File::put($themeDir . '/css/app.css', "/* {$name} Theme Styles */\n");

        File::ensureDirectoryExists($themeDir . '/js');
        File::put($themeDir . '/js/app.js', "// {$name} Theme Scripts\n");

        $defaultViewsDir = resource_path("themes/{$type}/moraine/views");

        if (File::exists($defaultViewsDir)) {
            File::copyDirectory($defaultViewsDir, $themeDir . '/views');
        } else {
            File::ensureDirectoryExists($themeDir . '/views');
            File::put($themeDir . '/views/.gitkeep', '');
        }

        $viteConfig = <<<JS
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/themes/{$type}/{$provider}/css/app.css',
                'resources/themes/{$type}/{$provider}/js/app.js',
            ],
            publicDirectory: '../../../public/themes/{$type}/{$provider}',
            buildDirectory: 'build',
            refresh: true,
        }),
    ],
});
JS;
        File::put($themeDir . '/vite.config.js', $viteConfig);

        $this->newLine();
        $this->info("Theme has been created successfully!");
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Provider (Slug)', $provider],
                ['Type', $type],
                ['Path', "resources/themes/{$type}/{$provider}"],
            ]
        );

        return self::SUCCESS;
    }
}
