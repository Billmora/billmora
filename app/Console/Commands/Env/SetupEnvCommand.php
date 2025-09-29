<?php

namespace App\Console\Commands\Env;

use Billmora;
use DateTimeZone;
use Laravel\Prompts;
use Illuminate\Console\Command;

class SetupEnvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billmora:env:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup environment configuration for Billmora';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Billmora Environment Setup');
        $this->newLine();

        $appEnv = Prompts\select(
            label: 'Application environment',
            required: true,
            options: ['production', 'development', 'local'],
            default: config('app.env')
        );

        $timezones = DateTimeZone::listIdentifiers();
        $appTz = Prompts\search(
            label: 'Application timezone',
            required: true,
            options: fn (string $value) => strlen($value) > 0
                ? array_values(array_filter(
                    $timezones,
                    fn ($tz) => str_contains(strtolower($tz), strtolower($value))
                ))
                : $timezones,
            placeholder: config('app.timezone')
        );

        $appUrl = Prompts\text(
            label: 'Application URL',
            placeholder: config('app.url'),
            required: true,
            validate: function (string $value) {
                if (! filter_var($value, FILTER_VALIDATE_URL)) {
                    return 'Invalid URL format (e.g. http://example.com).';
                }

                $scheme = parse_url($value, PHP_URL_SCHEME);
                if (! in_array($scheme, ['http', 'https'])) {
                    return 'The URL must start with http:// or https://';
                }

                return null;
            },
        );

        $cacheDriver = Prompts\select(
            label: 'Cache driver',
            required: true,
            options: [
                'redis' => 'redis (recommended)',
                'memcached' => 'memcached',
                'file' => 'file',
            ],
            default: config('cache.default')
        );

        $sessionDriver = Prompts\select(
            label: 'Session driver',
            required: true,
            options: [
                'redis' => 'redis (recommended)',
                'memcached' => 'memcached',
                'database' => 'database',
                'file' => 'file',
                'cookie' => 'cookie',
            ],
            default: config('session.driver')
        );

        $queueDriver = Prompts\select(
            label: 'Queue connection',
            required: true,
            options: [
                'redis' => 'redis (recommended)',
                'database' => 'database',
                'sync' => 'sync',
            ],
            default: config('queue.default')
        );

        $redisConfig = [];
        if (in_array('redis', [$cacheDriver, $sessionDriver, $queueDriver], true)) {
            $redisConfig = $this->setupRedis();
        }

        Billmora::setEnv(array_merge([
            'APP_ENV' => $appEnv,
            'APP_TIMEZONE' => $appTz,
            'APP_URL' => $appUrl,
            'CACHE_DRIVER' => $cacheDriver,
            'SESSION_DRIVER' => $sessionDriver,
            'QUEUE_CONNECTION' => $queueDriver,
        ], $redisConfig));

        $this->newLine();
        $this->info('Billmora environment setup complete!');
        return self::SUCCESS;
    }

    /**
     * Prompt user for Redis connection details & test connection.
     */
    protected function setupRedis(): array
    {
        $this->newLine();
        $this->warn('Redis has been selected. Please provide the connection details below.');
        $this->line('Defaults should work in most setups unless you’ve made changes.');
        $this->newLine();

        do {
            $host = Prompts\text(
                label: 'Redis host [127.0.0.1]',
                placeholder: (string) env('REDIS_HOST', '127.0.0.1')
            ) ?: env('REDIS_HOST', '127.0.0.1');

            $password = Prompts\password(
                label: 'Redis password (leave empty if none)',
                placeholder: (string) env('REDIS_PASSWORD', '')
            ) ?: env('REDIS_PASSWORD', '');

            $port = Prompts\text(
                label: 'Redis port [6379]',
                placeholder: (string) env('REDIS_PORT', '6379')
            ) ?: env('REDIS_PORT', '6379');

            try {
                $client = new \Redis();
                $client->connect($host, (int) $port, 2);

                if ($password !== '') {
                    $client->auth($password);
                }

                $client->ping();
                $this->info('Redis connection successful!');
                $valid = true;
            } catch (\Throwable $e) {
                $this->error("Redis connection failed: " . $e->getMessage());
                $valid = false;
            }
        } while (! $valid);

        return [
            'REDIS_HOST' => $host,
            'REDIS_PASSWORD' => $password,
            'REDIS_PORT' => $port,
        ];
    }
}
