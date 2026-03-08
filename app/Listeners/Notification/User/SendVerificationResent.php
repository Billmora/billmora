<?php

namespace App\Listeners\Notification\User;

use App\Events\User\VerificationResent;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendVerificationResent implements ShouldQueue
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
    public function handle(VerificationResent $event): void
    {
        $user = $event->user;

        $placeholder = [
            'client_name' => $user->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'verify_url' => route('client.email.verify', ['token' => $event->token]),
            'clientarea_url' => config('app.url'),
        ];

        NotificationJob::dispatch(
            $user->email,
            'user_resend_verification', 
            $placeholder,
            $user->language,
            $user->id
        );
    }
}
