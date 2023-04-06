<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_property_owner_can_add_property()
    {
        $ownerRole = Role::query()->where(['name' => 'Property Owner'])->first();
        $owner = User::factory()->create(['role_id' => $ownerRole->id]);

        $this->actingAs($owner)->postJson(route('properties.store'), [
            'name' => 'My property',
            'city_id' => City::value('id'),
            'address_street' => 'Street Address 1',
            'address_postcode' => '12345',
        ])->assertSuccessful();

        $this->assertDatabaseHas('properties', [
            'name' => 'My property',
            'address_street' => 'Street Address 1',
            'address_postcode' => '12345',
        ]);
    }

    public function test_property_owner_can_add_photo_to_property()
    {
        $this->withoutExceptionHandling();
        Storage::fake('test');
        config(['media-library.disk_name' => 'test']);
        /**
         * @var Property $property
         */
        $admin = User::factory()->admin()->create();
        $property = Property::factory()->create(['owner_id' => $admin->id]);
        $res = $this->actingAs($admin)->postJson(route('image-upload.properties.show'), [
            'photo' => UploadedFile::fake()->image('photo.png'),
            'property_id' => $property->id
        ]);
        $photo = $property->getFirstMedia('photos');
        $res->assertJsonPath('data.filename', $photo->getUrl())
        ->assertJsonPath('data.thumbnail', $photo->getUrl('thumbnail'));
        $this->assertCount(1, $property->getMedia('photos'));

        Storage::disk('test')->assertExists($photo->getPathRelativeToRoot());
        Storage::disk('test')->assertExists($photo->getPathRelativeToRoot('thumbnail'));
    }
}
