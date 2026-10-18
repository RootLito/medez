<?php

use function Livewire\Volt\{layout, title, state, rules};
use Illuminate\Support\Facades\Auth;

layout('components.layouts.client_auth');
title('Client Login');

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

    if (Auth::guard('client')->attempt(['username' => $this->username, 'password' => $this->password], $this->remember)) {
        session()->regenerate();

        return redirect()->intended(route('client.home'));
    }

    $this->addError('username', 'The provided credentials do not match our records.');
};

?>

<div class="max-w-md mx-auto space-y-6">
    <div class="text-center space-y-2">
        <div class="flex justify-center">
            <img src="{{ asset('malalag.png') }}" alt="Malalag Logo" width="128">
        </div>
        <flux:heading size="xl" level="1">Client Login</flux:heading>
        <flux:subheading>Sign in to access your portal.</flux:subheading>
    </div>

    <flux:card class="p-6 space-y-6">
        <form wire:submit="login" class="space-y-4">
            <flux:input label="Username" wire:model="username" icon="user" placeholder="Enter your username" required
                autofocus />

            <flux:input type="password" label="Password" wire:model="password" icon="key" placeholder="••••••••"
                required viewable />

            <div class="flex items-center justify-between pt-2">
                <flux:checkbox label="Remember me" wire:model="remember" />
            </div>

            <flux:button type="submit" variant="primary" class="w-full">
                Log In
            </flux:button>
        </form>

        <div class="text-center text-sm text-zinc-500 pt-2 border-t border-zinc-100 dark:border-zinc-800">
            Don't have an account yet?
            <flux:link href="{{ route('client.register') }}" wire:navigate>Register here</flux:link>
        </div>
    </flux:card>
</div>
