<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    public function facilityCategory()
    {
        return $this->belongsTo(FacilityCategory::class, 'facility_category_id');
    }

    public function apartments()
    {
        return $this->belongsToMany(Apartment::class);
    }

    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }
}
