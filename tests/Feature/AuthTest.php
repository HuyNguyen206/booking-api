<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_registration_fail_with_admin_role(): void
    {
        $adminRole = Role::query()->firstOrCreate(['name' => 'Administrator']);
        $this->postJson(route('register'), $user = User::factory()->raw(['role_id' => $adminRole->id]))
        ->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                'role_id'
            ]
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => $user['name'],
            'email' => $user['email']
        ]);
    }

    public function test_registration_succeeds_with_owner_role()
    {
        $ownerRole = Role::query()->firstOrCreate(['name' => 'Property Owner']);
        $this->postJson(route('register'), $user = [
            'name' => 'alan',
            'email' => 'alan@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $ownerRole->id
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'access_token'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $user['name'],
            'email' => $user['email']
        ]);
    }

    public function test_registration_succeds_with_user_role()
    {
        $simpleRole = Role::query()->firstOrCreate(['name' => 'Simple User']);
        $this->postJson(route('register'), $user = [
            'name' => 'alan2',
            'email' => 'alan2@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $simpleRole->id
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'access_token'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $user['name'],
            'email' => $user['email']
        ]);
    }
}
