<?php

namespace App\Jobs;

use App\Facades\Audit;
use App\Mail\BroadcastMail;
use App\Models\Broadcast;
use App\Models\User;
use Billmora;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class BroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Broadcast $broadcast;

    public function __construct(Broadcast $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    public function handle(): void
    {
        switch ($this->broadcast->recipient_group) {
            case 'all_users':
                $users = User::lazy();
                break;
            case 'custom_users':
                $userIds = $this->broadcast->recipient_custom ?? [];
                $users = User::whereIn('id', $userIds)->get();
                break;
            default:
                $users = collect();
        }

        $auditEmail = Audit::email(
            null,
            $this->broadcast->recipient_group,
            'broadcast.email',
            'pending',
        );

        try {
            foreach ($users as $user) {
                Mail::to($user->email)
                    ->cc($this->broadcast->cc ?? [])
                    ->bcc($this->broadcast->bcc ?? [])
                    ->send(new BroadcastMail(
                        $this->broadcast,
                        [
                            'client_name' => $user->fullname,
                            'company_name' => Billmora::getGeneral('company_name'),
                        ]
                    ));
            }

            $auditEmail->update([
                'status' => 'sent',
                'properties' => [
                    'id' => $this->broadcast->id,
                    'subject' => $this->broadcast->subject,
                    'recipient_group' => $this->broadcast->recipient_group,
                    'recipient_custom' => $this->broadcast->recipient_custom ?? null,
                    'schedule_at' => $this->broadcast->schedule_at ?? 'N/A',
                ],
            ]);
        } catch (\Throwable $e) {
            $auditEmail->update([
                'status' => 'failed',
                'properties' => [
                    'id' => $this->broadcast->id,
                    'subject' => $this->broadcast->subject,
                    'recipient_group' => $this->broadcast->recipient_group,
                    'recipient_custom' => $this->broadcast->recipient_custom ?? null,
                    'schedule_at' => $this->broadcast->schedule_at ?? 'N/A',
                    'error' => $e->getMessage(),
                ],
            ]);
        }
    }
}
