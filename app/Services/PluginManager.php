<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginManager
{
    /**
     * Get available plugin providers with manifests and configuration schemas.
     *
     * @param string $type
     * @return array<int, array<string, mixed>>
     */
    public function getAvailableProviders(string $type): array
    {
        $folderName = Str::plural(ucfirst($type));
        $path = base_path("plugin/{$folderName}");

        if (!File::exists($path)) {
            return [];
        }

        $providers = [];
        $directories = File::directories($path);

        foreach ($directories as $dir) {
            $jsonPath = $dir . '/plugin.json';
            
            if (File::exists($jsonPath)) {
                $manifest = json_decode(file_get_contents($jsonPath), true);
                
                if (($manifest['type'] ?? '') === $type) {
                    $providerName = $manifest['provider'];
                    $className = "Plugins\\{$folderName}\\{$providerName}\\{$providerName}" . ucfirst($type);
                    
                    $schema = [];
                    if (class_exists($className)) {
                        $instance = new $className(app());
                        $schema = $instance->getConfigSchema();
                    }

                    $providers[] = array_merge($manifest, ['schema' => $schema]);
                }
            }
        }

        return $providers;
    }

    /**
     * Boot plugin instance from database model with dependency injection.
     *
     * @param \App\Models\Plugin $plugin
     * @return mixed|null
     */
    public function bootInstance(Plugin $plugin)
    {
        $typePlural = Str::plural(ucfirst($plugin->type));
        $provider = $plugin->provider;

        $className = "Plugins\\{$typePlural}\\{$provider}\\{$provider}" . ucfirst($plugin->type);

        if (class_exists($className)) {
            $instance = new $className(app());

            $instance->setInstanceConfig($plugin->config ?? []);

            return $instance;
        }

        return null;
    }

}