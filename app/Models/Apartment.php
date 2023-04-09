<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class Apartment extends Model
{
    use HasFactory, HasEagerLimit;

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function apartmentType()
    {
        return $this->belongsTo(ApartmentType::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class);
    }

    public function apartmentPrices()
    {
        return $this->hasMany(ApartmentPrice::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getBedsInfo()
    {
        $bedTypes = Apartment::query()
            ->join('rooms', 'apartments.id', '=', 'rooms.apartment_id')
            ->join('beds', 'rooms.id', '=', 'beds.room_id')
            ->join('bed_types', 'beds.bed_type_id', '=', 'bed_types.id')
            ->where('apartments.id', $this->id)
            ->get(['bed_types.name', 'bed_types.id']);

        $numberOdBeds = $bedTypes->count();

        return match ($numberOdBeds) {
            0 => '',
            1 => "1 {$bedTypes->implode('name')}",
            2 => $this->getMesForTwoBedOrAbove($bedTypes),
            default => $this->getMesForTwoBedOrAbove($bedTypes)
        };
    }

    private function getMesForTwoBedOrAbove($bedTypes)
    {
        $numberOdBeds = $bedTypes->count();
        if (($numberOdBeds === 2) && $bedTypes->groupBy('id')->count() === 1) {
            return sprintf('%s %s',
                $numberOdBeds,
                Str::plural($bedTypes->first()->name, 2));
        }

        return sprintf('%s beds (%s)',
            $numberOdBeds,
            $bedTypes->map(function ($bedType){
                $bedType->name = "1 $bedType->name";
                return $bedType;
            })->sortByDesc('id')->implode('name', ', '));
    }

    public function scopeWithExtraCondition(
        Builder $query,
        $data = ['capacity_adults', 'capacity_children', 'start_date', 'end_date', 'price_from', 'price_to'],
        $conditions = [
            'hasCapacity' => false,
            'hasDateRange' => false,
            'hasPrice' => false
        ]
    )
    {
        if ($hasCapacity = (isset($conditions['hasCapacity']) && $conditions['hasCapacity'])) {

            $query->where('capacity_adults', '>=', $data['adults'])
                ->where('capacity_children', '>=', $data['children'])
                ->orderBy('capacity_adults')
                ->orderBy('capacity_children');
        }

        if ($hasDateRange = (isset($conditions['hasDateRange']) && $conditions['hasDateRange'])) {
            $query->withWhereHas('apartmentPrices', function ($query) use ($data) {
//                                 ->selectRaw('price * ? as price_with_tax', [1.0825])
                $query->select('*')
                    ->selectRaw("(datediff(apartment_prices.end_date, apartment_prices.start_date) + 1) * apartment_prices.price as totalPrice")
                    ->whereDate('start_date', '>=', $data['start_date'])
                    ->whereDate('end_date', '<=', $data['end_date']);
            });
        }

        if ($hasPrice = (isset($conditions['hasPrice']) && $conditions['hasPrice'])) {
            $query->withWhereHas('apartmentPrices', function ($query) use ($data) {
//                                 ->selectRaw('price * ? as price_with_tax', [1.0825])
                $query->where('price', '>=', $data['price_from'])
                      ->where('price', '<=', $data['price_to'])
                      ->orderByDesc('apartment_prices.id');
            });
        }

    }
}
