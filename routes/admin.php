<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManagerController;


Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
 Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
  Route::get('/manageUsers', [ManagerController::class, 'manageUsers'])->name('users');
  Route::get('/manageAgents',[ManagerController::class,'manageAgents'])->name('manageAgent');
});