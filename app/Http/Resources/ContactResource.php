<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => trim($this->first_name . ' ' . ($this->last_name ?? '')), // Hindari spasi ekstra jika last_name null
            'email'     => $this->email,
            'phone'     => $this->phone,
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
        ];
    }
}
