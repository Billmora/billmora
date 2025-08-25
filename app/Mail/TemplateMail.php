<?php

namespace App\Mail;

use App\Models\MailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Exception;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public MailTemplate $email;
    public array $data;
    public string $lang;

    /**
     * Create a new message instance.
     *
     * @param string $key Template key
     * @param array $data Placeholder replacement
     * @param string $lang language code, default en_US
     */
    public function __construct(string $key, array $data = [], ?string $lang = null)
    {
        $this->lang = $lang ?? config('app.fallback_locale');

        $this->email = MailTemplate::with(['translations'])
            ->where('key', $key)
            ->where('active', true)
            ->first();

        if (!$this->email) {
            throw new Exception("Email template '{$key}' not found or disabled.");
        }

        $translation = $this->email->translations->firstWhere('lang', $lang)
            ?? $this->email->translations->firstWhere('lang', config('app.fallback_locale'));

        if (!$translation) {
            throw new Exception("No translation found for template '{$key}' and fallback 'en_US'.");
        }

        $this->email->subject = $translation->subject;
        $this->email->body = $translation->body;

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
        $envelope = new Envelope(
            subject: $this->placeholder($this->email->subject),
        );

        if (!empty($this->email->cc)) {
            $envelope->cc($this->email->cc);
        }

        if (!empty($this->email->bcc)) {
            $envelope->bcc($this->email->bcc);
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
                'body' => $this->placeholder($this->email->body)
            ],
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
