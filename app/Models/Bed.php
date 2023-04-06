<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;

    public function bedType()
    {
        return $this->belongsTo(BedType::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
