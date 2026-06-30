<?php

namespace App\Listeners\Notification\User;

use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Request;

class SendLoginDetected implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    protected string $ipAddress;
    protected string $userAgent;

    /**
     * Create the event listener.
     * Capture request context immediately while still in HTTP scope,
     * before the listener is serialized and dispatched to the queue.
     */
    public function __construct()
    {
        $this->ipAddress = Request::ip() ?? '127.0.0.1';
        $this->userAgent = Request::userAgent() ?? 'Unknown Device';
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
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
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
