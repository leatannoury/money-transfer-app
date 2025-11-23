<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Agent\TransactionController;

Route::middleware(['auth', 'check.banned', 'role:Agent'])
    ->prefix('agent')
    ->name('agent.')
    ->group(function () {

        // -----------------------------
        // 🔹 Agent Dashboard & Profile
        // -----------------------------
        Route::get('/dashboard', [AgentController::class, 'dashboard'])
            ->name('dashboard');

        Route::post('/update-profile', [AgentController::class, 'updateProfile'])
            ->name('updateProfile');

        Route::post('/save-location', [AgentController::class, 'saveLocation'])
            ->name('saveLocation');

        Route::get('/edit-profile', [AgentController::class, 'editProfilePage'])
            ->name('edit.profile');

        // -----------------------------
        // 🔹 Agent Transactions
        // -----------------------------
        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions');

        Route::post('/transactions/{id}/accept', [TransactionController::class, 'accept'])
            ->name('accept');

        Route::post('/transactions/{id}/complete', [TransactionController::class, 'complete'])
            ->name('complete');

        Route::post('/transactions/{id}/reject', [TransactionController::class, 'reject'])
            ->name('reject');

        // -----------------------------
        // 🔹 Cash menu / Cash-In / Cash-Out
        // -----------------------------
        Route::get('/cash', [TransactionController::class, 'cashMenu'])
            ->name('cash.menu');

        // Cash-In
        Route::get('/cash-in', [TransactionController::class, 'cashInForm'])
            ->name('cash.in.form');

        Route::post('/cash-in', [TransactionController::class, 'cashIn'])
            ->name('cash.in');

        // Cash-Out
        Route::get('/cash-out', [TransactionController::class, 'cashOutForm'])
            ->name('cash.out.form');

        Route::post('/cash-out', [TransactionController::class, 'cashOut'])
            ->name('cash.out');

        // -----------------------------
        // 🔹 Notifications
        // -----------------------------
        Route::post('/notifications/read-all', [AgentController::class, 'markNotificationsRead'])
            ->name('notifications.read');

        Route::delete('/notifications', [AgentController::class, 'clearNotifications'])
            ->name('notifications.clear');
    });
