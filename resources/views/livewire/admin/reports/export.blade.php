<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Export Transaction Logs');
?>

<div>
    <flux:heading size="xl" level="1">Export Transaction Logs</flux:heading>
    <flux:subheading class="mb-6">Generate CSV, Excel, or PDF audit exports.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.arrow-down-tray class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Export Tools Placeholder</flux:heading>
        <flux:text class="mt-1">Log export filters and download triggers will be added here.</flux:text>
    </div>
</div>