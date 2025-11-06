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





    // Handle callback
  public function callback($provider)
{
    $socialUser = Socialite::driver($provider)->user();

    // Check if user already exists
    $user = User::where('email', $socialUser->getEmail())->first();

    if (!$user) {
        // Create a new user if not found
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname(),
            'email' => $socialUser->getEmail(),
            'password' => bcrypt(Str::random(16)),
        ]);
    }

    // Log the user in
Auth::login($user);

if (empty($user->password)) {
    return redirect('/set-password');
}


    return redirect('/dashboard');
}

    public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
}

}
