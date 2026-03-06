<?php

namespace App\Listeners\Notification\User;

use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Request;

class SendLoginDetected
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
    public function handle(Login $event): void
    {
        $user = $event->user;

        $placeholder = [
            'client_name' => $user->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent() ?? 'Unknown Device',
            'login_time' => now()->format('d M Y, H:i:s T'),
        ];

        NotificationJob::dispatch(
            $user->email,
            'user_login_detected',
            $placeholder,
            $user->language,
            $user->id
        );
    }
}
