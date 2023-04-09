<?php

namespace Tests\Feature;

use App\Models\Apartment;
use Carbon\Carbon;
use Tests\TestCase;

class ApartmentPriceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_apartment_calculate_price_1_day_correctly(): void
    {
        $this->withoutExceptionHandling();
        $apartment = Apartment::factory()->create();
        $apartment->apartmentPrices()->create([
            'start_date' => today(),
            'end_date' => today(),
            'price' => 100
        ]);

        $this->getJson(route('search.properties', ['start_date' => today()->format('Y-m-d'), 'end_date' => today()->format('Y-m-d')]))
            ->assertJsonPath('data.properties.0.apartments.0.apartmentPrices.totalPrice', 100);

    }

    public function test_apartment_calculate_price_2_day_correctly(): void
    {
        $this->withoutExceptionHandling();
        $apartment = Apartment::factory()->create();
        $apartment->apartmentPrices()->create([
            'start_date' => today(),
            'end_date' => today()->addDays(),
            'price' => 100
        ]);

        $this->getJson(route('search.properties', [
            'start_date' => today()->format('Y-m-d'),
            'end_date' => today()->addDays(2)->format('Y-m-d')
        ]))
            ->assertJsonPath('data.properties.0.apartments.0.apartmentPrices.totalPrice', 200);

    }

    public function test_apartment_calculate_price_several_date_range_correctly(): void
    {
        $this->withoutExceptionHandling();
        $apartment = Apartment::factory()->create();
        //2 days * 100
        $apartment->apartmentPrices()->create([
            'start_date' => today(),
            'end_date' => Carbon::tomorrow(),
            'price' => 100
        ]);
        // 3 days * 200
        $apartment->apartmentPrices()->create([
            'start_date' => Carbon::tomorrow()->addDays(),
            'end_date' => Carbon::tomorrow()->addDays(3),
            'price' => 200
        ]);

        $this->getJson(route('search.properties', [
            'start_date' => today()->format('Y-m-d'),
            'end_date' => today()->addDays(8)->format('Y-m-d')
        ]))
            ->assertJsonPath('data.properties.0.apartments.0.apartmentPrices.totalPrice', 800);
    }
}
