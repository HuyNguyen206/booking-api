<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Responsable\ResponseSuccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $property)
    {
        $property = Property::query()
            ->with('apartments.facilities')
            ->where('id', $property)
            ->when($request->adults && $request->children, function (Builder $builder) use ($request) {
                $builder->withWhereHas('apartments', function ($builder) use ($request) {
                    $builder->where('capacity_adults', '>=', $request->adults)
                            ->where('capacity_children', '>=', $request->children)
                            ->orderBy('capacity_adults')
                            ->orderBy('capacity_children');
                });
            })
        ->first();

        return new ResponseSuccess($property ? PropertyResource::make($property) : []);
    }
}
