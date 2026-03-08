<?php

namespace App\Listeners\Notification\User;

use App\Events\User\PasswordResetRequested;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPasswordReset implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PasswordResetRequested $event): void
    {
        $user = $event->user;

        $placeholder = [
            'client_name' => $user->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'verify_url' => route('client.password.reset', ['token' => $event->token]),
            'clientarea_url' => config('app.url'),
        ];

        NotificationJob::dispatch(
            $user->email,
            'user_password_reset', 
            $placeholder,
            $user->language,
            $user->id
        );
    }
}
