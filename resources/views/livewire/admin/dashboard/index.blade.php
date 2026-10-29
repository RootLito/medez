<?php

use function Livewire\Volt\{layout, title, state, computed};
use App\Models\LocClearance;
use App\Models\PaymentOrder;
use App\Models\Inspection;
use App\Models\ZoneLocation;
use Carbon\Carbon;
use Flux\Flux;

layout('components.layouts.admin');
title('Executive Operations Dashboard');

// 1. Executive Operational Metrics
$stats = computed(function () {
    $now = Carbon::now();

    $totalApplications = LocClearance::count();
    $thisMonthCount = LocClearance::whereMonth('created_at', $now->month)
        ->whereYear('created_at', $now->year)
        ->count();
    $lastMonthCount = LocClearance::whereMonth('created_at', $now->copy()->subMonth()->month)
        ->whereYear('created_at', $now->copy()->subMonth()->year)
        ->count();

    if ($lastMonthCount > 0) {
        $pctChange = round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100);
        $totalChange = ($pctChange >= 0 ? "+{$pctChange}%" : "{$pctChange}%") . ' vs last month';
    } else {
        $totalChange = $thisMonthCount > 0 ? "+{$thisMonthCount} this month" : 'No change';
    }

    $pendingCount = LocClearance::where('status', 'pending')->count();
    $urgentPending = LocClearance::where('status', 'pending')
        ->where('created_at', '<=', $now->copy()->subDays(3))
        ->count();

    $approvedThisMonth = LocClearance::whereIn('status', ['approve', 'approved'])
        ->whereMonth('updated_at', $now->month)
        ->whereYear('updated_at', $now->year)
        ->count();

    $totalDecided = LocClearance::whereIn('status', ['approve', 'approved', 'rejected'])->count();
    $totalApproved = LocClearance::whereIn('status', ['approve', 'approved'])->count();
    $approvalRate = $totalDecided > 0 ? round(($totalApproved / $totalDecided) * 100, 1) . '%' : '100%';

    $monthlyRevenue = class_exists(PaymentOrder::class)
        ? PaymentOrder::where('status', 'paid')
            ->whereMonth('payment_date', $now->month)
            ->whereYear('payment_date', $now->year)
            ->sum('grand_total')
        : 0;

    $pendingInspections = class_exists(Inspection::class)
        ? Inspection::whereIn('status', ['scheduled', 'in_progress'])->count()
        : 0;

    return [
        'total_applications'   => $totalApplications,
        'total_change'         => $totalChange,
        'pending_verification' => $pendingCount,
        'pending_urgent'       => $urgentPending,
        'approved_this_month'  => $approvedThisMonth,
        'approval_rate'        => $approvalRate,
        'monthly_revenue'      => (float) $monthlyRevenue,
        'pending_inspections'  => $pendingInspections,
    ];
});

// 2. Monthly Trend Chart Data (Past 6 Months)
$chartData = computed(function () {
    $months = [];
    $applications = [];
    $approved = [];

    for ($i = 5; $i >= 0; $i--) {
        $targetMonth = Carbon::now()->subMonths($i);
        $label = $targetMonth->format('M Y');
        $months[] = $label;

        $applications[] = LocClearance::whereMonth('created_at', $targetMonth->month)
            ->whereYear('created_at', $targetMonth->year)
            ->count();

        $approved[] = LocClearance::whereIn('status', ['approve', 'approved'])
            ->whereMonth('updated_at', $targetMonth->month)
            ->whereYear('updated_at', $targetMonth->year)
            ->count();
    }

    return [
        'labels'       => $months,
        'applications' => $applications,
        'approved'     => $approved,
    ];
});

// 3. Zoning Category Share
$zoningShare = computed(function () {
    return LocClearance::selectRaw('COALESCE(existing_land_use, project_type, "General") as category, count(*) as count')
        ->groupBy('category')
        ->orderByDesc('count')
        ->take(4)
        ->get();
});

// 4. Geotagged Clearances for Leaflet GIS Map
$mapLocations = computed(function () {
    if (class_exists(ZoneLocation::class)) {
        return ZoneLocation::latest()->take(25)->get()->map(function ($loc) {
            return [
                'id'        => $loc->id,
                'title'     => $loc->title ?? 'Clearance Location',
                'zone_type' => $loc->zone_type ?? 'Commercial',
                'lat'       => (float) $loc->latitude,
                'lng'       => (float) $loc->longitude,
                'notes'     => $loc->notes ?? '',
            ];
        })->toArray();
    }
    return [];
});

// 5. Recent 5 Applications
$recentApplications = computed(function () {
    return LocClearance::with('client')->latest()->take(5)->get();
});

// 6. Upcoming Field Inspections
$upcomingInspections = computed(function () {
    if (class_exists(Inspection::class)) {
        return Inspection::with(['locClearance', 'inspector'])
            ->whereIn('status', ['scheduled', 'rescheduled', 'in_progress'])
            ->whereDate('scheduled_date', '>=', today())
            ->orderBy('scheduled_date', 'asc')
            ->take(4)
            ->get();
    }
    return collect();
});

$refreshData = function () {
    Flux::toast(text: 'Operational metrics refreshed.', variant: 'success');
};

?>

<div class="space-y-6">
    <!-- Header -->
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-200 dark:border-zinc-800 pb-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">
                    Executive Operations Center
                </flux:heading>
                <flux:badge size="sm" variant="subtle" class="font-mono text-[10px]">
                    {{ now()->format('F Y') }}
                </flux:badge>
            </div>
            <flux:subheading class="text-xs sm:text-sm">
                Office of the Municipal Planning & Development Coordinator (MPDC) • Malalag, Davao del Sur
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button type="button" onclick="window.location.reload()" icon="arrow-path" size="sm" variant="filled" icon-variant="outline">
                Refresh
            </flux:button>
            <flux:button variant="primary" icon="plus" size="sm" wire:navigate
                href="{{ route('admin.applications.walkin-index') }}">
                Encode Walk-In
            </flux:button>
        </div>
    </div>

    @include('livewire.admin.dashboard.partials.metrics')
    @include('livewire.admin.dashboard.partials.chart-analytics')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
        @include('livewire.admin.dashboard.partials.recent-applications')
        @include('livewire.admin.dashboard.partials.upcoming-inspections')
    </div>
    @include('livewire.admin.dashboard.partials.gis-overview-map')
</div>