<?php

namespace App\Jobs\Automation;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Registrant;
use Billmora;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDomainRenewals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $invoiceGenerationDays = (int) Billmora::getAutomation('invoice_generation_days') ?: 14;

        $targetDate = now()->addDays($invoiceGenerationDays)->toDateString();


        $registrantsToInvoice = Registrant::where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('expires_at', '<=', $targetDate)
            ->whereDoesntHave('invoices', function ($query) {
                $query->whereIn('status', ['unpaid', 'paid']); 
            })
            ->get();

        foreach ($registrantsToInvoice as $registrant) {
            try {
                DB::transaction(function () use ($registrant) {
                    $invoice = Invoice::create([
                        'user_id' => $registrant->user_id,
                        'status' => 'unpaid',
                        'currency' => $registrant->currency,
                        'subtotal' => $registrant->price,
                        'total' => $registrant->price,
                        'due_date' => $registrant->expires_at, 
                    ]);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'registrant_id' => $registrant->id,
                        'description' => "Domain Renewal - {$registrant->domain} ({$registrant->years} Year(s))",
                        'quantity' => 1,
                        'unit_price' => $registrant->price,
                        'amount' => $registrant->price,
                    ]);
                });

                Log::info("Automation: Generated renewal invoice for Registrant ID {$registrant->id} ({$registrant->domain}).");

            } catch (\Throwable $e) {
                Log::error("Automation: Failed to generate renewal invoice for Registrant ID {$registrant->id} ({$registrant->domain}). Error: " . $e->getMessage());
            }
        }


        $expiredRegistrants = Registrant::where('status', 'active')
            ->where('expires_at', '<', now())
            ->whereDoesntHave('invoices', function ($query) {
                $query->where('status', 'unpaid');
            })
            ->get();

        foreach ($expiredRegistrants as $registrant) {
            $registrant->update([
                'status' => 'expired'
            ]);
        }
    }
}
