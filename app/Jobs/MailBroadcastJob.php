<?php

namespace App\Jobs;

use App\Mail\BroadcastMail;
use App\Models\MailBroadcast;
use App\Models\User;
use Billmora;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class MailBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public MailBroadcast $broadcast;

    public function __construct(MailBroadcast $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    public function handle(): void
    {
        foreach ($this->broadcast->recipients as $email) {
            Mail::to($email)
                ->cc($this->broadcast->cc ?? [])
                ->bcc($this->broadcast->bcc ?? [])
                ->send(new BroadcastMail(
                    $this->broadcast,
                    [
                        'client_name' => 'Billmora', // TODO: will be replaced with name of user.
                        'company_name' => Billmora::getGeneral('company_name'),
                    ]
                ));
        }
    }
}
