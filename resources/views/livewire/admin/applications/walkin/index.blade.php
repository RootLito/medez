<?php

use function Livewire\Volt\{layout, title, state, usesPagination, computed};
use App\Models\LocClearance;
use Flux\Flux;

layout('components.layouts.admin');
title('Walk-In Applications - Admin Portal');

usesPagination();

state([
    'search' => '',
    'status' => 'all',
    'deletingId' => null,
    'archivingId' => null,
]);

$clearances = computed(function () {
    return LocClearance::query()
        ->where('is_walk_in', true)
        ->where('is_archived', false) // Hide already archived records
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('application_no', 'like', '%' . $this->search . '%')
                    ->orWhere('applicant_name', 'like', '%' . $this->search . '%')
                    ->orWhere('or_number', 'like', '%' . $this->search . '%')
                    ->orWhere('corporation_name', 'like', '%' . $this->search . '%')
                    ->orWhere('project_type', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->status !== 'all', function ($query) {
            if (in_array($this->status, ['approve', 'approved'])) {
                $query->whereIn('status', ['approve', 'approved']);
            } else {
                $query->where('status', $this->status);
            }
        })
        ->latest()
        ->paginate(10);
});

// --- ARCHIVE LOGIC ---
$confirmArchive = function ($id) {
    $this->archivingId = $id;
    Flux::modal('archive-application-modal')->show();
};

$archiveApplication = function () {
    if (!$this->archivingId) return;

    $clearance = LocClearance::where('is_walk_in', true)->findOrFail($this->archivingId);
    $clearance->update(['is_archived' => true]);

    $this->archivingId = null;
    Flux::modal('archive-application-modal')->close();

    Flux::toast(text: 'Walk-in application moved to archive.', variant: 'success');
};


// --- DELETE LOGIC ---
$confirmDelete = function ($id) {
    $this->deletingId = $id;
    Flux::modal('delete-application-modal')->show();
};

$deleteApplication = function () {
    if (!$this->deletingId) return;

    $clearance = LocClearance::where('is_walk_in', true)->findOrFail($this->deletingId);
    $clearance->delete();

    $this->deletingId = null;
    Flux::modal('delete-application-modal')->close();

    Flux::toast(text: 'Walk-in record deleted successfully.', variant: 'success');
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Walk-In Applications</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Manage and encode locational clearance applications received
                in-person.</flux:subheading>
        </div>

        <flux:button href="{{ route('admin.applications.walkin-create') }}" wire:navigate variant="primary" size="sm"
            icon="plus" class="w-full sm:w-auto justify-center">
            Encode Walk-In Form
        </flux:button>
    </div>

    <!-- Toolbar: Search & Status Filter -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search app no, applicant, OR no, or project..." clearable class="w-full" />
        </div>

        <div class="w-full sm:w-52">
            <flux:select wire:model.live="status" placeholder="Filter Status">
                <flux:select.option value="all">All Statuses</flux:select.option>
                <flux:select.option value="pending">Pending</flux:select.option>
                <flux:select.option value="resubmit">Resubmit</flux:select.option>
                <flux:select.option value="approve">Approved</flux:select.option>
                <flux:select.option value="rejected">Rejected</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- MOBILE CARDS LIST (< md) -->
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

        $isApproved = in_array($rawStatus, ['approve', 'approved']);
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
                <div class="text-xs text-zinc-500 pt-0.5">
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $clearance->project_type ?? 'N/A'
                        }}</span>
                    <span class="block truncate text-zinc-400">{{ $clearance->project_location ?? 'N/A' }}</span>
                </div>
            </div>

            <div
                class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-400">
                <span>{{ $clearance->date_of_receipt?->format('M d, Y') ?? $clearance->created_at?->format('M d, Y')
                    }}</span>
                <div class="flex items-center gap-1.5">
                    <flux:button href="{{ route('admin.applications.walkin-show', $clearance->id) }}" wire:navigate
                        variant="subtle" size="xs" icon="eye" />
                    <flux:button href="{{ route('admin.applications.walkin-edit', $clearance->id) }}" wire:navigate
                        variant="subtle" size="xs" icon="pencil-square" />

                    <!-- Archive Button (Disabled if not approved) -->
                    <flux:button wire:click="confirmArchive({{ $clearance->id }})" variant="subtle" size="xs"
                        icon="archive-box-arrow-down" :disabled="!$isApproved" />

                    <!-- Delete Button -->
                    <flux:button wire:click="confirmDelete({{ $clearance->id }})" variant="ghost" size="xs" icon="trash"
                        class="text-red-600 dark:text-red-400" />
                </div>
            </div>
        </flux:card>
        @empty
        <flux:card class="text-center py-10 px-4 space-y-2">
            <flux:icon name="document-text" class="size-8 mx-auto text-zinc-400" />
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No walk-in applications found</p>
            <p class="text-xs text-zinc-500">No records match your search or filter criteria.</p>
        </flux:card>
        @endforelse

        <div>{{ $this->clearances->links() }}</div>
    </div>

    <!-- DESKTOP TABLE VIEW (>= md) -->
    <div class="hidden md:block">
        <flux:table :paginate="$this->clearances">
            <flux:table.columns>
                <flux:table.column>Application No.</flux:table.column>
                <flux:table.column>Applicant / Corporation</flux:table.column>
                <flux:table.column>Project Type / Location</flux:table.column>
                <flux:table.column>Date Encoded</flux:table.column>
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

                $isApproved = in_array($rawStatus, ['approve', 'approved']);
                @endphp
                <flux:table.row :key="$clearance->id">
                    <flux:table.cell>
                        <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $clearance->application_no ?? 'Unassigned' }}
                        </div>
                        @if ($clearance->or_number)
                        <div class="text-xs text-zinc-400 font-mono">
                            OR: {{ $clearance->or_number }}
                        </div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                            {{ $clearance->applicant_name }}
                        </div>
                        @if ($clearance->corporation_name)
                        <div class="text-xs text-zinc-400 truncate max-w-[200px]">
                            {{ $clearance->corporation_name }}
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
                        {{ $clearance->date_of_receipt?->format('M d, Y') ?? $clearance->created_at?->format('M d, Y')
                        }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" :color="$badgeColor">
                            {{ $displayStatus }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button variant="filled" size="sm" icon="eye" icon-variant="outline"
                                tooltip="View Details"
                                href="{{ route('admin.applications.walkin-show', $clearance->id) }}" wire:navigate />

                            <flux:button variant="filled" size="sm" icon="pencil-square" icon-variant="outline"
                                tooltip="Edit Record"
                                href="{{ route('admin.applications.walkin-edit', $clearance->id) }}" wire:navigate />

                            @if($isApproved)
                            <flux:button variant="filled" size="sm" icon="archive-box-arrow-down" icon-variant="outline"
                                tooltip="Archive Application" wire:click="confirmArchive({{ $clearance->id }})" />
                            @else
                            <flux:button variant="filled" size="sm" icon="archive-box-arrow-down" icon-variant="outline"
                                tooltip="Needs Approval to Archive" disabled />
                            @endif

                            <flux:button variant="filled" size="sm" icon="trash" icon-variant="outline" tooltip="Delete"
                                color="red" wire:click="confirmDelete({{ $clearance->id }})" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-12 text-zinc-500">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <flux:icon name="document-text" class="size-8 text-zinc-400" />
                            <span>No walk-in applications match your search criteria.</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Archive Confirmation Modal -->
    <flux:modal name="archive-application-modal" variant="dialog" class="w-full max-w-sm sm:max-w-md">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <flux:icon name="archive-box-arrow-down" class="size-6" />
                </div>
                <div>
                    <flux:heading size="lg">Archive Record?</flux:heading>
                    <flux:subheading class="text-xs">Move to archives</flux:subheading>
                </div>
            </div>

            <flux:text class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300">
                Are you sure you want to move this approved application to the <strong>Archived Records</strong>? It
                will no longer appear in the active logbook.
            </flux:text>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" size="sm" wire:click="archiveApplication">
                    Yes, Archive Record
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Responsive Delete Confirmation Modal -->
    <flux:modal name="delete-application-modal" variant="dialog" class="w-full max-w-sm sm:max-w-md">
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-red-500/10 text-red-600 dark:text-red-400">
                    <flux:icon name="exclamation-triangle" class="size-6" />
                </div>
                <div>
                    <flux:heading size="lg">Delete Walk-In Record?</flux:heading>
                    <flux:subheading class="text-xs">Permanent deletion</flux:subheading>
                </div>
            </div>

            <flux:text class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300">
                Are you sure you want to delete this walk-in application record? This action cannot be undone.
            </flux:text>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" size="sm" wire:click="deleteApplication">
                    Yes, Delete Record
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>