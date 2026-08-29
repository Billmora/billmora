<?php

namespace App\Jobs\Automation;

use App\Models\Invoice;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancelUnpaidInvoices implements ShouldQueue
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
    public function handle(): void
    {
        $cancelDays = (int) Billmora::getAutomation('invoice_auto_cancel_days');

        if ($cancelDays <= 0) {
            return;
        }

        $targetCancelDate = now()->startOfDay()->subDays($cancelDays)->format('Y-m-d');

        Invoice::unpaid()
            ->whereDate('due_date', '<=', $targetCancelDate)
            ->chunk(100, function ($invoices) {
                foreach ($invoices as $invoice) {
                    try {
                        $invoice->update([
                            'status' => 'cancelled',
                        ]);

                        $this->recordSystem('invoice.cancelled', [
                            'invoice_id' => $invoice->id,
                            'reason' => 'auto_cancel_unpaid',
                        ], 'cron');

                        Log::info("Automation: Auto-cancelled unpaid Invoice {$invoice->invoice_number} (overdue by automation setting).");

                    } catch (\Throwable $e) {
                        Log::error("Automation: Failed to auto-cancel Invoice {$invoice->invoice_number}. Error: " . $e->getMessage());
                    }
                }
            });
    }
}
