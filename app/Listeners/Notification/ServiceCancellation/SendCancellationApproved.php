<?php

namespace App\Listeners\Notification\ServiceCancellation;

use App\Events\ServiceCancellation\Approved;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCancellationApproved implements ShouldQueue
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
    public function handle(Approved $event): void
    {
        $cancellation = $event->cancellation;
        $client = $cancellation->user;
        $service = $cancellation->service;

        if (!$client || !$service) {
            return;
        }

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'service_name' => $service->name,
            'cancellation_type' => ucwords(str_replace('_', ' ', $cancellation->type)),
        ];

        NotificationJob::dispatch(
            $client->email,
            'service_cancellation_approved',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
