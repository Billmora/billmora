<?php

namespace App\Listeners\Notification\Domain;

use App\Events\Domain\Registered;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDomainRegistered implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $registrant = $event->registrant;
        $client = $registrant->user;

        if (!$client) {
            return;
        }

        $expiresAt = $registrant->expires_at
            ? $registrant->expires_at->format(Billmora::getGeneral('company_date_format'))
            : 'N/A';

        $placeholder = [
            'client_name'     => $client->fullname,
            'company_name'    => Billmora::getGeneral('company_name'),
            'domain_name'     => $registrant->domain,
            'registration_type' => ucfirst($registrant->registration_type),
            'expires_at'      => $expiresAt,
            'domain_url'      => route('client.domains.show', ['registrant' => $registrant->registrant_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'domain_registered',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
