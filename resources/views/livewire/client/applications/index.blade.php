<?php

use function Livewire\Volt\{layout, title, state, usesPagination, computed};
use App\Models\LocClearance;
use Flux\Flux;

layout('components.layouts.client');
title('Applications - Client Portal');

usesPagination();

state([
    'search' => '',
    'status' => 'all',
    'deletingId' => null,
]);

$clearances = computed(function () {
    return LocClearance::query()
        ->where('client_id', auth('client')->id())
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('application_no', 'like', '%' . $this->search . '%')
                    ->orWhere('applicant_name', 'like', '%' . $this->search . '%')
                    ->orWhere('corporation_name', 'like', '%' . $this->search . '%')
                    ->orWhere('project_type', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->status !== 'all', function ($query) {
            $query->where('status', $this->status);
        })
        ->latest()
        ->paginate(10);
});

// Trigger confirmation modal
$confirmDelete = function ($id) {
    $this->deletingId = $id;
    Flux::modal('delete-application-modal')->show();
};

// Handle deletion
$deleteApplication = function () {
    if (! $this->deletingId) return;

    $clearance = LocClearance::where('client_id', auth('client')->id())
        ->findOrFail($this->deletingId);

    $clearance->delete();

    $this->deletingId = null;

    Flux::modal('delete-application-modal')->close();

    Flux::toast(
        text: 'Application deleted successfully.',
        variant: 'success'
    );
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">
                Applications
            </flux:heading>
            <flux:subheading class="text-xs sm:text-sm">
                Manage and track your submitted locational clearances.
            </flux:subheading>
        </div>

        <!-- Desktop-Only Quick Create Button -->
        <div class="hidden sm:block">
            <flux:button href="{{ route('client.applications.create') }}" wire:navigate variant="primary" icon="plus">
                New Application
            </flux:button>
        </div>
    </div>

    <!-- Mobile-First Toolbar -->
    <div class="space-y-3">
        <!-- Search Input -->
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
            placeholder="Search by app #, applicant, or project..." clearable class="w-full" />

        <!-- Status Filter & Mobile New Button Row -->
        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
            <div class="col-span-1 sm:w-48">
                <flux:select wire:model.live="status" placeholder="Status">
                    <flux:select.option value="all">All Statuses</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="approved">Approved</flux:select.option>
                    <flux:select.option value="rejected">Rejected</flux:select.option>
                </flux:select>
            </div>

            <!-- Mobile-Only New Application Button -->
            <div class="col-span-1 sm:hidden">
                <flux:button href="{{ route('client.applications.create') }}" wire:navigate variant="primary"
                    icon="plus" class="w-full justify-center">
                    New App
                </flux:button>
            </div>
        </div>
    </div>

    <!-- MOBILE CARDS LIST (Visible on screens < md) -->
    <div class="space-y-3 md:hidden">
        @forelse($this->clearances as $clearance)
        @php
        $status = strtolower($clearance->status ?? 'pending');
        $color = match ($status) {
        'approved' => 'green',
        'rejected' => 'red',
        default => 'amber',
        };
        @endphp

        <flux:card class="p-4 space-y-3">
            <!-- Card Header: App No & Status -->
            <div class="flex items-start justify-between gap-2">
                <div class="space-y-0.5">
                    <span class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $clearance->application_no ?? 'Pending Assign' }}
                    </span>
                    <div class="text-[11px] text-zinc-400">
                        {{ $clearance->created_at?->format('M d, Y') ?? 'N/A' }}
                    </div>
                </div>

                <flux:badge size="sm" :color="$color" class="capitalize shrink-0">
                    {{ ucfirst($status) }}
                </flux:badge>
            </div>

            <!-- Card Body: Project & Applicant -->
            <div class="space-y-1.5 pt-1 border-t border-zinc-100 dark:border-zinc-800">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $clearance->project_type ?? 'N/A' }}
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">
                        {{ $clearance->project_location ?? 'N/A' }}
                    </p>
                </div>

                <div class="text-xs text-zinc-600 dark:text-zinc-300">
                    <span class="text-zinc-400">Applicant:</span> {{ $clearance->applicant_name }}
                    @if($clearance->corporation_name)
                    <span class="text-zinc-400 block text-[11px] truncate">{{ $clearance->corporation_name }}</span>
                    @endif
                </div>
            </div>

            <!-- Card Actions -->
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:button href="{{ route('client.applications.show', $clearance->id) }}" wire:navigate
                    variant="subtle" size="xs" icon="eye">
                    View
                </flux:button>

                <flux:button href="{{ route('client.applications.edit', $clearance->id) }}" wire:navigate
                    variant="subtle" size="xs" icon="pencil-square">
                    Edit
                </flux:button>

                <flux:button wire:click="confirmDelete({{ $clearance->id }})" variant="ghost" size="xs" icon="trash"
                    class="text-red-600 dark:text-red-400" />
            </div>
        </flux:card>
        @empty
        <flux:card class="text-center py-10 px-4 space-y-2">
            <flux:icon icon="document-text" class="size-8 mx-auto text-zinc-400" />
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No applications found</p>
            <p class="text-xs text-zinc-500">Try adjusting your search query or filter.</p>
        </flux:card>
        @endforelse

        <!-- Mobile Pagination -->
        <div>
            {{ $this->clearances->links() }}
        </div>
    </div>

    <!-- DESKTOP TABLE VIEW (Visible on screens md and up) -->
    <div class="hidden md:block">
        <flux:table :paginate="$this->clearances">
            <flux:table.columns>
                <flux:table.column>Application No.</flux:table.column>
                <flux:table.column>Applicant / Corporation</flux:table.column>
                <flux:table.column>Project Type / Location</flux:table.column>
                <flux:table.column>Date Submitted</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->clearances as $clearance)
                <flux:table.row :key="$clearance->id">
                    <flux:table.cell class="font-mono text-xs font-semibold">
                        {{ $clearance->application_no ?? 'Pending Assign' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $clearance->applicant_name }}
                        </div>
                        @if($clearance->corporation_name)
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $clearance->corporation_name }}</div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $clearance->project_type ?? 'N/A'
                            }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-xs">{{
                            $clearance->project_location ?? 'N/A' }}</div>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $clearance->created_at?->format('M d, Y') ?? 'N/A' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        @php
                        $status = strtolower($clearance->status ?? 'pending');
                        $color = match ($status) {
                        'approved' => 'green',
                        'rejected' => 'red',
                        default => 'amber',
                        };
                        @endphp
                        <flux:badge size="sm" :color="$color" class="capitalize">
                            {{ ucfirst($status) }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"
                                aria-label="Actions" />
                            <flux:menu>
                                <flux:menu.item icon="eye"
                                    href="{{ route('client.applications.show', $clearance->id) }}" wire:navigate>
                                    View
                                </flux:menu.item>
                                <flux:menu.item icon="pencil-square"
                                    href="{{ route('client.applications.edit', $clearance->id) }}" wire:navigate>
                                    Edit
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger"
                                    wire:click="confirmDelete({{ $clearance->id }})">
                                    Delete
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <flux:icon icon="document-text" class="size-8 text-zinc-400" />
                            <span>No applications match your search criteria.</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Responsive Delete Confirmation Modal -->
    <flux:modal name="delete-application-modal" class="w-full max-w-sm sm:max-w-md mx-4" backdrop="blur">
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-red-500/10 text-red-600 dark:text-red-400">
                    <flux:icon name="exclamation-triangle" class="size-6" />
                </div>
                <div>
                    <flux:heading size="lg">Delete Application?</flux:heading>
                    <flux:subheading class="text-xs">Permanent action</flux:subheading>
                </div>
            </div>

            <flux:text class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300">
                Are you sure you want to delete this application? This action cannot be undone and will erase all
                submitted details.
            </flux:text>

            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" size="sm" wire:click="deleteApplication">
                    Yes, Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>