<?php

namespace App\Jobs\Automation;

use App\Models\Service;
use App\Services\ProvisioningService;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessServiceSuspensions implements ShouldQueue
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
        $suspendDays = (int) Billmora::getAutomation('service_suspend_days');

        if ($suspendDays <= 0) {
            return;
        }

        $targetSuspendDate = now()->startOfDay()->subDays($suspendDays)->format('Y-m-d');

        Service::where('status', 'active')
            ->whereHas('invoices', function ($query) use ($targetSuspendDate) {
                $query->where('status', 'unpaid')
                      ->whereDate('due_date', '<=', $targetSuspendDate);
            })
            ->chunk(100, function ($services) use ($provisioningService) {
                foreach ($services as $service) {
                    try {
                        $service->loadMissing('provisioning');

                        if ($service->provisioning) {
                            [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);
                            $plugin->suspend($service, $instanceConfig);
                        }

                        $service->suspend(); 

                        $this->recordSystem('service.provisioning.suspend', [
                            'service_id' => $service->id,
                            'reason' => 'overdue_invoice',
                        ], 'cron');

                        Log::info("Automation: Successfully suspended Service ID {$service->id} due to overdue invoice.");

                    } catch (\Throwable $e) {
                        Log::error("Automation: Failed to suspend Service ID {$service->id}. Error: " . $e->getMessage());
                        
                        $this->recordSystem('service.provisioning.suspend.failed', [
                            'service_id' => $service->id,
                            'error' => $e->getMessage(),
                        ], 'cron');
                    }
                }
            });
    }
}
