<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;

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
    // $code = rand(100000, 999999);
    $code = 111111 ; 
    $expires = Carbon::now()->addMinutes(5);

    Otp::updateOrCreate(
        ['user_id' => $user->id],
        ['code' => $code, 'expires_at' => $expires]
    );

    // Send OTP via Azure SMTP
    // Mail::raw("Your login OTP is: $code", function($message) use ($user) {
    //     $message->to($user->email)
    //             ->subject("Your Login OTP");
    // });

    // Log out temporarily
    Auth::logout();

    // Store user ID in session for OTP verification
    $request->session()->put('otp_user_id', $user->id);

    return redirect()->route('otp.form');

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
