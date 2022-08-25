<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CustomerController;

Route::post('login', [ApiController::class, 'login']);
Route::post('register', [ApiController::class, 'register']);
Route::post('AddCustomer', [CustomerController::class, 'register']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('logout', [ApiController::class, 'logout']);
    Route::get('profile', [ApiController::class, 'profile']);
    Route::get('Day', [CustomerController::class, 'average_registrations_per_day']);
    Route::get('Week', [CustomerController::class, 'average_registrations_this_week']);
    Route::get('month', [CustomerController::class, 'average_registrations_last_month']);
    Route::get('year', [CustomerController::class, 'average_registrations_last_year']);
    Route::get('GetCustomers', [CustomerController::class, 'index']);
    
});
