<?php

use function Livewire\Volt\{layout, title, with, usesPagination};
use App\Models\AppStatus;
use Flux\Flux;

usesPagination();

layout('components.layouts.client');
title('Notifications');

// Automatically marks as read when either action is clicked, then redirects
$openApplication = function ($id, $mode = 'view') {
    $notification = AppStatus::whereHas('locClearance', function ($query) {
        $query->where('client_id', auth('client')->id());
    })->findOrFail($id);

    if (! $notification->is_read) {
        $notification->update(['is_read' => true]);
    }

    $targetRoute = $mode === 'edit'
        ? route('client.applications.edit', $notification->application_id)
        : route('client.applications.show', $notification->application_id);

    return $this->redirect($targetRoute, navigate: true);
};

$markAllAsRead = function () {
    AppStatus::whereHas('locClearance', function ($query) {
        $query->where('client_id', auth('client')->id());
    })->where('is_read', false)->update(['is_read' => true]);

    Flux::toast(text: 'All notifications marked as read.', variant: 'success');
};

with(function () {
    $query = AppStatus::whereHas('locClearance', function ($q) {
        $q->where('client_id', auth('client')->id());
    })->with('locClearance');

    return [
        'notifications' => (clone $query)->latest()->paginate(10),
        'unreadCount' => (clone $query)->where('is_read', false)->count(),
    ];
});

?>

{{-- wire:poll.5s keeps notifications updated in real-time --}}
<div wire:poll.5s class="max-w-3xl mx-auto space-y-4 px-1 sm:px-0">
    <!-- Header -->
    <div class="flex items-center justify-between gap-3 pb-1 border-b border-zinc-200/80 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl" level="1" class="text-lg sm:text-xl font-bold">Notifications</flux:heading>
                @if ($unreadCount > 0)
                <flux:badge size="sm" color="amber">{{ $unreadCount }} new</flux:badge>
                @endif
            </div>
            <flux:subheading class="text-xs text-zinc-500">
                Manage and track updates on your clearance applications.
            </flux:subheading>
        </div>

        @if ($unreadCount > 0)
        <flux:button wire:click="markAllAsRead" variant="subtle" size="sm" icon="check">
            Mark all read
        </flux:button>
        @endif
    </div>

    <!-- Notification Feed -->
    @if ($notifications->count() > 0)
    <div class="space-y-3">
        @foreach ($notifications as $item)
        @php
        $statusConfig = match ($item->status) {
        'approve' => [
        'color' => 'green',
        'label' => 'Approved',
        'icon' => 'check-circle',
        'border' => 'border-emerald-500/30',
        'bg' => 'bg-emerald-500/10',
        'text' => 'text-emerald-600 dark:text-emerald-400',
        'accent' => 'border-l-emerald-500',
        ],
        'resubmit' => [
        'color' => 'amber',
        'label' => 'Resubmission Required',
        'icon' => 'arrow-path',
        'border' => 'border-amber-500/30',
        'bg' => 'bg-amber-500/10',
        'text' => 'text-amber-600 dark:text-amber-400',
        'accent' => 'border-l-amber-500',
        ],
        'rejected' => [
        'color' => 'red',
        'label' => 'Rejected',
        'icon' => 'x-circle',
        'border' => 'border-red-500/30',
        'bg' => 'bg-red-500/10',
        'text' => 'text-red-600 dark:text-red-400',
        'accent' => 'border-l-red-500',
        ],
        default => [
        'color' => 'zinc',
        'label' => ucfirst($item->status),
        'icon' => 'information-circle',
        'border' => 'border-zinc-300 dark:border-zinc-700',
        'bg' => 'bg-zinc-500/10',
        'text' => 'text-zinc-600 dark:text-zinc-400',
        'accent' => 'border-l-zinc-400',
        ],
        };
        @endphp

        <!-- Notification Card -->
        <div
            class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs p-4 transition-all relative border-l-4 {{ $statusConfig['accent'] }} {{ !$item->is_read ? 'bg-zinc-50/70 dark:bg-zinc-800/40 ring-1 ring-blue-500/20' : '' }}">

            <!-- Card Top Header (App ID + Status Badge + Timestamp + Unread Dot) -->
            <div class="flex items-start justify-between gap-2 flex-wrap">
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="p-1.5 rounded-lg {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} shrink-0">
                        <flux:icon :name="$statusConfig['icon']" class="w-4 h-4" />
                    </div>

                    <span class="font-bold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">
                        Application #{{ str_pad($item->application_id, 5, '0', STR_PAD_LEFT) }}
                    </span>

                    <flux:badge size="sm" :color="$statusConfig['color']">
                        {{ $statusConfig['label'] }}
                    </flux:badge>
                </div>

                <div class="flex items-center gap-1.5 text-xs text-zinc-400 ml-auto pt-0.5">
                    <span>{{ $item->created_at->diffForHumans() }}</span>
                    @if (!$item->is_read)
                    <span class="w-2 h-2 rounded-full bg-blue-600 ring-2 ring-white dark:ring-zinc-900"
                        title="Unread"></span>
                    @endif
                </div>
            </div>

            <!-- Project Specifications Metadata -->
            <p class="mt-2 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Project: <span class="font-medium text-zinc-800 dark:text-zinc-200">{{
                    $item->locClearance?->project_type ?? 'N/A' }}</span>
                &bull; Location: <span class="text-zinc-700 dark:text-zinc-300">{{
                    $item->locClearance?->project_location ?? 'N/A' }}</span>
            </p>

            <!-- Admin Remarks Box -->
            @if ($item->remarks)
            <div
                class="mt-2.5 p-3 rounded-lg bg-zinc-100 dark:bg-zinc-800/80 text-xs sm:text-sm text-zinc-700 dark:text-zinc-300 border-l-2 {{ $statusConfig['border'] }}">
                <span class="font-medium text-zinc-900 dark:text-zinc-100 block mb-0.5">Admin Remarks:</span>
                {{ $item->remarks }}
            </div>
            @endif

            <!-- Action Buttons (Automatically mark as read on click) -->
            <div
                class="mt-3.5 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-end gap-2">
                <flux:button wire:click="openApplication({{ $item->id }}, 'view')" size="sm" variant="subtle"
                    icon-trailing="arrow-right" class="flex-1 sm:flex-none justify-center">
                    View Application
                </flux:button>

                @if ($item->status === 'resubmit')
                <flux:button wire:click="openApplication({{ $item->id }}, 'edit')" size="sm" variant="primary"
                    icon="pencil" class="flex-1 sm:flex-none justify-center">
                    Update Application
                </flux:button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="pt-2">
        {{ $notifications->links() }}
    </div>
    @else
    <!-- Empty State -->
    <flux:card class="text-center py-12 space-y-3">
        <div
            class="w-12 h-12 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
            <flux:icon.bell class="w-6 h-6" />
        </div>
        <flux:heading size="lg">No Notifications Yet</flux:heading>
        <flux:subheading>
            You will receive updates here whenever there are updates on your clearance applications.
        </flux:subheading>
    </flux:card>
    @endif
</div>