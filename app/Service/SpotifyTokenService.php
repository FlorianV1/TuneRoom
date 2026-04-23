<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class SpotifyTokenService
{
    /**
     * Ensure the user has a valid Spotify token.
     * If expired, refresh it automatically.
     * Returns the valid access token.
     */
    public function getValidToken(User $user): ?string
    {
        if (!$user->hasSpotifyConnected()) {
            return null;
        }

        if ($user->spotifyTokenIsExpired()) {
            return $this->refresh($user);
        }

        return $user->spotify_token;
    }

    /**
     * Refresh the Spotify access token using the refresh token.
     */
    public function refresh(User $user): ?string
    {
        $response = Http::asForm()
            ->withBasicAuth(
                config('services.spotify.client_id'),
                config('services.spotify.client_secret')
            )
            ->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->spotify_refresh_token,
            ]);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        $user->update([
            'spotify_token' => $data['access_token'],
            'spotify_token_expires_at' => now()->addSeconds($data['expires_in']),
            // Spotify sometimes returns a new refresh token too
            'spotify_refresh_token' => $data['refresh_token'] ?? $user->spotify_refresh_token,
        ]);

        return $data['access_token'];
    }
}
