<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SpotifyController;
use App\Http\Controllers\SyncPlaybackController;
use App\Livewire\CreateRoom;
use App\Livewire\Dashboard;
use App\Livewire\JoinRoom;
use App\Livewire\RoomPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('landing'))->name('landing');
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.post');
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.post');

Route::get('/creator', fn() => view('creator'))->name('creator');

Route::get('/auth/spotify', [SpotifyController::class, 'redirect'])->name('auth.spotify');
Route::get('/auth/spotify/callback', [SpotifyController::class, 'callback'])->name('auth.spotify.callback');

Route::middleware('auth')->group(function () {
    Route::get('/auth/spotify/connect', [SpotifyController::class, 'connect'])->name('auth.spotify.connect');
    Route::get('/auth/spotify/connect/callback', [SpotifyController::class, 'connectCallback'])->name('auth.spotify.connect.callback');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/rooms/create', CreateRoom::class)->name('rooms.create');
    Route::get('/rooms/join', JoinRoom::class)->name('rooms.join');
    Route::post('/rooms/sync-playback', [SyncPlaybackController::class, '__invoke'])->name('rooms.sync-playback');
    Route::get('/rooms/{code}', RoomPage::class)->name('rooms.show');

    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('logout');
});
