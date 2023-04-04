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
  Route::apiResource('owner/properties', \App\Http\Controllers\Owner\PropertyController::class);
  Route::apiResource('user/bookings', \App\Http\Controllers\User\BookingController::class);
});
Route::get('search', \App\Http\Controllers\Public\PropertySearchController::class)->name('search.properties');
Route::post('auth/register', App\Http\Controllers\Auth\RegisterController::class)->name('register');
