<?php

namespace App\Jobs;

use App\Facades\Audit;
use App\Mail\NotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $recipient,
        private string $notificationKey,
        private array $data = [],
        private ?string $lang = null,
        private ?int $userId = null
    ) {}

    public function handle(): void
    {
        $auditEmail = Audit::email(
            $this->userId,
            $this->recipient,
            'notification.' . $this->notificationKey,
            'pending',
        );

        try {
            Mail::to($this->recipient)
                ->send(new NotificationMail(
                    $this->notificationKey,
                    $this->data,
                    $this->lang
                ));

            $auditEmail->update([
                'status' => 'sent',
                'properties' => [
                    'key' => $this->notificationKey,
                    'recipient' => $this->recipient,
                    'lang' => $this->lang,
                ],
            ]);
        } catch (\Throwable $e) {
            $auditEmail->update([
                'status' => 'failed',
                'properties' => [
                    'key' => $this->notificationKey,
                    'recipient' => $this->recipient,
                    'error' => $e->getMessage(),
                ],
            ]);
        }
    }
}
