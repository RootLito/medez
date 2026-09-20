<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Activity & Audit Logs');
?>

<div>
    <flux:heading size="xl" level="1">Activity & Audit Logs</flux:heading>
    <flux:subheading class="mb-6">Track admin actions, modifications, and system events.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.queue-list class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Activity Logs Placeholder</flux:heading>
        <flux:text class="mt-1">Audit trail data and event logs will display here.</flux:text>
    </div>
</div>