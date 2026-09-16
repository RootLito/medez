<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->name('dashboard');

// Settings Routes (Auth Disabled)
Route::group([], function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// Admin Routes (Auth Disabled)
Route::prefix('admin')->name('admin.')->group(function () {
    // Redirect /admin directly to /admin/dashboard
    Route::redirect('/', '/admin/dashboard');

    // Renders resources/views/livewire/admin/dashboard/index.blade.php
    Volt::route('/dashboard', 'admin.dashboard.index')->name('dashboard');
});

// Client Routes (Auth Disabled)
Route::prefix('client')->name('client.')->group(function () {
    // Redirect /client directly to /client/application
    Route::redirect('/', '/client/applications');

    // Renders resources/views/livewire/client/application/index.blade.php
    Volt::route('/applications', 'client.applications.index')->name('applications');
});

require __DIR__ . '/auth.php';
