<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Agent\TransactionController;

Route::middleware(['auth', 'check.banned','role:Agent'])
    ->prefix('agent')
    ->group(function () {

    // -----------------------------
    // 🔹 Agent Dashboard & Profile
    // -----------------------------
    Route::get('/dashboard', [AgentController::class, 'dashboard'])
        ->name('agent.dashboard');

    Route::post('/update-profile', [AgentController::class, 'updateProfile'])
        ->name('agent.updateProfile');

    Route::post('/save-location', [AgentController::class, 'saveLocation'])
        ->name('agent.saveLocation');

    // -----------------------------
    // 🔹 Agent Transaction Management
    // -----------------------------
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('agent.transactions');

    Route::post('/transactions/{id}/accept', [TransactionController::class, 'accept'])
        ->name('agent.accept');

    Route::post('/transactions/{id}/complete', [TransactionController::class, 'complete'])
        ->name('agent.complete');

    Route::post('/transactions/{id}/reject', [TransactionController::class, 'reject'])
        ->name('agent.reject');

    // -----------------------------
    // 🔹 Cash In / Cash Out
    // -----------------------------
    Route::get('/cash', [TransactionController::class, 'cashForm'])
        ->name('agent.cash.form');

    Route::post('/cash-in', [TransactionController::class, 'cashIn'])
        ->name('agent.cash.in');   // 👉 URL: /agent/cash-in

    Route::post('/cash-out', [TransactionController::class, 'cashOut'])
        ->name('agent.cash.out');  // 👉 URL: /agent/cash-out

        Route::post('/notifications/read-all', [AgentController::class, 'markNotificationsRead'])
    ->name('agent.notifications.read');

        Route::delete('/notifications', [AgentController::class, 'clearNotifications'])
            ->name('agent.notifications.clear');
});
