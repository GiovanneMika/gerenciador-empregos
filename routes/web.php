<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitorController;

Route::get('/', function () {
    return view('welcome');
});

// Server Monitor
Route::get('/monitor', [MonitorController::class, 'index']);
Route::get('/monitor/logs', [MonitorController::class, 'logs']);
Route::get('/monitor/users', [MonitorController::class, 'activeUsers']);
Route::get('/monitor/stats', [MonitorController::class, 'stats']);
Route::post('/monitor/clear', [MonitorController::class, 'clearLogs']);
