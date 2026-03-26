<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BorrowingController;
use App\Http\Controllers\Api\DeviceController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::get('/devices',          [DeviceController::class, 'index']);
    Route::get('/devices/{device}', [DeviceController::class, 'show']);

    Route::get('/borrowings', [BorrowingController::class, 'index']);
    Route::post('/borrow',    [BorrowingController::class, 'borrow']);
    Route::post('/return',    [BorrowingController::class, 'returnDevice']);

    Route::middleware('admin')->group(function () {
        Route::post('/devices',            [DeviceController::class, 'store']);
        Route::put('/devices/{device}',    [DeviceController::class, 'update']);
        Route::delete('/devices/{device}', [DeviceController::class, 'destroy']);
    });
});
