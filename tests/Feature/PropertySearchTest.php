<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\City;
use App\Models\Country;
use App\Models\Geoobject;
use App\Models\Property;
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
            ->assertJsonCount(1, 'data')
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
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => $property->name]);

        $response->assertStatus(200);
    }

    public function test_property_search_by_geoobject_returns_correct_results(): void
    {
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
        $response->assertJsonCount(1, 'data');
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
            ->assertJsonCount(1, 'data')
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
            ->assertJsonCount(1, 'data.0.apartments')
            ->assertJsonFragment([
                   'name' => $apartment1->name
            ])
            ->assertSuccessfuL();
    }
}
