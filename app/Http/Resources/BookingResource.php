<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'apartment_name' => $this->apartment->property->name . ':' . $this->apartment->name,
            'booker_email' => $this->user->email,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'guest_adults' => $this->guest_adults,
            'guest_children' => $this->guest_children,
            'total_price' => $this->total_price,
            'canceled_at' => $this->deleted_at?->format('Y-m-d'),
        ];
    }
}
