<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class Property extends Model implements HasMedia
{
    use HasFactory, HasEagerLimit, InteractsWithMedia;

    protected static function booted()
    {
      self::creating(function (Property $property){
          if (auth()->check()) {
              $property->owner_id = auth()->id();
          }
          if ($property->lat === null && $property->long === null && !app()->environment('testing')) {
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

    public function registerMediaConversions(Media|null $media = null): void
    {
      $this->addMediaConversion('thumbnail')
      ->width(800);
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
     public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class);
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Apartment::class);
    }

    public function getFullAddress()
    {
        return $this->address_street . ', '
            . $this->address_postcode . ', '
            . $this->city->name . ', '
            . $this->city->country->name;
    }
}
