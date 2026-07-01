<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'item_type'        => $this->item_type,
            'description'      => $this->description,
            'quantity'         => $this->quantity,
            'billing_type'     => $this->billing_type,
            'billing_period'   => $this->billing_period,
            'billing_interval' => $this->billing_interval,
            'unit_price'       => $this->unit_price,
            'setup_fee'        => $this->setup_fee,
            'amount'           => $this->amount,
        ];
    }
}
