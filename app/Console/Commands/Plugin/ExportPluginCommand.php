<?php

namespace App\Console\Commands\Plugin;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\Prompts;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ExportPluginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billmora:plugin:export {name?} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a Billmora plugin to a distributable ZIP file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Billmora Plugin Exporter');
        $this->newLine();

        $name = $this->argument('name');
        if (!$name) {
            $name = Prompts\text(
                label: 'Plugin Name (e.g. MyProvider)',
                required: true
            );
        }

        $type = $this->option('type');
        $allowedTypes = ['provisioning', 'gateway', 'module', 'registrar'];
        
        if (!$type || !in_array($type, $allowedTypes)) {
            $type = Prompts\select(
                label: 'Plugin Type',
                options: [
                    'provisioning' => 'Provisioning (Server, Web Hosting, etc)',
                    'gateway' => 'Payment Gateway',
                    'module' => 'Module (Addon/Feature)',
                    'registrar' => 'Domain Registrar',
                ],
                required: true
            );
        }

        $typePluralMap = [
            'provisioning' => 'Provisionings',
            'gateway' => 'Gateways',
            'module' => 'Modules',
            'registrar' => 'Registrars',
        ];

        $typePlural = $typePluralMap[$type];
        $provider = Str::studly($name);
        
        $pluginDir = base_path("plugin/{$typePlural}/{$provider}");
        $jsonPath = $pluginDir . '/plugin.json';

        if (!File::exists($pluginDir)) {
            $this->error("Plugin directory not found at: {$pluginDir}");
            return self::FAILURE;
        }

        if (!File::exists($jsonPath)) {
            $this->error("Manifest file (plugin.json) not found at: {$jsonPath}");
            return self::FAILURE;
        }

        $manifest = json_decode(File::get($jsonPath), true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($manifest['version'])) {
            $this->error("Invalid plugin.json or version missing.");
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

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($pluginDir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file_name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = "{$provider}/" . substr($filePath, strlen($pluginDir) + 1);
                $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
            }
        }

        $zip->close();

        $this->newLine();
        $this->info("Plugin exported successfully!");
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
