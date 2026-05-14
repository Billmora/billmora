<?php

namespace App\Jobs\System;

use App\Facades\Audit;
use App\Services\UpdateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSystemUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum execution time for the update job (10 minutes).
     */
    public int $timeout = 600;

    /**
     * Number of retry attempts.
     */
    public int $tries = 1;

    /**
     * Whether to run in dry-run mode (for testing).
     */
    protected bool $dryRun;

    /**
     * The user ID who initiated the update.
     */
    protected ?int $userId;

    /**
     * Create a new job instance.
     *
     * @param bool     $dryRun  Whether to simulate the update.
     * @param int|null $userId  The user who triggered the update.
     */
    public function __construct(bool $dryRun = false, ?int $userId = null)
    {
        $this->dryRun = $dryRun;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $updater = app(UpdateService::class);

        Log::channel('single')->info('[SystemUpdate] Job started', [
            'dry_run' => $this->dryRun,
            'user_id' => $this->userId,
        ]);

        // Mark process as running (file-based, survives cache clears)
        $updater->writeStatus('running');

        try {
            $result = $updater->executeUpdate(null, $this->dryRun);

            // Write final status
            $updater->writeStatus(
                $result['success'] ? 'completed' : 'failed',
                $result['version']
            );

            // Audit logging
            if ($result['success']) {
                Audit::system($this->userId, 'system.update.completed', [
                    'version' => $result['version'],
                ]);
                Log::channel('single')->info('[SystemUpdate] Job completed successfully', [
                    'version' => $result['version'],
                ]);
            } else {
                Audit::system($this->userId, 'system.update.failed', [
                    'logs' => array_slice($result['logs'], -5),
                ]);
                Log::channel('single')->error('[SystemUpdate] Job finished with failure');
            }
        } catch (\Throwable $e) {
            $updater->writeStatus('failed');

            Log::channel('single')->error('[SystemUpdate] Job exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
