<?php

namespace App\Listeners\Notification\Service;

use App\Events\Service\ProvisioningFailed;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendServiceProvisioningFailed
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
    public function handle(ProvisioningFailed $event): void
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
            'action' => ucfirst($event->action), 
            'error_message' => $event->errorMessage,
            'service_url' => route('client.services.show', ['service' => $service->id]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'service_provisioning_failed',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
