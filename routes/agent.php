<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Agent\TransactionController;

Route::middleware(['auth', 'check.banned','role:Agent'])->prefix('agent')->group(function () {
    Route::get('/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');
    Route::post('/update-profile', [AgentController::class, 'updateProfile'])->name('agent.updateProfile');
    Route::post('/save-location', [AgentController::class, 'saveLocation'])->name('agent.saveLocation');

    // -----------------------------
    // 🔹 Agent Transaction Management
    // -----------------------------
    // Show all transactions assigned to this agent
    Route::get('/transactions', [TransactionController::class, 'index'])->name('agent.transactions');

    // Agent accepts a pending transaction
    Route::post('/transactions/{id}/accept', [TransactionController::class, 'accept'])->name('agent.accept');

    // Agent marks transaction as completed
    Route::post('/transactions/{id}/complete', [TransactionController::class, 'complete'])->name('agent.complete');
});
