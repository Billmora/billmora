<?php

namespace App\Listeners\Notification\Service;

use App\Events\Service\ProvisioningActivated;
use App\Jobs\NotificationJob;
use App\Services\CurrencyService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendServiceProvisioningActivated implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Create the event listener.
     */
    public function __construct(private CurrencyService $currencyService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProvisioningActivated $event): void
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
            'recurring_amount' => $this->currencyService->format($service->price, $service->currency),
            'billing_cycle' => $service->cycle_label, 
            'next_due_date' => $nextDueDate,
            'service_url' => route('client.services.show', ['service' => $service->service_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'service_provisioning_activated',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
