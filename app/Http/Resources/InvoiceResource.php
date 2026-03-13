<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'booking_id' => $this->booking_id,
            'booking' => new BookingResource($this->booking),
            'amount_paid' => $this->amount_paid,
            'payment_method' => $this->payment_method,
            'note' => $this->note,
            'status' => $this->status,
            'payment_date' => Carbon::parse($this->payment_date)->toDateString(),
            'created_at' => Carbon::parse($this->created_at)->toDateString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDateString(),
        ];
    }
}