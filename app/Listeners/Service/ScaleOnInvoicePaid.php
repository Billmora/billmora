<?php

namespace App\Listeners\Service;

use App\Events\Invoice as InvoiceEvents;
use App\Events\Service as ServiceEvents;
use App\Models\ServiceScaling;
use App\Services\ProvisioningService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class ScaleOnInvoicePaid
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

        $scaling = ServiceScaling::where('invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->first();

        if (!$scaling) {
            return;
        }

        $service = $scaling->service;
        $service->loadMissing('provisioning');

        try {
            DB::transaction(function () use ($scaling, $service) {
                $service->update([
                    'name' => $scaling->newPackage->name,
                    'package_id' => $scaling->new_package_id,
                    'package_price_id' => $scaling->new_package_price_id,
                    'price' => $scaling->new_price,
                    'variant_selections' => $scaling->variant_selections,
                ]);

                $scaling->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            });

            if ($service->provisioning) {
                [$plugin, $instanceConfig] = $this->provisioningService->bootPluginFor($service);
                
                if (method_exists($plugin, 'scale')) {
                    $plugin->scale($service, $instanceConfig);
                }
            }

            event(new ServiceEvents\ProvisioningScaled($service));

        } catch (\Throwable $e) {
            $scaling->update([
                'status' => 'failed',
            ]);
            
            event(new ServiceEvents\ProvisioningFailed($service, $e->getMessage(), 'scale'));
            $this->fail($e);
        }
    }
}
