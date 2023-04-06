<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Facility;
use Tests\TestCase;

class ApartmentShowTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_apartment_show_loads_apartment_with_facilities(): void
    {
      $apartment = Apartment::factory()->create();
      $apartment->facilities()->attach($facilities = Facility::with('facilityCategory')->inRandomOrder()->take(4)->get());
      $expectedResult = $facilities->groupBy('facilityCategory.name')->mapWithKeys(function ($facilities, $group) {
            return [$group => $facilities->pluck('name')];
        })->toArray();

      $this->getJson(route('public.apartments.show', $apartment))->assertJsonPath('data.facilities', $expectedResult);
    }
}
