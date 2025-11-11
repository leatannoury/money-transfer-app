<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SocialAuthController;


Route::get('/', function () {
     $user = Auth::user();
     if(!$user){
          return view('welcome');
     }
    if ($user->hasRole('Admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('Agent')) {
        return redirect()->route('agent.dashboard');
    }

    if ($user->hasRole('User')) {
        return redirect()->route('user.dashboard');
    }

  
});

Route::get('/dashboard', function () {
     $user = Auth::user();
     
    if ($user->hasRole('Admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('Agent')) {
        return redirect()->route('agent.dashboard');
    }

    if ($user->hasRole('User')) {
        return redirect()->route('user.dashboard');
    }


})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/user.php';

require __DIR__ . '/agent.php';
require __DIR__ . '/admin.php';

Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect']);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback']);
