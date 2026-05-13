<?php

namespace App\Jobs\Automation;

use App\Models\Invoice;
use App\Services\CreditService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAutoCreditPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     *
     * Find all unpaid invoices belonging to users who have auto_credit_payment
     * enabled and attempt to settle them using available credit balance.
     */
    public function handle(): void
    {
        if (!(bool) Billmora::getGeneral('credit_auto_payment')) {
            return;
        }

        $invoices = Invoice::where('status', 'unpaid')
            ->whereHas('user', function ($query) {
                $query->where('auto_credit_payment', true)
                    ->where('status', 'active');
            })
            ->whereDoesntHave('items', function ($query) {
                $query->where('description', 'like', 'Credit Deposit%');
            })
            ->with('user')
            ->get();

        if ($invoices->isEmpty()) {
            return;
        }

        $creditService = app(CreditService::class);

        foreach ($invoices as $invoice) {
            try {
                $creditService->attemptAutoPayment($invoice);
            } catch (\Throwable $e) {
                Log::error("Automation: Failed auto credit payment for Invoice {$invoice->invoice_number}. Error: " . $e->getMessage());
            }
        }
    }
}
