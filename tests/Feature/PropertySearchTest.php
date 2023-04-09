<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\BedType;
use App\Models\City;
use App\Models\Country;
use App\Models\Facility;
use App\Models\Geoobject;
use App\Models\Property;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class PropertySearchTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_user_can_search_property_by_city(): void
    {
        $city = City::orderBy('id')->first('id');
        $property = Property::factory()->create([
            'city_id' => $city->id
        ]);

        Property::factory()->create([
            'city_id' => City::query()->orderByDesc('id')->value('id')
        ]);

        $response = $this->getJson(route('search.properties', ['city' => $city->id]))
            ->assertJsonCount(1, 'data.properties')
            ->assertJsonFragment(['name' => $property->name]);

        $response->assertStatus(200);
    }

    public function test_user_can_search_property_by_country(): void
    {
        $country = Country::orderBy('id')->first();
        $property = Property::factory()->create([
            'city_id' => $country->cities()->value('id')
        ]);

        Property::factory()->create([
            'city_id' => Country::query()->orderByDesc('id')->first()->cities()->value('id')
        ]);

        $response = $this->getJson(route('search.properties', ['country' => $country->id]))
            ->assertJsonCount(1, 'data.properties')
            ->assertJsonFragment(['name' => $property->name]);

        $response->assertStatus(200);
    }

    public function test_property_search_by_geoobject_returns_correct_results(): void
    {
        $this->markTestSkipped('Please switch to db which support cos function like mysql for this test case');

        $cityId = City::value('id');
        $geoobject = Geoobject::first();
        $propertyNear = Property::factory()->create([
            'city_id' => $cityId,
            'lat' => $geoobject->lat,
            'long' => $geoobject->long,
        ]);
        $propertyFar = Property::factory()->create([
            'city_id' => $cityId,
            'lat' => $geoobject->lat + 10,
            'long' => $geoobject->long - 10,
        ]);

        $response = $this->getJson(route('search.properties', ['geoobject' => $geoobject->id]));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.properties');
        $response->assertJsonFragment(['id' => $propertyNear->id]);
    }

    public function test_property_search_by_capacity_returns_correct_results()
    {
        $apartment1 = Apartment::factory()->create([
            'capacity_adults' => 12,
            'capacity_children' => 2
        ]);
        $apartment2 = Apartment::factory()->create([
            'capacity_adults' => 10,
            'capacity_children' => 4
        ]);

        Property::factory()->create();

        $this->getJson(route('search.properties', ['adults' => 11, 'children' => 2]))
            ->assertJsonCount(1, 'data.properties')
            ->assertJsonFragment([
                'name' => $apartment1->property->name
            ])
            ->assertSuccessful();
    }

    public function test_property_search_by_capacity_returns_only_suitable_apartments()
    {
        $apartment1 = Apartment::factory()->create([
            'capacity_adults' => 12,
            'capacity_children' => 2
        ]);
        $apartment2 = Apartment::factory()->create([
            'capacity_adults' => 10,
            'capacity_children' => 4
        ]);

        $apartment3 = Apartment::factory()->create([
            'capacity_adults' => 4,
            'capacity_children' => 2
        ]);

        Property::factory()->create();

        $this->getJson(route('search.properties', ['adults' => 11, 'children' => 2]))
            ->assertJsonCount(1, 'data.properties.0.apartments')
            ->assertJsonFragment([
                   'name' => $apartment1->name
            ])
            ->assertSuccessfuL();
    }

    public function test_property_search_beds_list_all_cases()
    {
        //--------------
        //No bed room
        //--------------
        $apartment = Apartment::factory()->create([
            'property_id' => Property::factory()->create(['name' => 'This is test']),
            'capacity_adults' => 3,
            'capacity_children' => 4
        ]);

        $roomType = RoomType::first();
        $room = $apartment->rooms()->create([
            'name' => 'delux',
            'room_type_id' => $roomType->id,
        ]);

        $this->getJson(route('search.properties', ['adults' => 2, 'children' => 3]))
            ->assertJsonFragment([
                'name' => 'This is test'
            ])
            ->assertJsonPath('data.properties.0.apartments.0.beds_list', '');

        //--------------
        //One bed room
        //--------------
        $bedType = BedType::latest('id')->first();
        $room->beds()->create([
            'name' => 'Large',
            'bed_type_id' => $bedType->id
        ]);

        $this->getJson(route('search.properties', ['adults' => 2, 'children' => 3]))
            ->assertJsonFragment([
                'name' => 'This is test'
            ])
            ->assertJsonPath('data.properties.0.apartments.0.beds_list', "1 {$bedType->name}");

        //--------------
        //Two bed room with same type
        //--------------
        $room->beds()->create([
            'name' => 'Large again',
            'bed_type_id' => $bedType->id
        ]);
        $besMes = Str::plural($bedType->name, 2);
        $this->getJson(route('search.properties', ['adults' => 2, 'children' => 3]))
            ->assertJsonFragment([
                'name' => 'This is test'
            ])
            ->assertJsonPath('data.properties.0.apartments.0.beds_list', "2 $besMes");

        //--------------
        //Two or more bed room with different type
        //--------------
        $anotherBedType = BedType::oldest('id')->first();
        $room->beds()->create([
            'name' => 'Another Large',
            'bed_type_id' => $anotherBedType->id
        ]);

        $this->getJson(route('search.properties', ['adults' => 2, 'children' => 3]))
            ->assertJsonFragment([
                'name' => 'This is test'
            ])
            ->assertJsonPath('data.properties.0.apartments.0.beds_list', "3 beds (1 $bedType->name, 1 $bedType->name, 1 $anotherBedType->name)");
    }

    public function test_property_search_returns_one_best_apartment_per_property()
    {
        $cityId = City::value('id');
        $property = Property::factory()->create([
            'city_id' => $cityId,
        ]);
        $largeApartment = Apartment::factory()->create([
            'name' => 'Large apartment',
            'property_id' => $property->id,
            'capacity_adults' => 3,
            'capacity_children' => 2,
        ]);
        $midSizeApartment = Apartment::factory()->create([
            'name' => 'Mid size apartment',
            'property_id' => $property->id,
            'capacity_adults' => 2,
            'capacity_children' => 1,
        ]);
        $smallApartment = Apartment::factory()->create([
            'name' => 'Small apartment',
            'property_id' => $property->id,
            'capacity_adults' => 1,
            'capacity_children' => 0,
        ]);

        $response = $this->getJson('/api/search?city=' . $cityId . '&adults=2&children=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.properties.0.apartments');
        $response->assertJsonPath('data.properties.0.apartments.0.name', $midSizeApartment->name);
    }

    public function test_property_search_filters_by_facilities()
    {
        $facilities = Facility::query()->latest()->whereNull('facility_category_id')->take(2)->get();
        $facilitiesIds = $facilities->pluck('id')->toArray();
        //No facilities yet by default
        $property1 = Property::factory()->create();
        $this->getJson(route('search.properties'))
            ->assertJsonFragment([
                'name' => $property1->name
            ])
            ->assertJsonCount(1, 'data.properties')
            ->assertJsonCount(0, 'data.facilities');

        //No facilities yet
        $property1 = Property::factory()->create();
        $this->getJson(route('search.properties', ["facilities[]=$facilitiesIds[0]&facilities[]=$facilitiesIds[1]"]))
            ->assertJsonCount(0, 'data.properties')
            ->assertJsonCount(0, 'data.facilities');

        //Have facilities
        $property1->facilities()->attach($facilitiesIds);
        $property2 = Property::factory()->create();
        $property2->facilities()->attach(Facility::query()->oldest()->whereNull('category_facility_id')->take(2)->pluck('id')->toArray());

        $this->getJson(route('search.properties', ["facilities[]=$facilitiesIds[0]&facilities[]=$facilitiesIds[1]"]))
            ->assertJsonFragment([
                'name' => $property1->name
            ])
            ->assertJsonCount(1, 'data.properties')
            ->assertJsonCount(2, 'data.facilities')
            ->assertJsonFragment([
                $facilities->pluck('name')->first() => 1
            ])
            ->assertJsonFragment([
                $facilities->pluck('name')->last() => 1
            ]);
    }

    public function test_it_can_return_the_most_accurate_apartment_in_property()
    {
        $apartmentHuy = Apartment::factory()->create([
            'capacity_adults' => 4,
            'capacity_children' => 3
        ]);

        $apartment2Huy = Apartment::factory()->create([
            'capacity_adults' => 2,
            'capacity_children' => 2,
            'property_id' => $apartmentHuy->property_id
        ]);

        $apartmentNhung = Apartment::factory()->create([
            'capacity_adults' => 12,
            'capacity_children' => 13
        ]);

        $apartment2Nhung = Apartment::factory()->create([
            'capacity_adults' => 12,
            'capacity_children' => 8,
            'property_id' => $apartmentNhung->property_id
        ]);

        $this->getJson(route('search.properties', ['adults' => 2, 'children' => 2]))
            ->assertJsonCount(2, 'data.properties')
            ->assertJsonCount(1, 'data.properties.0.apartments')
            ->assertJsonPath('data.properties.0.apartments.0.name', $apartment2Nhung->name)
            ->assertJsonCount(1, 'data.properties.1.apartments')
            ->assertJsonPath('data.properties.1.apartments.0.name', $apartment2Huy->name);
    }

    public function test_property_search_filters_by_price()
    {
        $apartment = Apartment::factory()->create();
        $apartment->apartmentPrices()->create([
            'price' => 100,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::tomorrow()
        ]);
        $apartment->apartmentPrices()->create([
            'price' => 150,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::tomorrow()
        ]);
        $apartment->apartmentPrices()->create([
            'price' => 300,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::tomorrow()
        ]);

        $apartment2 = Apartment::factory()->create();
        $apartment2->apartmentPrices()->create([
            'price' => 170,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::tomorrow()
        ]);

        $this->getJson(route('search.properties', ['price_from' => 80, 'price_to' => 160]))
            ->assertJsonCount(1, 'data.properties')
            ->assertJsonCount(1, 'data.properties.0.apartments')
            ->assertJsonCount(2, 'data.properties.0.apartments.0.apartmentPrices.prices')
            ->assertJsonPath('data.properties.0.apartments.0.apartmentPrices.prices.0.price', 150)
            ->assertJsonPath('data.properties.0.apartments.0.apartmentPrices.prices.1.price', 100)
            ->assertJsonPath('data.properties.0.apartments.0.name', $apartment->name);
    }
}
