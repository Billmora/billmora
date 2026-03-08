<?php

namespace App\Listeners\Notification\Service;

use App\Events\Service\ProvisioningScaled;
use App\Jobs\NotificationJob;
use App\Services\CurrencyService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendServiceProvisioningScaled implements ShouldQueue
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
    public function handle(ProvisioningScaled $event): void
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
            'recurring_amount' => $this->currencyService->format($service->price, $service->currency),
            'billing_cycle' => $service->cycle_label, 
            
            'service_url' => route('client.services.show', ['service' => $service->id]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'service_provisioning_scaled',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
