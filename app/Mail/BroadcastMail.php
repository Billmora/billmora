<?php

namespace App\Mail;

use App\Models\MailBroadcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public MailBroadcast $broadcast;
    public array $data;

    /**
     * Create a new message instance.
     */
    /**
     * Create a new message instance.
     *
     * @param \App\Models\MailBroadcast $broadcast
     * @param array $data Placeholder replacement
     */
    public function __construct(MailBroadcast $broadcast, array $data = [])
    {
        $this->broadcast = $broadcast;
        $this->data = $data;
    }


    /**
     * Replace placeholders in subject/body
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
            subject: $this->placeholder($this->broadcast->subject),
        );

        if (!empty($this->broadcast->cc)) {
            $envelope->cc($this->broadcast->cc);
        }

        if (!empty($this->broadcast->bcc)) {
            $envelope->bcc($this->broadcast->bcc);
        }

        return $envelope;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email::layout',
            with: [
                'body' => $this->placeholder($this->broadcast->body),
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
