<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deleted_at' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function (Booking $booking) {
            if (auth()->user()) {
                $prices = ApartmentPrice::query()
                    ->select('*')
                    ->selectRaw('(datediff(apartment_prices.end_date, apartment_prices.start_date) + 1) * apartment_prices.price as totalPrice')
                    ->where('apartment_id', $booking->apartment_id)
                    ->get();
                $booking->total_price = $prices->sum('totalPrice');
            }
        });
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
