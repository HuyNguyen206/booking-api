<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleForPermissions = [
            'Property Owner' => ['properties-manage'],
            'Simple User' => ['bookings-manage']
        ];
        $allRoles = Role::all(['id', 'name'])->pluck('id', 'name');

        foreach ($allRoles as $roleName => $roleId) {
            foreach ($roleForPermissions[$roleName] ?? [] as $permissionName) {
                $permission = Permission::create(['name' => $permissionName]);
                Role::find($roleId)->givePermissionTo($permission);
            }
        }
    }
}
