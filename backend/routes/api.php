<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BorrowingController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/devices/categories', [DeviceController::class, 'categories']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Devices
    Route::get('/devices',            [DeviceController::class, 'index']);
    Route::get('/devices/{device}',   [DeviceController::class, 'show']);

    // Borrowings
    Route::get('/borrowings',                       [BorrowingController::class, 'index']);
    Route::post('/borrow',                          [BorrowingController::class, 'borrow']);
    Route::post('/return',                          [BorrowingController::class, 'returnDevice']);

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // Device management
        Route::post('/devices',            [DeviceController::class, 'store']);
        Route::put('/devices/{device}',    [DeviceController::class, 'update']);
        Route::delete('/devices/{device}', [DeviceController::class, 'destroy']);

        // Borrowing management
        Route::patch('/borrowings/{borrowing}/due-date', [BorrowingController::class, 'updateDueDate']);
        Route::post('/borrowings/{borrowing}/notify',    [BorrowingController::class, 'notify']);

        // Student list management
        Route::get('/students',                         [StudentController::class, 'index']);
        Route::post('/students',                        [StudentController::class, 'store']);
        Route::put('/students/{student}',               [StudentController::class, 'update']);
        Route::delete('/students/{student}',            [StudentController::class, 'destroy']);
        Route::post('/students/import',                 [StudentController::class, 'import']);
        Route::delete('/students/group/{group}',        [StudentController::class, 'destroyGroup']);
        Route::get('/students/{student}/borrowings',    [StudentController::class, 'borrowingHistory']);
    });
});
