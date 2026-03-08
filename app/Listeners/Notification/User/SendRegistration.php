<?php

namespace App\Listeners\Notification\User;

use App\Events\User\Registered;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRegistration implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;
    
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
    public function handle(Registered $event): void
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
            'user_registration', 
            $placeholder,
            $user->language,
            $user->id
        );
    }
}
