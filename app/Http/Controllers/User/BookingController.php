<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Responsable\ResponseSuccess;

class BookingController extends Controller
{
    public function index()
    {
        $this->authorize('bookings-manage');

        return new ResponseSuccess();
    }
}
