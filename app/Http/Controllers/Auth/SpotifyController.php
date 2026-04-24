<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SpotifyController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('spotify')
            ->scopes([
                'user-read-email',
                'user-read-private',
                'user-modify-playback-state',
                'user-read-playback-state',
                'user-read-recently-played',
                'user-top-read',
            ])
            ->stateless()
            ->redirect();
    }

    public function callback()
    {
        $spotifyUser = Socialite::driver('spotify')->stateless()->user();

        $user = User::where('spotify_id', $spotifyUser->getId())->first()
            ?? User::where('email', $spotifyUser->getEmail())->first();

        if ($user) {
            $user->update([
                'spotify_id' => $spotifyUser->getId(),
                'spotify_token' => $spotifyUser->token,
                'spotify_refresh_token' => $spotifyUser->refreshToken,
                'spotify_token_expires_at' => now()->addSeconds($spotifyUser->expiresIn),
            ]);
        } else {
            $user = User::create([
                'name' => $spotifyUser->getName(),
                'email' => $spotifyUser->getEmail(),
                'password' => null,
                'avatar' => $spotifyUser->getAvatar(),
                'spotify_id' => $spotifyUser->getId(),
                'spotify_token' => $spotifyUser->token,
                'spotify_refresh_token' => $spotifyUser->refreshToken,
                'spotify_token_expires_at' => now()->addSeconds($spotifyUser->expiresIn),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->route('dashboard');
    }

    public function connect()
    {
        return Socialite::driver('spotify')
            ->scopes([
                'user-read-email',
                'user-read-private',
                'user-modify-playback-state',
                'user-read-playback-state',
                'user-read-recently-played',
                'user-top-read',
            ])
            ->stateless()
            ->redirect();
    }

    public function connectCallback()
    {
        $spotifyUser = Socialite::driver('spotify')->stateless()->user();

        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'spotify_id' => $spotifyUser->getId(),
            'spotify_token' => $spotifyUser->token,
            'spotify_refresh_token' => $spotifyUser->refreshToken,
            'spotify_token_expires_at' => now()->addSeconds($spotifyUser->expiresIn),
        ]);

        return redirect()->route('dashboard')->with('success', 'Spotify connected!');
    }
}
