<?php

use function Livewire\Volt\{layout, title, state, rules};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\ActivityLog;

layout('components.layouts.admin_auth');
title('Admin Login - MPDC Portal');

state([
    'username' => '',
    'password' => '',
    'remember' => false,
]);

rules([
    'username' => ['required', 'string'],
    'password' => ['required', 'string'],
]);

$login = function () {
    $this->validate();

    if (! Auth::guard('admin')->attempt(['username' => $this->username, 'password' => $this->password], $this->remember)) {
        throw ValidationException::withMessages([
            'username' => __('auth.failed'),
        ]);
    }

    $admin = Auth::guard('admin')->user();

    if (! $admin->is_active) {
        Auth::guard('admin')->logout();
        throw ValidationException::withMessages([
            'username' => 'Your account has been deactivated. Please contact the administrator.',
        ]);
    }

    $admin->update(['last_login_at' => now()]);

    ActivityLog::create([
        'admin_id' => $admin->id,
        'action' => 'LOGIN',
        'module' => 'Authentication',
        'description' => "User {$admin->username} logged into the admin portal.",
        'ip_address' => request()->ip(),
    ]);

    session()->regenerate();

    return redirect()->intended(route('admin.dashboard'));
};

?>

<div class="max-w-md mx-auto space-y-6">
    <div class="text-center space-y-2">
        <div class="flex justify-center">
            <img src="https://fluxui.dev/img/demo/logo.png" alt="Logo" class="h-12 w-auto" />
        </div>
        <flux:heading size="xl" level="1">MPDC Admin Portal</flux:heading>
        <flux:subheading>Enter your credentials to access the administrative dashboard</flux:subheading>
    </div>

    <flux:card class="p-6 space-y-6">
        <form wire:submit="login" class="space-y-4">
            <flux:input 
                label="Username" 
                wire:model="username" 
                icon="user" 
                placeholder="Enter your username" 
                required 
                autofocus />

            <flux:input 
                type="password" 
                label="Password" 
                wire:model="password" 
                icon="key" 
                placeholder="••••••••" 
                required 
                viewable />

            <div class="flex items-center justify-between pt-2">
                <flux:checkbox label="Remember me" wire:model="remember" />
            </div>

            <flux:button type="submit" variant="primary" class="w-full">
                Sign In
            </flux:button>
        </form>
    </flux:card>
</div>