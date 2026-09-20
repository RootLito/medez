<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Pending Verification');
?>

<div>
    <flux:heading size="xl" level="1">Pending Verification</flux:heading>
    <flux:subheading class="mb-6">Applications awaiting staff review and approval.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.clock class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Pending Applications Placeholder</flux:heading>
        <flux:text class="mt-1">List of items awaiting verification will be shown here.</flux:text>
    </div>
</div>