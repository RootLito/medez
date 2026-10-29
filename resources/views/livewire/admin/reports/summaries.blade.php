<?php

use function Livewire\Volt\{layout, title, state, computed, updated};
use App\Models\LocClearance;
use App\Models\PaymentOrder;
use App\Models\Inspection;
use Carbon\Carbon;
use Flux\Flux;

layout('components.layouts.admin');
title('Application Summaries');

state([
    // Period filter: this_month, this_quarter, this_year, custom
    'period' => 'this_month',
    'dateFrom' => now()->startOfMonth()->format('Y-m-d'),
    'dateTo' => now()->endOfMonth()->format('Y-m-d'),
    'showPrintModal' => false,
]);

// Handle date presets when period filter changes
updated([
    'period' => function ($value) {
        match ($value) {
            'this_month' => [
                $this->dateFrom = now()->startOfMonth()->format('Y-m-d'),
                $this->dateTo = now()->endOfMonth()->format('Y-m-d'),
            ],
            'last_month' => [
                $this->dateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d'),
                $this->dateTo = now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            'this_quarter' => [
                $this->dateFrom = now()->firstOfQuarter()->format('Y-m-d'),
                $this->dateTo = now()->lastOfQuarter()->format('Y-m-d'),
            ],
            'this_year' => [
                $this->dateFrom = now()->startOfYear()->format('Y-m-d'),
                $this->dateTo = now()->endOfYear()->format('Y-m-d'),
            ],
            'custom' => null,
        };
    },
]);

// Computed: High-level KPI metrics
$kpi = computed(function () {
    $apps = LocClearance::whereBetween('created_at', [
        Carbon::parse($this->dateFrom)->startOfDay(),
        Carbon::parse($this->dateTo)->endOfDay(),
    ]);

    $totalApps = (clone $apps)->count();
    $approvedApps = (clone $apps)->whereIn('status', ['approve', 'approved'])->count();
    $pendingApps = (clone $apps)->where('status', 'pending')->count();
    $walkInApps = (clone $apps)->where('is_walk_in', true)->count();
    $onlineApps = (clone $apps)->where('is_walk_in', false)->count();

    // Total Revenue collected via Payment Orders
    $revenueCollected = PaymentOrder::where('status', 'paid')
        ->whereBetween('payment_date', [$this->dateFrom, $this->dateTo])
        ->sum('grand_total');

    // Inspections conducted
    $inspectionsCompleted = Inspection::where('status', 'completed')
        ->whereBetween('inspected_at', [
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay(),
        ])
        ->count();

    return [
        'total_apps' => $totalApps,
        'approved_apps' => $approvedApps,
        'pending_apps' => $pendingApps,
        'walk_in' => $walkInApps,
        'online' => $onlineApps,
        'revenue' => (float) $revenueCollected,
        'inspections_done' => $inspectionsCompleted,
        'approval_rate' => $totalApps > 0 ? round(($approvedApps / $totalApps) * 100, 1) : 0,
    ];
});

// Computed: Breakdown by Land Use / Category
$categoryBreakdown = computed(function () {
    return LocClearance::selectRaw('COALESCE(existing_land_use, project_type) as category, count(*) as count')
        ->whereBetween('created_at', [
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay(),
        ])
        ->groupBy('category')
        ->orderByDesc('count')
        ->get();
});

// Computed: Breakdown by Application Status
$statusBreakdown = computed(function () {
    return LocClearance::selectRaw('status, count(*) as total')
        ->whereBetween('created_at', [
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay(),
        ])
        ->groupBy('status')
        ->get()
        ->pluck('total', 'status')
        ->toArray();
});

$openPrintModal = function () {
    Flux::modal('summary-print-modal')->show();
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Application Summaries</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">MPDC executive analytics, revenue collection, and regulatory
                zoning accomplishment metrics.</flux:subheading>
        </div>

        <!-- Fixed -->
        <flux:button wire:click="openPrintModal" variant="primary" size="sm" icon="printer">
            Generate Accomplishment Report
        </flux:button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 sm:gap-3 items-end">
        <div class="sm:col-span-4">
            <flux:select wire:model.live="period" placeholder="Select Period" label="Select Period">
                <flux:select.option value="this_month">This Month</flux:select.option>
                <flux:select.option value="last_month">Last Month</flux:select.option>
                <flux:select.option value="this_quarter">This Quarter</flux:select.option>
                <flux:select.option value="this_year">This Calendar Year</flux:select.option>
                <flux:select.option value="custom">Custom Date Range</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:col-span-8">
            <flux:input type="date" wire:model.live="dateFrom" :disabled="$period !== 'custom'" label="From" />
            <flux:input type="date" wire:model.live="dateTo" :disabled="$period !== 'custom'" label="To" />
        </div>
    </div>

    <!-- Partials -->
    @include('livewire.admin.reports.partials.summary-cards')
    @include('livewire.admin.reports.partials.summary-breakdowns')
    @include('livewire.admin.reports.partials.summary-print-modal')
</div>