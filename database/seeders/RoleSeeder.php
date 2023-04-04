<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['Administrator', 'Property Owner', 'Simple User'];
        collect($roles)->each(function ($role){
            Role::query()->firstOrcreate([
                'name' => $role
            ]);
        });
    }
}
