<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Responsable\ResponseSuccess;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $this->authorize('properties-manage');

        return new ResponseSuccess();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'city_id' => 'required|exists:cities,id',
            'address_street' => 'required',
            'address_postcode' => 'required',
        ]);
        $this->authorize('properties-manage');

        return new ResponseSuccess(Property::create($data));
    }
}
