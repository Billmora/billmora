<?php

namespace App\Listeners\Notification\Service;

use App\Events\Service\ProvisioningUnsuspended;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendServiceProvisioningUnsuspended
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
    public function handle(ProvisioningUnsuspended $event): void
    {
        $service = $event->service;
        $client = $service->user;

        if (!$client) {
            return;
        }

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'service_name' => $service->name,
            'service_url' => route('client.services.show', ['service' => $service->id]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'service_provisioning_unsuspended',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
