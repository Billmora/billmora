<?php

namespace App\Listeners\Notification\ServiceCancellation;

use App\Events\ServiceCancellation\Rejected;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCancellationRejected implements ShouldQueue
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
    public function handle(Rejected $event): void
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
            'rejection_note' => $cancellation->rejection_note ?: '-', 
        ];

        NotificationJob::dispatch(
            $client->email,
            'service_cancellation_rejected',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
