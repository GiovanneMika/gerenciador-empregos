<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\API\ProtocolErrorController;

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

    // Rotas de vagas
    Route::post('/jobs', [JobController::class, 'store']);
    Route::get('/jobs/{jobId}', [JobController::class, 'show'])->whereNumber('jobId');
    Route::post('/jobs/search', [JobController::class, 'search']);
    Route::post('/companies/{companyId}/jobs', [JobController::class, 'listByCompany'])->whereNumber('companyId');
    Route::patch('/jobs/{jobId}', [JobController::class, 'update'])->whereNumber('jobId');
    Route::delete('/jobs/{jobId}', [JobController::class, 'destroy'])->whereNumber('jobId');
    Route::post('/jobs/{jobId}/feedback', [JobController::class, 'sendFeedback'])->whereNumber('jobId');
    Route::post('/jobs/{jobId}', [JobController::class, 'apply'])->whereNumber('jobId');
    Route::get('/users/{userId}/jobs', [JobController::class, 'listUserApplications'])->whereNumber('userId');
    Route::get('/companies/{companyId}/jobs/{jobId}', [JobController::class, 'listJobCandidates'])
        ->whereNumber('companyId')
        ->whereNumber('jobId');

    // Rota de fallback de protocolo
    Route::post('/error', [ProtocolErrorController::class, 'store']);
});