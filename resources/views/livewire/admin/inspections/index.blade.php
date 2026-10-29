<?php

use function Livewire\Volt\{layout, title, state, usesPagination, computed, updated};
use App\Models\Inspection;
use App\Models\LocClearance;
use Flux\Flux;

layout('components.layouts.admin');
title('Inspection Schedule');

usesPagination();

state([
    // Filters & Search
    'search' => '',
    'status' => 'all',
    'dateFrom' => '',
    'dateTo' => '',

    // Schedule Inspection Form State
    'application_id' => '',
    'inspector_id' => '',
    'inspection_type' => 'Ocular Site Inspection',
    'scheduled_date' => '',
    'scheduled_time' => '09:00',
    'remarks' => '',

    // Complete / Findings Form State
    'completing_id' => null,
    'compliance_status' => 'compliant',
    'findings' => '',
    'recommendations' => '',
]);

updated([
    'search' => fn () => $this->resetPage(),
    'status' => fn () => $this->resetPage(),
]);

// Computed: Inspections query
$inspections = computed(function () {
    return Inspection::query()
        ->with(['locClearance.client', 'inspector'])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('inspection_no', 'like', '%' . $this->search . '%')
                  ->orWhere('inspection_type', 'like', '%' . $this->search . '%')
                  ->orWhereHas('locClearance', function ($cq) {
                      $cq->where('application_no', 'like', '%' . $this->search . '%')
                         ->orWhere('applicant_name', 'like', '%' . $this->search . '%')
                         ->orWhere('project_location', 'like', '%' . $this->search . '%');
                  });
            });
        })
        ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
        ->when($this->dateFrom, fn ($q) => $q->whereDate('scheduled_date', '>=', $this->dateFrom))
        ->when($this->dateTo, fn ($q) => $q->whereDate('scheduled_date', '<=', $this->dateTo))
        ->latest('scheduled_date')
        ->paginate(15);
});

// Computed: Active clearances that can be scheduled
$availableClearances = computed(function () {
    return LocClearance::where('is_archived', false)
        ->latest()
        ->get();
});

// Computed: Available inspectors (Admin / Staff)
$inspectors = computed(function () {
    $model = config('auth.providers.admins.model')
        ?? (class_exists(\App\Models\Admin::class) ? \App\Models\Admin::class : \App\Models\User::class);

    return $model::all();
});

// Computed: The inspection currently being completed
$completingInspection = computed(function () {
    return $this->completing_id ? Inspection::with('locClearance')->find($this->completing_id) : null;
});

// Action: Open Schedule Modal
$openScheduleModal = function () {
    $this->reset(['application_id', 'remarks']);
    $this->scheduled_date = now()->addDay()->format('Y-m-d');
    $this->scheduled_time = '09:00';
    $this->inspection_type = 'Ocular Site Inspection';
    $this->inspector_id = (string) (auth('admin')->id() ?? auth()->id() ?? '');

    Flux::modal('schedule-modal')->show();
};

// Action: Store New Inspection Schedule
$createSchedule = function () {
    $this->validate([
        'application_id'  => ['required', 'exists:loc_clearances,id'],
        'inspector_id'    => ['required'],
        'inspection_type' => ['required', 'string'],
        'scheduled_date'  => ['required', 'date'],
        'scheduled_time'  => ['nullable'],
    ], [
        'application_id.required' => 'Please choose a clearance application.',
        'inspector_id.required'   => 'Please assign an inspector.',
        'scheduled_date.required' => 'Please set an inspection date.',
    ]);

    $nextId = (Inspection::max('id') ?? 0) + 1;
    $inspectionNo = 'INSP-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

    Inspection::create([
        'inspection_no'     => $inspectionNo,
        'application_id'    => $this->application_id,
        'inspector_id'      => $this->inspector_id,
        'inspection_type'   => $this->inspection_type,
        'scheduled_date'    => $this->scheduled_date,
        'scheduled_time'    => $this->scheduled_time,
        'status'            => 'scheduled',
        'compliance_status' => 'pending',
        'remarks'           => $this->remarks,
    ]);

    Flux::modal('schedule-modal')->close();
    Flux::toast(text: 'Site inspection successfully scheduled.', variant: 'success');
};

// Action: Open Complete / Findings Modal
$openCompleteModal = function ($id) {
    $this->completing_id = $id;
    $inspection = Inspection::findOrFail($id);

    $this->compliance_status = $inspection->compliance_status !== 'pending' ? $inspection->compliance_status : 'compliant';
    $this->findings = $inspection->findings ?? '';
    $this->recommendations = $inspection->recommendations ?? '';

    Flux::modal('complete-modal')->show();
};

// Action: Save Findings & Complete Inspection
$saveFindings = function () {
    $this->validate([
        'compliance_status' => ['required', 'in:compliant,non_compliant,conditional'],
        'findings'          => ['required', 'string', 'min:5'],
        'recommendations'   => ['nullable', 'string'],
    ], [
        'findings.required' => 'Please document the site findings and setback observations.',
    ]);

    $inspection = Inspection::findOrFail($this->completing_id);
    $inspection->update([
        'compliance_status' => $this->compliance_status,
        'findings'          => $this->findings,
        'recommendations'   => $this->recommendations,
        'status'            => 'completed',
        'inspected_at'      => now(),
    ]);

    $this->completing_id = null;
    Flux::modal('complete-modal')->close();
    Flux::toast(text: 'Inspection findings recorded and marked as completed!', variant: 'success');
};

// Action: Cancel Scheduled Inspection
$cancelInspection = function ($id) {
    Inspection::where('id', $id)->update(['status' => 'cancelled']);
    Flux::toast(text: 'Inspection schedule cancelled.', variant: 'danger');
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Inspection Schedule</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Manage ocular site visits, assign inspectors, and log site
                compliance.</flux:subheading>
        </div>

        <flux:button wire:click="openScheduleModal" variant="primary" size="sm" icon="plus">
            Schedule Inspection
        </flux:button>
    </div>

    <!-- Filters matching Digital Logbook & Payments -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 sm:gap-3">
        <div class="sm:col-span-2 lg:col-span-5">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search insp no, app no, applicant, location..." clearable />
        </div>

        <div class="lg:col-span-3">
            <flux:select wire:model.live="status" placeholder="Status">
                <flux:select.option value="all">All Statuses</flux:select.option>
                <flux:select.option value="scheduled">Scheduled</flux:select.option>
                <flux:select.option value="in_progress">In Progress</flux:select.option>
                <flux:select.option value="completed">Completed</flux:select.option>
                <flux:select.option value="rescheduled">Rescheduled</flux:select.option>
                <flux:select.option value="cancelled">Cancelled</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:col-span-4">
            <flux:input type="date" wire:model.live="dateFrom" />
            <flux:input type="date" wire:model.live="dateTo" />
        </div>
    </div>

    <!-- Content Partials -->
    @include('livewire.admin.inspections.partials.table')
    @include('livewire.admin.inspections.partials.schedule-modal')
    @include('livewire.admin.inspections.partials.complete-modal')
</div>