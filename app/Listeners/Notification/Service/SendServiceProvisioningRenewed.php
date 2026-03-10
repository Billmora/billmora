<?php

namespace App\Listeners\Notification\Service;

use App\Events\Service\ProvisioningRenewed;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendServiceProvisioningRenewed implements ShouldQueue
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
    public function handle(ProvisioningRenewed $event): void
    {
        $service = $event->service;
        $client = $service->user;

        if (!$client) {
            return;
        }

        $nextDueDate = $service->next_due_date ? $service->next_due_date->format(Billmora::getGeneral('company_date_format')) : 'N/A';

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'service_name' => $service->name,
            'next_due_date' => $nextDueDate,
            'service_url' => route('client.services.show', ['service' => $service->service_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'service_provisioning_renewed',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
