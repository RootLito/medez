<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Encode Walk-In');
?>

<div>
    <flux:heading size="xl" level="1">Encode Walk-In Application</flux:heading>
    <flux:subheading class="mb-6">Manually record an application brought in person.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.plus-circle class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Walk-In Form Placeholder</flux:heading>
        <flux:text class="mt-1">Encode walk-in entry forms will be built here.</flux:text>
    </div>
</div>