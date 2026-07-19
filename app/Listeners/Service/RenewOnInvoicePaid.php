<?php

namespace App\Listeners\Service;

use App\Events\Invoice as InvoiceEvents;
use App\Events\Service as ServiceEvents;
use App\Services\ProvisioningService;
use App\Facades\Audit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RenewOnInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public int $timeout = 60;

    public int $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct(
        private ProvisioningService $provisioningService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(InvoiceEvents\Paid $event): void
    {
        $invoice = $event->invoice;

        $invoice->loadMissing('services.package');

        $services = $invoice->services;

        if ($services->isEmpty()) {
            return;
        }

        foreach ($services as $service) {
            
            if (!in_array($service->status, ['active', 'suspended'])) {
                continue;
            }

            try {
                $wasSuspended = $service->status === 'suspended';
                $plugin = null;

                if ($service->provisioning) {
                    [$plugin, $instanceConfig] = $this->provisioningService->bootPluginFor($service);
                }

                if ($wasSuspended) {
                    if ($plugin) {
                        $plugin->unsuspend($service);
                    }
                    
                    $service->unsuspend();

                    Audit::system($invoice->user_id, 'service.provisioning.unsuspend', [
                        'service_id' => $service->id,
                        'status' => 'success',
                        'trigger' => 'invoice_paid',
                    ]);
                }

                $nextDueDate = $service->calculateNextDueDate();
                
                if ($plugin) {
                    $plugin->renew($service);
                }

                $service->update([
                    'next_due_date' => $nextDueDate,
                ]);

                Audit::system($invoice->user_id, 'service.provisioning.renew', [
                    'service_id' => $service->id,
                    'status' => 'success',
                    'trigger' => 'invoice_paid',
                ]);

                if ($wasSuspended) {
                    event(new ServiceEvents\ProvisioningUnsuspended($service));
                }
                event(new ServiceEvents\ProvisioningRenewed($service));

            } catch (\Throwable $e) {
                // If it fails, log and fail the job
                event(new ServiceEvents\ProvisioningFailed($service, $e->getMessage(), 'renew'));
                $this->fail($e);
            }
        }
    }
}
