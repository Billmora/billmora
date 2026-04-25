<?php

namespace App\Listeners\Notification\Domain;

use App\Events\Domain\Unsuspended;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDomainUnsuspended implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the event.
     */
    public function handle(Unsuspended $event): void
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
            'client_name'  => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'domain_name'  => $registrant->domain,
            'expires_at'   => $expiresAt,
            'domain_url'   => route('client.registrants.show', ['registrant' => $registrant->registrant_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'domain_unsuspended',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
