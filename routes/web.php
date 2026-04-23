<?php

use App\Http\Controllers\SyncPlaybackController;
use App\Http\Controllers\Auth\SpotifyController;
use App\Livewire\CreateRoom;
use App\Livewire\Dashboard;
use App\Livewire\RoomPage;
use App\Livewire\JoinRoom;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/auth/spotify', [SpotifyController::class, 'redirect'])->name('auth.spotify');
Route::get('/auth/spotify/callback', [SpotifyController::class, 'callback'])->name('auth.spotify.callback');

Route::middleware('auth')->group(function () {
    Route::get('/auth/spotify/connect', [SpotifyController::class, 'connect'])->name('auth.spotify.connect');
    Route::get('/auth/spotify/connect/callback', [SpotifyController::class, 'connectCallback'])->name('auth.spotify.connect.callback');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/rooms/create', CreateRoom::class)->name('rooms.create');
    Route::get('/rooms/join', JoinRoom::class)->name('rooms.join');
    Route::get('/rooms/{code}', RoomPage::class)->name('rooms.show');

    Route::post('/rooms/sync-playback', SyncPlaybackController::class)->name('rooms.sync-playback');

    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('logout');
});

Route::get('/test-spotify', function() {
    $spotify = app(App\Services\SpotifyService::class);
    $user = auth()->user();
    $result = $spotify->play($user, '3n3Ppam7vgaVa1iaRUIOKE', 0); // Shape of You
    return response()->json(['result' => $result, 'token' => $user->spotify_token ? 'exists' : 'missing']);
});

Route::get('/', fn() => redirect()->route('dashboard'));

Route::get('/login', fn() => redirect()->route('auth.spotify'))->name('login');
