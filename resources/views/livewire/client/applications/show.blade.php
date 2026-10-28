<?php

use function Livewire\Volt\{layout, title, state, mount};
use App\Models\LocClearance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

layout('components.layouts.client');
title('Application Details');

state([
    'clearance' => null,
]);

mount(function (LocClearance $clearance) {
    // Ensure clients can only view their own applications
    if ($clearance->client_id !== auth('client')->id()) {
        abort(403);
    }

    $this->clearance = $clearance;
});

?>

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1">Application #{{ str_pad($clearance->id, 5, '0', STR_PAD_LEFT) }}
                </flux:heading>

                @php
                $statusColor = match($clearance->status ?? 'pending') {
                'approved' => 'green',
                'rejected' => 'red',
                'under_review' => 'blue',
                default => 'yellow',
                };
                @endphp
                <flux:badge size="sm" :color="$statusColor" class="capitalize">
                    {{ str_replace('_', ' ', $clearance->status ?? 'pending') }}
                </flux:badge>
            </div>
            <flux:subheading>
                Submitted on {{ $clearance->created_at->format('F d, Y \a\t h:i A') }}
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle"
                icon="arrow-left">
                Back to Applications
            </flux:button>

            @if(in_array($clearance->status, ['pending', 'under_review', null]))
            <flux:button href="{{ route('client.applications.edit', $clearance) }}" wire:navigate variant="primary"
                icon="pencil">
                Edit Application
            </flux:button>
            @endif
        </div>
    </div>

    <!-- Quick Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <flux:card class="p-4">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Project
                Type</span>
            <p class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $clearance->project_type }}</p>
        </flux:card>

        <flux:card class="p-4">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Estimated
                Cost</span>
            <p class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mt-1">
                ₱{{ number_format((float) $clearance->project_cost, 2) }}
            </p>
        </flux:card>

        <flux:card class="p-4">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Lot Area</span>
            <p class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mt-1">
                {{ number_format((float) $clearance->lot_area_sqm, 2) }} sq.m
            </p>
        </flux:card>

        <flux:card class="p-4">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Preferred
                Release</span>
            <p class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mt-1 capitalize">
                {{ str_replace('_', ' ', $clearance->preferred_mode_of_release ?? 'Pick-up') }}
            </p>
        </flux:card>
    </div>

    <!-- Section 1: Applicant & Representation Details -->
    <flux:card class="space-y-4">
        <flux:heading size="lg">1. Applicant & Representative Information</flux:heading>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">1. Name of Applicant</dt>
                <dd class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $clearance->applicant_name }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">2. Name of Corporation</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $clearance->corporation_name ?: '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">3. Address / Tel. No. of Applicant</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $clearance->representative_address_tel }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">4. Address / Tel. No. of Corporation
                </dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $clearance->corporation_address_tel ?:
                    '—' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">5. Authorized Representative</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">{{
                    $clearance->authorized_representative_name ?: '—' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">6. Address / Tel. No. of Authorized
                    Representative</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">{{
                    $clearance->authorized_representative_address_tel ?: '—' }}</dd>
            </div>
        </div>
    </flux:card>

    <!-- Section 2: Project Details & Measurements -->
    <flux:card class="space-y-4">
        <flux:heading size="lg">2. Project Details & Measurements</flux:heading>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">7. Project Type</dt>
                <dd class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $clearance->project_type }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">8. Project Nature</dt>
                <dd class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $clearance->project_nature }}
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">9. Project Location</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $clearance->project_location }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">10a. Lot Area</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">{{ number_format((float)
                    $clearance->lot_area_sqm, 2) }} sq.m</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">10b. Building Area</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">
                    {{ $clearance->building_area_sqm ? number_format((float) $clearance->building_area_sqm, 2) . ' sq.m'
                    : '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">10c. Perimeter</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">
                    {{ $clearance->perimeter_m ? number_format((float) $clearance->perimeter_m, 2) . ' m' : '—' }}
                </dd>
            </div>
        </div>
    </flux:card>

    <!-- Section 3: Right Over Land, Land Use & Cost -->
    <flux:card class="space-y-4">
        <flux:heading size="lg">3. Land Ownership, Land Use & Cost</flux:heading>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">11. Right Over Land</dt>
                <dd class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-0.5 capitalize">
                    {{ $clearance->right_over_land }}
                    @if($clearance->right_over_land === 'others' && $clearance->right_over_land_specify)
                    <span class="text-zinc-500">({{ $clearance->right_over_land_specify }})</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">12. Project Tenure</dt>
                <dd class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-0.5 capitalize">{{
                    $clearance->project_tenure }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">13. Existing Land Use</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5 capitalize">
                    {{ str_replace('_', ' ', $clearance->existing_land_use) }}
                    @if($clearance->existing_land_use === 'agriculture' && $clearance->agriculture_crops_specify)
                    <span class="text-zinc-500">(Crops: {{ $clearance->agriculture_crops_specify }})</span>
                    @elseif($clearance->existing_land_use === 'others' && $clearance->existing_land_use_specify)
                    <span class="text-zinc-500">({{ $clearance->existing_land_use_specify }})</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Site Status</dt>
                <dd class="text-sm text-zinc-900 dark:text-zinc-100 mt-0.5">
                    @if($clearance->is_tenanted)
                    <flux:badge size="sm" color="amber">Currently Tenanted</flux:badge>
                    @else
                    <flux:badge size="sm" color="zinc">Not Tenanted</flux:badge>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">14. Project Cost</dt>
                <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mt-0.5">
                    ₱{{ number_format((float) $clearance->project_cost, 2) }}
                </dd>
            </div>
        </div>
    </flux:card>

    <!-- Section 4: Written Notices & Related Actions -->
    <flux:card class="space-y-4">
        <flux:heading size="lg">4. Written Notices & Related Actions</flux:heading>

        <div class="space-y-4">
            <!-- Item 15 -->
            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">15. Subject of Written Notice(s)?</dt>
                <dd class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-1">
                    @if($clearance->has_written_notice)
                    <flux:badge size="sm" color="red">Yes</flux:badge>
                    @else
                    <flux:badge size="sm" color="zinc">No</flux:badge>
                    @endif
                </dd>

                @if($clearance->has_written_notice)
                <div
                    class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700">
                    <div>
                        <span class="text-xs text-zinc-500">Issuing Officer:</span>
                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{
                            $clearance->notice_issuing_officer ?: '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Order in Notice:</span>
                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{
                            $clearance->notice_order_number ?: '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Date of Notice:</span>
                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ $clearance->notice_date ? \Carbon\Carbon::parse($clearance->notice_date)->format('M d,
                            Y') : '—' }}
                        </p>
                    </div>
                </div>
                @endif
            </div>

            <flux:separator />

            <!-- Item 16 -->
            <div>
                <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">16. Subject of Related Action(s)?</dt>
                <dd class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-1">
                    @if($clearance->has_related_action)
                    <flux:badge size="sm" color="red">Yes</flux:badge>
                    @else
                    <flux:badge size="sm" color="zinc">No</flux:badge>
                    @endif
                </dd>

                @if($clearance->has_related_action)
                <div
                    class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700">
                    <div>
                        <span class="text-xs text-zinc-500">Office Filed:</span>
                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{
                            $clearance->action_office_filed ?: '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Date Filed:</span>
                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ $clearance->action_date_filed ?
                            \Carbon\Carbon::parse($clearance->action_date_filed)->format('M d, Y') : '—' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-500">Action Taken:</span>
                        <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $clearance->action_taken ?:
                            '—' }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </flux:card>

    <!-- Section 5: Attached Requirements & Documents -->
    @php
    $requirements = is_array($clearance->requirements) ? $clearance->requirements :
    (json_decode($clearance->requirements, true) ?? []);

    $categories = [
    'basic_docs_files' => [
    'number' => 1,
    'title' => 'Basic Application Documents',
    'files' => $requirements['basic_docs_files'] ?? [],
    ],
    'land_right_files' => [
    'number' => 2,
    'title' => 'Proof of Right Over Land',
    'files' => $requirements['land_right_files'] ?? [],
    ],
    'plans_specs_files' => [
    'number' => 3,
    'title' => 'Plans & Technical Specifications',
    'files' => $requirements['plans_specs_files'] ?? [],
    ],
    'local_clearances_files' => [
    'number' => 4,
    'title' => 'Supporting Local Clearances',
    'files' => $requirements['local_clearances_files'] ?? [],
    ],
    'special_req_files' => [
    'number' => 5,
    'title' => 'Special / Conditional Requirements',
    'files' => $requirements['special_req_files'] ?? [],
    ],
    ];
    @endphp

    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">5. Requirements & Supporting Documents</flux:heading>
            <flux:subheading>Click any thumbnail or document to preview or download.</flux:subheading>
        </div>

        <div class="space-y-6">
            @foreach ($categories as $key => $category)
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <flux:heading size="base"
                        class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <flux:badge size="sm" color="zinc">{{ $category['number'] }}</flux:badge>
                        {{ $category['title'] }}
                    </flux:heading>

                    @if (count($category['files']) > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ count($category['files']) }} file(s)
                    </flux:badge>
                    @else
                    <flux:badge size="sm" color="zinc">None uploaded</flux:badge>
                    @endif
                </div>

                @if (count($category['files']) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    @foreach ($category['files'] as $filePath)
                    @php
                    $isPdf = Str::endsWith(strtolower($filePath), '.pdf');
                    $url = Storage::url($filePath);
                    @endphp

                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                        class="group block relative rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:border-zinc-400 dark:hover:border-zinc-500 transition shadow-sm bg-zinc-50 dark:bg-zinc-800">
                        @if ($isPdf)
                        <div class="h-24 flex flex-col items-center justify-center p-2 text-red-500">
                            <flux:icon.document-text class="w-8 h-8" />
                            <span
                                class="text-[10px] text-zinc-600 dark:text-zinc-300 font-medium truncate w-full text-center mt-1">PDF
                                File</span>
                        </div>
                        @else
                        <img src="{{ $url }}" alt="Document preview"
                            class="h-24 w-full object-cover group-hover:scale-105 transition duration-150" />
                        @endif

                        <div
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <span class="text-[11px] font-medium text-white px-2 py-1 rounded bg-black/50">View</span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-zinc-400 italic pl-6">No supporting files attached for this category.</p>
                @endif
            </div>

            @if (!$loop->last)
            <flux:separator />
            @endif
            @endforeach
        </div>
    </flux:card>
</div>