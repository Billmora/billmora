<?php

namespace App\Jobs\Automation;

use App\Models\AuditUser;
use App\Models\User;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessInactiveUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $inactiveDays = (int) Billmora::getAutomation('user_inactive_days');

        if ($inactiveDays <= 0) {
            return; 
        }

        $thresholdDate = now()->subDays($inactiveDays);

        $activeUserIds = AuditUser::where('event', 'account.login')
            ->where('created_at', '>=', $thresholdDate)
            ->pluck('user_id');

        $inactiveUsers = User::where('status', 'active')
            ->where('created_at', '<', $thresholdDate)
            ->whereNotIn('id', $activeUserIds)
            ->get();

        foreach ($inactiveUsers as $user) {
            $user->update(['status' => 'inactive']);
        }
    }
}
