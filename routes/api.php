<?php
use App\Http\Controllers\RawDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResponseController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function(){
    Route::post('login', [AuthController::class, 'login']);
});

Route::prefix('prompt')->group(function(){
    Route::post('', [ResponseController::class, 'generateResponse']);
    Route::post('additional', [ResponseController::class, 'additionalPrompt']);
});

Route::prefix('data')->group(function(){
    Route::post('sync', [RawDataController::class, 'sync']);
});
