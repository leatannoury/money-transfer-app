<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
         $request->authenticate();

    $request->session()->regenerate();

    $user = auth()->user();

    // Double-check if user is banned (safety measure, handle null status)
    if ($user && $user->status && $user->status === 'banned') {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('error', 'Your account has been banned. Please contact support for assistance.');
    }

    // Redirect based on role
    if ($user->hasRole('Admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('Agent')) {
        return redirect()->route('agent.dashboard');
    }

    if ($user->hasRole('User')) {
        return redirect()->route('user.dashboard');
    }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
