<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'facebook_link' => $this->facebook_link,
            'twitter_link' => $this->twitter_link,
            'instagram_link' => $this->instagram_link,
            'youtube_link' => $this->youtube_link,
            'logo' => $this->logo,
            'advance_payment_taken_in_percentage' => $this->advance_payment_taken_in_percentage,
            'created_at' => Carbon::parse($this->created_at)->toDateString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDateString(),
        ];
    }
}