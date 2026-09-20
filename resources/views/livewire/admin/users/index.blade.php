<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('User Management');
?>

<div>
    <flux:heading size="xl" level="1">User Management</flux:heading>
    <flux:subheading class="mb-6">Manage staff accounts, credentials, and access.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.users class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">User Management Placeholder</flux:heading>
        <flux:text class="mt-1">Staff user table and creation modal will go here.</flux:text>
    </div>
</div>