<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\ApiController;

Route::post('/register', [ApiController::class, 'register']);
Route::post('/login', [ApiController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [ApiController::class, 'logout']);
    Route::post('/profile', [ApiController::class, 'profile']);
    Route::post('/profile/update', [ApiController::class, 'profileUpdate']);
    Route::post('/profile/password', [ApiController::class, 'password']);
    Route::post('/contacts', [ApiController::class, 'myContacts']);
    Route::post('/contacts/add', [ApiController::class, 'addContact']);
    Route::post('/users/all', [ApiController::class, 'getUsers']);
    Route::post('/users/contacts/all', [ApiController::class, 'getUserContacts']);
});
