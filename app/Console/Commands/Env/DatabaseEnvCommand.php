<?php

namespace App\Console\Commands\Env;

use Billmora;
use Laravel\Prompts;
use Illuminate\Console\Command;

class DatabaseEnvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billmora:env:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup MariaDB (database) environment variables for Billmora';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Billmora Database Environment Setup (MariaDB)');
        $this->newLine();

        $host = Prompts\text(
            label: 'Database host [127.0.0.1]',
            placeholder: env('DB_HOST', '127.0.0.1')
        ) ?: env('DB_HOST', '127.0.0.1');

        $port = Prompts\text(
            label: 'Database port [3306]',
            placeholder: env('DB_PORT', '3306')
        ) ?: env('DB_PORT', '3306');

        $database = Prompts\text(
            label: 'Database name [billmora]',
            placeholder: env('DB_DATABASE', 'billmora')
        ) ?: env('DB_DATABASE', 'billmora');

        $username = Prompts\text(
            label: 'Database username [billmora]',
            placeholder: env('DB_USERNAME', 'billmora')
        ) ?: env('DB_USERNAME', 'billmora');

        $password = Prompts\password(
            label: 'Database password (leave empty if none)',
            placeholder: env('DB_PASSWORD', '')
        ) ?: env('DB_PASSWORD', '');

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);

            $this->info('Database connection successful!');
        } catch (\Throwable $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        Billmora::setEnv([
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ]);

        $this->newLine();
        $this->info('Billmora database environment setup complete!');
        return self::SUCCESS;
    }
}
