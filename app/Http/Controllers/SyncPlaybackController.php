<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\SpotifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyncPlaybackController extends Controller
{
    public function __invoke(Request $request, SpotifyService $spotify)
    {
        $validated = $request->validate([
            'room_id' => 'required|integer|exists:rooms,id',
            'status' => 'required|in:playing,paused,stopped',
            'track_id' => 'nullable|string',
            'position_ms' => 'required|integer|min:0',
            'server_time' => 'required|integer',
        ]);

        $user = Auth::user();

        if (!$user->hasSpotifyConnected()) {
            return response()->json(['error' => 'Spotify not connected'], 422);
        }

        // Calculate latency-compensated position
        $latencyMs = max(0, now()->valueOf() - $validated['server_time']);
        $positionMs = $validated['position_ms'] + $latencyMs;

        $trackId = $validated['track_id'];
        $status = $validated['status'];

        if ($status === 'playing' && $trackId) {
            $spotify->play($user, $trackId, $positionMs);
        } elseif ($status === 'paused') {
            $spotify->pause($user);
        }

        return response()->json(['ok' => true, 'latency_ms' => $latencyMs]);
    }
}
