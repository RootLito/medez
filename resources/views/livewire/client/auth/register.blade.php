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

<div class="max-w-md mx-auto my-10 p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-800">
    <div class="mb-6">
        <flux:heading size="xl" level="1">Create Client Account</flux:heading>
        <flux:subheading>Register to apply and track locational clearances.</flux:subheading>
    </div>

    <form wire:submit="register" class="space-y-4">
        <flux:input label="First Name" wire:model="first_name" required />

        <flux:input label="Middle Name (Optional)" wire:model="middle_name" />

        <flux:input label="Last Name" wire:model="last_name" required />

        <flux:input label="Birthdate" type="date" wire:model="birthdate" />

        <flux:textarea label="Address" wire:model="address" rows="2" />

        <flux:input label="Username" wire:model="username" required />

        <flux:input label="Password" type="password" wire:model="password" viewable required />

        <flux:input label="Confirm Password" type="password" wire:model="password_confirmation" viewable required />

        <flux:button type="submit" variant="primary" class="w-full">Register Account</flux:button>
    </form>

    <div class="mt-4 text-center text-sm text-zinc-500">
        Already have an account?
        <flux:link href="{{ route('client.login') }}" wire:navigate>Log in here</flux:link>
    </div>
</div>