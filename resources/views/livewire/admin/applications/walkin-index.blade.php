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
]);

$clearances = computed(function () {
    return LocClearance::query()
        ->where('is_walk_in', true)
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
            $query->where('status', $this->status);
        })
        ->latest()
        ->paginate(10);
});

// Trigger the deletion confirmation modal
$confirmDelete = function ($id) {
    $this->deletingId = $id;
    Flux::modal('delete-application-modal')->show();
};

// Handle actual record deletion
$deleteApplication = function () {
    if (!$this->deletingId) {
        return;
    }

    $clearance = LocClearance::where('is_walk_in', true)->findOrFail($this->deletingId);
    $clearance->delete();

    $this->deletingId = null;

    Flux::modal('delete-application-modal')->close();

    Flux::toast(text: 'Walk-in record deleted successfully.', variant: 'success');
};

?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl" level="1">Walk-In Applications</flux:heading>
        <flux:subheading>Manage and encode locational clearance applications received in-person.</flux:subheading>
    </div>

    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 sm:gap-4">
        <div class="w-full sm:w-96">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search app no, applicant, OR no, or project..." clearable />
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="flex-1 sm:w-48">
                <flux:select wire:model.live="status" placeholder="Filter Status">
                    <flux:select.option value="all">All Statuses</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="approved">Approved</flux:select.option>
                    <flux:select.option value="rejected">Rejected</flux:select.option>
                </flux:select>
            </div>

            <div class="shrink-0">
                <flux:button href="{{ route('admin.applications.walkin-create') }}" wire:navigate variant="primary"
                    icon="plus" class="w-full sm:w-auto">
                    Encode Walk-In Form
                </flux:button>
            </div>
        </div>
    </div>

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
                <flux:table.row :key="$clearance->id">
                    <flux:table.cell>
                        <div class="font-mono text-xs font-semibold">
                            {{ $clearance->application_no ?? 'Pending Assign' }}
                        </div>
                        @if ($clearance->or_number)
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                OR: {{ $clearance->or_number }}
                            </div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium">
                            {{ $clearance->applicant_name }}
                        </div>
                        @if ($clearance->corporation_name)
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $clearance->corporation_name }}
                            </div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium">{{ $clearance->project_type ?? 'N/A' }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                            {{ $clearance->project_location ?? 'N/A' }}</div>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap text-xs">
                        {{ $clearance->date_of_receipt?->format('M d, Y') ?? $clearance->created_at?->format('M d, Y') }}
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
                        <div class="flex items-center justify-end gap-1">
                            <flux:button variant="filled" size="sm" icon:variant="outline" icon="eye"
                                tooltip="View Details"
                                href="{{ route('admin.applications.walkin-show', $clearance->id) }}" wire:navigate />
                            <flux:button variant="filled" size="sm" icon:variant="outline" icon="pencil-square"
                                tooltip="Edit Record"
                                href="{{ route('admin.applications.walkin-edit', $clearance->id) }}" wire:navigate />
                            <flux:button variant="filled" color="red" size="sm" icon:variant="outline" icon="trash"
                                tooltip="Delete" wire:click="confirmDelete({{ $clearance->id }})" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <flux:icon icon="document-text" class="size-8 text-zinc-400" />
                            <span>No walk-in applications match your search criteria.</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="delete-application-modal" class="min-w-100" backdrop="blur">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Walk-In Record?</flux:heading>

                <flux:text class="mt-2">
                    Are you sure you want to delete this walk-in application record?<br>
                    This action cannot be undone.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="subtle">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="deleteApplication">
                    Delete Record
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
