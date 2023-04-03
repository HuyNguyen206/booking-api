<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Responsable\ResponseSuccess;

class PropertyController extends Controller
{
    public function index()
    {
        $this->authorize('properties-manage');

        return new ResponseSuccess();
    }
}
