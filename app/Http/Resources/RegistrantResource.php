<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'registrant_number' => $this->registrant_number,
            'domain'            => $this->domain,
            'status'            => $this->status,
            'registration_type' => $this->registration_type,
            'years'             => $this->years,
            'price'             => $this->price,
            'currency'          => $this->currency,
            'auto_renew'        => $this->auto_renew,
            'nameservers'       => $this->nameservers,
            'registered_at'     => $this->registered_at,
            'expires_at'        => $this->expires_at,
            'suspended_at'      => $this->suspended_at,
            'cancelled_at'      => $this->cancelled_at,
            'user'              => new UserResource($this->whenLoaded('user')),
            'tld'               => new TldResource($this->whenLoaded('tld')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
