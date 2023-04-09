<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\FacilityCategory;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FacilityCategory::create(['name' => 'Bedroom']);
        FacilityCategory::create(['name' => 'Kitchen']);
        FacilityCategory::create(['name' => 'Bathroom']);
        FacilityCategory::create(['name' => 'Room Amenities']);
        FacilityCategory::create(['name' => 'General']);
        FacilityCategory::create(['name' => 'Media & Technology']);

        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Bedroom')->value('id'), 'name' => 'Linen']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Bedroom')->value('id'), 'name' => 'Wardrobe or closet']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Kitchen')->value('id'), 'name' => 'Electric kettle']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Kitchen')->value('id'), 'name' => 'Microwave']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Room Amenities')->value('id'), 'name' => 'Washing mashine']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Room Amenities')->value('id'), 'name' => 'Private bathroom']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Room Amenities')->value('id'), 'name' => 'Shower']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Room Amenities')->value('id'), 'name' => 'Towels']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Room Amenities')->value('id'), 'name' => 'Drying rack for clothing']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'General')->value('id'), 'name' => 'No smoking']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'General')->value('id'), 'name' => 'Fan']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Media & Technology')->value('id'), 'name' => 'WiFi']);
        Facility::create(['facility_category_id' => FacilityCategory::where('name', 'Media & Technology')->value('id'), 'name' => 'TV']);
    }
}
