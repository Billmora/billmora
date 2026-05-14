<?php

namespace App\Console\Commands;

use App\Facades\Audit;
use App\Services\UpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExecuteSystemUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billmora:update:execute
                            {--dry-run : Simulate the update without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute the system update process in the background.';

    /**
     * Execute the console command.
     */
    public function handle(UpdateService $updater): int
    {
        $isDryRun = $this->option('dry-run');

        Log::channel('single')->info('[SystemUpdate] Artisan command started', [
            'dry_run' => $isDryRun,
        ]);

        // Mark process as running
        $updater->writeStatus('running');

        try {
            $result = $updater->executeUpdate(null, $isDryRun);

            // Write final status
            $updater->writeStatus(
                $result['success'] ? 'completed' : 'failed',
                $result['version']
            );

            if ($result['success']) {
                Audit::system(null, 'system.update.completed', [
                    'version' => $result['version'],
                ]);
                $this->info("Update to v{$result['version']} completed successfully.");
                Log::channel('single')->info('[SystemUpdate] Completed successfully', [
                    'version' => $result['version'],
                ]);
            } else {
                Audit::system(null, 'system.update.failed', [
                    'logs' => array_slice($result['logs'], -5),
                ]);
                $this->error('Update failed.');
                Log::channel('single')->error('[SystemUpdate] Update failed');
            }

            return $result['success'] ? self::SUCCESS : self::FAILURE;

        } catch (\Throwable $e) {
            $updater->writeStatus('failed');
            $this->error("Fatal error: {$e->getMessage()}");
            Log::channel('single')->error('[SystemUpdate] Fatal exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
