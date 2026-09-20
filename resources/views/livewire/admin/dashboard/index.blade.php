<?php

use function Livewire\Volt\{layout, state, title};

layout('components.layouts.admin');
title('Dashboard');

state([
    'stats' => [
        'total_applications' => 1248,
        'total_change' => '+12% from last month',
        'pending_verification' => 42,
        'pending_urgent' => 8,
        'scheduled_inspections' => 19,
        'today_inspections' => 5,
        'approved_this_month' => 310,
        'approval_rate' => '94.2%',
    ],
    'recentApplications' => [
        [
            'id' => 'APP-2026-0891',
            'applicant' => 'Maria Santos',
            'type' => 'Zoning Clearance',
            'date' => '10 mins ago',
            'status' => 'Pending Verification',
            'status_variant' => 'warning',
        ],
        [
            'id' => 'APP-2026-0890',
            'applicant' => 'Juan Dela Cruz',
            'type' => 'Building Permit',
            'date' => '25 mins ago',
            'status' => 'Under Inspection',
            'status_variant' => 'indigo',
        ],
        [
            'id' => 'APP-2026-0889',
            'applicant' => 'Apex Logistics Corp.',
            'type' => 'Special Use Permit',
            'date' => '1 hour ago',
            'status' => 'Approved',
            'status_variant' => 'success',
        ],
        [
            'id' => 'APP-2026-0888',
            'applicant' => 'Elena Reyes',
            'type' => 'Business Permit',
            'date' => '3 hours ago',
            'status' => 'Rejected',
            'status_variant' => 'danger',
        ],
        [
            'id' => 'APP-2026-0887',
            'applicant' => 'Green Valley Hardware',
            'type' => 'Locational Clearance',
            'date' => '5 hours ago',
            'status' => 'Approved',
            'status_variant' => 'success',
        ],
    ],
    'upcomingInspections' => [
        [
            'code' => 'INSP-402',
            'location' => 'Zone 4 - Brgy. Poblacion',
            'inspector' => 'Engr. Aris Thorne',
            'time' => '10:30 AM Today',
            'type' => 'On-Site Structural Inspection',
        ],
        [
            'code' => 'INSP-403',
            'location' => 'Zone 2 - North Commercial',
            'inspector' => 'Arch. Sarah Jenkins',
            'time' => '02:00 PM Today',
            'type' => 'Zoning Alignment Review',
        ],
        [
            'code' => 'INSP-404',
            'location' => 'Zone 7 - Industrial Park',
            'inspector' => 'Engr. Aris Thorne',
            'time' => '09:00 AM Tomorrow',
            'type' => 'Environmental Assessment',
        ],
    ],
]);

?>

<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Admin Dashboard</flux:heading>
            <flux:subheading>Real-time portal overview and daily application metrics.</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button icon="arrow-path" size="sm" variant="subtle">Refresh Data</flux:button>
            <flux:button variant="primary" icon="plus" size="sm" wire:navigate href="{{ route('admin.applications.create-walkin') }}">
                Encode Walk-In
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- Key Metrics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1 --}}
        <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs">
            <div class="flex items-center justify-between">
                <flux:text size="sm" class="font-medium text-zinc-500">Total Applications</flux:text>
                <div class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg">
                    <flux:icon.document-duplicate class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <flux:heading size="xl" class="font-semibold">{{ number_format($stats['total_applications']) }}</flux:heading>
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">{{ $stats['total_change'] }}</span>
            </div>
        </div>

        {{-- Card 2 --}}
        <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs">
            <div class="flex items-center justify-between">
                <flux:text size="sm" class="font-medium text-zinc-500">Pending Verification</flux:text>
                <div class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-lg">
                    <flux:icon.clock class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <flux:heading size="xl" class="font-semibold">{{ $stats['pending_verification'] }}</flux:heading>
                <flux:badge size="sm" variant="solid" color="warning">{{ $stats['pending_urgent'] }} urgent</flux:badge>
            </div>
        </div>

        {{-- Card 3 --}}
        <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs">
            <div class="flex items-center justify-between">
                <flux:text size="sm" class="font-medium text-zinc-500">Scheduled Inspections</flux:text>
                <div class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg">
                    <flux:icon.calendar class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <flux:heading size="xl" class="font-semibold">{{ $stats['scheduled_inspections'] }}</flux:heading>
                <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ $stats['today_inspections'] }} for today</span>
            </div>
        </div>

        {{-- Card 4 --}}
        <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs">
            <div class="flex items-center justify-between">
                <flux:text size="sm" class="font-medium text-zinc-500">Approved This Month</flux:text>
                <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-lg">
                    <flux:icon.check-circle class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <flux:heading size="xl" class="font-semibold">{{ $stats['approved_this_month'] }}</flux:heading>
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">{{ $stats['approval_rate'] }} rate</span>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Recent Applications Table --}}
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <flux:heading size="lg">Recent Applications</flux:heading>
                    <flux:subheading size="sm">Latest submissions needing attention</flux:subheading>
                </div>
                <flux:button variant="ghost" size="sm" icon-trailing="chevron-right" wire:navigate href="{{ route('admin.applications.index') }}">
                    View All
                </flux:button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                    <thead class="border-b border-zinc-200 dark:border-zinc-800 text-xs uppercase text-zinc-400 font-medium">
                        <tr>
                            <th class="pb-3">Reference No.</th>
                            <th class="pb-3">Applicant</th>
                            <th class="pb-3">Type</th>
                            <th class="pb-3">Submitted</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        @foreach ($recentApplications as $app)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="py-3 font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $app['id'] }}
                                </td>
                                <td class="py-3 text-zinc-900 dark:text-zinc-200 font-medium">
                                    {{ $app['applicant'] }}
                                </td>
                                <td class="py-3 text-zinc-500">{{ $app['type'] }}</td>
                                <td class="py-3 text-xs text-zinc-400">{{ $app['date'] }}</td>
                                <td class="py-3">
                                    <flux:badge size="sm" :color="$app['status_variant']" inset="top bottom">
                                        {{ $app['status'] }}
                                    </flux:badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Upcoming Field Inspections List --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <flux:heading size="lg">Upcoming Inspections</flux:heading>
                        <flux:subheading size="sm">Field schedule breakdown</flux:subheading>
                    </div>
                    <flux:button variant="ghost" size="sm" icon-trailing="chevron-right" wire:navigate href="{{ route('admin.inspections.index') }}">
                        Calendar
                    </flux:button>
                </div>

                <div class="space-y-3">
                    @foreach ($upcomingInspections as $inspection)
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ $inspection['code'] }}
                                </span>
                                <flux:badge size="sm" variant="subtle">{{ $inspection['time'] }}</flux:badge>
                            </div>
                            <flux:text size="sm" class="font-medium text-zinc-900 dark:text-zinc-100 mt-1">
                                {{ $inspection['type'] }}
                            </flux:text>
                            <div class="mt-2 text-xs text-zinc-500 flex items-center justify-between">
                                <span>📍 {{ $inspection['location'] }}</span>
                                <span class="text-zinc-400">{{ $inspection['inspector'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <flux:button class="w-full" variant="subtle" icon="map" wire:navigate href="{{ route('admin.geospatial.index') }}">
                    Open Interactive Map
                </flux:button>
            </div>
        </div>

    </div>
</div>