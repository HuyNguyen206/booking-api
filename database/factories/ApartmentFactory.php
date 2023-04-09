<?php

namespace Database\Factories;

use App\Models\ApartmentType;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Apartment>
 */
class ApartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->text(20),
            'capacity_adults' => $this->faker->numberBetween(1, 15),
            'capacity_children' => $this->faker->numberBetween(1, 15),
            'property_id' => fn() => Property::factory()->create()->id,
            'apartment_type_id' => fn() => ApartmentType::query()->inRandomOrder()->value('id')
        ];
    }
}
