<?php

namespace App\Listeners\Notification\User;

use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Auth\Events\Login;

class SendLoginDetected
{
    /**
     * Dispatch a login-detected notification email directly from the controller.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ipAddress
     * @param  string  $userAgent
     * @return void
     */
    public static function dispatch($user, string $ipAddress, string $userAgent): void
    {
        $placeholder = [
            'client_name' => $user->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
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

    /**
     * Handle the Login event (no-op to prevent auto-discovery errors).
     * 
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event): void
    {
        // 
    }
}

