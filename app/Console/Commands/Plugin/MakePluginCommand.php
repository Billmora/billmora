<?php

namespace App\Console\Commands\Plugin;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\Prompts;

class MakePluginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billmora:plugin:make {name?} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Billmora plugin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Billmora Plugin Creator');
        $this->newLine();

        $name = $this->argument('name');
        if (!$name) {
            $name = Prompts\text(
                label: 'Plugin Name (e.g. MyProvider)',
                required: true,
                validate: fn ($value) => preg_match('/^[a-zA-Z0-9_]+$/', $value) ? null : 'Name can only contain alphanumeric characters and underscores.'
            );
        } else {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                $this->error('Name can only contain alphanumeric characters and underscores.');
                return self::FAILURE;
            }
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

        if (File::exists($pluginDir)) {
            $this->error("Plugin directory already exists at: {$pluginDir}");
            return self::FAILURE;
        }

        File::ensureDirectoryExists($pluginDir);

        $pluginJson = [
            'name' => $provider,
            'provider' => $provider,
            'type' => $type,
            'version' => '1.0.0',
            'description' => "{$provider} {$type} plugin for Billmora.",
            'author' => 'Your Name',
        ];
        File::put($pluginDir . '/plugin.json', json_encode($pluginJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $className = "{$provider}" . Str::studly($type);
        $classContent = $this->getClassStub($type, $provider, $className);
        File::put($pluginDir . "/{$className}.php", $classContent);

        if ($type === 'module') {
            File::ensureDirectoryExists($pluginDir . '/routes');
            File::put($pluginDir . '/routes/admin.php', "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n// Route::get('/', 'AdminController@index')->name('index');\n");
            File::put($pluginDir . '/routes/client.php', "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n// Route::get('/', 'ClientController@index')->name('index');\n");
            
            File::ensureDirectoryExists($pluginDir . '/resources/views/admin');
            File::put($pluginDir . '/resources/views/admin/.gitkeep', '');
            
            File::ensureDirectoryExists($pluginDir . '/resources/views/client');
            File::put($pluginDir . '/resources/views/client/.gitkeep', '');
        }

        $this->newLine();
        $this->info("Plugin has been created successfully!");
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $provider],
                ['Type', $type],
                ['Path', "plugin/{$typePlural}/{$provider}"],
                ['Class', $className],
            ]
        );

        return self::SUCCESS;
    }

    protected function getClassStub(string $type, string $provider, string $className): string
    {
        $namespace = "Plugins\\" . Str::studly(Str::plural($type)) . "\\{$provider}";
        
        switch ($type) {
            case 'provisioning':
                return <<<PHP
<?php

namespace {$namespace};

use App\Support\AbstractPlugin;
use App\Contracts\ProvisioningInterface;
use App\Models\Service;

class {$className} extends AbstractPlugin implements ProvisioningInterface
{
    public function getConfigSchema(): array
    {
        return [
            // 'api_key' => ['label' => 'API Key', 'type' => 'password', 'rules' => 'required'],
        ];
    }

    public function getPackageSchema(): array
    {
        return [
            // 'disk_space' => ['label' => 'Disk Space (MB)', 'type' => 'number', 'rules' => 'required|integer'],
        ];
    }

    public function getCheckoutSchema(): array
    {
        return [
            // 'hostname' => ['label' => 'Hostname', 'type' => 'text', 'rules' => 'required'],
        ];
    }

    public function getClientAction(Service \$service): array
    {
        return [
            // 'restart' => ['label' => 'Restart', 'icon' => 'fa-solid fa-rotate', 'type' => 'submit', 'method' => 'POST'],
        ];
    }

    public function handleClientAction(Service \$service, string \$slug, array \$data = [])
    {
        // Handle custom client actions
    }

    public function testConnection(array \$config): bool
    {
        return true;
    }

    public function create(Service \$service): void
    {
        // Provision the service
    }

    public function suspend(Service \$service): void
    {
        // Suspend the service
    }

    public function unsuspend(Service \$service): void
    {
        // Unsuspend the service
    }

    public function terminate(Service \$service): void
    {
        // Terminate the service
    }

    public function renew(Service \$service): void
    {
        // Renew the service
    }

    public function scale(Service \$service, array \$newConfig): void
    {
        // Scale the service
    }
}
PHP;

            case 'gateway':
                return <<<PHP
<?php

namespace {$namespace};

use App\Support\AbstractPlugin;
use App\Contracts\GatewayInterface;
use App\Support\GatewayCallbackResponse;
use Illuminate\Http\Request;

class {$className} extends AbstractPlugin implements GatewayInterface
{
    public function getConfigSchema(): array
    {
        return [
            // 'api_key' => ['label' => 'API Key', 'type' => 'password', 'rules' => 'required'],
        ];
    }

    public function isApplicable(float \$amount, string \$currency): bool
    {
        return true;
    }

    public function pay(string \$invoiceNumber, float \$amount, string \$currency, array \$options = []): mixed
    {
        // Return redirect URL or form data
        return [
            'success' => true,
            'type' => 'redirect', // or 'form', 'html'
            'data' => 'https://payment-url.com/pay',
        ];
    }

    public function webhook(Request \$request): GatewayCallbackResponse
    {
        // Validate webhook signature and process payment
        
        return new GatewayCallbackResponse(
            isValid: true,
            isSuccess: true,
            orderNumber: 'INV-123',
            gatewayReference: 'TXN-ABC',
            amount: 100.00,
            fee: 0.0
        );
    }

    public function return(Request \$request): GatewayCallbackResponse
    {
        // Handle return URL after payment
        
        return new GatewayCallbackResponse(
            isValid: false,
            isSuccess: false,
            orderNumber: 'INV-123'
        );
    }
}
PHP;

            case 'module':
                return <<<PHP
<?php

namespace {$namespace};

use App\Support\AbstractPlugin;
use App\Contracts\ModuleInterface;

class {$className} extends AbstractPlugin implements ModuleInterface
{
    public function getConfigSchema(): array
    {
        return [];
    }

    public function getPermissions(): array
    {
        return [
            // 'modules.{$provider}.view',
            // 'modules.{$provider}.manage',
        ];
    }

    public function getNavigationAdmin(): array
    {
        return [
            // 'my_module' => [
            //     'label' => '{$provider}',
            //     'icon' => 'lucide-box',
            //     'route' => route('admin.modules.' . strtolower('{$provider}') . '.index'),
            //     'permission' => 'modules.{$provider}.manage',
            // ],
        ];
    }

    public function getNavigationClient(): array
    {
        return [
            // 'my_module' => [
            //     'label' => '{$provider}',
            //     'icon' => 'lucide-box',
            //     'route' => route('client.modules.' . strtolower('{$provider}') . '.index'),
            // ],
        ];
    }
}
PHP;

            case 'registrar':
                return <<<PHP
<?php

namespace {$namespace};

use App\Support\AbstractPlugin;
use App\Contracts\RegistrarInterface;
use App\Models\Registrant;

class {$className} extends AbstractPlugin implements RegistrarInterface
{
    public function getConfigSchema(): array
    {
        return [
            // 'api_key' => ['label' => 'API Key', 'type' => 'password', 'rules' => 'required'],
        ];
    }

    public function testConnection(array \$config): bool
    {
        return true;
    }

    public function getTldPricing(): array
    {
        // Return pricing for importing TLDs
        return [];
    }

    public function checkAvailability(string \$domain): bool
    {
        return true;
    }

    public function register(Registrant \$registrant, array \$nameservers = []): void
    {
        // Register domain
    }

    public function transfer(Registrant \$registrant, string \$eppCode): void
    {
        // Transfer domain
    }

    public function renew(Registrant \$registrant): void
    {
        // Renew domain
    }

    public function getNameservers(Registrant \$registrant): array
    {
        return [];
    }

    public function setNameservers(Registrant \$registrant, array \$nameservers): void
    {
        // Set nameservers
    }

    public function getDomainInfo(Registrant \$registrant): array
    {
        return [];
    }

    public function getEppCode(Registrant \$registrant): string
    {
        return '';
    }

    public function getContactInfo(Registrant \$registrant): array
    {
        return [];
    }

    public function setContactInfo(Registrant \$registrant, array \$contactInfo): void
    {
        // Update contact info
    }

    public function registerNameserver(Registrant \$registrant, string \$nameserver, string \$ipAddress): void
    {
        // Register nameserver
    }

    public function updateNameserver(Registrant \$registrant, string \$nameserver, string \$currentIpAddress, string \$newIpAddress): void
    {
        // Update nameserver
    }

    public function deleteNameserver(Registrant \$registrant, string \$nameserver): void
    {
        // Delete nameserver
    }

    public function enableLock(Registrant \$registrant): void
    {
        // Enable registrar lock
    }

    public function disableLock(Registrant \$registrant): void
    {
        // Disable registrar lock
    }
}
PHP;

        }

        return '';
    }
}
