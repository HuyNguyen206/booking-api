<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected static function booted()
    {
      self::creating(function (Property $property){
          if (auth()->check()) {
              $property->owner_id = auth()->id();
          }
          if ($property->lat === null && $property->long === null) {
              $fullAddress = $property->getFullAddress();
              $result = app('geocoder')->geocode($fullAddress)->get();
              if ($result->isNotEmpty()) {
                  $coordinates = $result[0]->getCoordinates();
                  $property->lat = $coordinates->getLatitude();
                  $property->long = $coordinates->getLongitude();
              }
          }
      });
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
     public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function getFullAddress()
    {
        return $this->address_street . ', '
            . $this->address_postcode . ', '
            . $this->city->name . ', '
            . $this->city->country->name;
    }
}
