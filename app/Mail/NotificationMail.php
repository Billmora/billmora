<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Pelago\Emogrifier\CssInliner;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Notification $notification;
    public array $data;

    /**
     * Create a new message instance.
     * 
     * @param \App\Models\Notification $notification
     * @param array $data Placeholder replacement
     */
    public function __construct(Notification $notification, array $data = [])
    {
        $this->notification = $notification;
        $this->data = $data;
    }

    /**
     * Replace placeholders in subject or body.
     */
    private function placeholder(string $field): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            return $this->data[$matches[1]] ?? '';
        }, $field);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->placeholder($this->notification->subject),
        );

        if (!empty($this->notification->cc)) {
            $envelope->cc($this->notification->cc);
        }

        if (!empty($this->notification->bcc)) {
            $envelope->bcc($this->notification->bcc);
        }

        return $envelope;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $rawHtml = view('email::index', [
            'body' => $this->placeholder($this->notification->body)
        ])->render();

        $inlinedHtml = CssInliner::fromHtml($rawHtml)->inlineCss()->render();

        return new Content(
            htmlString: $inlinedHtml
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
