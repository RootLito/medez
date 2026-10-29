<?php

use function Livewire\Volt\{layout, title, state, usesFileUploads};
use App\Models\LocClearance;
use App\Models\ZoneLocation;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

usesFileUploads();

layout('components.layouts.admin');
title('Encode Walk-In Application - Admin Portal');

state([
    // Step state tracking (1 = Form, 2 = Requirements, 3 = Location Map)
    'step' => 3,

    // Header / Official Admin Details (Hidden from UI, defaults applied)
    'application_no' => '',
    'or_number' => '',
    'amount_paid' => '',
    'date_of_receipt' => fn() => now()->format('Y-m-d'),
    'status' => 'pending',

    // Phase 1: Applicant & Representation Details (Items 1-6)
    'applicant_name' => '',
    'corporation_name' => '',
    'representative_address_tel' => '',
    'corporation_address_tel' => '',
    'authorized_representative_name' => '',
    'authorized_representative_address_tel' => '',

    // Project Specifications & Measurements (Items 7-10)
    'project_type' => '',
    'project_nature' => '',
    'project_location' => '',
    'lot_area_sqm' => '',
    'building_area_sqm' => '',
    'perimeter_m' => '',

    // Right Over Land & Tenure (Items 11-12)
    'right_over_land' => 'owner',
    'right_over_land_specify' => '',
    'project_tenure' => 'permanent',

    // Existing Land Use (Item 13)
    'existing_land_use' => 'residential',
    'existing_land_use_specify' => '',
    'agriculture_crops_specify' => '',
    'is_tenanted' => false,

    // Project Cost (Item 14)
    'project_cost' => '',

    // Written Notice Details (Item 15)
    'has_written_notice' => false,
    'notice_issuing_officer' => '',
    'notice_order_number' => '',
    'notice_date' => '',

    // Related Action Details (Item 16)
    'has_related_action' => false,
    'action_office_filed' => '',
    'action_date_filed' => '',
    'action_taken' => '',

    // Preferred Release (Item 17)
    'preferred_mode_of_release' => 'pick_up',

    // Phase 2: Requirements Uploads
    'basic_docs_files' => [],
    'land_right_files' => [],
    'plans_specs_files' => [],
    'local_clearances_files' => [],
    'special_req_files' => [],

    // Phase 3: Coordinates
    'latitude' => null,
    'longitude' => null,
]);

$removeFile = function ($category, $index) {
    if (isset($this->{$category}[$index])) {
        unset($this->{$category}[$index]);
        $this->{$category} = array_values($this->{$category});
    }
};

$goToStep2 = function () {
    $this->validate([
        'application_no' => ['nullable', 'string', 'max:255'],
        'or_number' => ['nullable', 'string', 'max:255'],
        'amount_paid' => ['nullable', 'numeric', 'min:0'],
        'date_of_receipt' => ['required', 'date'],
        'status' => ['required', 'in:pending,approve,approved,rejected'],

        'applicant_name' => ['required', 'string', 'max:255'],
        'corporation_name' => ['nullable', 'string', 'max:255'],
        'representative_address_tel' => ['required', 'string', 'max:255'],
        'corporation_address_tel' => ['nullable', 'string', 'max:255'],
        'authorized_representative_name' => ['nullable', 'string', 'max:255'],
        'authorized_representative_address_tel' => ['nullable', 'string', 'max:255'],

        'project_type' => ['required', 'string', 'max:255'],
        'project_nature' => ['required', 'string', 'max:255'],
        'project_location' => ['required', 'string'],
        'lot_area_sqm' => ['required', 'numeric', 'min:0'],
        'building_area_sqm' => ['nullable', 'numeric', 'min:0'],
        'perimeter_m' => ['nullable', 'numeric', 'min:0'],

        'right_over_land' => ['required', 'string', 'max:255'],
        'right_over_land_specify' => ['nullable', 'required_if:right_over_land,others', 'string', 'max:255'],
        'project_tenure' => ['required', 'string', 'max:255'],
        'existing_land_use' => ['required', 'string', 'max:255'],
        'existing_land_use_specify' => ['nullable', 'required_if:existing_land_use,others', 'string', 'max:255'],
        'agriculture_crops_specify' => ['nullable', 'required_if:existing_land_use,agriculture', 'string', 'max:255'],
        'is_tenanted' => ['boolean'],
        'project_cost' => ['required', 'numeric', 'min:0'],

        'has_written_notice' => ['boolean'],
        'notice_issuing_officer' => ['nullable', 'required_if:has_written_notice,true,1', 'string', 'max:255'],
        'notice_order_number' => ['nullable', 'required_if:has_written_notice,true,1', 'string', 'max:255'],
        'notice_date' => ['nullable', 'required_if:has_written_notice,true,1', 'date'],

        'has_related_action' => ['boolean'],
        'action_office_filed' => ['nullable', 'required_if:has_related_action,true,1', 'string', 'max:255'],
        'action_date_filed' => ['nullable', 'required_if:has_related_action,true,1', 'date'],
        'action_taken' => ['nullable', 'required_if:has_related_action,true,1', 'string', 'max:255'],

        'preferred_mode_of_release' => ['required', 'string', 'max:255'],
    ]);

    $this->step = 2;
};

$goToStep3 = function () {
    $this->validate([
        'basic_docs_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'land_right_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'plans_specs_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'local_clearances_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'special_req_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
    ]);

    $this->step = 3;
};

$submit = function () {
    $this->validate([
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
    ], [
        'latitude.required' => 'Please pinpoint the project site location on the map.',
        'longitude.required' => 'Please pinpoint the project site location on the map.',
    ]);

    $uploadCategories = ['basic_docs_files', 'land_right_files', 'plans_specs_files', 'local_clearances_files', 'special_req_files'];
    $requirements = [];

    foreach ($uploadCategories as $category) {
        $requirements[$category] = array_map(function ($file) use ($category) {
            return $file->store("locational-clearances/{$category}", 'public');
        }, $this->{$category} ?? []);
    }

    DB::transaction(function () use ($requirements) {
        $clearance = LocClearance::create([
            'client_id' => null,
            'is_walk_in' => true,
            'encoded_by' => auth('admin')->id() ?? auth()->id(),

            'application_no' => $this->application_no ?: null,
            'or_number' => $this->or_number ?: null,
            'amount_paid' => $this->amount_paid ?: null,
            'date_of_receipt' => $this->date_of_receipt,
            'status' => $this->status === 'approved' ? 'approve' : $this->status,

            'applicant_name' => $this->applicant_name,
            'corporation_name' => $this->corporation_name ?: null,
            'representative_address_tel' => $this->representative_address_tel,
            'corporation_address_tel' => $this->corporation_address_tel ?: null,
            'authorized_representative_name' => $this->authorized_representative_name ?: null,
            'authorized_representative_address_tel' => $this->authorized_representative_address_tel ?: null,

            'project_type' => $this->project_type,
            'project_nature' => $this->project_nature,
            'project_location' => $this->project_location,
            'lot_area_sqm' => $this->lot_area_sqm,
            'building_area_sqm' => $this->building_area_sqm ?: null,
            'perimeter_m' => $this->perimeter_m ?: null,

            'right_over_land' => $this->right_over_land,
            'right_over_land_specify' => $this->right_over_land === 'others' ? $this->right_over_land_specify : null,
            'project_tenure' => $this->project_tenure,

            'existing_land_use' => $this->existing_land_use,
            'existing_land_use_specify' => $this->existing_land_use === 'others' ? $this->existing_land_use_specify : null,
            'agriculture_crops_specify' => $this->existing_land_use === 'agriculture' ? $this->agriculture_crops_specify : null,
            'is_tenanted' => (bool) $this->is_tenanted,

            'project_cost' => $this->project_cost,

            'has_written_notice' => (bool) $this->has_written_notice,
            'notice_issuing_officer' => $this->has_written_notice ? $this->notice_issuing_officer : null,
            'notice_order_number' => $this->has_written_notice ? $this->notice_order_number : null,
            'notice_date' => $this->has_written_notice ? ($this->notice_date ?: null) : null,

            'has_related_action' => (bool) $this->has_related_action,
            'action_office_filed' => $this->has_related_action ? $this->action_office_filed : null,
            'action_date_filed' => $this->has_related_action ? ($this->action_date_filed ?: null) : null,
            'action_taken' => $this->has_related_action ? $this->action_taken : null,

            'preferred_mode_of_release' => $this->preferred_mode_of_release,
            'requirements' => $requirements,
        ]);

        ZoneLocation::create([
            'application_id' => $clearance->id,
            'title' => $this->project_type . ' - ' . $this->applicant_name,
            'zone_type' => ucfirst($this->existing_land_use ?? 'Commercial'),
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'notes' => $this->project_location,
        ]);
    });

    Flux::toast(text: 'Walk-in application encoded and geotagged successfully!', variant: 'success');

    return redirect()->route('admin.applications.walkin-index');
};

?>

<div class="h-full w-full flex flex-col space-y-6">
    <!-- Header with Back Button -->
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-200/80 dark:border-zinc-800 pb-4">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">
                Encode Walk-In Application
            </flux:heading>
            <flux:subheading class="text-xs sm:text-sm mt-0.5">
                @if ($step === 1)
                Phase 1 of 3: Applicant identification and project specifications.
                @elseif ($step === 2)
                Phase 2 of 3: Attached scans and requirements checklist.
                @else
                Phase 3 of 3: Geospatial boundary pinpointing on the interactive map.
                @endif
            </flux:subheading>
        </div>
    </div>

    <!-- Node-Based Order Tracking Stepper -->
    <div class="py-5 px-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs">
        <div class="flex items-center justify-between relative max-w-xl mx-auto">

            <!-- Step 1: Form Details -->
            <button type="button" wire:click="$set('step', 1)"
                class="relative z-10 flex flex-col items-center group cursor-pointer focus:outline-hidden">
                <div
                    class="size-10 rounded-full flex items-center justify-center border-2 transition-all shadow-md {{ $step > 1 ? 'bg-emerald-600 text-white border-emerald-600' : ($step === 1 ? 'bg-white text-zinc-900 border-white ring-4 ring-white/20' : 'bg-zinc-900 text-zinc-400 border-zinc-700') }}">
                    @if($step > 1)
                    <flux:icon name="check" class="size-5" />
                    @else
                    <flux:icon name="document-text" class="size-5" />
                    @endif
                </div>
                <span
                    class="mt-2 text-xs font-medium {{ $step === 1 ? 'text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200' }}">
                    Form Details
                </span>
            </button>

            <!-- Step 2: Requirements -->
            <button type="button" @if($step> 1) wire:click="$set('step', 2)" @endif
                class="relative z-10 flex flex-col items-center group {{ $step > 1 ? 'cursor-pointer' : 'cursor-default'
                }} focus:outline-hidden">
                <div
                    class="size-10 rounded-full flex items-center justify-center border-2 transition-all shadow-md {{ $step > 2 ? 'bg-emerald-600 text-white border-emerald-600' : ($step === 2 ? 'bg-white text-zinc-900 border-white ring-4 ring-white/20' : 'bg-zinc-900 text-zinc-500 border-zinc-700') }}">
                    @if($step > 2)
                    <flux:icon name="check" class="size-5" />
                    @else
                    <flux:icon name="folder-open" class="size-5" />
                    @endif
                </div>
                <span
                    class="mt-2 text-xs font-medium {{ $step === 2 ? 'text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200' }}">
                    Requirements
                </span>
            </button>

            <!-- Step 3: Location Map -->
            <button type="button" @if($step> 2) wire:click="$set('step', 3)" @endif
                class="relative z-10 flex flex-col items-center group {{ $step > 2 ? 'cursor-pointer' : 'cursor-default'
                }} focus:outline-hidden">
                <div
                    class="size-10 rounded-full flex items-center justify-center border-2 transition-all shadow-md {{ $step === 3 ? 'bg-white text-zinc-900 border-white ring-4 ring-white/20' : 'bg-zinc-900 text-zinc-500 border-zinc-700' }}">
                    <flux:icon name="map-pin" class="size-5" />
                </div>
                <span
                    class="mt-2 text-xs font-medium {{ $step === 3 ? 'text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200' }}">
                    Location Map
                </span>
            </button>

        </div>
    </div>

    @if ($step === 1)
    <div wire:key="step-1" class="flex-1">
        @include('livewire.admin.applications.walkin.partials.step-form')
    </div>
    @elseif ($step === 2)
    <div wire:key="step-2" class="flex-1">
        @include('livewire.admin.applications.walkin.partials.step-requirements')
    </div>
    @elseif ($step === 3)
    <div wire:key="step-3" class="flex-1">
        @include('livewire.admin.applications.walkin.partials.step-location')
    </div>
    @endif
</div>