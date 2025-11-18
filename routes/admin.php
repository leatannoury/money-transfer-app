<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManageUserController;
use App\Http\Controllers\Admin\ManageAgentController;
use App\Http\Controllers\Admin\ManageTransactionController;
use App\Http\Controllers\Admin\ReviewManagementController;
use App\Http\Controllers\Admin\FeesController;
use App\Http\Controllers\Admin\SuspiciousController;
use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\RefundRequestController;



Route::middleware(['auth','check.banned','role:Admin'])->prefix('admin')->name('admin.')->group(function () {
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/hourly-fees', [DashboardController::class, 'getHourlyFees'])->name('dashboard.hourlyFees');
  Route::get('/manageUsers', [ManageUserController::class, 'manageUsers'])->name('users');

  Route::post('/users/{id}/ban', [ManageUserController::class, 'banUser'])->name('users.ban');
  Route::post('/users/{id}/activate', [ManageUserController::class, 'activateUser'])->name('users.activateUser');
  Route::get('/users/add', [ManageUserController::class, 'addUserForm'])->name('users.add');
  Route::post('/users/store', [ManageUserController::class, 'storeUser'])->name('users.store');
  Route::get('/users/{id}/edit', [ManageUserController::class, 'editUserForm'])->name('users.edit');
Route::post('/users/{id}/update', [ManageUserController::class, 'updateUser'])->name('users.update');


  Route::get('/manageAgents',[ManageAgentController::class,'manageAgents'])->name('agents');
    Route::post('/agents/{id}/ban', [ManageAgentController::class, 'banAgent'])->name('agents.ban');
  Route::post('/agents/{id}/activate', [ManageAgentController::class, 'activateAgent'])->name('agents.activateAgent');
  Route::get('/agents/add', [ManageAgentController::class, 'addAgentForm'])->name('agents.add');
  Route::post('/agents/store', [ManageAgentController::class, 'storeAgent'])->name('agents.store');
  Route::get('/agents/{id}/edit', [ManageAgentController::class, 'editAgentForm'])->name('agents.edit');
Route::post('/agents/{id}/update', [ManageAgentController::class, 'updateAgent'])->name('agents.update');
  
  // Agent Requests
  Route::get('/agents/requests', [ManageAgentController::class, 'agentRequests'])->name('agents.requests');
  Route::post('/agents/requests/{id}/approve', [ManageAgentController::class, 'approveAgentRequest'])->name('agents.requests.approve');
  Route::post('/agents/requests/{id}/reject', [ManageAgentController::class, 'rejectAgentRequest'])->name('agents.requests.reject');


Route::get('/manageTransactions',[ManageTransactionController::class,'manageTransaction'])->name("transactions");
Route::get('/reviews', [ReviewManagementController::class, 'index'])->name('reviews.index');
Route::post('/reviews/{review}/approve', [ReviewManagementController::class, 'approve'])->name('reviews.approve');
Route::delete('/reviews/{review}', [ReviewManagementController::class, 'destroy'])->name('reviews.destroy');


Route::get('/fees', [FeesController::class, 'index'])->name('fees');
Route::post('/fees/update', [FeesController::class, 'update'])->name('fees.update');

Route::get('/transactions/suspicious', [ManageTransactionController::class, 'suspiciousTransactions'])->name('transactions.suspicious');
    Route::post('/transactions/{id}/accept', [SuspiciousController::class, 'acceptSuspicious'])->name('transactions.accept');
    Route::post('/transactions/{id}/reject', [SuspiciousController::class, 'rejectSuspicious'])->name('transactions.reject');
    
    Route::post('/notifications/read-all', [AdminNotificationController::class, 'markRead'])
        ->name('notifications.read');
    Route::delete('/notifications', [AdminNotificationController::class, 'clear'])
        ->name('notifications.clear');
        
       Route::get('/chats', [AdminChatController::class, 'index'])
        ->name('chat.index');

    Route::get('/chats/{chatRoom}', [AdminChatController::class, 'show'])
        ->name('chat.show');

    Route::post('/chats/{chatRoom}/send', [AdminChatController::class, 'sendMessage'])
        ->name('chat.send');

    Route::post('/chats/{chatRoom}/close', [AdminChatController::class, 'close'])
        ->name('chat.close');
         Route::get('/refunds', [RefundRequestController::class, 'index'])->name('refunds.index');
    Route::post('/refunds/{refundRequest}/decision', [RefundRequestController::class, 'decide'])->name('refunds.decide');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManageUserController;
use App\Http\Controllers\Admin\ManageAgentController;
use App\Http\Controllers\Admin\ManageTransactionController;
use App\Http\Controllers\Admin\ReviewManagementController;
use App\Http\Controllers\Admin\FeesController;
use App\Http\Controllers\Admin\SuspiciousController;
use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AdminNotificationController;



Route::middleware(['auth','check.banned','role:Admin'])->prefix('admin')->name('admin.')->group(function () {
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/hourly-fees', [DashboardController::class, 'getHourlyFees'])->name('dashboard.hourlyFees');
  Route::get('/manageUsers', [ManageUserController::class, 'manageUsers'])->name('users');

  Route::post('/users/{id}/ban', [ManageUserController::class, 'banUser'])->name('users.ban');
  Route::post('/users/{id}/activate', [ManageUserController::class, 'activateUser'])->name('users.activateUser');
  Route::get('/users/add', [ManageUserController::class, 'addUserForm'])->name('users.add');
  Route::post('/users/store', [ManageUserController::class, 'storeUser'])->name('users.store');
  Route::get('/users/{id}/edit', [ManageUserController::class, 'editUserForm'])->name('users.edit');
Route::post('/users/{id}/update', [ManageUserController::class, 'updateUser'])->name('users.update');


  Route::get('/manageAgents',[ManageAgentController::class,'manageAgents'])->name('agents');
    Route::post('/agents/{id}/ban', [ManageAgentController::class, 'banAgent'])->name('agents.ban');
  Route::post('/agents/{id}/activate', [ManageAgentController::class, 'activateAgent'])->name('agents.activateAgent');
  Route::get('/agents/add', [ManageAgentController::class, 'addAgentForm'])->name('agents.add');
  Route::post('/agents/store', [ManageAgentController::class, 'storeAgent'])->name('agents.store');
  Route::get('/agents/{id}/edit', [ManageAgentController::class, 'editAgentForm'])->name('agents.edit');
Route::post('/agents/{id}/update', [ManageAgentController::class, 'updateAgent'])->name('agents.update');
  
  // Agent Requests
  Route::get('/agents/requests', [ManageAgentController::class, 'agentRequests'])->name('agents.requests');
  Route::post('/agents/requests/{id}/approve', [ManageAgentController::class, 'approveAgentRequest'])->name('agents.requests.approve');
  Route::post('/agents/requests/{id}/reject', [ManageAgentController::class, 'rejectAgentRequest'])->name('agents.requests.reject');


Route::get('/manageTransactions',[ManageTransactionController::class,'manageTransaction'])->name("transactions");
Route::get('/reviews', [ReviewManagementController::class, 'index'])->name('reviews.index');
Route::post('/reviews/{review}/approve', [ReviewManagementController::class, 'approve'])->name('reviews.approve');
Route::delete('/reviews/{review}', [ReviewManagementController::class, 'destroy'])->name('reviews.destroy');


Route::get('/fees', [FeesController::class, 'index'])->name('fees');
Route::post('/fees/update', [FeesController::class, 'update'])->name('fees.update');

Route::get('/transactions/suspicious', [ManageTransactionController::class, 'suspiciousTransactions'])->name('transactions.suspicious');
    Route::post('/transactions/{id}/accept', [SuspiciousController::class, 'acceptSuspicious'])->name('transactions.accept');
    Route::post('/transactions/{id}/reject', [SuspiciousController::class, 'rejectSuspicious'])->name('transactions.reject');
    
    Route::delete('/notifications', [AdminNotificationController::class, 'clear'])
        ->name('notifications.clear');
        
       Route::get('/chats', [AdminChatController::class, 'index'])
        ->name('chat.index');

    Route::get('/chats/{chatRoom}', [AdminChatController::class, 'show'])
        ->name('chat.show');

    Route::post('/chats/{chatRoom}/send', [AdminChatController::class, 'sendMessage'])
        ->name('chat.send');

    Route::post('/chats/{chatRoom}/close', [AdminChatController::class, 'close'])
        ->name('chat.close');


    
    Route::get('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

 Route::get('/refunds', [RefundRequestController::class, 'index'])->name('refunds.index');
    Route::post('/refunds/{refundRequest}/decision', [RefundRequestController::class, 'decide'])->name('refunds.decide');

});

});