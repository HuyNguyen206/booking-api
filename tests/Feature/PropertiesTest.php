<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class PropertiesTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_property_owner_has_access_to_properties_feature(): void
    {
        $ownerRole = Role::query()->where(['name' => 'Property Owner'])->first();
        $owner = User::factory()->create(['role_id' => $ownerRole->id]);


        $this->actingAs($owner)->getJson(route('properties.index'))->assertSuccessful();
    }

    public function test_user_does_not_has_access_to_properties_feature(): void
    {
        $simpleRole = Role::query()->where(['name' => 'Simple User'])->first();
        $user = User::factory()->create(['role_id' => $simpleRole->id]);


        $this->actingAs($user)->getJson(route('properties.index'))->assertForbidden();
    }
}
