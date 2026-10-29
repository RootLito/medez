<?php

use function Livewire\Volt\{layout, title, state, usesPagination, computed};
use App\Models\ActivityLog;

layout('components.layouts.admin');
title('Activity & Audit Logs');

usesPagination();

state([
    'search' => '',
    'dateFrom' => '',
    'dateTo' => '',
]);

$logs = computed(function () {
    return ActivityLog::with('admin')
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('action', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('admin', function ($aq) {
                        $aq->where('first_name', 'like', '%' . $this->search . '%')
                           ->orWhere('last_name', 'like', '%' . $this->search . '%')
                           ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            });
        })
        ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
        ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
        ->latest()
        ->paginate(15);
});

// Helper function to reset filters
$resetFilters = function () {
    $this->reset(['search', 'dateFrom', 'dateTo']);
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Activity & Audit Logs</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Track admin actions, modifications, and system events.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button wire:click="resetFilters" variant="subtle" size="sm" icon="arrow-path"
                class="w-full sm:w-auto justify-center">
                Reset Filters
            </flux:button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 sm:gap-3">
        <div class="sm:col-span-2 lg:col-span-6">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search by action, description, or user..." clearable class="w-full" />
        </div>

        <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:col-span-6">
            <flux:input type="date" wire:model.live="dateFrom" placeholder="From Date" />
            <flux:input type="date" wire:model.live="dateTo" placeholder="To Date" />
        </div>
    </div>

    <!-- MOBILE CARDS LIST (< md) -->
    <div class="space-y-3 md:hidden">
        @forelse($this->logs as $log)
        @php
        $rawAction = strtolower($log->action ?? 'system');
        $badgeColor = match(true) {
        str_contains($rawAction, 'create') || str_contains($rawAction, 'add') => 'green',
        str_contains($rawAction, 'update') || str_contains($rawAction, 'edit') => 'blue',
        str_contains($rawAction, 'delete') || str_contains($rawAction, 'remove') => 'red',
        str_contains($rawAction, 'login') => 'emerald',
        str_contains($rawAction, 'logout') => 'zinc',
        default => 'zinc',
        };
        @endphp
        <flux:card class="p-4 space-y-3">
            <div class="flex items-start justify-between gap-2 border-b border-zinc-200/80 dark:border-zinc-800 pb-2">
                <div>
                    <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                        {{ $log->admin?->full_name ?? $log->admin?->first_name ?? 'System Event' }}
                    </div>
                    <div class="text-[11px] text-zinc-400">
                        {{ $log->created_at->format('M d, Y h:i A') }}
                    </div>
                </div>
                <flux:badge size="sm" :color="$badgeColor" class="uppercase text-[10px] tracking-wider">
                    {{ $log->action ?? 'Event' }}
                </flux:badge>
            </div>

            <div class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed">
                {{ $log->description ?? 'No description provided.' }}
            </div>
        </flux:card>
        @empty
        <flux:card class="text-center py-10 px-4 space-y-2">
            <flux:icon name="queue-list" class="size-8 mx-auto text-zinc-400" />
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No logs found</p>
            <p class="text-xs text-zinc-500">Try adjusting your search or date filters.</p>
        </flux:card>
        @endforelse

        <div>{{ $this->logs->links() }}</div>
    </div>

    <div class="hidden md:block">
        <flux:table :paginate="$this->logs">
            <flux:table.columns>
                <flux:table.column>Timestamp</flux:table.column>
                <flux:table.column>User / Admin</flux:table.column>
                <flux:table.column>Action Event</flux:table.column>
                <flux:table.column>Description Details</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->logs as $log)
                @php
                $rawAction = strtolower($log->action ?? 'system');
                $badgeColor = match(true) {
                str_contains($rawAction, 'create') || str_contains($rawAction, 'add') => 'green',
                str_contains($rawAction, 'update') || str_contains($rawAction, 'edit') => 'blue',
                str_contains($rawAction, 'delete') || str_contains($rawAction, 'remove') => 'red',
                str_contains($rawAction, 'login') => 'emerald',
                str_contains($rawAction, 'logout') => 'zinc',
                default => 'zinc',
                };
                @endphp
                <flux:table.row :key="$log->id">
                    <flux:table.cell class="whitespace-nowrap">
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $log->created_at->format('M d, Y') }}
                        </div>
                        <div class="text-xs text-zinc-400">
                            {{ $log->created_at->format('h:i:s A') }}
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <div
                                class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-bold text-zinc-500 dark:text-zinc-400 shrink-0">
                                {{ mb_substr($log->admin?->first_name ?? 'S', 0, 1) }}
                            </div>
                            <span class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $log->admin?->full_name ?? $log->admin?->first_name ?? 'System' }}
                            </span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" :color="$badgeColor" class="uppercase text-[10px] tracking-wider">
                            {{ $log->action ?? 'Event' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="w-full">
                        <span class="text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $log->description ?? 'No details available.' }}
                        </span>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-12 text-zinc-500">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <flux:icon name="queue-list" class="size-8 text-zinc-400" />
                            <span>No activity logs match your filter criteria.</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>