<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Digital Logbook');
?>

<div>
    <flux:heading size="xl" level="1">Digital Logbook</flux:heading>
    <flux:subheading class="mb-6">View, filter, and manage all submitted applications.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.document-duplicate class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Digital Logbook Placeholder</flux:heading>
        <flux:text class="mt-1">Application records and table data will appear here.</flux:text>
    </div>
</div>