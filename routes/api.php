<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BudgetController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\SavingsGoalController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    
    Route::apiResource('budgets', BudgetController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::get('transactions/summary', [TransactionController::class, 'summary'])
        ->middleware('auth:api');
    Route::apiResource('transactions', TransactionController::class)
        ->middleware('auth:api');
    Route::apiResource('savings-goals', SavingsGoalController::class);
    Route::post('savings-goals/{savings_goal}/contribute', [SavingsGoalController::class, 'contribute'])
        ->name('savings-goals.contribute');
}); 