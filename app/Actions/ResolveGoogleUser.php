<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class ResolveGoogleUser
{
    public function resolve(SocialiteUser $googleUser): User
    {
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            return $user;
        }

        $email = $googleUser->getEmail();
        $normalizedEmail = $email !== null ? strtolower($email) : $email;

        $existingUser = User::where('email', $normalizedEmail)->first();

        if ($existingUser) {
            $existingUser->update([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $existingUser->email_verified_at ?? now(),
            ]);

            return $existingUser;
        }

        return User::create([
            'name' => $googleUser->getName(),
            'email' => $normalizedEmail,
            'google_id' => $googleUser->getId(),
            'password' => Str::random(24),
            'email_verified_at' => now(),
        ]);
    }
}
