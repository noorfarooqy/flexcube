<?php

use Illuminate\Support\Facades\Route;
use Noorfarooqy\NoorAuth\Http\Controllers\AuthController;

Route::group(['prefix' => '/api/v1/na/', 'as' => 'na.api.'], function () {

    Route::post('/login', [AuthController::class, 'loginAuth'])->name('login');
    Route::post('/register', [AuthController::class, 'registerAuth'])->name('login');
});
