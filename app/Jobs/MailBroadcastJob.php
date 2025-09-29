<?php

namespace App\Jobs;

use App\Facades\Audit;
use App\Mail\BroadcastMail;
use App\Models\MailBroadcast;
use App\Models\User;
use Billmora;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class MailBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public MailBroadcast $broadcast;

    public function __construct(MailBroadcast $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    public function handle(): void
    {
        switch ($this->broadcast->recipient_group) {
            case 'all_users':
                $recipients = User::pluck('email')->toArray();
                break;
            case 'custom_users':
                $recipients = $this->broadcast->recipient_custom ?? [];
                break;
            default:
                $recipients = [];
        }

        $auditEmail = Audit::email(
            null,
            $this->broadcast->recipient_group,
            'broadcast_mail',
            'pending',
        );

        try {
            foreach ($recipients as $recipient) {
                $user = User::where('email', $recipient)->first();

                Mail::to($recipient)
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
