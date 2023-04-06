<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use App\Responsable\ResponseSuccess;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Apartment $apartment)
    {
        $apartment->load('facilities.facilityCategory');
        $apartment->setAttribute('facilities', $apartment->facilities->groupBy('facilityCategory.name')->mapWithKeys(function ($facilities, $group) {
            return [$group => $facilities->pluck('name')];
        }));

        return new ResponseSuccess(ApartmentResource::make($apartment));
    }
}
