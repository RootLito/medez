<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Roles & Permissions');
?>

<div>
    <flux:heading size="xl" level="1">Roles & Permissions</flux:heading>
    <flux:subheading class="mb-6">Define authorization levels and module permissions.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.key class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Roles & Permissions Placeholder</flux:heading>
        <flux:text class="mt-1">Role matrix and permission checkboxes will be configured here.</flux:text>
    </div>
</div>