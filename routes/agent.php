<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;

Route::middleware(['auth'])->prefix('agent')->group(function () {
    Route::get('/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');
    Route::post('/update-profile', [AgentController::class, 'updateProfile'])->name('agent.updateProfile');
    Route::post('/save-location', [AgentController::class, 'saveLocation'])->name('agent.saveLocation');
});
