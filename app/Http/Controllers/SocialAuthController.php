<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SocialAuthController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            $driver = Socialite::driver($provider)->stateless();

            // Apply provider-specific configuration
            if ($provider === 'facebook') {
                $driver->fields(['name', 'first_name', 'last_name', 'email'])
                       ->scopes(['email']);
            } elseif ($provider === 'google') {
                $driver->scopes(['email', 'profile']);
            }

            // Get user info from the provider
            $socialUser = $driver->user();

            // Some providers might not return an email
            $email = $socialUser->getEmail() ?? ($socialUser->getId() . "@{$provider}.local");

            // Create or find user
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $socialUser->getName()]
            );

            // Log the user in
            Auth::login($user, true);

            return redirect()->route('dashboard')->with('success', "Logged in with {$provider}!");
        } catch (\Exception $e) {
            return redirect()->route('register')->with('error', 'Login failed: ' . $e->getMessage());
        }
    }
}
