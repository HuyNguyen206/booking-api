<?php

use App\Http\Controllers\Public;
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
  Route::apiResource('owner/properties', \App\Http\Controllers\Owner\PropertyController::class)->only(['index', 'store']);
  Route::apiResource('user/bookings', \App\Http\Controllers\User\BookingController::class)->only('index');
  Route::apiResource('owner/image-upload/properties', \App\Http\Controllers\Owner\PropertyPhotoController::class)
      ->only(['store'])
      ->name('store', 'image-upload.properties.show');
});
Route::get('search', Public\PropertySearchController::class)->name('search.properties');
Route::get('properties/{property}', Public\PropertyController::class)->name('public.properties.show');
Route::get('apartments/{apartment}', Public\ApartmentController::class)->name('public.apartments.show');
Route::post('auth/register', [App\Http\Controllers\Auth\AuthenticationController::class, 'register'])->name('register');
Route::post('auth/login', [App\Http\Controllers\Auth\AuthenticationController::class, 'login'])->name('login');
