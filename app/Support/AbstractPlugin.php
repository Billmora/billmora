<?php

namespace App\Support;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PluginInterface;
use Illuminate\Support\Facades\Route;
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
     * Create a new plugin instance and load manifest configuration.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $reflection = new ReflectionClass($this);
        $this->pluginPath = dirname($reflection->getFileName());

        $this->loadManifest();
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
     * Bootstrap plugin services including views, migrations, and routes.
     *
     * @return void
     */
    public function boot(): void
    {
        $provider = $this->getProvider();

        if (is_dir($this->pluginPath . '/views')) {
            $this->loadViewsFrom($this->pluginPath . '/views', $provider);
        }

        if (is_dir($this->pluginPath . '/database/migrations')) {
            $this->loadMigrationsFrom($this->pluginPath . '/database/migrations');
        }

        $this->bootRoutes($provider);

        $this->setup();
    }

    /**
     * Register plugin routes for web, admin, and API with type-aware prefixes.
     *
     * @return void
     */
    protected function bootRoutes(): void
    {
        $providerSlug = strtolower($this->getProvider());
        
        $typeRaw = $this->manifest['type'] ?? 'module';
        $typeSlug = Str::plural(strtolower($typeRaw));

        $urlPrefix = "plugins/{$typeSlug}/{$providerSlug}";
        
        $namePrefix = "plugin." . strtolower($typeRaw) . ".{$providerSlug}.";

        if (file_exists($this->pluginPath . '/routes/web.php')) {
            Route::middleware(['web'])
                ->prefix($urlPrefix)
                ->name($namePrefix)
                ->group($this->pluginPath . '/routes/web.php');
        }

        if (file_exists($this->pluginPath . '/routes/admin.php')) {
            Route::middleware(['web', 'auth', 'verified'])
                ->prefix("admin/{$urlPrefix}")
                ->name("admin.{$namePrefix}")
                ->group($this->pluginPath . '/routes/admin.php');
        }

        if (file_exists($this->pluginPath . '/routes/api.php')) {
            Route::prefix("api/{$urlPrefix}")
                ->middleware('api')
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