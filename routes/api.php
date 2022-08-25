<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CustomerController;

Route::post('login', [ApiController::class, 'login']);
Route::post('register', [ApiController::class, 'register']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('logout', [ApiController::class, 'logout']);
    Route::get('profile', [ApiController::class, 'profile']);

});
Route::post('AddCustomer', [CustomerController::class, 'register']);