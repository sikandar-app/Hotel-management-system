<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'contact_number',
        'number_of_guests',
        'cnic_number',
        'passport',
        'room_id',
        'check_in_date',
        'check_out_date',
        'number_of_nights',
        'approach_via',
        'price_per_night',
        'total_amount',
        'discount',
        'net_total',
        'status',
        'document_image',
        'booking_dates',
        'booking_number',
        'tax_id',
        'user_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->invoices()->where('status', 'approved')->sum('amount_paid');
    }

    public function getTotalPaidReservedAttribute()
    {
        return $this->invoices()->sum('amount_paid');
    }

    public function getIsFullyPaidAttribute()
    {
        return doubleval($this->total_paid_reserved) >= doubleval($this->net_total);
    }

    public function getRemainingAmountAttribute()
    {
        return doubleval($this->net_total) - doubleval($this->total_paid);
    }

    public function getIsValidDatesAttribute()
    {
        // Find bookings for the same room that overlap with this booking's dates, excluding itself
        $overlap = self::where('room_id', $this->room_id)
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->whereBetween('check_in_date', [$this->check_in_date, $this->check_out_date])
                    ->orWhereBetween('check_out_date', [$this->check_in_date, $this->check_out_date])
                    ->orWhere(function ($q) {
                        $q->where('check_in_date', '<=', $this->check_in_date)
                            ->where('check_out_date', '>=', $this->check_out_date);
                    });
            })
            ->exists();

        return !$overlap;
    }

    public function getAdvancePaidAttribute()
    {
        $totalPaid = doubleval($this->total_paid);
        $netTotal = doubleval($this->net_total);
        return $netTotal > 0 && ($totalPaid / $netTotal) >= 0.3;
    }

    public function getTaxAmountAttribute()
    {
        $totalAmount = doubleval($this->total_amount);
        $tax = doubleval($this->tax->value);
        $taxType = $this->tax->type;

        if ($taxType === 'percentage') {
            return ($totalAmount * $tax) / 100;
        } else {
            return $tax;
        }
    }
}
