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

<div class="max-w-md mx-auto my-10 p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-800">
    <div class="mb-6">
        <flux:heading size="xl" level="1">Client Login</flux:heading>
        <flux:subheading>Sign in to access your portal.</flux:subheading>
    </div>

    <form wire:submit="login" class="space-y-4">
        <flux:input label="Username" wire:model="username" required />

        <flux:input label="Password" type="password" wire:model="password" required />

        <flux:checkbox label="Remember me" wire:model="remember" />

        <flux:button type="submit" variant="primary" class="w-full">Log In</flux:button>
    </form>

    <div class="mt-4 text-center text-sm text-zinc-500">
        Don't have an account yet? 
        <flux:link href="{{ route('client.register') }}" wire:navigate>Register here</flux:link>
    </div>
</div>