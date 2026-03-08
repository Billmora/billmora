<?php

namespace App\Jobs\Automation;

use App\Models\AuditEmail;
use App\Models\AuditSystem;
use App\Models\AuditUser;
use App\Models\TicketAttachment;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PruneSystemData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AuditsSystem;

    public int $timeout = 300;

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
        $emailDays = (int) Billmora::getAutomation('prune_email_history_days');
        $userDays = (int) Billmora::getAutomation('prune_user_activity_days');
        $logDays = (int) Billmora::getAutomation('prune_system_logs_days');
        $attachmentDays = (int) Billmora::getAutomation('prune_ticket_attachments_days');

        if ($logDays > 0) {
            $logTarget = now()->subDays($logDays)->format('Y-m-d H:i:s');
            $systemDeleted = AuditSystem::where('created_at', '<=', $logTarget)->delete();
            
            if ($systemDeleted > 0) {
                Log::info("Automation: Pruned {$systemDeleted} system logs.");
            }
        }
        
        if ($userDays > 0) {
            $userTarget = now()->subDays($userDays)->format('Y-m-d H:i:s');
            $userDeleted = AuditUser::where('created_at', '<=', $userTarget)->delete();
            
            if ($userDeleted > 0) {
                Log::info("Automation: Pruned {$userDeleted} user activity logs.");
            }
        }

        if ($emailDays > 0) {
            $emailTarget = now()->subDays($emailDays)->format('Y-m-d H:i:s');
            $emailDeleted = AuditEmail::where('created_at', '<=', $emailTarget)->delete();
            
            if ($emailDeleted > 0) {
                Log::info("Automation: Pruned {$emailDeleted} email history logs.");
            }
        }

        if ($attachmentDays > 0) {
            $attachmentTarget = now()->subDays($attachmentDays)->format('Y-m-d H:i:s');
            
            TicketAttachment::whereHas('message.ticket', function ($query) use ($attachmentTarget) {
                $query->where('status', 'closed')
                      ->where('closed_at', '<=', $attachmentTarget);
            })->chunk(100, function ($attachments) {
                $deletedCount = 0;
                foreach ($attachments as $attachment) {
                    try {
                        if (Storage::disk('public')->exists($attachment->file_path)) {
                            Storage::disk('public')->delete($attachment->file_path);
                        }
                        
                        $attachment->delete();
                        $deletedCount++;
                    } catch (\Throwable $e) {
                        Log::error("Automation: Failed to delete attachment ID {$attachment->id}. Error: " . $e->getMessage());
                    }
                }

                if ($deletedCount > 0) {
                    Log::info("Automation: Pruned {$deletedCount} old ticket attachments.");
                }
            });
        }
    }
}
