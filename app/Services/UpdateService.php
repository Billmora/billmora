<?php

namespace App\Services;

use App\Services\BillmoraService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

class UpdateService
{
    /**
     * GitHub repository identifier.
     */
    protected const GITHUB_REPO = 'billmora/billmora';

    /**
     * GitHub API base URL.
     */
    protected const GITHUB_API = 'https://api.github.com';

    /**
     * Cache key for latest release data.
     */
    protected const CACHE_KEY = 'billmora_latest_release';

    /**
     * Cache TTL for release data (24 hours in seconds).
     */
    protected const CACHE_TTL = 86400;

    /**
     * Temporary storage path for update files.
     */
    protected string $tempPath;

    /**
     * Collected log entries during update process.
     *
     * @var array<int, array{time: string, message: string, status: string}>
     */
    protected array $logs = [];

    public function __construct()
    {
        $this->tempPath = storage_path('app/tmp/update');
    }

    /**
     * Fetch the latest release data from GitHub API with caching.
     *
     * @param bool $fresh  Force a fresh fetch, bypassing cache.
     * @return array|null
     */
    public function getLatestRelease(bool $fresh = false): ?array
    {
        if ($fresh) {
            Cache::forget(self::CACHE_KEY);
            Cache::forget('billmora_latest_version');
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'Billmora-Update-Service'])
                    ->get(self::GITHUB_API . '/repos/' . self::GITHUB_REPO . '/releases/latest');

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'tag_name' => $data['tag_name'] ?? null,
                        'name' => $data['name'] ?? null,
                        'body' => $data['body'] ?? null,
                        'published_at' => $data['published_at'] ?? null,
                        'html_url' => $data['html_url'] ?? null,
                        'tarball_url' => $this->findTarballAssetUrl($data),
                        'asset_size' => $this->findTarballAssetSize($data),
                    ];
                }
            } catch (\Exception $e) {
                // Return null if API call fails
            }
            return null;
        });
    }

    /**
     * Determine if an update is available by comparing versions.
     *
     * @return bool
     */
    public function isUpdateAvailable(): bool
    {
        $release = $this->getLatestRelease();

        if (!$release || !$release['tag_name']) {
            return false;
        }

        $latest = ltrim($release['tag_name'], 'vV');
        $current = BillmoraService::VERSION;

        return version_compare($latest, $current, '>');
    }

    /**
     * Get the current application version.
     *
     * @return string
     */
    public function getCurrentVersion(): string
    {
        return BillmoraService::VERSION;
    }

    /**
     * Check system requirements for performing an update.
     *
     * @return array<string, array{label: string, required: string, current: string, satisfied: bool}>
     */
    public function checkRequirements(): array
    {
        $phpBinary = $this->findPhpBinary();
        $composerBinary = $this->findComposerBinary();

        return [
            'php_version' => [
                'label' => 'PHP Version',
                'required' => '≥ 8.2',
                'current' => PHP_VERSION,
                'satisfied' => version_compare(PHP_VERSION, '8.2.0', '>='),
            ],
            'phar_extension' => [
                'label' => 'Phar Extension',
                'required' => 'Enabled',
                'current' => class_exists('PharData') ? 'Enabled' : 'Disabled',
                'satisfied' => class_exists('PharData'),
            ],
            'composer' => [
                'label' => 'Composer',
                'required' => 'Available',
                'current' => $composerBinary ? 'Available' : 'Not Found',
                'satisfied' => (bool) $composerBinary,
            ],
            'disk_space' => [
                'label' => 'Available Disk Space',
                'required' => '≥ 100 MB',
                'current' => $this->formatBytes(disk_free_space(base_path())),
                'satisfied' => disk_free_space(base_path()) >= 104857600,
            ],
            'writable' => [
                'label' => 'Application Directory',
                'required' => 'Writable',
                'current' => is_writable(base_path()) ? 'Writable' : 'Read Only',
                'satisfied' => is_writable(base_path()),
            ],
        ];
    }

    /**
     * Execute the full update process.
     *
     * @param callable|null $onLog  Optional callback to receive log entries in real-time.
     * @return array{success: bool, logs: array, version: string|null}
     */
    public function executeUpdate(?callable $onLog = null): array
    {
        $release = $this->getLatestRelease();

        if (!$release || !$this->isUpdateAvailable()) {
            return ['success' => false, 'logs' => [['time' => now()->format('H:i:s'), 'message' => 'No update available.', 'status' => 'error']], 'version' => null];
        }

        $newVersion = ltrim($release['tag_name'], 'vV');

        set_time_limit(0);
        ignore_user_abort(true);

        try {
            // Step 1: Enable maintenance mode
            $this->log('Enabling maintenance mode...', 'running', $onLog);
            $this->enableMaintenance();
            $this->log('Maintenance mode enabled.', 'success', $onLog);

            // Step 2: Download release archive
            $this->log("Downloading release v{$newVersion}...", 'running', $onLog);
            $archivePath = $this->downloadRelease($release['tarball_url'], $release['asset_size']);
            $this->log('Download complete.', 'success', $onLog);

            // Step 3: Extract and overwrite files
            $this->log('Extracting files...', 'running', $onLog);
            $this->extractRelease($archivePath);
            $this->log('Files extracted and applied.', 'success', $onLog);

            // Step 4: Run composer install
            $this->log('Installing dependencies (composer install)...', 'running', $onLog);
            $this->runComposerInstall();
            $this->log('Dependencies installed.', 'success', $onLog);

            // Step 5: Run database migrations
            $this->log('Running database migrations...', 'running', $onLog);
            $this->runMigrations();
            $this->log('Migrations complete.', 'success', $onLog);

            // Step 6: Clear and rebuild cache
            $this->log('Clearing application cache...', 'running', $onLog);
            $this->clearCache();
            $this->log('Cache cleared.', 'success', $onLog);

            $this->log('Optimizing application...', 'running', $onLog);
            $this->optimizeApplication();
            $this->log('Optimization complete.', 'success', $onLog);

            // Step 7: Restart queue workers
            $this->log('Restarting queue workers...', 'running', $onLog);
            $this->restartQueues();
            $this->log('Queue workers restarted.', 'success', $onLog);

            // Step 8: Disable maintenance mode
            $this->log('Disabling maintenance mode...', 'running', $onLog);
            $this->disableMaintenance();
            $this->log('Maintenance mode disabled.', 'success', $onLog);

            // Step 9: Cleanup
            $this->log('Cleaning up temporary files...', 'running', $onLog);
            $this->cleanup();
            $this->log('Cleanup complete.', 'success', $onLog);

            // Clear version cache so it reflects the new version
            Cache::forget(self::CACHE_KEY);
            Cache::forget('billmora_latest_version');

            $this->log("Update to v{$newVersion} completed successfully!", 'success', $onLog);

            return ['success' => true, 'logs' => $this->logs, 'version' => $newVersion];

        } catch (\Exception $e) {
            $this->log("Error: {$e->getMessage()}", 'error', $onLog);

            // Ensure maintenance mode is disabled on failure
            try {
                $this->disableMaintenance();
                $this->log('Maintenance mode disabled after error.', 'warning', $onLog);
            } catch (\Exception $ex) {
                // Ignore if maintenance mode disable also fails
            }

            // Cleanup temp files on failure
            try {
                $this->cleanup();
            } catch (\Exception $ex) {
                // Ignore cleanup failures
            }

            return ['success' => false, 'logs' => $this->logs, 'version' => null];
        }
    }

    /**
     * Download the release archive from GitHub.
     *
     * @param string   $url   The download URL for the release tarball.
     * @param int|null $expectedSize  Expected file size for validation.
     * @return string  Path to the downloaded archive.
     *
     * @throws \RuntimeException
     */
    protected function downloadRelease(string $url, ?int $expectedSize = null): string
    {
        File::ensureDirectoryExists($this->tempPath);

        $archivePath = $this->tempPath . '/billmora-update.tar.gz';

        $response = Http::timeout(120)
            ->withHeaders(['User-Agent' => 'Billmora-Update-Service'])
            ->withOptions(['sink' => $archivePath])
            ->get($url);

        if (!File::exists($archivePath) || File::size($archivePath) === 0) {
            throw new \RuntimeException('Failed to download the release archive.');
        }

        // Validate file size if expected size is available
        if ($expectedSize && abs(File::size($archivePath) - $expectedSize) > 1024) {
            File::delete($archivePath);
            throw new \RuntimeException('Downloaded file size does not match expected size. File may be corrupted.');
        }

        return $archivePath;
    }

    /**
     * Extract the tar.gz release archive and overwrite existing application files.
     *
     * @param string $archivePath  Path to the downloaded tar.gz archive.
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function extractRelease(string $archivePath): void
    {
        if (!class_exists('PharData')) {
            throw new \RuntimeException('PharData extension is required to extract .tar.gz archives.');
        }

        $extractPath = $this->tempPath . '/extracted';
        File::ensureDirectoryExists($extractPath);

        try {
            $phar = new \PharData($archivePath);
            $phar->extractTo($extractPath, null, true);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to extract release archive: ' . $e->getMessage());
        }

        // GitHub tarballs extract into a subdirectory (e.g., "billmora-billmora-abc1234/")
        // Find the actual source directory
        $dirs = File::directories($extractPath);

        if (count($dirs) === 1) {
            $sourceDir = $dirs[0];
        } else {
            $sourceDir = $extractPath;
        }

        // Protected files/directories that should NOT be overwritten
        $protected = ['.env', 'storage', '.git'];

        $basePath = base_path();

        // Copy files from source to application root
        $items = File::allFiles($sourceDir);
        foreach ($items as $file) {
            $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $file->getPathname());

            // Skip protected files
            $isProtected = false;
            foreach ($protected as $protectedItem) {
                if (str_starts_with($relativePath, $protectedItem)) {
                    $isProtected = true;
                    break;
                }
            }

            if ($isProtected) {
                continue;
            }

            $targetPath = $basePath . DIRECTORY_SEPARATOR . $relativePath;

            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($file->getPathname(), $targetPath);
        }
    }

    /**
     * Run composer install to update PHP dependencies.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function runComposerInstall(): void
    {
        $composer = $this->findComposerBinary();

        if (!$composer) {
            throw new \RuntimeException('Composer binary not found. Please install Composer or add it to your PATH.');
        }

        $process = new Process([$composer, 'install', '--no-dev', '--optimize-autoloader', '--no-interaction'], base_path());
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Composer install failed: ' . $process->getErrorOutput());
        }
    }

    /**
     * Run database migrations.
     *
     * @return void
     */
    protected function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    /**
     * Clear all application caches.
     *
     * @return void
     */
    protected function clearCache(): void
    {
        Artisan::call('optimize:clear');
    }

    /**
     * Rebuild the application optimization cache.
     *
     * @return void
     */
    protected function optimizeApplication(): void
    {
        Artisan::call('optimize');
    }

    /**
     * Signal queue workers to restart after their current job finishes.
     *
     * @return void
     */
    protected function restartQueues(): void
    {
        Artisan::call('queue:restart');
    }

    /**
     * Enable application maintenance mode using the Billmora setting.
     *
     * @return void
     */
    protected function enableMaintenance(): void
    {
        BillmoraService::setSetting('general', [
            'company_maintenance' => true,
            'company_maintenance_message' => 'System is being updated. Please check back shortly.',
        ]);
    }

    /**
     * Disable application maintenance mode.
     *
     * @return void
     */
    protected function disableMaintenance(): void
    {
        BillmoraService::setSetting('general', [
            'company_maintenance' => false,
        ]);
    }

    /**
     * Remove all temporary update files.
     *
     * @return void
     */
    protected function cleanup(): void
    {
        if (File::exists($this->tempPath)) {
            File::deleteDirectory($this->tempPath);
        }
    }

    /**
     * Find the tar.gz asset download URL from the GitHub release data.
     *
     * @param array $releaseData  Raw GitHub API response data.
     * @return string|null
     */
    protected function findTarballAssetUrl(array $releaseData): ?string
    {
        // First, check uploaded assets for a tar.gz file
        if (!empty($releaseData['assets'])) {
            foreach ($releaseData['assets'] as $asset) {
                if (str_ends_with($asset['name'] ?? '', '.tar.gz')) {
                    return $asset['browser_download_url'];
                }
            }
        }

        // Fallback to the auto-generated tarball URL
        return $releaseData['tarball_url'] ?? null;
    }

    /**
     * Find the tar.gz asset file size from the GitHub release data.
     *
     * @param array $releaseData  Raw GitHub API response data.
     * @return int|null
     */
    protected function findTarballAssetSize(array $releaseData): ?int
    {
        if (!empty($releaseData['assets'])) {
            foreach ($releaseData['assets'] as $asset) {
                if (str_ends_with($asset['name'] ?? '', '.tar.gz')) {
                    return $asset['size'] ?? null;
                }
            }
        }

        return null;
    }

    /**
     * Find the PHP binary path.
     *
     * @return string|null
     */
    protected function findPhpBinary(): ?string
    {
        $finder = new PhpExecutableFinder();
        return $finder->find() ?: null;
    }

    /**
     * Find the Composer binary path.
     *
     * @return string|null
     */
    protected function findComposerBinary(): ?string
    {
        // Check common locations
        $possiblePaths = [
            'composer',
            '/usr/local/bin/composer',
            '/usr/bin/composer',
            base_path('composer.phar'),
        ];

        foreach ($possiblePaths as $path) {
            try {
                $process = new Process([$path, '--version']);
                $process->setTimeout(5);
                $process->run();
                if ($process->isSuccessful()) {
                    return $path;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Format bytes to human readable size.
     *
     * @param float|int $bytes
     * @return string
     */
    protected function formatBytes(float|int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string) (int) $bytes) - 1) / 3);

        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor] ?? 'B');
    }

    /**
     * Add a log entry and optionally call the real-time callback.
     *
     * @param string        $message  The log message.
     * @param string        $status   The log status (running, success, error, warning).
     * @param callable|null $onLog    Optional real-time callback.
     * @return void
     */
    protected function log(string $message, string $status, ?callable $onLog = null): void
    {
        $entry = [
            'time' => now()->format('H:i:s'),
            'message' => $message,
            'status' => $status,
        ];

        $this->logs[] = $entry;

        if ($onLog) {
            $onLog($entry);
        }
    }
}
