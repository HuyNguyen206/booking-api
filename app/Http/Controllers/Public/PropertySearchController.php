<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Geoobject;
use App\Models\Property;
use App\Responsable\ResponseSuccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PropertySearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $properties = Property::query()
            ->with([
                'city',
                'apartments.apartmentType',
                'apartments.rooms.beds.bedType',
                'apartments.rooms.roomType',
                'facilities'
            ])
            ->when($request->city, function (Builder $builder) use ($request){
                $builder->where('city_id', $request->city);
            })
            ->when($request->country, function (Builder $builder) use ($request) {
                $builder->whereHas('city', function (Builder $builder) use ($request) {
                    $builder->where('country_id', $request->country);
                });
            })
            ->when($request->geoobject, function (Builder $query) use ($request) {
                $geoobject = Geoobject::find($request->geoobject);
                if ($geoobject) {
                    $condition = "(
                        6371 * acos(
                            cos(radians(" . $geoobject->lat . "))
                            * cos(radians(`lat`))
                            * cos(radians(`long`) - radians(" . $geoobject->long . "))
                            + sin(radians(" . $geoobject->lat . ")) * sin(radians(`lat`))
                        ) < 10
                    )";
                    $query->whereRaw($condition);
                }
            })
            ->when($request->adults && $request->children, function (Builder $query) use ($request) {
                $query->withWhereHas('apartments', function ($query) use ($request) {
                   $query->where('capacity_adults', '>=', $request->adults)
                         ->where('capacity_children', '>=', $request->children)
                         ->orderBy('capacity_adults')
                         ->orderBy('capacity_children')
                         ->take(1)
                   ;
                });
            })
            ->when($request->facilities, function ($query) use ($request) {
                $query->whereHas('facilities', function ($query) use ($request) {
                   $query->whereIn('facilities.id', $request->facilities);
                });
            })
            ->orderByDesc('properties.id')
            ->get();

        // Use collection
        $facilities = $properties->pluck('facilities')->flatten()
            ->groupBy('name')
            ->mapWithKeys(function ($facilities, $facilityName) {
                return [$facilityName => $facilities->count()];
            });

        //Use query DB
//        $facilities = Facility::query()
//            ->withCount(['properties' => function ($property) use ($properties) {
//                $property->whereIn('id', $properties->pluck('id'));
//            }])
//            ->get()
//            ->where('properties_count', '>', 0)
//            ->sortByDesc('properties_count')
//            ->pluck('properties_count', 'name');

        return new ResponseSuccess([
            'properties' => PropertyResource::collection($properties),
            'facilities'=> $facilities
        ]);
    }
}
