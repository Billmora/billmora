<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'service_id'    => $this->service_id,
            'registrant_id' => $this->registrant_id,
            'description'   => $this->description,
            'quantity'      => $this->quantity,
            'unit_price'    => $this->unit_price,
            'amount'        => $this->amount,
        ];
    }
}
