<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Facility;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(RoleSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(PermissionSeeder::class);

        $this->call(CountrySeeder::class);
        $this->call(CitySeeder::class);
        $this->call(GeoobjectSeeder::class);

        $this->call(FacilityCategorySeeder::class);
        $this->call(FacilitySeeder::class);

        // Property facilities:
        Facility::create(['name' => 'Family rooms']);
        Facility::create(['name' => 'Free WiFi']);
        Facility::create(['name' => 'Parking']);
        Facility::create(['name' => 'Pets allowed']);
        Facility::create(['name' => 'Swimming pool']);

//        \App\Models\Apartment::factory(10)->create();
    }
}
