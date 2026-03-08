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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessServiceTerminations implements ShouldQueue
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
        $terminateDays = (int) Billmora::getAutomation('service_terminate_days');

        if ($terminateDays <= 0) {
            return;
        }

        $targetTerminateDate = now()->startOfDay()->subDays($terminateDays)->format('Y-m-d');

        Service::where('status', 'suspended')
            ->whereHas('invoices', function ($query) use ($targetTerminateDate) {
                $query->where('status', 'unpaid')
                      ->whereDate('due_date', '<=', $targetTerminateDate);
            })
            ->chunk(100, function ($services) use ($provisioningService) {
                foreach ($services as $service) {
                    try {
                        $service->loadMissing('provisioning');

                        DB::transaction(function () use ($service, $provisioningService) {
                            if ($service->provisioning) {
                                [$plugin, $instanceConfig] = $provisioningService->bootPluginFor($service);
                                $plugin->terminate($service, $instanceConfig);
                            }

                            $service->invoices()->unpaid()->update([
                                'status' => 'cancelled'
                            ]);

                            $service->terminate();

                            $this->recordSystem('service.provisioning.terminate', [
                                'service_id' => $service->id,
                                'status' => 'success',
                                'reason' => 'overdue_invoice_limit',
                            ], 'cron');
                        });

                        Log::info("Automation: Successfully terminated Service ID {$service->id} and cancelled related unpaid invoices.");

                    } catch (\Throwable $e) {
                        Log::error("Automation: Failed to terminate Service ID {$service->id}. Error: " . $e->getMessage());
                        
                        $this->recordSystem('service.provisioning.terminate', [
                            'service_id' => $service->id,
                            'status' => 'failed',
                            'error' => $e->getMessage(),
                        ], 'cron');
                    }
                }
            });
    }
}
