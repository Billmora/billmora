<?php

namespace App\Listeners\Registrant;

use App\Events\Invoice as InvoiceEvents;
use App\Events\Registrant as RegistrantEvents;
use App\Events\Domain as DomainEvents;
use App\Services\RegistrarService;
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
        private RegistrarService $registrarService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(InvoiceEvents\Paid $event): void
    {
        $invoice = $event->invoice;

        $invoice->loadMissing('registrants');

        $registrants = $invoice->registrants;

        if ($registrants->isEmpty()) {
            return;
        }

        foreach ($registrants as $registrant) {
            
            if (!in_array($registrant->status, ['active', 'suspended'])) {
                continue;
            }

            try {
                $wasSuspended = $registrant->status === 'suspended';
                $plugin = null;

                if ($registrant->plugin_id) {
                    [$plugin] = $this->registrarService->bootPluginFor($registrant);
                }

                if ($wasSuspended) {
                    if ($plugin) {
                        $plugin->unsuspend($registrant);
                    }
                    
                    $registrant->unsuspend();

                    Audit::system($invoice->user_id, 'domain.unsuspend', [
                        'registrant_id' => $registrant->id,
                        'status' => 'success',
                        'trigger' => 'invoice_paid',
                    ]);
                }

                if ($plugin) {
                    $plugin->renew($registrant, $registrant->years);
                }

                $registrant->update([
                    'expires_at' => $registrant->expires_at->addYears($registrant->years),
                ]);

                Audit::system($invoice->user_id, 'domain.renew', [
                    'registrant_id' => $registrant->id,
                    'status' => 'success',
                    'trigger' => 'invoice_paid',
                ]);

                if ($wasSuspended) {
                    event(new DomainEvents\Unsuspended($registrant));
                }
                event(new DomainEvents\Renewed($registrant));
                event(new RegistrantEvents\Renewed($registrant));

            } catch (\Throwable $e) {
                // If it fails, log and fail the job
                $this->fail($e);
            }
        }
    }
}
