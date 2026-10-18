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
    'selectedClearance' => null,
]);

$clearances = computed(function () {
    return LocClearance::query()
        ->where('is_walk_in', false)
        ->with('client')
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('application_no', 'like', '%' . $this->search . '%')
                    ->orWhere('applicant_name', 'like', '%' . $this->search . '%')
                    ->orWhere('corporation_name', 'like', '%' . $this->search . '%')
                    ->orWhere('project_type', 'like', '%' . $this->search . '%')
                    ->orWhere('or_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($cq) {
                        $cq->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            });
        })
        ->when($this->status !== 'all', function ($query) {
            $query->where('status', $this->status);
        })
        ->when($this->dateFrom, function ($query) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        })
        ->when($this->dateTo, function ($query) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        })
        ->latest()
        ->paginate(15);
});

$viewDetails = function ($id) {
    $this->selectedClearance = LocClearance::with('client')->findOrFail($id);
    Flux::modal('view-details-modal')->show();
};

$resetFilters = function () {
    $this->search = '';
    $this->status = 'all';
    $this->dateFrom = '';
    $this->dateTo = '';
};

?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl" level="1">Digital Logbook</flux:heading>
        <flux:subheading>View, filter, and manage all submitted locational clearance records.</flux:subheading>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
        <div class="lg:col-span-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search app no, name, OR no, or client..." clearable />
        </div>

        <div class="lg:col-span-2">
            <flux:select wire:model.live="status" placeholder="Status">
                <flux:select.option value="all">All Statuses</flux:select.option>
                <flux:select.option value="pending">Pending</flux:select.option>
                <flux:select.option value="approved">Approved</flux:select.option>
                <flux:select.option value="rejected">Rejected</flux:select.option>
            </flux:select>
        </div>

        <div class="lg:col-span-2">
            <flux:input type="date" wire:model.live="dateFrom" placeholder="From Date" />
        </div>

        <div class="lg:col-span-2">
            <flux:input type="date" wire:model.live="dateTo" placeholder="To Date" />
        </div>
    </div>

    <flux:table :paginate="$this->clearances">
        <flux:table.columns>
            <flux:table.column>App / OR No.</flux:table.column>
            <flux:table.column>Client Account</flux:table.column>
            <flux:table.column>Applicant & Corporation</flux:table.column>
            <flux:table.column>Project Info</flux:table.column>
            <flux:table.column>Date Submitted</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($this->clearances as $clearance)
                <flux:table.row :key="$clearance->id">
                    <flux:table.cell>
                        <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $clearance->application_no ?? 'Unassigned' }}
                        </div>
                        @if ($clearance->or_number)
                            <div class="text-xs text-zinc-500 dark:text-zinc-400 font-mono">
                                OR: {{ $clearance->or_number }}
                            </div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-sm">
                            {{ $clearance->client->name ?? 'N/A' }}
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $clearance->client->email ?? '' }}
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-sm">
                            {{ $clearance->applicant_name }}
                        </div>
                        @if ($clearance->corporation_name)
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $clearance->corporation_name }}
                            </div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-sm">
                            {{ $clearance->project_type ?? 'N/A' }}
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                            {{ $clearance->project_location ?? 'N/A' }}
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap text-sm">
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

                    <flux:table.cell>
                        <flux:button href="{{ route('admin.applications.show', $clearance->id) }}" variant="filled"
                            size="sm" icon:variant="outline" icon="eye" tooltip="View Details" wire:navigate />
                        <flux:button variant="filled" size="sm" icon:variant="outline"
                            icon="archive-box-arrow-down" tooltip="Achive" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <flux:icon icon="document-duplicate" class="size-8 text-zinc-400" />
                            <span>No clearance records found matching your filters.</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
