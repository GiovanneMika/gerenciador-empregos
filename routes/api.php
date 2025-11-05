<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CompanyController;

// ROTAS PÚBLICAS (NÃO PRECISAM DE LOGIN)
Route::post('/users', [UserController::class, 'store']);
Route::post('/companies', [CompanyController::class, 'store']); // Cadastro de empresa
Route::post('/login', [AuthController::class, 'login']);

// ROTAS PROTEGIDAS (PRECISAM DE LOGIN E TOKEN)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Rotas de usuários
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    
    // Rotas de empresas
    Route::get('/companies/{company}', [CompanyController::class, 'show']);
    Route::patch('/companies/{company}', [CompanyController::class, 'update']);
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy']);
});