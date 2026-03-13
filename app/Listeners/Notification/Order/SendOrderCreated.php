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
    public function handle(Created $event): void
    {
        $order = $event->order;
        $client = $order->user;

        if (!$client) {
            return;
        }
        
        $order->loadMissing('items');

        $itemsData = [];
        foreach ($order->items as $item) {
            $itemsData[] = [
                $item->description,
                $item->quantity,
                $this->currencyService->format($item->amount, $order->currency), 
            ];
        }

        $totalsData = [
            [
                'label' => 'Subtotal', 
                'value' => $this->currencyService->format($order->subtotal ?? $order->total, $order->currency)
            ]
        ];

        if (isset($order->discount) && $order->discount > 0) {
            $totalsData[] = [
                'label' => 'Discount', 
                'value' => $this->currencyService->format($order->discount, $order->currency),
                'is_discount' => true 
            ];
        }

        $totalsData[] = [
            'label' => 'Total', 
            'value' => $this->currencyService->format($order->total, $order->currency),
            'is_highlighted' => true 
        ];

        $orderItemsHtml = view('email::components.items', [
            'headers' => ['Description', 'Qty', 'Amount'], 
            'items' => $itemsData,
            'totals' => $totalsData
        ])->render();

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'order_number' => $order->order_number,
            'order_items_table' => $orderItemsHtml,
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
