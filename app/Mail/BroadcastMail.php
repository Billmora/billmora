<?php

namespace App\Mail;

use App\Models\Broadcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Pelago\Emogrifier\CssInliner;

class BroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public Broadcast $broadcast;
    public array $data;

    /**
     * Create a new message instance.
     */
    /**
     * Create a new message instance.
     *
     * @param \App\Models\Broadcast $broadcast
     * @param array $data Placeholder replacement
     */
    public function __construct(Broadcast $broadcast, array $data = [])
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
        $rawHtml = view('email::index', [
            'body' => $this->placeholder($this->broadcast->body)
        ])->render();

        $inlinedHtml = CssInliner::fromHtml($rawHtml)->inlineCss()->render();

        return new Content(
            htmlString: $inlinedHtml
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
