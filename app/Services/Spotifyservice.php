<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class SpotifyService
{
    public function __construct(
        private SpotifyTokenService $tokens
    )
    {
    }

    /**
     * Search for tracks on Spotify.
     * Returns a clean array of track data.
     */
    public function searchTracks(User $user, string $query, int $limit = 10): array
    {
        $token = $this->tokens->getValidToken($user);

        if (!$token) return [];

        $response = Http::withToken($token)
            ->get('https://api.spotify.com/v1/search', [
                'q' => $query,
                'type' => 'track',
                'limit' => $limit,
            ]);

        if ($response->failed()) return [];

        return collect($response->json('tracks.items', []))
            ->map(fn($track) => $this->formatTrack($track))
            ->toArray();
    }

    /**
     * Get a single track by Spotify track ID or URI.
     */
    public function getTrack(User $user, string $trackId): ?array
    {
        // Handle full URIs like spotify:track:xxx or URLs
        $trackId = $this->extractTrackId($trackId);

        $token = $this->tokens->getValidToken($user);
        if (!$token) return null;

        $response = Http::withToken($token)
            ->get("https://api.spotify.com/v1/tracks/{$trackId}");

        if ($response->failed()) return null;

        return $this->formatTrack($response->json());
    }

    /**
     * Tell Spotify to start playing a track at a specific position.
     * Called on each room member's behalf when sync broadcasts.
     */
    public function play(User $user, string $spotifyTrackId, int $positionMs = 0): bool
    {
        $token = $this->tokens->getValidToken($user);
        if (!$token) return false;

        $response = Http::withToken($token)
            ->put('https://api.spotify.com/v1/me/player/play', [
                'uris' => ["spotify:track:{$spotifyTrackId}"],
                'position_ms' => $positionMs,
            ]);

        return $response->successful() || $response->status() === 204;
    }

    /**
     * Pause playback on the user's Spotify.
     */
    public function pause(User $user): bool
    {
        $token = $this->tokens->getValidToken($user);
        if (!$token) return false;

        $response = Http::withToken($token)
            ->put('https://api.spotify.com/v1/me/player/pause');

        return $response->successful() || $response->status() === 204;
    }

    /**
     * Seek to a position in the current track.
     */
    public function seek(User $user, int $positionMs): bool
    {
        $token = $this->tokens->getValidToken($user);
        if (!$token) return false;

        $response = Http::withToken($token)
            ->put('https://api.spotify.com/v1/me/player/seek', [
                'position_ms' => $positionMs,
            ]);

        return $response->successful() || $response->status() === 204;
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function formatTrack(array $track): array
    {
        return [
            'spotify_track_id' => $track['id'],
            'title' => $track['name'],
            'artist' => collect($track['artists'])->pluck('name')->join(', '),
            'album' => $track['album']['name'] ?? '',
            'cover_url' => $track['album']['images'][0]['url'] ?? null,
            'duration_ms' => $track['duration_ms'],
        ];
    }

    private function extractTrackId(string $input): string
    {
        // spotify:track:XXXX
        if (str_starts_with($input, 'spotify:track:')) {
            return str_replace('spotify:track:', '', $input);
        }

        // https://open.spotify.com/track/XXXX
        if (str_contains($input, 'open.spotify.com/track/')) {
            preg_match('/track\/([a-zA-Z0-9]+)/', $input, $matches);
            return $matches[1] ?? $input;
        }

        return $input;
    }
}
