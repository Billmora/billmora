<?php

namespace App\Jobs\Automation;

use App\Events\Invoice as InvoiceEvents;
use App\Models\Invoice;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceReminders implements ShouldQueue
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
        $reminderDays = (int) Billmora::getAutomation('invoice_reminder_days');
        $overdue1Days = (int) Billmora::getAutomation('invoice_overdue_first_days');
        $overdue2Days = (int) Billmora::getAutomation('invoice_overdue_second_days');
        $overdue3Days = (int) Billmora::getAutomation('invoice_overdue_third_days');

        $today = now()->startOfDay();
        
        $targetReminderDate = $today->copy()->addDays($reminderDays)->format('Y-m-d');
        $targetOverdue1Date = $today->copy()->subDays($overdue1Days)->format('Y-m-d');
        $targetOverdue2Date = $today->copy()->subDays($overdue2Days)->format('Y-m-d');
        $targetOverdue3Date = $today->copy()->subDays($overdue3Days)->format('Y-m-d');

        Invoice::unpaid()->chunk(200, function ($invoices) use (
            $reminderDays, $overdue1Days, $overdue2Days, $overdue3Days,
            $targetReminderDate, $targetOverdue1Date, $targetOverdue2Date, $targetOverdue3Date,
        ) {
            foreach ($invoices as $invoice) {
                $dueDateStr = $invoice->due_date->format('Y-m-d');

                if ($reminderDays > 0 && $dueDateStr === $targetReminderDate) {
                    $this->processReminder($invoice, 'reminder');
                } elseif ($overdue1Days > 0 && $dueDateStr === $targetOverdue1Date) {
                    $this->processReminder($invoice, 'overdue_1');
                } elseif ($overdue2Days > 0 && $dueDateStr === $targetOverdue2Date) {
                    $this->processReminder($invoice, 'overdue_2');
                } elseif ($overdue3Days > 0 && $dueDateStr === $targetOverdue3Date) {
                    $this->processReminder($invoice, 'overdue_3');
                }
            }
        });
    }

    protected function processReminder(Invoice $invoice, string $noticeLevel): void
    {
        try {
            event(new InvoiceEvents\Overdue($invoice, $noticeLevel));

            $this->recordSystem('invoice.notice.sent', [
                'invoice_id' => $invoice->id,
                'notice_level' => $noticeLevel,
                'invoice' => $invoice->toArray(),
            ], 'cron');

            Log::info("Automation: Sent {$noticeLevel} notice for Invoice {$invoice->invoice_number}");

        } catch (\Throwable $e) {
            Log::error("Automation: Failed to send {$noticeLevel} for Invoice {$invoice->invoice_number}. Error: " . $e->getMessage());
        }
    }
}
