<?php

namespace App\Http\Controllers\Auth;

use App\Actions\ResolveGoogleUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(ResolveGoogleUser $resolveGoogleUser): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal melakukan autentikasi menggunakan Google. Silakan coba lagi.',
            ]);
        }

        $user = $resolveGoogleUser->resolve($googleUser);

        Auth::login($user, remember: true);

        request()->session()->regenerate();

        return $user->is_admin
            ? redirect()->intended(route('admin.dashboard', absolute: false))
            : redirect()->intended(route('dashboard', absolute: false));
    }
}
