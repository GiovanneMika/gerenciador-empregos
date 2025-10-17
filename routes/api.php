<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;

// ROTAS PÚBLICAS (NÃO PRECISAM DE LOGIN)
Route::post('/users', [UserController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);

// ROTAS PROTEGIDAS (PRECISAM DE LOGIN E TOKEN)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});