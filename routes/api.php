<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function (){
  Route::get('owner/properties', [\App\Http\Controllers\Owner\PropertyController::class, 'index'])->name('properties.index');
  Route::get('user/bookings', [\App\Http\Controllers\User\BookingController::class, 'index'])->name('bookings.index');
});

Route::post('auth/register', App\Http\Controllers\Auth\RegisterController::class)->name('register');
