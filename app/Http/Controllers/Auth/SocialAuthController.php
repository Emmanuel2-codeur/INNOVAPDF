<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Échec de la connexion Google', ['error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors([
                'email' => "La connexion avec Google a échoué. Réessaie ou utilise ton e-mail.",
            ]);
        }

        // Compte déjà lié à ce Google ID → connexion directe.
        $user = User::where('google_id', $googleUser->getId())->first();

        // Sinon, un compte existe déjà avec cet e-mail (inscrit via mot de passe) → on le relie.
        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        }

        // Sinon, nouveau compte.
        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Utilisateur',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => Str::random(40), // Jamais utilisé : connexion Google uniquement.
                'email_verified_at' => now(), // E-mail déjà vérifié par Google.
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
