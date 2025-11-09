<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\TransferController;
use App\Http\Controllers\User\TransactionController;

// All user routes will share these middlewares
Route::middleware(['auth','role:User'])->prefix('user')->name('user.')->group(function () {

    //  Dashboard (show balance)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //  Send money
    Route::get('/transfer', [TransferController::class, 'index'])->name('transfer');
    Route::post('/transfer', [TransferController::class, 'send'])->name('transfer.send');

    //  Transaction history
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
});
