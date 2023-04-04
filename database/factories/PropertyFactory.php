<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => fn() => User::factory()->create(['role_id' => Role::query()->firstOrCreate(['name' => 'Property Owner'])->id])->id,
            'name' => $this->faker->words(5, true),
            'city_id' => fn() => City::value('id'),
            'address_street' => $this->faker->address,
            'address_postcode' => $this->faker->postcode,
            'lat' => $this->faker->latitude,
            'long' => $this->faker->longitude
        ];
    }
}
