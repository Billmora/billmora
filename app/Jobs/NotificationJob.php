<?php

namespace App\Jobs;

use App\Facades\Audit;
use App\Mail\NotificationMail;
use App\Models\Notification;
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
            $notification = Notification::with(['translations'])
                ->where('key', $this->notificationKey)
                ->first();

            if (!$notification) {
                throw new \Exception("Notification key {$this->notificationKey} does not exist.");
            }

            if (!$notification->is_active) {
                throw new \Exception("Notification {$this->notificationKey} is currently inactive.");
            }

            $lang = $this->lang ?? config('app.fallback_locale');
            $translation = $notification->translations->firstWhere('lang', $lang)
                ?? $notification->translations->firstWhere('lang', config('app.fallback_locale'));

            if (!$translation) {
                throw new \Exception("Translation not found for {$this->notificationKey} in language {$lang}.");
            }

            $notification->subject = $translation->subject;
            $notification->body = $translation->body;

            Mail::to($this->recipient)
                ->send(new NotificationMail($notification, $this->data));

            $auditEmail->update([
                'status' => 'sent',
                'properties' => [
                    'key' => $this->notificationKey,
                    'recipient' => $this->recipient,
                    'lang' => $lang,
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
