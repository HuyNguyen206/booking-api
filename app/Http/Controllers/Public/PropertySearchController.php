<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Geoobject;
use App\Models\Property;
use App\Responsable\ResponseSuccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PropertySearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $properties = Property::with('city')
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
                         ->where('capacity_children', '>=', $request->children);
                });
            })
            ->get();

        return new ResponseSuccess($properties);
    }
}
