<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BorrowingController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

// Public: login only
Route::post('/login', [AuthController::class, 'login']);

// Public device filters
Route::get('/devices/categories', [DeviceController::class, 'categories']);
Route::get('/devices/capacities', [DeviceController::class, 'capacities']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Devices — all authenticated users can browse
    Route::get('/devices',          [DeviceController::class, 'index']);
    Route::get('/devices/{device}', [DeviceController::class, 'show']);

    // Borrowings — students see own, admin sees all
    Route::get('/borrowings', [BorrowingController::class, 'index']);

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // CSV export
        Route::get('/devices/export', [DeviceController::class, 'export']);

        // Device management
        Route::post('/devices',            [DeviceController::class, 'store']);
        Route::put('/devices/{device}',    [DeviceController::class, 'update']);
        Route::delete('/devices/{device}', [DeviceController::class, 'destroy']);

        // Borrow / return
        Route::post('/borrow',  [BorrowingController::class, 'borrow']);
        Route::post('/return',  [BorrowingController::class, 'returnDevice']);

        // Borrowing management
        Route::patch('/borrowings/{borrowing}/due-date', [BorrowingController::class, 'updateDueDate']);
        Route::post('/borrowings/{borrowing}/notify',    [BorrowingController::class, 'notify']);

        // User account management
        Route::post('/register',           [AuthController::class, 'register']);
        Route::get('/users',               [AuthController::class, 'users']);
        Route::put('/users/{user}',        [AuthController::class, 'updateUser']);
        Route::delete('/users/{user}',     [AuthController::class, 'deleteUser']);

        // Student list management
        Route::get('/students',                      [StudentController::class, 'index']);
        Route::post('/students',                     [StudentController::class, 'store']);
        Route::put('/students/{student}',            [StudentController::class, 'update']);
        Route::delete('/students/{student}',         [StudentController::class, 'destroy']);
        Route::post('/students/import',              [StudentController::class, 'import']);
        Route::delete('/students/group/{group}',     [StudentController::class, 'destroyGroup']);
        Route::get('/students/{student}/borrowings', [StudentController::class, 'borrowingHistory']);
    });
});