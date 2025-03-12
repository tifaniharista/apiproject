<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AddressController;
use App\Http\Middleware\ApiAuthMiddleware;

// User Authentication Routes
Route::controller(UserController::class)->group(function () {
    Route::post('/users', 'register');  // Register
    Route::post('/users/login', 'login'); // Login
});

// Protected routes using authentication middleware
Route::middleware(ApiAuthMiddleware::class)->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/users/current', 'profile'); // Get user profile
        Route::patch('/users/current', 'update'); // Update user
        Route::post('/users/logout', 'logout'); // Logout user
    });

    // Contacts
    Route::apiResource('contacts', ContactController::class);

    // Addresses (Nested Resource under Contacts)
    Route::apiResource('contacts.addresses', AddressController::class)
        ->only(['store', 'update', 'destroy']);
});
