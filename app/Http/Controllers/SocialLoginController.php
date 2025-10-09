<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    /**
     * Redirect to Microsoft OAuth provider
     */
    public function redirectToMicrosoft()
    {
        return Socialite::driver('azure')->redirect();
    }

    /**
     * Handle Microsoft OAuth callback
     */
    public function handleMicrosoftCallback()
    {
        try {
            $microsoftUser = Socialite::driver('azure')->user();
            
            // Try to find existing user by email
            $user = User::where('email', $microsoftUser->getEmail())->first();
            
            if ($user) {
                // Update existing user with Microsoft info
                $user->update([
                    'microsoft_id' => $microsoftUser->getId(),
                    'avatar' => $microsoftUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $microsoftUser->getName(),
                    'email' => $microsoftUser->getEmail(),
                    'microsoft_id' => $microsoftUser->getId(),
                    'avatar' => $microsoftUser->getAvatar(),
                    'password' => Hash::make(Str::random(24)), // Random password since they login via OAuth
                    'email_verified_at' => now(), // Auto-verify email for Microsoft users
                    'role' => User::ROLE_COMMISSION_MEMBER, // Default role
                ]);
            }
            
            // Log in the user
            Auth::login($user, true);
            
            return redirect()->intended('/home');
            
        } catch (\Exception $e) {
            return redirect('/login')->withErrors([
                'microsoft' => 'Nepodarilo sa prihlásiť cez Microsoft účet. Skúste to znovu.'
            ]);
        }
    }
    
    /**
     * Handle logout and revoke Microsoft token if needed
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}