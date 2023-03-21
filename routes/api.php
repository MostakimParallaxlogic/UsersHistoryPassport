<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//api for login user using passport
Route::post('/login', [\App\Http\Controllers\AuthApiController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\AuthApiController::class, 'logout'])->middleware("auth:api");
Route::post('/update', [\App\Http\Controllers\UserApiController::class, 'update'])->middleware("auth:api");
