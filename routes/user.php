<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\TransferController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\BeneficiaryController;

use App\Http\Controllers\User\AgentsMapController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\SettingsController;
use App\Http\Controllers\User\PaymentMethodController;
// All user routes will share these middlewares
Route::middleware(['auth','check.banned','role:User'])->prefix('user')->name('user.')->group(function () {

    //  Dashboard (show balance)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //  Send money
    Route::get('/transfer', [TransferController::class, 'index'])->name('transfer');
    Route::post('/transfer', [TransferController::class, 'send'])->name('transfer.send');

    //  Transaction history
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    
    //  Agents Map
    Route::get('/agents-map', [AgentsMapController::class, 'index'])->name('agents-map');
    
    //  Reviews
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    
    Route::resource('beneficiary', BeneficiaryController::class);
    Route::post('/beneficiary/add-from-transaction/{transaction}', [BeneficiaryController::class, 'addFromTransaction'])->name('beneficiary.addFromTransaction');


    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::resource('payment-methods', PaymentMethodController::class);
});
