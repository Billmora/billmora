<?php

namespace App\Listeners\Registrant;

use App\Events\Invoice as InvoiceEvents;
use App\Events\Registrant as RegistrantEvents;
use App\Services\RegistrarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RegisterOnInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

    public int $timeout = 60;

    public int $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct(
        private RegistrarService $registrarService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(InvoiceEvents\Paid $event): void
    {
        $invoice = $event->invoice;

        $invoice->loadMissing('order.items.registrants');

        $order = $invoice->order;
        if (!$order) {
            return;
        }


        $registrants = $order->items->flatMap(function ($item) {
            return $item->registrants;
        });

        if ($registrants->isEmpty()) {
            return;
        }

        foreach ($registrants as $registrant) {

            if ($registrant->status !== 'pending') {
                continue;
            }

            if (!$registrant->plugin_id) {

                $registrant->update([
                    'status' => 'active', 
                    'registered_at' => now(),
                    'expires_at' => now()->addYears($registrant->years)
                ]);
                event(new RegistrantEvents\RegistrationCompleted($registrant));
                continue;
            }

            try {
                [$plugin] = $this->registrarService->bootPluginFor($registrant);

                if ($registrant->registration_type === 'register') {
                    $plugin->registerDomain($registrant);
                } elseif ($registrant->registration_type === 'transfer') {
                    $plugin->transferDomain($registrant);
                }

                $registrant->update([
                    'status' => $registrant->registration_type === 'transfer' ? 'pending_transfer' : 'active',
                    'registered_at' => now(),
                    'expires_at' => now()->addYears($registrant->years)
                ]);

                event(new RegistrantEvents\RegistrationCompleted($registrant));

            } catch (\Throwable $e) {
                event(new RegistrantEvents\RegistrationFailed($registrant, $e->getMessage()));
                $this->fail($e);
            }
        }
    }
}
