<?php

namespace App\Support;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PluginInterface;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class AbstractPlugin extends ServiceProvider implements PluginInterface
{
    /**
     * The plugin manifest data loaded from plugin.json file.
     *
     * @var array<string, mixed>
     */
    protected array $manifest;

    /**
     * The absolute path to the plugin directory.
     *
     * @var string
     */
    protected string $pluginPath;

    /**
     * The instance configuration data from database provisioning settings.
     *
     * @var array<string, mixed>
     */
    protected array $instanceConfig = [];

    /**
     * Create a new plugin instance, load manifest, and register view namespace.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $reflection = new ReflectionClass($this);
        $this->pluginPath = dirname($reflection->getFileName());

        $this->loadManifest();

        $this->registerViewNamespace();
    }

    /**
     * Load plugin manifest data from plugin.json file.
     *
     * @return void
     */
    protected function loadManifest(): void
    {
        $jsonPath = $this->pluginPath . '/plugin.json';
        $this->manifest = file_exists($jsonPath)
            ? json_decode(file_get_contents($jsonPath), true)
            : [];
    }

    /**
     * Register the view namespace for the plugin.
     *
     * @return void
     */
    protected function registerViewNamespace(): void
    {
        $providerSlug = strtolower($this->getProvider());
        $typeSlug = strtolower($this->manifest['type']);
        
        $viewNamespace = "{$typeSlug}.{$providerSlug}";
        $viewsPath = $this->pluginPath . '/resources/views';

        View::addNamespace($viewNamespace, $viewsPath);
    }

    /**
     * Set instance configuration from database provisioning settings.
     *
     * @param array<string, mixed> $config
     * @return void
     */
    public function setInstanceConfig(array $config): void
    {
        $this->instanceConfig = $config;
    }

    /**
     * Get instance configuration value by key with optional default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getInstanceConfig(string $key, $default = null)
    {
        return data_get($this->instanceConfig, $key, $default);
    }

    /**
     * Get the plugin provider name from manifest or class basename.
     *
     * @return string
     */
    public function getProvider(): string
    {
        return $this->manifest['provider'] ?? class_basename($this);
    }

    /**
     * Get the configuration schema for plugin settings form.
     *
     * @return array<string, mixed>
     */
    public function getConfigSchema(): array
    {
        return [];
    }

    /**
     * Get the custom permissions required by this plugin.
     * Returns an array of permission names (strings).
     *
     * @return array<int, string>
     */
    public function getPermissions(): array
    {
        return [];
    }

    /**
     * Get the navigation items for the admin area.
     *
     * @return array
     */
    public function getNavigationAdmin(): array
    {
        return [];
    }

    /**
     * Get the navigation items for the client area.
     *
     * @return array
     */
    public function getNavigationClient(): array
    {
        return [];
    }

    /**
     * Get the navigation items for the portal area.
     *
     * @return array
     */
    public function getNavigationPortal(): array
    {
        return [];
    }

    /**
     * Get the events and their corresponding listener methods for this module.
     *
     * @return array<class-string, string> Example: [\App\Events\SomeEvent::class => 'handleSomeEvent']
     */
    public function getSubscribedEvents(): array
    {
        return [];
    }

    /**
     * Bootstrap plugin services including migrations, routes, and custom setup.
     *
     * @return void
     */
    public function boot(): void
    {
        if (is_dir($this->pluginPath . '/database/migrations')) {
            $this->loadMigrationsFrom($this->pluginPath . '/database/migrations');
        }

        $this->bootRoutes();

        $this->setup();
    }

    /**
     * Register plugin routes for client, admin, portal, and API with type-aware prefixes.
     *
     * @return void
     */
    protected function bootRoutes(): void
    {
        $providerSlug = strtolower($this->getProvider());
        
        $typeRaw = $this->manifest['type']; 
        $typeSlug = Str::plural(strtolower($typeRaw));

        $namePrefix = "{$typeSlug}.{$providerSlug}.";

        if (file_exists($this->pluginPath . '/routes/admin.php')) {
            Route::middleware(['web', 'auth', 'admin']) 
                ->prefix("admin/{$typeSlug}/{$providerSlug}") 
                ->name("admin.{$namePrefix}")
                ->group($this->pluginPath . '/routes/admin.php');
        }

        if (file_exists($this->pluginPath . '/routes/client.php')) {
            Route::middleware(['web', 'maintenance'])
                ->prefix($providerSlug)
                ->name("client.{$namePrefix}")
                ->group($this->pluginPath . '/routes/client.php');
        }

        if (file_exists($this->pluginPath . '/routes/portal.php')) {
            Route::middleware(['web'])
                ->prefix($providerSlug)
                ->name("portal.{$namePrefix}")
                ->group($this->pluginPath . '/routes/portal.php');
        }

        if (file_exists($this->pluginPath . '/routes/api.php')) {
            Route::middleware(['api'])
                ->prefix("api/{$typeSlug}/{$providerSlug}")
                ->name("api.{$namePrefix}")
                ->group($this->pluginPath . '/routes/api.php');
        }
    }

    /**
     * Register plugin services into the container.
     *
     * @return void
     */
    public function register()
    {
        // Required by ServiceProvider
    }

    /**
     * Perform additional plugin setup after bootstrapping.
     *
     * @return void
     */
    protected function setup(): void
    {
        // Hook for child classes to override
    }
}