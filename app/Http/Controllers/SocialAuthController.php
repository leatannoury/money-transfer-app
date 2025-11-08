<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    // Redirect to provider
    public function redirect($provider)
    {
        return Socialite::driver($provider)
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    // Handle callback from provider
   public function callback($provider)
{
    if (request()->has('error')) {
        return redirect('/register')->with('error', 'You cancelled the login or permissions were denied.');
    }

    try {
        $socialUser = Socialite::driver($provider)
            ->stateless() // optional, only if you don't use sessions
            ->fields(['name', 'first_name', 'last_name', 'email'])
            ->scopes(['email'])
            ->user();

        $email = $socialUser->getEmail() ?? $socialUser->getId().'@facebook.local';

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $socialUser->getName()]
        );

        Auth::login($user, true);

        return redirect('/home');

    } catch (\Exception $e) {
        return redirect('/login')->with('error', 'Failed to login with Facebook: '.$e->getMessage());
    }
}


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
