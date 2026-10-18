<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


// 1. Root Route: Always redirect directly to /client/login
Route::redirect('/', '/client/login')->name('home');

// 2. Client Portal Routes
Route::prefix('client')->name('client.')->group(function () {
    Route::redirect('/', '/client/login');

    // Guest Client Routes
    Route::middleware('guest:client')->group(function () {
        Volt::route('/login', 'client.auth.login')->name('login');
        Volt::route('/register', 'client.auth.register')->name('register');
    });

    // Authenticated Client Routes
    Route::middleware('auth:client')->group(function () {
        Volt::route('/home', 'client.home.index')->name('home');
        Volt::route('/applications', 'client.applications.index')->name('applications.index');
        Volt::route('/applications/create', 'client.applications.create')->name('applications.create');
        Volt::route('/applications/{clearance}', 'client.applications.show')->name('applications.show');
        Volt::route('/applications/{clearance}/edit', 'client.applications.edit')->name('applications.edit');
        Volt::route('/profile', 'client.profile.index')->name('profile');

        Route::post('/logout', function () {
            Auth::guard('client')->logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('client.login');
        })->name('logout');
    });
});

// 3. Admin Portal Routes
Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        return Auth::guard('admin')->check()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('admin.login');
    });

    Route::middleware('guest:admin')->group(function () {
        Volt::route('/login', 'admin.auth.login')->name('login');
    });

    Route::middleware('auth:admin')->group(function () {
        Volt::route('/dashboard', 'admin.dashboard.index')->name('dashboard');

        Route::prefix('applications')->name('applications.')->group(function () {
            Volt::route('/', 'admin.applications.index')->name('index');
            Volt::route('/applications/{clearance}', 'admin.applications.show')->name('applications.show');

            Volt::route('/walkin', 'admin.applications.walkin-index')->name('walkin-index');
            Volt::route('/walkin/create', 'admin.applications.walkin-create')->name('walkin-create');
            Volt::route('/walkin/{locClearance}', 'admin.applications.walkin-show')->name('walkin-show');
            Volt::route('/walkin/{locClearance}/edit', 'admin.applications.walkin-edit')->name('walkin-edit');


            Volt::route('/pending', 'admin.applications.pending')->name('pending');
            Volt::route('/archived', 'admin.applications.archived')->name('archived');

            Volt::route('/{clearance}', 'admin.applications.show')->name('show');
            Volt::route('/{clearance}/edit', 'admin.applications.edit')->name('edit');
        });

        Route::prefix('geospatial')->name('geospatial.')->group(function () {
            Volt::route('/', 'admin.geospatial.index')->name('index');
        });

        Route::prefix('inspections')->name('inspections.')->group(function () {
            Volt::route('/', 'admin.inspections.index')->name('index');
            Volt::route('/logs', 'admin.inspections.logs')->name('logs');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Volt::route('/summaries', 'admin.reports.summaries')->name('summaries');
            Volt::route('/export', 'admin.reports.export')->name('export');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Volt::route('/', 'admin.users.index')->name('index');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Volt::route('/', 'admin.roles.index')->name('index');
        });

        Route::prefix('logs')->name('logs.')->group(function () {
            Volt::route('/', 'admin.logs.index')->name('index');
        });

        Volt::route('/register', 'admin.auth.register')->name('register');

        Route::post('/logout', function () {
            Auth::guard('admin')->logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('admin.login');
        })->name('logout');
    });
});


