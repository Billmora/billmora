<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;
    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(string $key, array $data = [])
    {
        $this->emailTemplate = EmailTemplate::where('key', $key)->where('status', true)->first();

        if (!$this->emailTemplate) {
            throw new \Exception("Email template '{$key}' not found or disabled.");
        }

        $this->data = $data;
    }

    /**
     * Replace placeholders in the email template
     */
    private function placeholder(string $template): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            return $this->data[$matches[1]] ?? '';
        }, $template);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->placeholder($this->emailTemplate->subject),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email::notification',
            with: [
                'body' => $this->placeholder($this->emailTemplate->body)
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
