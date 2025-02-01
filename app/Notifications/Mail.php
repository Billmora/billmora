<?php

namespace App\Notifications;

use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail as MailSend;

class Mail
{
    public static function sendTestMail()
    {
        try {
            $email = auth()->user()->email;
            $subject = 'Welcome to Billmora!';
            $body = 'This is a test email to verify the configuration.';

            MailSend::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)
                    ->subject($subject);
            });

            Notification::make()
                ->title('Test email sent successfully!')
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Failed to send test email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}