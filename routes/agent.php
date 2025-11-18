<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Agent\TransactionController;

Route::middleware(['auth', 'check.banned', 'role:Agent'])
    ->prefix('agent')
    ->name('agent.')   // 👈 all route names will start with "agent."
    ->group(function () {

        // -----------------------------
        // 🔹 Agent Dashboard & Profile
        // -----------------------------
        Route::get('/dashboard', [AgentController::class, 'dashboard'])
            ->name('dashboard');              // => route name: agent.dashboard

        Route::post('/update-profile', [AgentController::class, 'updateProfile'])
            ->name('updateProfile');          // => agent.updateProfile

        Route::post('/save-location', [AgentController::class, 'saveLocation'])
            ->name('saveLocation');           // => agent.saveLocation

        // -----------------------------
        // 🔹 Agent Transaction Management
        // -----------------------------
        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions');           // => agent.transactions

        Route::post('/transactions/{id}/accept', [TransactionController::class, 'accept'])
            ->name('accept');                 // => agent.accept

        Route::post('/transactions/{id}/complete', [TransactionController::class, 'complete'])
            ->name('complete');               // => agent.complete

        Route::post('/transactions/{id}/reject', [TransactionController::class, 'reject'])
            ->name('reject');                 // => agent.reject

        // -----------------------------
        // 🔹 Cash menu / Cash-In / Cash-Out
        // -----------------------------
        // Cash menu with 2 big buttons
        Route::get('/cash', [TransactionController::class, 'cashMenu'])
            ->name('cash.menu');              // => agent.cash.menu

        // Cash-In
        Route::get('/cash-in', [TransactionController::class, 'cashInForm'])
            ->name('cash.in.form');           // => agent.cash.in.form

        Route::post('/cash-in', [TransactionController::class, 'cashIn'])
            ->name('cash.in');                // => agent.cash.in

        // Cash-Out
        Route::get('/cash-out', [TransactionController::class, 'cashOutForm'])
            ->name('cash.out.form');          // => agent.cash.out.form

        Route::post('/cash-out', [TransactionController::class, 'cashOut'])
            ->name('cash.out');               // => agent.cash.out

Route::get('/edit-profile', [AgentController::class, 'editProfilePage'])
    ->name('edit.profile');        // 🔹 Notifications
        // -----------------------------
        Route::post('/notifications/read-all', [AgentController::class, 'markNotificationsRead'])
    ->name('agent.notifications.read');
     Route::delete('/notifications', [AgentController::class, 'clearNotifications'])
            ->name('agent.notifications.clear');
});
