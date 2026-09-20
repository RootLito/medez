<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Inspection Logs');
?>

<div>
    <flux:heading size="xl" level="1">Inspection Logs</flux:heading>
    <flux:subheading class="mb-6">History and records of completed site visits.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.clipboard-document-check class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Inspection Logs Placeholder</flux:heading>
        <flux:text class="mt-1">Completed inspection logs and reports will be listed here.</flux:text>
    </div>
</div>