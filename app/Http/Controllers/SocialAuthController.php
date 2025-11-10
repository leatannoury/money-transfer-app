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

            // Check if user already exists
            $user = User::where('email', $email)->first();
            $isNewUser = false;

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $email,
                    'status' => 'active',
                ]);
                
                // Mark email as verified (social providers verify emails)
                $user->email_verified_at = now();
                $user->save();
                
                // Assign User role to new users
                $user->assignRole('User');
                $isNewUser = true;
            } else {
                // For existing users, ensure they have a role (fix for users created before roles were assigned)
                if (!$user->hasAnyRole(['Admin', 'Agent', 'User'])) {
                    $user->assignRole('User');
                }
                
                // Ensure status is set for existing users
                if (!$user->status) {
                    $user->status = 'active';
                    $user->save();
                }
                
                // Mark email as verified if not already (social providers verify emails)
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $user->save();
                }
            }

            // Check if user is banned (handle null status)
            if ($user->status && $user->status === 'banned') {
                return redirect()->route('login')->with('error', 'Your account has been banned. Please contact support for assistance.');
            }

            // Log the user in
            Auth::login($user, true);
            
            // Regenerate session for security
            request()->session()->regenerate();

            // Redirect based on role (same logic as AuthenticatedSessionController)
            if ($user->hasRole('Admin')) {
                return redirect()->route('admin.dashboard')->with('success', $isNewUser ? "Registered and logged in with {$provider}!" : "Logged in with {$provider}!");
            }

            if ($user->hasRole('Agent')) {
                return redirect()->route('agent.dashboard')->with('success', $isNewUser ? "Registered and logged in with {$provider}!" : "Logged in with {$provider}!");
            }

            if ($user->hasRole('User')) {
                return redirect()->route('user.dashboard')->with('success', $isNewUser ? "Registered and logged in with {$provider}!" : "Logged in with {$provider}!");
            }

            // Fallback (should not happen, but just in case)
            return redirect()->route('user.dashboard')->with('success', $isNewUser ? "Registered and logged in with {$provider}!" : "Logged in with {$provider}!");
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Social auth error: ' . $e->getMessage(), [
                'provider' => $provider,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')->with('error', 'Login failed. Please try again or contact support if the problem persists.');
        }
    }
}
