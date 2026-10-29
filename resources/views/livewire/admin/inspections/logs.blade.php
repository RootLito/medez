<?php

use function Livewire\Volt\{layout, title, state, usesPagination, computed, updated};
use App\Models\Inspection;
use Flux\Flux;

layout('components.layouts.admin');
title('Inspection Logs');

usesPagination();

state([
    'search' => '',
    'compliance' => 'all',
    'dateFrom' => '',
    'dateTo' => '',
    'selected_log_id' => null,
]);

updated([
    'search' => fn () => $this->resetPage(),
    'compliance' => fn () => $this->resetPage(),
]);

// Computed: Summary stats for completed inspections
$stats = computed(function () {
    $base = Inspection::where('status', 'completed');

    return [
        'total' => (clone $base)->count(),
        'compliant' => (clone $base)->where('compliance_status', 'compliant')->count(),
        'conditional' => (clone $base)->where('compliance_status', 'conditional')->count(),
        'non_compliant' => (clone $base)->where('compliance_status', 'non_compliant')->count(),
    ];
});

// Computed: Paginated Inspection Logs query
$logs = computed(function () {
    return Inspection::query()
        ->where('status', 'completed')
        ->with(['locClearance.client', 'inspector'])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('inspection_no', 'like', '%' . $this->search . '%')
                  ->orWhere('findings', 'like', '%' . $this->search . '%')
                  ->orWhereHas('locClearance', function ($cq) {
                      $cq->where('application_no', 'like', '%' . $this->search . '%')
                         ->orWhere('applicant_name', 'like', '%' . $this->search . '%')
                         ->orWhere('project_location', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('inspector', function ($iq) {
                      $iq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        })
        ->when($this->compliance !== 'all', fn ($q) => $q->where('compliance_status', $this->compliance))
        ->when($this->dateFrom, fn ($q) => $q->whereDate('inspected_at', '>=', $this->dateFrom))
        ->when($this->dateTo, fn ($q) => $q->whereDate('inspected_at', '<=', $this->dateTo))
        ->latest('inspected_at')
        ->paginate(15);
});

// Computed: Active log selected for detailed modal & printing
$selectedLog = computed(function () {
    return $this->selected_log_id 
        ? Inspection::with(['locClearance.client', 'inspector'])->find($this->selected_log_id) 
        : null;
});

// Action: Open Detail / Print Modal
$openLogDetail = function ($id) {
    $this->selected_log_id = $id;
    Flux::modal('log-detail-modal')->show();
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Inspection Logs</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Audit trail of completed site inspections, ocular observations,
                and setback evaluations.</flux:subheading>
        </div>

        <flux:button href="{{ route('admin.inspections.index') }}" wire:navigate variant="subtle" size="sm"
            icon="calendar-days">
            View Schedule
        </flux:button>
    </div>

    <!-- Quick Stats Cards -->
    {{-- <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <flux:card class="p-3.5 sm:p-4 space-y-1">
            <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Conducted</span>
            <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total'] }}</div>
        </flux:card>

        <flux:card class="p-3.5 sm:p-4 space-y-1">
            <span
                class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Compliant</span>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $this->stats['compliant'] }}</div>
        </flux:card>

        <flux:card class="p-3.5 sm:p-4 space-y-1">
            <span
                class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Conditional</span>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $this->stats['conditional'] }}</div>
        </flux:card>

        <flux:card class="p-3.5 sm:p-4 space-y-1">
            <span
                class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">Non-Compliant</span>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->stats['non_compliant'] }}</div>
        </flux:card>
    </div> --}}

    <!-- Filters matching Digital Logbook & Payments -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 sm:gap-3">
        <div class="sm:col-span-2 lg:col-span-5">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search insp no, app no, applicant, findings..." clearable />
        </div>

        <div class="lg:col-span-3">
            <flux:select wire:model.live="compliance" placeholder="Compliance Result">
                <flux:select.option value="all">All Outcomes</flux:select.option>
                <flux:select.option value="compliant">Compliant</flux:select.option>
                <flux:select.option value="conditional">Conditional</flux:select.option>
                <flux:select.option value="non_compliant">Non-Compliant</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:col-span-4">
            <flux:input type="date" wire:model.live="dateFrom" />
            <flux:input type="date" wire:model.live="dateTo" />
        </div>
    </div>

    <!-- Partials -->
    @include('livewire.admin.inspections.partials.log-table')
    @include('livewire.admin.inspections.partials.log-detail-modal')
</div>