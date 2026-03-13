<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'booking_number' => $this->booking_number,
            'name' => $this->name,
            'date' => $this->date,
            'contact_number' => $this->contact_number,
            'number_of_guests' => $this->number_of_guests,
            'cnic_number' => $this->cnic_number,
            'passport'=> $this->passport,
            'room_id' => $this->room_id,
            'room' => new RoomResource($this->room),
            'check_in_date' => Carbon::parse($this->check_in_date)->toDateString(),
            'check_out_date' => Carbon::parse($this->check_out_date)->toDateString(),
            'number_of_nights' => $this->number_of_nights,
            'approach_via' => $this->approach_via,
            'price_per_night' => $this->price_per_night,
            'total_amount' => $this->total_amount,
            'discount' => $this->discount,
            'net_total' => $this->net_total,
            'document_image' => $this->document_image,
            'tax_id' => $this->tax_id,
            'tax' => $this->tax,
            'tax_amount' => $this->tax_amount,
            'status' => $this->status,
            'total_paid' => $this->total_paid,
            'is_fully_paid' => $this->is_fully_paid,
            'is_valid_dates' => $this->is_valid_dates,
            'booking_dates' => json_decode($this->booking_dates),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
