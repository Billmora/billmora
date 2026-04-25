<?php

namespace App\Listeners\Notification\Domain;

use App\Events\Domain\RegistrationFailed;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDomainRegistrationFailed implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the event.
     */
    public function handle(RegistrationFailed $event): void
    {
        $registrant = $event->registrant;
        $client = $registrant->user;

        if (!$client) {
            return;
        }

        $placeholder = [
            'client_name'    => $client->fullname,
            'company_name'   => Billmora::getGeneral('company_name'),
            'domain_name'    => $registrant->domain,
            'registration_type' => ucfirst($registrant->registration_type),
            'error_message'  => $event->reason,
        ];

        NotificationJob::dispatch(
            $client->email,
            'domain_registration_failed',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
