<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Application Summaries');
?>

<div>
    <flux:heading size="xl" level="1">Application Summaries</flux:heading>
    <flux:subheading class="mb-6">High-level statistics and transaction reporting.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.chart-bar class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Application Summaries Placeholder</flux:heading>
        <flux:text class="mt-1">Summary charts and tables will render here.</flux:text>
    </div>
</div>