<?php

namespace App\Jobs\Automation;

use App\Models\Ticket;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CloseInactiveTickets implements ShouldQueue
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
        $closeDays = (int) Billmora::getAutomation('ticket_close_days');

        if ($closeDays <= 0) {
            return;
        }

        $targetDate = now()->startOfDay()->subDays($closeDays)->format('Y-m-d H:i:s');

        Ticket::where('status', '!=', 'closed')
            ->where('updated_at', '<=', $targetDate)
            ->chunk(100, function ($tickets) {
                foreach ($tickets as $ticket) {
                    try {
                        $ticket->update([
                            'status' => 'closed',
                            'closed_at' => now(),
                        ]);

                        $this->recordSystem('ticket.close', [
                            'ticket' => $ticket->toArray(),
                        ], 'cron');

                        Log::info("Automation: Auto-closed Ticket {$ticket->ticket_number} due to inactivity.");

                    } catch (\Throwable $e) {
                        Log::error("Automation: Failed to auto-close Ticket {$ticket->ticket_number}. Error: " . $e->getMessage());
                    }
                }
            });
    }
}
