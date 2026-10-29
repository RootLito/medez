<?php

use function Livewire\Volt\{layout, title, state, usesFileUploads};
use App\Models\LocClearance;
use App\Models\ZoneLocation;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

usesFileUploads();

layout('components.layouts.client');
title('New Locational Clearance Application');

state([
    // Step state tracking (1 = Form, 2 = Requirements, 3 = Location)
    'step' => 1,

    // Step 2: Uploads
    'basic_docs_files' => [],
    'land_right_files' => [],
    'plans_specs_files' => [],
    'local_clearances_files' => [],
    'special_req_files' => [],

    // Step 1: Form details
    'applicant_name' => fn() => trim((auth('client')->user()->first_name ?? '') . ' ' . (auth('client')->user()->middle_name ? auth('client')->user()->middle_name . ' ' : '') . (auth('client')->user()->last_name ?? '')),
    'corporation_name' => '',
    'representative_address_tel' => fn() => auth('client')->user()->address ?? '',
    'corporation_address_tel' => '',
    'authorized_representative_name' => '',
    'authorized_representative_address_tel' => '',

    'project_type' => '',
    'project_nature' => '',
    'project_location' => '',
    'lot_area_sqm' => '',
    'building_area_sqm' => '',
    'perimeter_m' => '',

    'right_over_land' => 'owner',
    'right_over_land_specify' => '',
    'project_tenure' => 'permanent',

    'existing_land_use' => 'residential',
    'existing_land_use_specify' => '',
    'agriculture_crops_specify' => '',
    'is_tenanted' => false,

    'project_cost' => '',

    'has_written_notice' => false,
    'notice_issuing_officer' => '',
    'notice_order_number' => '',
    'notice_date' => '',

    'has_related_action' => false,
    'action_office_filed' => '',
    'action_date_filed' => '',
    'action_taken' => '',

    'preferred_mode_of_release' => 'pick_up',

    // Step 3: Coordinates
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
        'notice_issuing_officer' => ['nullable', 'required_if:has_written_notice,1,true', 'string', 'max:255'],
        'notice_order_number' => ['nullable', 'required_if:has_written_notice,1,true', 'string', 'max:255'],
        'notice_date' => ['nullable', 'required_if:has_written_notice,1,true', 'date'],

        'has_related_action' => ['boolean'],
        'action_office_filed' => ['nullable', 'required_if:has_related_action,1,true', 'string', 'max:255'],
        'action_date_filed' => ['nullable', 'required_if:has_related_action,1,true', 'date'],
        'action_taken' => ['nullable', 'required_if:has_related_action,1,true', 'string', 'max:255'],

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
    // Strictly require real location coordinates
    $this->validate([
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
    ], [
        'latitude.required' => 'You must grant GPS location permission before submitting.',
        'longitude.required' => 'You must grant GPS location permission before submitting.',
    ]);

    // Store uploaded files
    $uploadCategories = ['basic_docs_files', 'land_right_files', 'plans_specs_files', 'local_clearances_files', 'special_req_files'];
    $requirements = [];

    foreach ($uploadCategories as $category) {
        $requirements[$category] = array_map(function ($file) use ($category) {
            return $file->store("locational-clearances/{$category}", 'public');
        }, $this->{$category} ?? []);
    }

    DB::transaction(function () use ($requirements) {
        $clearance = LocClearance::create([
            'client_id' => auth('client')->id(),
            'status' => 'pending',

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
            'zone_type' => $this->existing_land_use,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'notes' => $this->project_location,
        ]);
    });

    Flux::toast(text: 'Application and location submitted successfully!', variant: 'success');

    return redirect()->route('client.applications.index');
};

?>

<div class="max-w-4xl mx-auto space-y-5 sm:space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">
                Application for Locational Clearance
            </flux:heading>
            <flux:subheading class="text-xs sm:text-sm mt-0.5">
                @if ($step === 1)
                Phase 1 of 3: Applicant & Project Details
                @elseif ($step === 2)
                Phase 2 of 3: Supporting Requirements Checklist
                @else
                Phase 3 of 3: GPS Geolocation & Site Pinpointing
                @endif
            </flux:subheading>
        </div>

        <div class="self-start sm:self-auto">
            <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle" size="sm"
                icon="arrow-left">
                Back to List
            </flux:button>
        </div>
    </div>

    <!-- Stepper Navigation -->
    <div
        class="grid grid-cols-3 gap-1.5 sm:gap-2 p-1.5 bg-zinc-100 dark:bg-zinc-800/60 rounded-xl text-xs font-semibold">
        <button type="button" wire:click="$set('step', 1)"
            class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg transition {{ $step === 1 ? 'bg-white dark:bg-zinc-700 shadow-xs text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400' }}">
            <span
                class="size-5 rounded-full flex items-center justify-center text-[10px] {{ $step === 1 ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900' : 'bg-zinc-300 dark:bg-zinc-600' }}">1</span>
            <span class="truncate">Form</span>
        </button>

        <button type="button" @if($step> 1) wire:click="$set('step', 2)" @endif
            class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg transition {{ $step === 2 ? 'bg-white
            dark:bg-zinc-700 shadow-xs text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400' }}">
            <span
                class="size-5 rounded-full flex items-center justify-center text-[10px] {{ $step === 2 ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900' : 'bg-zinc-300 dark:bg-zinc-600' }}">2</span>
            <span class="truncate">Requirements</span>
        </button>

        <div
            class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg transition {{ $step === 3 ? 'bg-white dark:bg-zinc-700 shadow-xs text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400' }}">
            <span
                class="size-5 rounded-full flex items-center justify-center text-[10px] {{ $step === 3 ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900' : 'bg-zinc-300 dark:bg-zinc-600' }}">3</span>
            <span class="truncate">Location Map</span>
        </div>
    </div>

    <!-- Phase 1: Form Details Component -->
    @if ($step === 1)
    @include('livewire.client.applications.partials.step-form')
    @elseif ($step === 2)
    @include('livewire.client.applications.partials.step-requirements')
    @elseif ($step === 3)
    @include('livewire.client.applications.partials.step-location')
    @endif
</div>