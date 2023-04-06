<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Facility;
use App\Models\Property;
use Tests\TestCase;

class PropertyShowTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_property_show_loads_property_correctly(): void
    {
        /*
        *  Load correct property
        */
        $property = Property::factory()->create();
        $anotherProperty = Property::factory()->create();

        $response = $this->getJson(route('public.properties.show', $property->id))
        ->assertJsonFragment([
            'name' => $property->name
        ])
            ->assertStatus(200);

      /*
       *  Load correct property with adult/children param
       */
       $apartment1 = Apartment::factory()->create([
            'property_id' => $property->id,
            'capacity_adults' => 3,
            'capacity_children' => 5,
        ]);
        $apartment2 = Apartment::factory()->create([
            'property_id' => $property->id,
            'capacity_adults' => 1,
            'capacity_children' => 2,
        ]);
        $apartment3 = Apartment::factory()->create([
            'property_id' => $property->id,
            'capacity_adults' => 5,
            'capacity_children' => 2,
        ]);
        $apartment4 = Apartment::factory()->create([
            'property_id' => $property->id,
            'capacity_adults' => 3,
            'capacity_children' => 6,
        ]);

        $this->getJson(route('public.properties.show', ['property' => $property->id, 'adults' => 3, 'children' => 5]))
            ->assertJsonFragment([
                'name' => $property->name
            ])
            ->assertJsonFragment([
                'name' => $apartment1->name
            ])
            ->assertJsonFragment([
                'name' => $apartment4->name
            ])
            ->assertJsonCount(2, 'data.apartments')
            ->assertJsonCount(0, 'data.apartments.0.facilities')
            ->assertStatus(200);

         /*
          *  Load correct property with adult/children param and facility
          */
        $facilities = Facility::query()->inRandomOrder()->take(3)->get();
        $apartment1->facilities()->attach($facilities->pluck('id')->toArray());
        $this->getJson(route('public.properties.show', ['property' => $property->id, 'adults' => 3, 'children' => 5]))
            ->assertJsonFragment([
                'name' => $facilities[0]->name
            ])
            ->assertJsonFragment([
                'name' => $facilities[1]->name
            ])
            ->assertJsonFragment([
                'name' => $facilities[2]->name
            ])
            ->assertJsonCount(3, 'data.apartments.0.facilities')->assertStatus(200);
    }
}
