<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal melakukan autentikasi menggunakan Google. Silakan coba lagi.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $existingUser = User::where('email', $googleUser->getEmail())->first();

            if ($existingUser) {
                $existingUser->update([
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => $existingUser->email_verified_at ?? now(),
                ]);

                $user = $existingUser;
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(24)),
                    'email_verified_at' => now(),
                ]);
            }
        }

        Auth::login($user, remember: true);

        request()->session()->regenerate();

        return $user->is_admin
            ? redirect()->intended(route('admin.dashboard', absolute: false))
            : redirect()->intended(route('dashboard', absolute: false));
    }
}
