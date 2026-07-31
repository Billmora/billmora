<?php

namespace App\Listeners\Service;

use App\Events\Invoice as InvoiceEvents;
use App\Events\Service as ServiceEvents;
use App\Facades\Audit;
use App\Services\ProvisioningService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProvisionOnInvoicePaid implements ShouldQueue
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

        $invoice->loadMissing('order.services');

        $services = $invoice->order?->services;

        if (!$services || $services->isEmpty()) {
            return;
        }

        foreach ($services as $service) {
            
            if ($service->status !== 'pending') {
                continue;
            }

            if (!$service->package->auto_provision) {
                continue;
            }

            if (!$service->provisioning) {
                $service->activate();
                continue;
            }

            try {
                [$plugin, $instanceConfig] = $this->provisioningService->bootPluginFor($service);

                $plugin->create($service);

                $service->activate();

                Audit::system($invoice->user_id, 'service.provisioning.create', [
                    'service_id' => $service->id,
                    'status' => 'success',
                    'trigger' => 'invoice_paid',
                ]);

            } catch (\Throwable $e) {
                event(new ServiceEvents\ProvisioningFailed($service, $e->getMessage(), 'create'));
                
                $this->fail($e);
            }
        }
    }
}
