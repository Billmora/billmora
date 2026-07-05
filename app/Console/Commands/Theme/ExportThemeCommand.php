<?php

namespace App\Console\Commands\Theme;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\Prompts;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ExportThemeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billmora:theme:export {name?} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a Billmora theme to a distributable ZIP file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Billmora Theme Exporter');
        $this->newLine();

        $name = $this->argument('name');
        if (!$name) {
            $name = Prompts\text(
                label: 'Theme Name (e.g. MyTheme)',
                required: true
            );
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
        
        $resourceDir = resource_path("themes/{$type}/{$provider}");
        $publicDir = public_path("themes/{$type}/{$provider}");
        $jsonPath = $resourceDir . '/theme.json';

        if (!File::exists($resourceDir)) {
            $this->error("Theme resource directory not found at: {$resourceDir}");
            return self::FAILURE;
        }

        if (!File::exists($jsonPath)) {
            $this->error("Manifest file (theme.json) not found at: {$jsonPath}");
            return self::FAILURE;
        }

        $manifest = json_decode(File::get($jsonPath), true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($manifest['version'])) {
            $this->error("Invalid theme.json or version missing.");
            return self::FAILURE;
        }

        $version = $manifest['version'];
        $zipFileName = strtolower("{$provider}-{$type}-{$version}.zip");
        $exportPath = storage_path("app/exports");
        
        File::ensureDirectoryExists($exportPath);
        $zipPath = "{$exportPath}/{$zipFileName}";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Cannot create zip file at: {$zipPath}");
            return self::FAILURE;
        }

        // Add theme.json
        $zip->addFile($jsonPath, "{$provider}/theme.json");

        // Add config.blade.php if exists
        $configPath = $resourceDir . '/config.blade.php';
        if (File::exists($configPath)) {
            $zip->addFile($configPath, "{$provider}/config.blade.php");
        }

        // Add views
        $viewsDir = $resourceDir . '/views';
        if (File::exists($viewsDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($viewsDir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file_name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = "{$provider}/views/" . substr($filePath, strlen($viewsDir) + 1);
                    $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
                }
            }
        }

        // Add assets from public folder
        if (File::exists($publicDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($publicDir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file_name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = "{$provider}/assets/" . substr($filePath, strlen($publicDir) + 1);
                    $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
                }
            }
        }

        $zip->close();

        $this->newLine();
        $this->info("Theme exported successfully!");
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $provider],
                ['Type', $type],
                ['Version', $version],
                ['Output Path', $zipPath],
            ]
        );

        return self::SUCCESS;
    }
}
