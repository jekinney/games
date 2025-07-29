<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to social provider
     */
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle social provider callback
     */
    public function callback(string $provider)
    {
        $this->validateProvider($provider);
        
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Unable to login using ' . ucfirst($provider) . '. Please try again.']);
        }

        // Find or create user
        $user = $this->findOrCreateUser($socialUser, $provider);
        
        // Assign default role if user doesn't have any roles
        if (!$user->hasAnyRole()) {
            $defaultRole = config('roles.default_role', 'player');
            $user->assignRole($defaultRole);
        }

        // Login user
        Auth::login($user, true);

        return redirect()->intended(route('profile'));
    }

    /**
     * Find or create user from social provider
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        // First, try to find user by provider and provider_id
        $user = User::where('provider', $provider)
                   ->where('provider_id', $socialUser->getId())
                   ->first();

        if ($user) {
            // Update user information
            $user->update([
                'name' => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
            ]);
            return $user;
        }

        // Then, try to find user by email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link social account to existing user
            $user->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ]);
            return $user;
        }

        // Create new user
        return User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(), // Social logins are considered verified
        ]);
    }

    /**
     * Validate the provider
     */
    private function validateProvider(string $provider)
    {
        if (!in_array($provider, ['github', 'google', 'facebook'])) {
            abort(404);
        }
    }
}
