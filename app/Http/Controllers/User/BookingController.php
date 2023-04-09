<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Responsable\ResponseSuccess;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('bookings-manage');
        $bookings = $request->user()->bookings()
            ->with('apartment.property')
            ->with('user')
            ->withTrashed()
            ->orderBy('start_date')
            ->get();

        return new ResponseSuccess(BookingResource::collection($bookings));
    }

    public function show(Booking $booking)
    {
        $this->authorize('bookings-manage');
        abort_if($booking->user_id !== auth()->id(), code: 403, message: 'You can not view other\'s booking');

        return new ResponseSuccess(BookingResource::make($booking));
    }

    public function destroy(Request $request, Booking $booking)
    {
        $this->authorize('bookings-manage');
        abort_if($booking->user_id !== auth()->id(), code: 403, message: 'You can not view other\'s booking');

        $booking->delete();

        return new ResponseSuccess(statusCode: 204);
    }

    public function store(StoreBookingRequest $request)
    {
        $booking = $request->user()->bookings()->create($request->validated());

        return new ResponseSuccess(BookingResource::make($booking));
    }
}
