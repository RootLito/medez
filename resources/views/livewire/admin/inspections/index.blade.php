<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.admin');
title('Inspection Schedule');
?>

<div>
    <flux:heading size="xl" level="1">Inspection Schedule</flux:heading>
    <flux:subheading class="mb-6">Calendar and timetable for field inspections.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
        <flux:icon.calendar class="mx-auto h-12 w-12 text-zinc-400" />
        <flux:heading size="lg" class="mt-4">Inspection Schedule Placeholder</flux:heading>
        <flux:text class="mt-1">Scheduler and calendar interface will go here.</flux:text>
    </div>
</div>