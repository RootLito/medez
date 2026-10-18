<?php

use function Livewire\Volt\{layout, title, state, rules};
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

layout('components.layouts.client_auth');
title('Client Registration');

state([
    'first_name' => '',
    'middle_name' => '',
    'last_name' => '',
    'birthdate' => '',
    'address' => '',
    'username' => '',
    'password' => '',
    'password_confirmation' => '',
]);

rules([
    'first_name' => ['required', 'string', 'max:255'],
    'middle_name' => ['nullable', 'string', 'max:255'],
    'last_name' => ['required', 'string', 'max:255'],
    'birthdate' => ['nullable', 'date'],
    'address' => ['nullable', 'string'],
    'username' => ['required', 'string', 'max:255', 'unique:clients,username'],
    'password' => ['required', 'string', 'min:8', 'confirmed'],
]);

$register = function () {
    $validated = $this->validate();

    $client = Client::create($validated);

    Auth::guard('client')->login($client);

    return redirect()->route('client.home');
};

?>

<div class="max-w-md mx-auto space-y-6 my-10">
    <div class="text-center space-y-2">
        <div class="flex justify-center">
            <img src="{{ asset('malalag.png') }}" alt="Malalag Logo" width="128">
        </div>
        <flux:heading size="xl" level="1">Create Client Account</flux:heading>
        <flux:subheading>Register to apply and track locational clearances.</flux:subheading>
    </div>

    <flux:card class="p-6 space-y-6">
        <form wire:submit="register" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input label="First Name" wire:model="first_name" placeholder="First name" required autofocus />

                <flux:input label="Last Name" wire:model="last_name" placeholder="Last name" required />
            </div>

            <flux:input label="Middle Name (Optional)" wire:model="middle_name" placeholder="Middle name" />

            <flux:input label="Birthdate" type="date" wire:model="birthdate" />

            <flux:textarea label="Address" wire:model="address" placeholder="Enter your address" rows="2" />

            <flux:input label="Username" wire:model="username" icon="user" placeholder="Choose a username"
                required />

            <flux:input type="password" label="Password" wire:model="password" icon="key" placeholder="••••••••"
                required viewable />

            <flux:input type="password" label="Confirm Password" wire:model="password_confirmation" icon="key"
                placeholder="••••••••" required viewable />

            <flux:button type="submit" variant="primary" class="w-full">
                Register Account
            </flux:button>
        </form>

        <div class="text-center text-sm text-zinc-500 pt-2 border-t border-zinc-100 dark:border-zinc-800">
            Already have an account?
            <flux:link href="{{ route('client.login') }}" wire:navigate>Log in here</flux:link>
        </div>
    </flux:card>
</div>
