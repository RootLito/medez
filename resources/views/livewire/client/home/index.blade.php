<?php

use function Livewire\Volt\{layout, title, state, mount};
use App\Models\LocClearance;

layout('components.layouts.client');
title('Home - Client Portal');

state([
    'totalCount' => 0,
    'pendingCount' => 0,
    'resubmitCount' => 0,
    'approvedCount' => 0,
    'recentApplications' => [],
]);

mount(function () {
    $clientId = auth('client')->id();

    // Query application metrics
    $this->totalCount = LocClearance::where('client_id', $clientId)->count();

    $this->pendingCount = LocClearance::where('client_id', $clientId)
        ->where('status', 'pending')
        ->count();

    $this->resubmitCount = LocClearance::where('client_id', $clientId)
        ->where('status', 'resubmit')
        ->count();

    $this->approvedCount = LocClearance::where('client_id', $clientId)
        ->whereIn('status', ['approve', 'approved'])
        ->count();

    // Fetch the 5 most recent applications
    $this->recentApplications = LocClearance::where('client_id', $clientId)
        ->latest()
        ->take(5)
        ->get();
});

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header with Quick Action -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            @php
            $client = auth('client')->user();
            $fullName = trim(($client?->first_name ?? '') . ' ' . ($client?->last_name ?? '')) ?: 'Client';
            @endphp

            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">
                Welcome back, {{ $fullName }}!
            </flux:heading>
            <flux:subheading class="text-xs sm:text-sm">
                Overview of your locational clearance applications and current status.
            </flux:subheading>
        </div>

        <div class="w-full sm:w-auto">
            <flux:button href="{{ route('client.applications.create') }}" wire:navigate variant="primary" icon="plus"
                class="w-full sm:w-auto justify-center">
                New Application
            </flux:button>
        </div>
    </div>

    <!-- Overview Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Applications -->
        <flux:card class="p-3.5 sm:p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs sm:text-sm font-medium text-zinc-500 dark:text-zinc-400">Total</span>
                <div class="p-1.5 sm:p-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                    <flux:icon name="folder" class="size-3.5 sm:size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ $totalCount }}
                </div>
                <p class="text-[10px] sm:text-xs text-zinc-400 mt-0.5 truncate">Total submitted</p>
            </div>
        </flux:card>

        <!-- Pending (Blue) -->
        <flux:card
            class="p-3.5 sm:p-5 flex flex-col justify-between border-blue-500/20 bg-blue-50/20 dark:bg-blue-950/10">
            <div class="flex items-center justify-between">
                <span class="text-xs sm:text-sm font-medium text-blue-700 dark:text-blue-400">Pending</span>
                <div class="p-1.5 sm:p-2 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                    <flux:icon name="clock" class="size-3.5 sm:size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-xl sm:text-3xl font-bold tracking-tight text-blue-600 dark:text-blue-400">
                    {{ $pendingCount }}
                </div>
                <p class="text-[10px] sm:text-xs text-blue-600/70 dark:text-blue-400/70 mt-0.5 truncate">Under
                    evaluation</p>
            </div>
        </flux:card>

        <!-- Resubmit (Yellow) -->
        <flux:card
            class="p-3.5 sm:p-5 flex flex-col justify-between border-yellow-500/20 bg-yellow-50/20 dark:bg-yellow-950/10">
            <div class="flex items-center justify-between">
                <span class="text-xs sm:text-sm font-medium text-yellow-700 dark:text-yellow-400">Resubmit</span>
                <div
                    class="p-1.5 sm:p-2 rounded-lg bg-yellow-100 dark:bg-yellow-900/40 text-yellow-600 dark:text-yellow-400">
                    <flux:icon name="arrow-path" class="size-3.5 sm:size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-xl sm:text-3xl font-bold tracking-tight text-yellow-600 dark:text-yellow-400">
                    {{ $resubmitCount }}
                </div>
                <p class="text-[10px] sm:text-xs text-yellow-600/70 dark:text-yellow-400/70 mt-0.5 truncate">Needs
                    compliance</p>
            </div>
        </flux:card>

        <!-- Approved (Green) -->
        <flux:card
            class="p-3.5 sm:p-5 flex flex-col justify-between border-emerald-500/20 bg-emerald-50/20 dark:bg-emerald-950/10">
            <div class="flex items-center justify-between">
                <span class="text-xs sm:text-sm font-medium text-emerald-700 dark:text-emerald-400">Approved</span>
                <div
                    class="p-1.5 sm:p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
                    <flux:icon name="check-circle" class="size-3.5 sm:size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-xl sm:text-3xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
                    {{ $approvedCount }}
                </div>
                <p class="text-[10px] sm:text-xs text-emerald-600/70 dark:text-emerald-400/70 mt-0.5 truncate">Issued /
                    Cleared</p>
            </div>
        </flux:card>
    </div>

    <!-- Recent Applications Section -->
    <flux:card class="p-4 sm:p-6 space-y-4">
        <div class="flex items-center justify-between gap-2 border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <div>
                <flux:heading size="lg" class="text-base sm:text-lg">Recent Applications</flux:heading>
                <flux:subheading class="text-xs">Your latest submitted clearances</flux:subheading>
            </div>

            <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle" size="sm"
                icon-trailing="chevron-right" class="text-xs">
                View All
            </flux:button>
        </div>

        @if($recentApplications->isEmpty())
        <div
            class="text-center py-10 px-4 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl space-y-3">
            <div
                class="size-10 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                <flux:icon name="document-text" class="size-5" />
            </div>
            <div>
                <flux:heading size="sm">No applications found</flux:heading>
                <flux:text class="text-xs text-zinc-500 max-w-xs mx-auto mt-1">
                    You haven't submitted any locational clearance applications yet.
                </flux:text>
            </div>
            <flux:button href="{{ route('client.applications.create') }}" wire:navigate variant="primary" size="sm"
                icon="plus">
                Create First Application
            </flux:button>
        </div>
        @else
        <!-- Mobile Card List View (< md) -->
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800 md:hidden -mx-4 -mb-4">
            @foreach($recentApplications as $app)
            @php
            $rawStatus = strtolower($app->status ?? 'pending');
            $badgeColor = match($rawStatus) {
            'approve', 'approved' => 'green',
            'resubmit' => 'yellow',
            'pending' => 'blue',
            'rejected' => 'red',
            default => 'zinc',
            };
            $displayStatus = match($rawStatus) {
            'approve', 'approved' => 'Approved',
            'resubmit' => 'Resubmit',
            'pending' => 'Pending',
            'rejected' => 'Rejected',
            default => ucfirst($rawStatus),
            };
            @endphp
            <div class="p-4 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition flex flex-col gap-2.5">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $app->application_no ?? '#' . $app->id }}
                    </span>
                    <flux:badge :color="$badgeColor" size="sm" class="text-[11px]">
                        {{ $displayStatus }}
                    </flux:badge>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 line-clamp-1">
                        {{ $app->project_type ?? 'Unspecified Project' }}
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                        {{ $app->project_location ?? 'No location provided' }}
                    </p>
                </div>

                <div
                    class="flex items-center justify-between pt-1 border-t border-zinc-100 dark:border-zinc-800/50 text-[11px] text-zinc-400">
                    <span>Filed: {{ $app->created_at->format('M d, Y') }}</span>
                    <flux:button href="{{ route('client.applications.edit', $app->id) }}" wire:navigate variant="subtle"
                        size="xs" icon="pencil-square">
                        Edit
                    </flux:button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Desktop Table View (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                <thead
                    class="text-xs uppercase bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3">App No.</th>
                        <th class="px-4 py-3">Project Type</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Date Filed</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($recentApplications as $app)
                    @php
                    $rawStatus = strtolower($app->status ?? 'pending');
                    $badgeColor = match($rawStatus) {
                    'approve', 'approved' => 'green',
                    'resubmit' => 'yellow',
                    'pending' => 'blue',
                    'rejected' => 'red',
                    default => 'zinc',
                    };
                    $displayStatus = match($rawStatus) {
                    'approve', 'approved' => 'Approved',
                    'resubmit' => 'Resubmit',
                    'pending' => 'Pending',
                    'rejected' => 'Rejected',
                    default => ucfirst($rawStatus),
                    };
                    @endphp
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100 font-mono text-xs">
                            {{ $app->application_no ?? '#' . $app->id }}
                        </td>
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $app->project_type }}</td>
                        <td class="px-4 py-3 truncate max-w-xs">{{ $app->project_location }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $app->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <flux:badge :color="$badgeColor" size="sm">
                                {{ $displayStatus }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button href="{{ route('client.applications.edit', $app->id) }}" wire:navigate
                                variant="subtle" size="sm" icon="pencil-square">
                                Edit
                            </flux:button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </flux:card>
</div>