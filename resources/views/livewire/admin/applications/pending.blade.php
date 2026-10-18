<?php

use App\Models\LocClearance;
use Flux\Flux;
use function Livewire\Volt\{layout, state, title, with};

layout('components.layouts.admin');
title('Pending Verification');

state([
    'search' => '',
    'typeFilter' => 'all',
    'deletingId' => null,
    'confirmAppNo' => '',
    'showDeleteModal' => false,
    'deletingApplication' => null,
]);

with(function () {
    $query = LocClearance::where('status', 'pending');

    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('application_no', 'like', '%' . $this->search . '%')
                ->orWhere('applicant_name', 'like', '%' . $this->search . '%')
                ->orWhere('project_type', 'like', '%' . $this->search . '%');
        });
    }

    if ($this->typeFilter === 'walk_in') {
        $query->where('is_walk_in', true);
    } elseif ($this->typeFilter === 'online') {
        $query->where('is_walk_in', false);
    }

    return [
        'applications' => $query->latest()->paginate(10),
    ];
});

// Open modal for deletion target
$confirmDelete = function ($id) {
    $this->deletingId = $id;
    $this->deletingApplication = LocClearance::find($id);
    $this->confirmAppNo = '';
    $this->showDeleteModal = true;
};

// Execute deletion after matching application number
$delete = function () {
    if (!$this->deletingId) {
        return;
    }

    $application = LocClearance::findOrFail($this->deletingId);

    if (!$application->is_walk_in) {
        Flux::toast(text: 'Client applications cannot be deleted.', variant: 'danger');
        $this->showDeleteModal = false;
        return;
    }

    $targetNumber = trim($application->application_no ?? 'N/A');

    if (trim($this->confirmAppNo) !== $targetNumber) {
        Flux::toast(text: 'Application number does not match.', variant: 'danger');
        return;
    }

    $application->delete();

    $this->deletingId = null;
    $this->deletingApplication = null;
    $this->confirmAppNo = '';
    $this->showDeleteModal = false;

    Flux::toast(text: 'Walk-in application deleted successfully.', variant: 'success');
};

?>

<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1">Pending Verification</flux:heading>
            <flux:subheading>Applications awaiting staff review and approval.</flux:subheading>
        </div>
    </div>

    {{-- Filters & Search Bar --}}
    <div class="flex flex-col sm:flex-row items-center gap-4 mb-6">
        <div class="w-full sm:w-80">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search App No., Applicant..."
                icon="magnifying-glass" />
        </div>

        <div class="w-full sm:w-48">
            <flux:select wire:model.live="typeFilter">
                <flux:select.option value="all">All Applications</flux:select.option>
                <flux:select.option value="walk_in">Walk-in Only</flux:select.option>
                <flux:select.option value="online">Online Client Only</flux:select.option>
            </flux:select>
        </div>
    </div>

    @if ($applications->isEmpty())
        <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
            <flux:icon.clock class="mx-auto h-12 w-12 text-zinc-400" />
            <flux:heading size="lg" class="mt-4">No Applications Found</flux:heading>
            <flux:text class="mt-1">No pending applications match your current criteria.</flux:text>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>App No.</flux:table.column>
                <flux:table.column>Applicant</flux:table.column>
                <flux:table.column>Project Type</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Date Submitted</flux:table.column>
                <flux:table.column class="w-px whitespace-nowrap">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($applications as $app)
                    <flux:table.row :key="$app->id">
                        <flux:table.cell class="font-medium">
                            {{ $app->application_no ?? 'N/A' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $app->applicant_name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $app->project_type }}
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($app->is_walk_in)
                                <flux:badge color="zinc" size="sm">Walk-in</flux:badge>
                            @else
                                <flux:badge color="blue" size="sm">Online Client</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $app->created_at->format('M d, Y') }}
                        </flux:table.cell>

                        <flux:table.cell class="w-px whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button :href="route('admin.applications.show', $app->id)" size="sm"
                                    variant="filled" icon="eye" wire:navigate icon:variant="outline" />

                                @if ($app->is_walk_in)
                                    <flux:button :href="route('admin.applications.walkin-edit', $app->id)"
                                        size="sm" variant="filled" icon="pencil-square" icon:variant="outline"
                                        wire:navigate />

                                    <flux:button wire:click="confirmDelete({{ $app->id }})" size="sm"
                                        variant="filled" color="red" icon="trash" icon:variant="outline" />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        <div class="mt-4">
            {{ $applications->links() }}
        </div>
    @endif

    {{-- Updated Modal Component --}}
    <flux:modal name="delete-confirmation-modal" wire:model="showDeleteModal" class="md:w-[28rem]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-500">Delete Walk-in Application
                </flux:heading>
                <flux:subheading class="mt-1">
                    This action cannot be undone. To confirm, type <strong
                        class="text-zinc-900 dark:text-zinc-100">{{ $deletingApplication?->application_no ?? 'N/A' }}</strong>
                    below:
                </flux:subheading>
            </div>

            @if ($deletingApplication)
                <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-md text-sm">
                    <p><span class="text-zinc-500">Applicant:</span>
                        <strong>{{ $deletingApplication->applicant_name }}</strong>
                    </p>
                    <p><span class="text-zinc-500">Target App No:</span>
                        <strong class="select-all font-mono text-red-600 dark:text-red-400">
                            {{ $deletingApplication->application_no ?? 'N/A' }}
                        </strong>
                    </p>
                </div>
            @endif

            <flux:input wire:model.live="confirmAppNo" placeholder="Enter application number..." autocomplete="off" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger" 
                    :disabled="trim($confirmAppNo) !== trim($deletingApplication?->application_no ?? 'N/A')">
                    Confirm Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
