<?php

use function Livewire\Volt\{layout, title, state, usesPagination, computed};
use App\Models\LocClearance;
use Flux\Flux;

layout('components.layouts.admin');
title('Digital Logbook');

usesPagination();

state([
    'search' => '',
    'status' => 'all',
    'dateFrom' => '',
    'dateTo' => '',
    'archivingId' => null,
]);

$clearances = computed(function () {
    return LocClearance::query()
        ->where('is_walk_in', false)
        ->where('is_archived', false) // <-- Only active records
        ->with('client')
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('application_no', 'like', '%' . $this->search . '%')
                    ->orWhere('applicant_name', 'like', '%' . $this->search . '%')
                    ->orWhere('corporation_name', 'like', '%' . $this->search . '%')
                    ->orWhere('project_type', 'like', '%' . $this->search . '%')
                    ->orWhere('or_number', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->status !== 'all', function ($query) {
            if (in_array($this->status, ['approve', 'approved'])) {
                $query->whereIn('status', ['approve', 'approved']);
            } else {
                $query->where('status', $this->status);
            }
        })
        ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
        ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
        ->latest()
        ->paginate(15);
});

$confirmArchive = function ($id) {
    $this->archivingId = $id;
    Flux::modal('archive-modal')->show();
};

// Archive: Sets is_archived = true (Client can still see it!)
$archiveApplication = function () {
    if (! $this->archivingId) return;

    LocClearance::where('id', $this->archivingId)->update(['is_archived' => true]);

    $this->archivingId = null;
    Flux::modal('archive-modal')->close();

    Flux::toast(text: 'Application moved to archive.', variant: 'success');
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Digital Logbook</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Manage submitted locational clearance records.</flux:subheading>
        </div>

        <flux:button href="{{ route('admin.applications.walkin-index') }}" wire:navigate variant="primary" size="sm"
            icon="plus">
            Encode Walk-In
        </flux:button>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 sm:gap-3">
        <div class="sm:col-span-2 lg:col-span-5">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search app no, name, OR no..." clearable />
        </div>

        <div class="lg:col-span-3">
            <flux:select wire:model.live="status" placeholder="Status">
                <flux:select.option value="all">All Statuses</flux:select.option>
                <flux:select.option value="pending">Pending</flux:select.option>
                <flux:select.option value="resubmit">Resubmit</flux:select.option>
                <flux:select.option value="approve">Approved</flux:select.option>
                <flux:select.option value="rejected">Rejected</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:col-span-4">
            <flux:input type="date" wire:model.live="dateFrom" />
            <flux:input type="date" wire:model.live="dateTo" />
        </div>
    </div>

    <!-- Mobile Card View (< md) -->
    <div class="space-y-3 md:hidden">
        @forelse($this->clearances as $clearance)
        @php
        $rawStatus = strtolower($clearance->status ?? 'pending');
        $badgeColor = match($rawStatus) {
        'approve', 'approved' => 'green',
        'resubmit' => 'yellow',
        'pending' => 'blue',
        'rejected' => 'red',
        default => 'zinc',
        };
        $displayStatus = match($rawStatus) {
        'approve', 'approved' => 'Approved',
        'resubmit' => 'Resubmit',
        'pending' => 'Pending',
        'rejected' => 'Rejected',
        default => ucfirst($rawStatus),
        };
        @endphp
        <flux:card class="p-4 space-y-3">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $clearance->application_no ?? 'Unassigned' }}
                    </div>
                    @if ($clearance->or_number)
                    <span class="text-[11px] font-mono text-zinc-500">OR: {{ $clearance->or_number }}</span>
                    @endif
                </div>
                <flux:badge size="sm" :color="$badgeColor" class="text-[11px]">
                    {{ $displayStatus }}
                </flux:badge>
            </div>

            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 space-y-1">
                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $clearance->applicant_name }}
                </div>
                @if ($clearance->corporation_name)
                <div class="text-xs text-zinc-500 truncate">{{ $clearance->corporation_name }}</div>
                @endif
                <div class="text-xs text-zinc-500 pt-1">
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $clearance->project_type ?? 'N/A'
                        }}</span>
                    <span class="block truncate text-zinc-400">{{ $clearance->project_location ?? 'N/A' }}</span>
                </div>
            </div>

            <div
                class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-400">
                <span>{{ $clearance->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                <div class="flex items-center gap-1.5">
                    <flux:button href="{{ route('admin.applications.show', $clearance->id) }}" wire:navigate
                        variant="subtle" size="xs" icon="eye">
                        View
                    </flux:button>
                    <flux:button wire:click="confirmArchive({{ $clearance->id }})" variant="ghost" size="xs"
                        icon="archive-box-arrow-down" class="text-amber-600 dark:text-amber-400">
                        Archive
                    </flux:button>
                </div>
            </div>
        </flux:card>
        @empty
        <flux:card class="text-center py-10 px-4 space-y-2">
            <flux:icon name="document-duplicate" class="size-8 mx-auto text-zinc-400" />
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No active applications found</p>
        </flux:card>
        @endforelse

        <div>{{ $this->clearances->links() }}</div>
    </div>

    <!-- Desktop Table View (>= md) -->
    <div class="hidden md:block">
        <flux:table :paginate="$this->clearances">
            <flux:table.columns>
                <flux:table.column>App / OR No.</flux:table.column>
                <flux:table.column>Applicant & Corporation</flux:table.column>
                <flux:table.column>Project Info</flux:table.column>
                <flux:table.column>Date Submitted</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->clearances as $clearance)
                @php
                $rawStatus = strtolower($clearance->status ?? 'pending');
                $badgeColor = match($rawStatus) {
                'approve', 'approved' => 'green',
                'resubmit' => 'yellow',
                'pending' => 'blue',
                'rejected' => 'red',
                default => 'zinc',
                };
                $displayStatus = match($rawStatus) {
                'approve', 'approved' => 'Approved',
                'resubmit' => 'Resubmit',
                'pending' => 'Pending',
                'rejected' => 'Rejected',
                default => ucfirst($rawStatus),
                };
                @endphp
                <flux:table.row :key="$clearance->id">
                    <flux:table.cell>
                        <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $clearance->application_no ?? 'Unassigned' }}
                        </div>
                        @if ($clearance->or_number)
                        <div class="text-xs text-zinc-400 font-mono">OR: {{ $clearance->or_number }}</div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{ $clearance->applicant_name
                            }}</div>
                        @if ($clearance->corporation_name)
                        <div class="text-xs text-zinc-400 truncate max-w-[200px]">{{ $clearance->corporation_name }}
                        </div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{ $clearance->project_type ??
                            'N/A' }}</div>
                        <div class="text-xs text-zinc-400 truncate max-w-xs">{{ $clearance->project_location ?? 'N/A' }}
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500">
                        {{ $clearance->created_at?->format('M d, Y') ?? 'N/A' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" :color="$badgeColor">
                            {{ $displayStatus }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button href="{{ route('admin.applications.show', $clearance->id) }}" variant="subtle"
                                size="sm" icon="eye" tooltip="View Details" wire:navigate />
                            <flux:button wire:click="confirmArchive({{ $clearance->id }})" variant="subtle" size="sm"
                                icon="archive-box-arrow-down" tooltip="Archive" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-12 text-zinc-500">
                        No clearance records found.
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Archive Confirmation Modal -->
    <flux:modal name="archive-modal" class="w-full max-w-sm" backdrop="blur">
        <div class="space-y-4">
            <flux:heading size="lg">Archive Application?</flux:heading>
            <flux:text class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300">
                This record will be moved to <strong>Archived Records</strong> and hidden from the active logbook. The
                client can still view their application.
            </flux:text>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" size="sm" wire:click="archiveApplication">
                    Archive
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>