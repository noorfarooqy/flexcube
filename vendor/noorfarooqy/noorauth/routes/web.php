<?php

use Illuminate\Support\Facades\Route;
use Noorfarooqy\NoorAuth\Http\Controllers\AuthController;


Route::get('/login', [AuthController::class, 'loginView'])->name('login');
Route::get('/register', [AuthController::class, 'registerView'])->name('register');
Route::group(['prefix' => '/v1/na/', 'as' => 'na.'], function () {
});
