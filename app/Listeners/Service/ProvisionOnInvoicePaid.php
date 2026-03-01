<?php

namespace App\Listeners\Service;

use App\Events\Invoice as InvoiceEvents;
use App\Events\Service as ServiceEvents;
use App\Services\ProvisioningService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProvisionOnInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

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

        $invoice->loadMissing('order.service.provisioning');

        $service = $invoice->order?->service;

        if (!$service || $service->status !== 'pending') {
            return;
        }

        if (!$service->provisioning) {
            $service->activate();
            return;
        }

        try {
            [$plugin, $instanceConfig] = $this->provisioningService->bootPluginFor($service);

            $plugin->create($service, $instanceConfig);

            $service->activate();

        } catch (\Throwable $e) {
            event(new ServiceEvents\ProvisioningFailed($service, $e->getMessage(), 'create'));
            
            $this->fail($e);
        }
    }
}
