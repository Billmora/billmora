<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Get grouped navigation items for the Admin area from all active plugins.
     *
     * @return array
     */
    public function getNavigationAdmin(): array
    {
        return $this->getNavigation('getNavigationAdmin');
    }

    /**
     * Get grouped navigation items for the Client area from all active plugins.
     *
     * @return array
     */
    public function getNavigationClient(): array
    {
        return $this->getNavigation('getNavigationClient');
    }

    /**
     * Get grouped navigation items for the Portal area from all active plugins.
     *
     * @return array
     */
    public function getNavigationPortal(): array
    {
        return $this->getNavigation('getNavigationPortal');
    }

    /**
     * Get grouped navigation items for a specific area from all active plugins.
     *
     * @param string $method
     * @return array
     */
    private function getNavigation(string $method): array
    {
        $menus = [];
        $activePlugins = Plugin::where('is_active', true)->get();
        
        foreach ($activePlugins as $pluginRecord) {
            $pluginInstance = $this->bootInstance($pluginRecord);
            
            if ($pluginInstance && method_exists($pluginInstance, $method)) {
                $typeTitle = ucfirst(Str::singular($pluginRecord->type));
                
                $navItems = $pluginInstance->$method();
                if (!empty($navItems)) {
                    $filteredItems = array_filter($navItems, function ($item) {
                        
                        if (isset($item['auth'])) {
                            if ($item['auth'] === true && !Auth::check()) {
                                return false; 
                            }
                            if ($item['auth'] === false && Auth::check()) {
                                return false; 
                            }
                        }

                        if (!empty($item['permission'])) {
                            return Auth::check() && Auth::user()->can($item['permission']);
                        }

                        return true;
                    });

                    if (!empty($filteredItems)) {
                        $menus[$typeTitle] = array_merge($menus[$typeTitle] ?? [], $filteredItems);
                    }
                }
            }
        }
        
        return $menus;
    }
}