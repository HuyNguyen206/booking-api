<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class BookingTest extends TestCase
{
    public function test_user_has_access_to_booking_feature(): void
    {
        $userRole = Role::query()->where(['name' => 'Simple User'])->first();
        $user = User::factory()->create(['role_id' => $userRole->id]);


        $this->actingAs($user)->getJson(route('bookings.index'))->assertSuccessful();
    }

    public function test_owner_does_not_has_access_to_booking_feature(): void
    {
        $ownerRole = Role::query()->where(['name' => 'Property Owner'])->first();
        $owner = User::factory()->create(['role_id' => $ownerRole->id]);


        $this->actingAs($owner)->getJson(route('bookings.index'))->assertForbidden();
    }

    public function test_user_can_book_apartment_successfully_but_not_twice()
    {
        $apartment = Apartment::factory()->create([
            'capacity_adults' => 3,
            'capacity_children' => 2
        ]);
        $this->assertCount(0, $apartment->bookings);
        $this->assertDatabaseMissing('bookings', [
            'apartment_id' => $apartment->id,
            'guest_adults' => 2,
            'guest_children' => 1,
        ]);

        $this->actingAs(User::factory()->admin()->create())->postJson(route('bookings.store'), [
            'apartment_id' => $apartment->id,
            'guest_adults' => 2,
            'guest_children' => 1,
            'start_date' => today()->format('Y-m-d'),
            'end_date' => today()->addDays()->format('Y-m-d')
        ])
        ->assertSuccessful();

        $this->assertCount(1, $apartment->fresh()->bookings);
        $this->assertDatabaseHas('bookings', [
            'apartment_id' => $apartment->id,
            'guest_adults' => 2,
            'guest_children' => 1,
        ]);

        $this->actingAs(User::factory()->admin()->create())->postJson(route('bookings.store'), [
            'apartment_id' => $apartment->id,
            'guest_adults' => 2,
            'guest_children' => 1,
            'start_date' => today()->format('Y-m-d'),
            'end_date' => today()->addDays()->format('Y-m-d')
        ])
            ->assertStatus(422);
    }

    public function test_user_can_get_only_their_bookings()
    {
        $apartment = Apartment::factory()->create([
            'capacity_adults' => 3,
            'capacity_children' => 2
        ]);

        $this->actingAs($user = User::factory()->admin()->create())->postJson(route('bookings.store'), [
            'apartment_id' => $apartment->id,
            'guest_adults' => 2,
            'guest_children' => 1,
            'start_date' => today()->format('Y-m-d'),
            'end_date' => today()->addDays()->format('Y-m-d')
        ])
            ->assertSuccessful();

        $this->actingAs(User::factory()->admin()->create())->postJson(route('bookings.store'), [
            'apartment_id' => $apartment->id,
            'guest_adults' => 2,
            'guest_children' => 1,
            'start_date' => today()->addDays(2)->format('Y-m-d'),
            'end_date' => today()->addDays(5)->format('Y-m-d')
        ])
            ->assertSuccessful();

        $this->actingAs($user)->getJson(route('bookings.index'))
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.booker_email', $user->email);

        $this->actingAs($user)->getJson(route('bookings.show', 1))
            ->assertSuccessful();

        $this->actingAs($user)->getJson(route('bookings.show', 2))
            ->assertStatus(403);
    }

    public function test_user_can_cancel_their_booking_but_still_view_it()
    {
        $apartment = Apartment::factory()->create([
            'capacity_adults' => 3,
            'capacity_children' => 2
        ]);

        $this->actingAs($user = User::factory()->admin()->create())->postJson(route('bookings.store'), [
            'apartment_id' => $apartment->id,
            'guest_adults' => 2,
            'guest_children' => 1,
            'start_date' => today()->format('Y-m-d'),
            'end_date' => today()->addDays()->format('Y-m-d')
        ])
            ->assertSuccessful();

        $this->actingAs($user)->postJson(route('bookings.store'), [
            'apartment_id' => $apartment->id,
            'guest_adults' => 2,
            'guest_children' => 1,
            'start_date' => today()->addDays(2)->format('Y-m-d'),
            'end_date' => today()->addDays(5)->format('Y-m-d')
        ])
            ->assertSuccessful();

        $this->actingAs($user)->deleteJson(route('bookings.destroy', 1))
            ->assertStatus(204);

          $this->actingAs($user)->getJson(route('bookings.index'))
            ->assertJsonCount(2, 'data.data')
            ->assertJsonPath('data.data.0.canceled_at', now()->format('Y-m-d'));

    }
}
