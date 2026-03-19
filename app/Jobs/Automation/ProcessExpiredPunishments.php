<?php

namespace App\Jobs\Automation;

use App\Models\Punishment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExpiredPunishments implements ShouldQueue
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
     * 
     * Finds all punishments that have passed their expires_at date,
     * restores the user status to active, and deletes the punishment record.
     */
    public function handle(): void
    {
        $expiredPunishments = Punishment::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->with('user')
            ->get();

        foreach ($expiredPunishments as $punishment) {
            if ($punishment->user && $punishment->user->status === $punishment->status) {
                $punishment->user->update(['status' => 'active']);
            }

            $punishment->delete();
        }
    }
}
