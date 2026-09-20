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

// Trigger the confirmation modal
$confirmDelete = function ($id) {
    $this->deletingId = $id;
    Flux::modal('delete-application-modal')->show();
};

// Handle actual deletion
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

<div class="space-y-6">
    <!-- Header -->
    <div>
        <flux:heading size="xl" level="1">Locational Clearance Applications</flux:heading>
        <flux:subheading>Manage and track your submitted clearance application forms.</flux:subheading>
    </div>

    <!-- Toolbar: Search, Filter, & Create Button -->
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 sm:gap-4">
        <div class="w-full sm:w-100">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search application no, applicant, or project..."
                clearable />
        </div>

        <!-- Mobile Row: Status Filter & Action Button Side-by-Side -->
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <!-- Status Filter -->
            <div class="flex-1 sm:w-48">
                <flux:select wire:model.live="status" placeholder="Filter Status">
                    <flux:select.option value="all">All Statuses</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="approved">Approved</flux:select.option>
                    <flux:select.option value="rejected">Rejected</flux:select.option>
                </flux:select>
            </div>

            <!-- New Application Button -->
            <div class="shrink-0">
                <flux:button
                    href="{{ route('client.applications.create') }}"
                    wire:navigate
                    variant="primary"
                    icon="plus"
                    class="w-full sm:w-auto">
                    Submit New Application
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
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
                    <div class="font-medium">
                        {{ $clearance->applicant_name }}
                    </div>
                    @if($clearance->corporation_name)
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $clearance->corporation_name }}</div>
                    @endif
                </flux:table.cell>

                <flux:table.cell>
                    <div class="font-medium">{{ $clearance->project_type ?? 'N/A' }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-xs">{{ $clearance->project_location ?? 'N/A' }}</div>
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap">
                    {{ $clearance->created_at?->format('M d, Y') ?? 'N/A' }}
                </flux:table.cell>

                <flux:table.cell>
                    @php
                    $status = strtolower($clearance->status ?? 'pending');
                    $color = match ($status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                    };
                    @endphp
                    <flux:badge size="sm" :color="$color" inset="top bottom">
                        {{ ucfirst($status) }}
                    </flux:badge>
                </flux:table.cell>

                <flux:table.cell align="end">
                    <flux:dropdown position="bottom" align="end">
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="ellipsis-horizontal"
                            inset="top bottom"
                            aria-label="Actions" />

                        <flux:menu>
                            <!-- View Action -->
                            <flux:menu.item
                                icon="eye"
                                href="{{ route('client.applications.show', $clearance->id) }}"
                                wire:navigate>
                                View
                            </flux:menu.item>

                            <!-- Edit/Update Action -->
                            <flux:menu.item
                                icon="pencil-square"
                                href="{{ route('client.applications.edit', $clearance->id) }}"
                                wire:navigate>
                                Edit
                            </flux:menu.item>

                            <flux:menu.separator />

                            <!-- Delete Action -->
                            <flux:menu.item
                                icon="trash"
                                variant="danger"
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

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-application-modal" class="min-w-100" backdrop="blur">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Application?</flux:heading>

                <flux:text class="mt-2">
                    Are you sure you want to delete this application?<br>
                    This action cannot be undone.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="subtle">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="deleteApplication">
                    Delete Application
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>