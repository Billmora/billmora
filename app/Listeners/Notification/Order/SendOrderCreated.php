<?php

namespace App\Listeners\Notification\Order;

use App\Events\Order\Created;
use App\Jobs\NotificationJob;
use App\Services\CurrencyService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderCreated implements ShouldQueue
{
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
    public function handle(Created $event): void
    {
        $order = $event->order;
        $client = $order->user;

        if (!$client) {
            return;
        }
        
        $order->loadMissing('package');

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'order_number' => $order->order_number,
            'package_name' => $order->package->name,
            'order_total' => $this->currencyService->format($order->total, $order->currency),
        ];

        NotificationJob::dispatch(
            $client->email,
            'order_created',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
