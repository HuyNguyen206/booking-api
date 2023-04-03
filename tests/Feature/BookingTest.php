<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingTest extends TestCase
{
    public function test_user_has_access_to_booking_feature(): void
    {
        $userRole = Role::query()->where(['name' => 'Simple User'])->first();
        $user = User::factory()->create()->assignRole($userRole->id);


        $this->actingAs($user)->getJson(route('bookings.index'))->assertSuccessful();
    }

    public function test_owner_does_not_has_access_to_booking_feature(): void
    {
        $ownerRole = Role::query()->where(['name' => 'Property Owner'])->first();
        $owner = User::factory()->create()->assignRole($ownerRole->id);


        $this->actingAs($owner)->getJson(route('bookings.index'))->assertForbidden();
    }
}
