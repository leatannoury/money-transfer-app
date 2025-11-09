<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManageUserController;
use App\Http\Controllers\Admin\ManageAgentController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
  Route::get('/manageUsers', [ManageUserController::class, 'manageUsers'])->name('users');

  Route::post('/users/{id}/ban', [ManageUserController::class, 'banUser'])->name('users.ban');
  Route::post('/users/{id}/activate', [ManageUserController::class, 'activateUser'])->name('users.activateUser');
  Route::get('/users/add', [ManageUserController::class, 'addUserForm'])->name('users.add');
  Route::post('/users/store', [ManageUserController::class, 'storeUser'])->name('users.store');
  Route::get('/users/{id}/edit', [ManageUserController::class, 'editUserForm'])->name('users.edit');
Route::post('/users/{id}/update', [ManageUserController::class, 'updateUser'])->name('users.update');


  Route::get('/manageAgents',[ManageAgentController::class,'manageAgents'])->name('manageAgent');
});