<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

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
                Role::find($roleId)->permissions()->syncWithoutDetaching($permission->id);
            }
        }
    }
}
