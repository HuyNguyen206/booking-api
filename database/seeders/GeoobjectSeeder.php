<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Geoobject;
use Illuminate\Database\Seeder;

class GeoobjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::all();
        Geoobject::create([
            'city_id' =>  $cities->sortByDesc('id')->first()->id,
            'name' => 'Statue of Liberty',
            'lat' => 40.689247,
            'long' => -74.044502
        ]);

        Geoobject::create([
            'city_id' =>  $cities->sortByDesc('id')->last()->id,
            'name' => 'Big Ben',
            'lat' => 51.500729,
            'long' => -0.124625
        ]);
    }
}
