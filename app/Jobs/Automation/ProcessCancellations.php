<?php

namespace App\Jobs\Automation;

use App\Models\ServiceCancellation;
use App\Services\ProvisioningService;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessCancellations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AuditsSystem;

    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ProvisioningService $provisioningService): void
    {
        $isAutoAccept = (bool) Billmora::getAutomation('auto_accept_cancellation');

        if (!$isAutoAccept) {
            return;
        }

        ServiceCancellation::where('status', 'pending')
            ->with('service')
            ->chunk(100, function ($cancellations) use ($provisioningService) {
                
                $today = now()->startOfDay();

                foreach ($cancellations as $cancellation) {
                    $service = $cancellation->service;

                    if (!$service || in_array($service->status, ['terminated', 'cancelled'])) {
                        $cancellation->update(['status' => 'approved', 'cancelled_at' => now()]);
                        continue;
                    }

                    $shouldProcess = false;

                    if ($cancellation->type === 'immediate') {
                        $shouldProcess = true;
                    } elseif ($cancellation->type === 'end_of_period') {
                        if (!$service->next_due_date || $today->gte($service->next_due_date->startOfDay())) {
                            $shouldProcess = true;
                        }
                    }

                    if ($shouldProcess) {
                        try {
                            $service->loadMissing('provisioning');

                            DB::transaction(function () use ($cancellation, $service, $provisioningService) {
                                if ($service->provisioning) {
                                    [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);
                                    $plugin->terminate($service, $instanceConfig);
                                }

                                $service->invoices()->unpaid()->update([
                                    'status' => 'cancelled'
                                ]);

                                $cancellation->update([
                                    'status' => 'approved',
                                    'reviewed_at' => now(),
                                    'cancelled_at' => now(),
                                ]);

                                $service->update([
                                    'status' => 'cancelled',
                                    'cancelled_at' => now(),
                                    'suspended_at' => null,
                                    'next_due_date' => null,
                                ]);

                                $this->recordSystem('service.cancellation.approved', [
                                    'cancellation_id' => $cancellation->id,
                                    'service_id' => $service->id,
                                    'type' => $cancellation->type
                                ], 'cron');
                            });

                            Log::info("Automation: Auto-processed {$cancellation->type} cancellation for Service ID {$service->id}.");

                        } catch (\Throwable $e) {
                            Log::error("Automation: Failed to auto-process cancellation for Service ID {$service->id}. Error: " . $e->getMessage());

                            $this->recordSystem('service.cancellation.approve', [
                                'cancellation_id' => $cancellation->id,
                                'service_id' => $service->id,
                                'status' => 'failed',
                                'error' => $e->getMessage(),
                            ], 'cron');
                        }
                    }
                }
            });
    }
}
