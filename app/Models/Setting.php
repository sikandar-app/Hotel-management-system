<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'facebook_link',
        'twitter_link',
        'instagram_link',
        'youtube_link',
        'address',
        'logo',
        'advance_payment_taken_in_percentage',
    ];
}
