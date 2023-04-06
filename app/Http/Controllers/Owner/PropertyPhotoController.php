<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Responsable\ResponseSuccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyPhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:5000'],
            'property_id' => ['required', Rule::exists('properties', 'id')]
        ]);
        $property = Property::find($request->property_id);
        $this->authorize('properties-manage');
        abort_if($request->user()->id !== $property->owner_id, 403,'You can not upload to other');

        $photo = $property->addMediaFromRequest('photo')
            ->toMediaCollection('photos');
        $medias = $property->media;
        if ($medias->count() >= 2) {
            $photo->update(['position' => $medias->max('position') + 1]);
        }

        return new ResponseSuccess([
            'filename' => $photo->getUrl(),
            'thumbnail' => $photo->getUrl('thumbnail')
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
