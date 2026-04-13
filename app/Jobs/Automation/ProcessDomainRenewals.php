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



        }


        $expiredRegistrants = Registrant::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredRegistrants as $registrant) {
            $registrant->update([
                'status' => 'expired'
            ]);
        }
    }
}
