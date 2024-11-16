<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BudgetController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::apiResource('budgets', BudgetController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('transactions', TransactionController::class);
        Route::get('transactions/summary', [TransactionController::class, 'summary']);
    });
}); 