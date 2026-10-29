<?php

use function Livewire\Volt\{layout, title, state, usesPagination, computed};
use App\Models\LocClearance;
use Flux\Flux;

layout('components.layouts.admin');
title('Archived Records');

usesPagination();

state([
    'search' => '',
]);

$archivedClearances = computed(function () {
    return LocClearance::query()
        ->where('is_archived', true) // Only archived records
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('application_no', 'like', '%' . $this->search . '%')
                    ->orWhere('applicant_name', 'like', '%' . $this->search . '%')
                    ->orWhere('corporation_name', 'like', '%' . $this->search . '%')
                    ->orWhere('project_type', 'like', '%' . $this->search . '%');
            });
        })
        ->latest('updated_at')
        ->paginate(15);
});

// Restore: Sets is_archived = false
$restoreApplication = function ($id) {
    LocClearance::where('id', $id)->update(['is_archived' => false]);

    Flux::toast(text: 'Application restored to Digital Logbook.', variant: 'success');
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Archived Records</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Historical and archived application records.</flux:subheading>
        </div>
    </div>

    <!-- Search Toolbar -->
    <div class="max-w-md">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
            placeholder="Search archived applications..." clearable />
    </div>

    <!-- Mobile Card View (< md) -->
    <div class="space-y-3 md:hidden">
        @forelse($this->archivedClearances as $record)
        <flux:card class="p-4 space-y-3">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $record->application_no ?? '#' . $record->id }}
                    </span>
                    <div class="text-[11px] text-zinc-400">
                        Archived: {{ $record->updated_at->format('M d, Y') }}
                    </div>
                </div>
                <flux:badge size="sm" color="zinc">Archived</flux:badge>
            </div>

            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 space-y-1">
                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $record->applicant_name }}</div>
                @if ($record->corporation_name)
                <div class="text-xs text-zinc-500 truncate">{{ $record->corporation_name }}</div>
                @endif
                <div class="text-xs text-zinc-500 pt-0.5">
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->project_type ?? 'N/A'
                        }}</span>
                    <span class="block truncate text-zinc-400">{{ $record->project_location ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Card Actions -->
            <div class="flex items-center justify-end gap-1.5 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:button href="{{ route('admin.applications.show', $record->id) }}" wire:navigate variant="filled"
                    icon-variant="outline" size="xs" icon="eye">
                    View
                </flux:button>
                <flux:button wire:click="restoreApplication({{ $record->id }})" variant="filled" icon-variant="outline"
                    size="xs" icon="arrow-path">
                    Restore
                </flux:button>
            </div>
        </flux:card>
        @empty
        <flux:card class="text-center py-10 px-4 space-y-2">
            <flux:icon name="archive-box" class="size-8 mx-auto text-zinc-400" />
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No archived records found</p>
        </flux:card>
        @endforelse

        <div>{{ $this->archivedClearances->links() }}</div>
    </div>

    <!-- Desktop Table View (>= md) -->
    <div class="hidden md:block">
        <flux:table :paginate="$this->archivedClearances">
            <flux:table.columns>
                <flux:table.column>App No.</flux:table.column>
                <flux:table.column>Applicant / Corporation</flux:table.column>
                <flux:table.column>Project Info</flux:table.column>
                <flux:table.column>Date Archived</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->archivedClearances as $record)
                <flux:table.row :key="$record->id">
                    <flux:table.cell class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $record->application_no ?? '#' . $record->id }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{ $record->applicant_name }}
                        </div>
                        @if ($record->corporation_name)
                        <div class="text-xs text-zinc-400 truncate max-w-[200px]">{{ $record->corporation_name }}</div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $record->project_type ??
                            'N/A' }}</div>
                        <div class="text-xs text-zinc-400 truncate max-w-xs">{{ $record->project_location ?? 'N/A' }}
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="text-sm text-zinc-500 whitespace-nowrap">
                        {{ $record->updated_at->format('M d, Y h:i A') }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button href="{{ route('admin.applications.show', $record->id) }}" variant="filled"
                                icon-variant="outline" size="sm" icon="eye" tooltip="View Details" wire:navigate />
                            <flux:button wire:click="restoreApplication({{ $record->id }})" variant="filled"
                                icon-variant="outline" size="sm" icon="arrow-path" tooltip="Restore Record" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-12 text-zinc-500">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <flux:icon name="archive-box" class="size-8 text-zinc-400" />
                            <span>No archived applications found.</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>