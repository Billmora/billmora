<?php

namespace App\Jobs\Automation;

use App\Events\Invoice as InvoiceEvents;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateRecurringInvoices implements ShouldQueue
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
        $generationDays = (int) Billmora::getAutomation('invoice_generation_days');
        $targetDate = now()->addDays($generationDays)->endOfDay();

        $services = Service::where('status', 'active')
            ->where('billing_type', 'recurring')
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<=', $targetDate)
            ->whereDoesntHave('invoices', function ($query) {
                $query->where('status', 'unpaid');
            })
            ->get();

        if ($services->isEmpty()) {
            return;
        }

        foreach ($services as $service) {
            try {
                $currentDueDate = $service->next_due_date->format('d M Y');
                $nextDueDateObj = $service->calculateNextDueDate(); 
                $newDueDate = $nextDueDateObj ? $nextDueDateObj->format('d M Y') : 'N/A';

                $invoice = null;

                DB::transaction(function () use ($service, $currentDueDate, $newDueDate, &$invoice) {
                    
                    $invoice = new Invoice([
                        'user_id' => $service->user_id,
                        'status' => 'unpaid',
                        'currency' => $service->currency,
                        'subtotal' => $service->price,
                        'discount' => 0,
                        'total' => $service->price,
                        'due_date' => $service->next_due_date,
                    ]);

                    $invoice->sendEmailNotification = false; 
                    
                    $invoice->save(); 

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'service_id' => $service->id,
                        'description' => "Service Renewal - {$service->name} ({$currentDueDate} - {$newDueDate})",
                        'quantity' => 1,
                        'unit_price' => $service->price,
                        'amount' => $service->price,
                    ]);

                    $this->recordSystem('invoice.created', [
                        'service_id' => $service->id,
                        'invoice' => $invoice->toArray(),
                    ], 'cron');
                });

                if ($invoice) {
                    event(new InvoiceEvents\Generated($invoice));

                    Log::info("Automation: Generated recurring invoice {$invoice->invoice_number} for Service ID {$service->id}");
                }
                
            } catch (\Throwable $e) {
                Log::error("Automation: Failed to generate invoice for Service ID {$service->id}. Error: " . $e->getMessage());
            }
        }
    }
}
