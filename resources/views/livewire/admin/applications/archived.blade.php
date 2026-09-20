<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Archived Records');
?>

<div>
    <flux:heading size="xl" level="1">Archived Records</flux:heading>
    <flux:subheading class="mb-6">Historical and processed application archives.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.archive-box class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Archived Records Placeholder</flux:heading>
        <flux:text class="mt-1">Archived data tables will be listed here.</flux:text>
    </div>
</div>