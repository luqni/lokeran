<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\JobBoard;
use App\Livewire\Admin\PlatformManager;

Route::get('/', JobBoard::class)->name('home');
Route::get('/admin/platforms', PlatformManager::class)->middleware(['auth', 'verified'])->name('admin.platforms');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
