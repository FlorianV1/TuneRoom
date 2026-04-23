<?php

use App\Http\Controllers\Auth\SpotifyController;
use App\Livewire\CreateRoom;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/auth/spotify', [SpotifyController::class, 'redirect'])->name('auth.spotify');
Route::get('/auth/spotify/callback', [SpotifyController::class, 'callback'])->name('auth.spotify.callback');

Route::middleware('auth')->group(function () {
    Route::get('/auth/spotify/connect', [SpotifyController::class, 'connect'])->name('auth.spotify.connect');
    Route::get('/auth/spotify/connect/callback', [SpotifyController::class, 'connectCallback'])->name('auth.spotify.connect.callback');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/rooms/create', CreateRoom::class)->name('rooms.create');
    Route::get('/rooms/join', fn() => 'coming soon')->name('rooms.join');
    Route::get('/rooms/{code}', fn($code) => 'coming soon')->name('rooms.show');

    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('logout');
});

Route::get('/', fn() => redirect()->route('dashboard'));

Route::get('/login', fn() => redirect()->route('auth.spotify'))->name('login');
