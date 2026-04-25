<?php

namespace App\Listeners\Notification\Domain;

use App\Events\Domain\Expired;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDomainExpired implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the event.
     */
    public function handle(Expired $event): void
    {
        $registrant = $event->registrant;
        $client = $registrant->user;

        if (!$client) {
            return;
        }

        $placeholder = [
            'client_name'  => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'domain_name'  => $registrant->domain,
            'domain_url'   => route('client.registrants.show', ['registrant' => $registrant->registrant_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'domain_expired',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
