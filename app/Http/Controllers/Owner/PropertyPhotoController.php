<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Responsable\ResponseError;
use App\Responsable\ResponseSuccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PropertyPhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function changePositionPhoto(Request $request, Media $photo)
    {
        $data = $request->validate([
            'position' => ['required', 'numeric']
        ]);

        abort_if($photo->model->owner_id !== $request->user()->id, 403, 'You dont have permission to change position of this photo');
        if ($photo->position == $data['position']) {
            return new ResponseError(message: 'This photo already have this position', statusCode: 400);
        }
        abort_if($data['position'] < 1 ||  $data['position'] > $photo->model->media()->max('position'), 403, 'The position is invalid');

        $photo->model->media()->where('position', $data['position'])->update(['position' => $photo->position]);
        $photo->update($data);

        return new ResponseSuccess(PropertyResource::make($photo->model));
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

        return new ResponseSuccess(PhotoResource::make($photo));
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
