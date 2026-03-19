<?php

namespace App\Console\Commands;

use Billmora;
use App\Jobs\Automation as AutomationJobs;
use Illuminate\Console\Command;

class RunAutomationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billmora:automation:run {--force : Force run ignoring the time of day setting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run daily billing and service automation tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Billmora Automation...');

        $scheduledTime = Billmora::getAutomation('time_of_day');
        $currentTime = now()->format('H:i');

        if (!$this->option('force') && $currentTime !== $scheduledTime) {
            $this->line("Skipping automation. Scheduled for {$scheduledTime}, current time is {$currentTime}.");
            return Command::SUCCESS;
        }

        $this->info("Executing automation tasks for schedule: {$scheduledTime}");

        $this->comment('1. Dispatching Invoice Generation...');
        AutomationJobs\GenerateRecurringInvoices::dispatch();

        $this->comment('2. Dispatching Invoice Reminders...');
        AutomationJobs\SendInvoiceReminders::dispatch();

        $this->comment('3. Dispatching Service Suspensions...');
        AutomationJobs\ProcessServiceSuspensions::dispatch();

        $this->comment('4. Dispatching Service Terminations...');
        AutomationJobs\ProcessServiceTerminations::dispatch();

        $this->comment('5. Dispatching Auto Cancellations...');
        AutomationJobs\ProcessCancellations::dispatch();

        $this->comment('6. Dispatching Ticket Auto-Close...');
        AutomationJobs\CloseInactiveTickets::dispatch();

        $this->comment('7. Dispatching Data Pruning...');
        AutomationJobs\PruneSystemData::dispatch();

        $this->comment('8. Dispatching User Auto-Inactive...');
        AutomationJobs\ProcessInactiveUsers::dispatch();

        $this->comment('9. Dispatching Expired Punishments...');
        AutomationJobs\ProcessExpiredPunishments::dispatch();

        $this->info('Automation dispatch completed successfully!');
        
        Billmora::setAutomation(['last_run' => now()->toDateTimeString()]);
        
        return Command::SUCCESS;
    }
}
