<?php

use function Livewire\Volt\{layout, title, state, mount};
use App\Models\LocClearance;

layout('components.layouts.client');
title('Home - Client Portal');

state([
    'activeCount' => 0,
    'approvedCount' => 0,
    'pendingCount' => 0,
    'recentApplications' => [],
]);

mount(function () {
    $clientId = auth('client')->id();

    // Query application metrics
    $this->activeCount = LocClearance::where('client_id', $clientId)
        ->whereIn('status', ['pending', 'under_review', 'processing'])
        ->count();

    $this->approvedCount = LocClearance::where('client_id', $clientId)
        ->where('status', 'approved')
        ->count();

    $this->pendingCount = LocClearance::where('client_id', $clientId)
        ->whereIn('status', ['pending', 'action_required'])
        ->count();

    // Fetch the 5 most recent applications
    $this->recentApplications = LocClearance::where('client_id', $clientId)
        ->latest()
        ->take(5)
        ->get();
});

?>

<div class="space-y-6">
    <!-- Header with Quick Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            @php
            $client = auth('client')->user();
            $fullName = trim(($client?->first_name ?? '') . ' ' . ($client?->last_name ?? '')) ?: 'Client';
            @endphp

            <flux:heading size="xl" level="1">Welcome back, {{ $fullName }}!</flux:heading>
            <flux:subheading size="lg">Overview of your locational clearance applications and status.</flux:subheading>
        </div>

        <div>
            <flux:button href="{{ route('client.applications.create') }}" wire:navigate variant="primary" icon="plus">
                New Application
            </flux:button>
        </div>
    </div>

    <!-- Overview Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between">
                <flux:subheading class="font-medium">Active Applications</flux:subheading>
                <flux:icon name="document-text" class="size-5 text-zinc-400" />
            </div>
            <div class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                {{ $activeCount }}
            </div>
            <flux:text class="text-xs text-zinc-500">Currently undergoing evaluation</flux:text>
        </flux:card>

        <flux:card class="space-y-2">
            <div class="flex items-center justify-between">
                <flux:subheading class="font-medium">Approved Clearances</flux:subheading>
                <flux:icon name="check-circle" class="size-5 text-emerald-500" />
            </div>
            <div class="text-3xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
                {{ $approvedCount }}
            </div>
            <flux:text class="text-xs text-zinc-500">Ready for release or issued</flux:text>
        </flux:card>

        <flux:card class="space-y-2">
            <div class="flex items-center justify-between">
                <flux:subheading class="font-medium">Pending Actions</flux:subheading>
                <flux:icon name="exclamation-circle" class="size-5 text-amber-500" />
            </div>
            <div class="text-3xl font-bold tracking-tight text-amber-600 dark:text-amber-400">
                {{ $pendingCount }}
            </div>
            <flux:text class="text-xs text-zinc-500">Requires review or submission</flux:text>
        </flux:card>
    </div>

    <!-- Recent Applications Section -->
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Recent Applications</flux:heading>
                <flux:subheading>Your latest submitted locational clearances.</flux:subheading>
            </div>
            <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle" size="sm" icon-trailing="chevron-right">
                View All
            </flux:button>
        </div>

        @if($recentApplications->isEmpty())
        <div class="text-center py-8 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
            <flux:icon name="document-text" class="size-10 mx-auto text-zinc-400 mb-2" />
            <flux:heading size="sm">No applications found</flux:heading>
            <flux:text class="mt-1 mb-4">You haven't submitted any locational clearance applications yet.</flux:text>
            <flux:button href="{{ route('client.applications.create') }}" wire:navigate variant="primary" size="sm" icon="plus">
                Create Your First Application
            </flux:button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                <thead class="text-xs uppercase bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
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
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $app->application_no ?? '#' . $app->id }}
                        </td>
                        <td class="px-4 py-3">{{ $app->project_type }}</td>
                        <td class="px-4 py-3 truncate max-w-xs">{{ $app->project_location }}</td>
                        <td class="px-4 py-3">{{ $app->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            @php
                            $badgeColor = match($app->status) {
                            'approved' => 'green',
                            'pending' => 'amber',
                            'rejected' => 'red',
                            default => 'zinc',
                            };
                            @endphp
                            <flux:badge :color="$badgeColor" size="sm" class="capitalize">
                                {{ str_replace('_', ' ', $app->status ?? 'Pending') }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button href="{{ route('client.applications.edit', $app->id) }}" wire:navigate variant="subtle" size="sm" icon="pencil-square">
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