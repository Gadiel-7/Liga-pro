<?php

use App\Http\Controllers\StandingsController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;

// --- 1. Rutas de Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas de Verificación de Correo (Hash opcional para evitar bloqueos por parámetros)
Route::get('/email/verify/{id}/{hash?}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');

Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth:sanctum'])
    ->name('verification.send');

// --- 2. Rutas de Consulta Públicas
Route::get('/teams', [TeamController::class, 'index']);
Route::get('/teams/{team}', [TeamController::class, 'show']);

Route::get('/matches', [MatchController::class, 'index']);
Route::get('/matches/{match}', [MatchController::class, 'show']);
Route::get('/standings', [StandingsController::class, 'index']);
Route::get('/players', [PlayerController::class, 'index']);
Route::get('/players/{player}', [PlayerController::class, 'show']);
Route::get('/statistics', [StatisticsController::class, 'index']);
Route::get('/statistics/player/{id}', [StatisticsController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::get('/leagues', [LeagueController::class, 'index']);
Route::get('/leagues/{league}', [LeagueController::class, 'show']);

// --- 3. Rutas Protegidas (Requieren Login y Correo Verificado)
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/my-league', [LeagueController::class, 'myLeague']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/teams', [TeamController::class, 'store']);
        Route::put('/teams/{team}', [TeamController::class, 'update']);
        Route::delete('/teams/{team}', [TeamController::class, 'destroy']);

        Route::post('/players', [PlayerController::class, 'store']);
        Route::put('/players/{player}', [PlayerController::class, 'update']);
        Route::delete('/players/{player}', [PlayerController::class, 'destroy']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::post('/matches/generate', [MatchController::class, 'generate']);
        Route::post('/matches', [MatchController::class, 'store']);
        Route::put('/matches/{match}', [MatchController::class, 'update']);
        Route::post('/matches/{match}/lineup', [MatchController::class, 'storeLineup']);
        Route::delete('/matches/{match}', [MatchController::class, 'destroy']);
        Route::put('/matches/{match}/result', [MatchController::class, 'updateResult']);

        Route::post('/leagues', [LeagueController::class, 'store']);
        Route::put('/leagues/{league}', [LeagueController::class, 'update']);
    });
});
