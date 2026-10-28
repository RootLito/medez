<?php

use function Livewire\Volt\{layout, title, state, mount, usesFileUploads};
use App\Models\LocClearance;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

usesFileUploads();

layout('components.layouts.client');
title('Edit Locational Clearance Application');

state([
    'clearance' => null,

    // Step state tracking (1 = Form, 2 = Requirements Checklist)
    'step' => 1,

    // New image uploads state
    'basic_docs_files' => [],
    'land_right_files' => [],
    'plans_specs_files' => [],
    'local_clearances_files' => [],
    'special_req_files' => [],

    // Existing stored files
    'existing_basic_docs_files' => [],
    'existing_land_right_files' => [],
    'existing_plans_specs_files' => [],
    'existing_local_clearances_files' => [],
    'existing_special_req_files' => [],

    // 1-6. Applicant & Representation Details
    'applicant_name' => '',
    'corporation_name' => '',
    'representative_address_tel' => '',
    'corporation_address_tel' => '',
    'authorized_representative_name' => '',
    'authorized_representative_address_tel' => '',

    // 7-10. Project Specifications & Measurements
    'project_type' => '',
    'project_nature' => '',
    'project_location' => '',
    'lot_area_sqm' => '',
    'building_area_sqm' => '',
    'perimeter_m' => '',

    // 11-12. Right Over Land & Tenure
    'right_over_land' => 'owner',
    'right_over_land_specify' => '',
    'project_tenure' => 'permanent',

    // 13. Existing Land Use
    'existing_land_use' => 'residential',
    'existing_land_use_specify' => '',
    'agriculture_crops_specify' => '',
    'is_tenanted' => false,

    // 14. Project Cost
    'project_cost' => '',

    // 15. Written Notice Details
    'has_written_notice' => false,
    'notice_issuing_officer' => '',
    'notice_order_number' => '',
    'notice_date' => '',

    // 16. Related Action Details
    'has_related_action' => false,
    'action_office_filed' => '',
    'action_date_filed' => '',
    'action_taken' => '',

    // 17. Preferred Mode of Release
    'preferred_mode_of_release' => 'pick_up',
]);

mount(function (LocClearance $clearance) {
    // Ensure clients can only edit their own applications
    if ($clearance->client_id !== auth('client')->id()) {
        abort(403);
    }

    $this->clearance = $clearance;

    // Hydrate form states with existing record values
    $this->applicant_name = $clearance->applicant_name;
    $this->corporation_name = $clearance->corporation_name ?? '';
    $this->representative_address_tel = $clearance->representative_address_tel;
    $this->corporation_address_tel = $clearance->corporation_address_tel ?? '';
    $this->authorized_representative_name = $clearance->authorized_representative_name ?? '';
    $this->authorized_representative_address_tel = $clearance->authorized_representative_address_tel ?? '';

    $this->project_type = $clearance->project_type;
    $this->project_nature = $clearance->project_nature;
    $this->project_location = $clearance->project_location;
    $this->lot_area_sqm = $clearance->lot_area_sqm;
    $this->building_area_sqm = $clearance->building_area_sqm ?? '';
    $this->perimeter_m = $clearance->perimeter_m ?? '';

    $this->right_over_land = $clearance->right_over_land ?? 'owner';
    $this->right_over_land_specify = $clearance->right_over_land_specify ?? '';
    $this->project_tenure = $clearance->project_tenure ?? 'permanent';

    $this->existing_land_use = $clearance->existing_land_use ?? 'residential';
    $this->existing_land_use_specify = $clearance->existing_land_use_specify ?? '';
    $this->agriculture_crops_specify = $clearance->agriculture_crops_specify ?? '';
    $this->is_tenanted = (bool) $clearance->is_tenanted;

    $this->project_cost = $clearance->project_cost;

    $this->has_written_notice = $clearance->has_written_notice ? 1 : 0;
    $this->notice_issuing_officer = $clearance->notice_issuing_officer ?? '';
    $this->notice_order_number = $clearance->notice_order_number ?? '';
    $this->notice_date = $clearance->notice_date ? $clearance->notice_date->format('Y-m-d') : '';

    $this->has_related_action = $clearance->has_related_action ? 1 : 0;
    $this->action_office_filed = $clearance->action_office_filed ?? '';
    $this->action_date_filed = $clearance->action_date_filed ? $clearance->action_date_filed->format('Y-m-d') : '';
    $this->action_taken = $clearance->action_taken ?? '';

    $this->preferred_mode_of_release = $clearance->preferred_mode_of_release ?? 'pick_up';

    // Hydrate existing requirements
    $requirements = is_array($clearance->requirements) ? $clearance->requirements : (json_decode($clearance->requirements, true) ?? []);
    $this->existing_basic_docs_files = $requirements['basic_docs_files'] ?? [];
    $this->existing_land_right_files = $requirements['land_right_files'] ?? [];
    $this->existing_plans_specs_files = $requirements['plans_specs_files'] ?? [];
    $this->existing_local_clearances_files = $requirements['local_clearances_files'] ?? [];
    $this->existing_special_req_files = $requirements['special_req_files'] ?? [];
});

$removeNewFile = function ($category, $index) {
    if (isset($this->{$category}[$index])) {
        unset($this->{$category}[$index]);
        $this->{$category} = array_values($this->{$category});
    }
};

$removeExistingFile = function ($category, $index) {
    $prop = "existing_{$category}";
    if (isset($this->{$prop}[$index])) {
        $filePath = $this->{$prop}[$index];
        Storage::disk('public')->delete($filePath);
        unset($this->{$prop}[$index]);
        $this->{$prop} = array_values($this->{$prop});
    }
};

$goToNext = function () {
    $this->validate([
        // Section 1
        'applicant_name' => ['required', 'string', 'max:255'],
        'corporation_name' => ['nullable', 'string', 'max:255'],
        'representative_address_tel' => ['required', 'string', 'max:255'],
        'corporation_address_tel' => ['nullable', 'string', 'max:255'],
        'authorized_representative_name' => ['nullable', 'string', 'max:255'],
        'authorized_representative_address_tel' => ['nullable', 'string', 'max:255'],

        // Section 2
        'project_type' => ['required', 'string', 'max:255'],
        'project_nature' => ['required', 'string', 'max:255'],
        'project_location' => ['required', 'string'],
        'lot_area_sqm' => ['required', 'numeric', 'min:0'],
        'building_area_sqm' => ['nullable', 'numeric', 'min:0'],
        'perimeter_m' => ['nullable', 'numeric', 'min:0'],

        // Section 3: Right Over Land & Tenure
        'right_over_land' => ['required', 'string', 'max:255'],
        'right_over_land_specify' => ['nullable', 'required_if:right_over_land,others', 'string', 'max:255'],
        'project_tenure' => ['required', 'string', 'max:255'],

        // Existing Land Use
        'existing_land_use' => ['required', 'string', 'max:255'],
        'existing_land_use_specify' => ['nullable', 'required_if:existing_land_use,others', 'string', 'max:255'],
        'agriculture_crops_specify' => ['nullable', 'required_if:existing_land_use,agriculture', 'string', 'max:255'],
        'is_tenanted' => ['boolean'],

        // Project Cost
        'project_cost' => ['required', 'numeric', 'min:0'],

        // Item 15: Written Notice
        'has_written_notice' => ['boolean'],
        'notice_issuing_officer' => ['nullable', 'required_if:has_written_notice,1,true', 'string', 'max:255'],
        'notice_order_number' => ['nullable', 'required_if:has_written_notice,1,true', 'string', 'max:255'],
        'notice_date' => ['nullable', 'required_if:has_written_notice,1,true', 'date'],

        // Item 16: Related Action
        'has_related_action' => ['boolean'],
        'action_office_filed' => ['nullable', 'required_if:has_related_action,1,true', 'string', 'max:255'],
        'action_date_filed' => ['nullable', 'required_if:has_related_action,1,true', 'date'],
        'action_taken' => ['nullable', 'required_if:has_related_action,1,true', 'string', 'max:255'],

        // Item 17: Preferred Release
        'preferred_mode_of_release' => ['required', 'string', 'max:255'],
    ]);

    $this->step = 2;
};

$goToPrevious = function () {
    $this->step = 1;
};

$save = function () {
    // Validate optional new uploads in step 2
    $this->validate([
        'basic_docs_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'land_right_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'plans_specs_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'local_clearances_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'special_req_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
    ]);

    // Store newly uploaded files and merge with remaining existing files
    $uploadCategories = ['basic_docs_files', 'land_right_files', 'plans_specs_files', 'local_clearances_files', 'special_req_files'];
    $requirements = [];

    foreach ($uploadCategories as $category) {
        $existing = $this->{"existing_{$category}"} ?? [];
        $newFiles = array_map(function ($file) use ($category) {
            return $file->store("locational-clearances/{$category}", 'public');
        }, $this->{$category} ?? []);

        $requirements[$category] = array_values(array_merge($existing, $newFiles));
    }

    $this->clearance->update([
        // 1-6
        'applicant_name' => $this->applicant_name,
        'corporation_name' => $this->corporation_name ?: null,
        'representative_address_tel' => $this->representative_address_tel,
        'corporation_address_tel' => $this->corporation_address_tel ?: null,
        'authorized_representative_name' => $this->authorized_representative_name ?: null,
        'authorized_representative_address_tel' => $this->authorized_representative_address_tel ?: null,

        // 7-10
        'project_type' => $this->project_type,
        'project_nature' => $this->project_nature,
        'project_location' => $this->project_location,
        'lot_area_sqm' => $this->lot_area_sqm,
        'building_area_sqm' => $this->building_area_sqm ?: null,
        'perimeter_m' => $this->perimeter_m ?: null,

        // 11-12
        'right_over_land' => $this->right_over_land,
        'right_over_land_specify' => $this->right_over_land === 'others' ? $this->right_over_land_specify : null,
        'project_tenure' => $this->project_tenure,

        // 13
        'existing_land_use' => $this->existing_land_use,
        'existing_land_use_specify' => $this->existing_land_use === 'others' ? $this->existing_land_use_specify : null,
        'agriculture_crops_specify' => $this->existing_land_use === 'agriculture' ? $this->agriculture_crops_specify : null,
        'is_tenanted' => (bool) $this->is_tenanted,

        // 14
        'project_cost' => $this->project_cost,

        // 15
        'has_written_notice' => (bool) $this->has_written_notice,
        'notice_issuing_officer' => $this->has_written_notice ? $this->notice_issuing_officer : null,
        'notice_order_number' => $this->has_written_notice ? $this->notice_order_number : null,
        'notice_date' => $this->has_written_notice ? ($this->notice_date ?: null) : null,

        // 16
        'has_related_action' => (bool) $this->has_related_action,
        'action_office_filed' => $this->has_related_action ? $this->action_office_filed : null,
        'action_date_filed' => $this->has_related_action ? ($this->action_date_filed ?: null) : null,
        'action_taken' => $this->has_related_action ? $this->action_taken : null,

        // 17
        'preferred_mode_of_release' => $this->preferred_mode_of_release,

        // Requirements
        'requirements' => $requirements,
    ]);

    Flux::toast(text: 'Application updated successfully!', variant: 'success');

    return redirect()->route('client.applications.index');
};

?>

<div class="max-w-4xl mx-auto space-y-5 sm:space-y-6">
    <!-- Header with Back Button -->
    <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">
                    Edit Application
                </flux:heading>
                <span
                    class="font-mono text-xs font-semibold px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                    {{ $clearance->application_no ?? '#' . $clearance->id }}
                </span>
            </div>
            <flux:subheading class="text-xs sm:text-sm mt-0.5">
                @if ($step === 1)
                Step 1 of 2: Update application form details.
                @else
                Step 2 of 2: Manage or attach supporting requirements and photos.
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

    <!-- Stepper Navigation Bar -->
    <div class="grid grid-cols-2 gap-2 p-1.5 bg-zinc-100 dark:bg-zinc-800/60 rounded-xl">
        <button type="button" wire:click="goToPrevious"
            class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-semibold transition {{ $step === 1 ? 'bg-white dark:bg-zinc-700 shadow-xs text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400' }}">
            <span
                class="size-5 rounded-full flex items-center justify-center text-[10px] {{ $step === 1 ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                1
            </span>
            <span>1. Form Details</span>
        </button>

        <button type="button" wire:click="goToNext"
            class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-semibold transition {{ $step === 2 ? 'bg-white dark:bg-zinc-700 shadow-xs text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400' }}">
            <span
                class="size-5 rounded-full flex items-center justify-center text-[10px] {{ $step === 2 ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                2
            </span>
            <span>2. Requirements</span>
        </button>
    </div>

    @if ($step === 1)
    <!-- ========================================== -->
    <!-- STEP 1: Application Form Details           -->
    <!-- ========================================== -->
    <form wire:submit.prevent="goToNext" class="space-y-4 sm:space-y-6">

        <!-- Section 1: Applicant Information -->
        <flux:card class="p-4 sm:p-6 space-y-4">
            <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                <flux:heading size="lg" class="text-base sm:text-lg">Applicant & Representative Information
                </flux:heading>
                <flux:subheading class="text-xs">Identify the legal applicant or corporate body</flux:subheading>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <flux:field>
                    <flux:label>1. Applicant Full Name <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="applicant_name" placeholder="Last Name, First Name, Middle Name" required />
                    <flux:error name="applicant_name" />
                </flux:field>

                <flux:field>
                    <flux:label>2. Name of Corporation</flux:label>
                    <flux:input wire:model="corporation_name" placeholder="Leave blank if personal" />
                    <flux:error name="corporation_name" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <flux:field>
                    <flux:label>3. Applicant Address / Tel. No. <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="representative_address_tel" placeholder="Address and mobile/phone"
                        required />
                    <flux:error name="representative_address_tel" />
                </flux:field>

                <flux:field>
                    <flux:label>4. Corporation Address / Tel. No.</flux:label>
                    <flux:input wire:model="corporation_address_tel" placeholder="If applicable" />
                    <flux:error name="corporation_address_tel" />
                </flux:field>
            </div>

            <!-- Representative Sub-group -->
            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 space-y-3">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider block">
                    Authorized Representative (Optional)
                </span>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <flux:field>
                        <flux:label>5. Name of Representative</flux:label>
                        <flux:input wire:model="authorized_representative_name"
                            placeholder="Full name of representative" />
                        <flux:error name="authorized_representative_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>6. Address / Tel. No.</flux:label>
                        <flux:input wire:model="authorized_representative_address_tel"
                            placeholder="Representative contact info" />
                        <flux:error name="authorized_representative_address_tel" />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        <!-- Section 2: Project Specifications -->
        <flux:card class="p-4 sm:p-6 space-y-4">
            <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                <flux:heading size="lg" class="text-base sm:text-lg">Project Details & Measurements</flux:heading>
                <flux:subheading class="text-xs">Physical specifications of the intended structure or land development
                </flux:subheading>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <flux:field>
                    <flux:label>7. Project Type <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="project_type" placeholder="e.g. Residential, Commercial, Warehouse"
                        required />
                    <flux:error name="project_type" />
                </flux:field>

                <flux:field>
                    <flux:label>8. Project Nature <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="project_nature" placeholder="e.g. New Construction, Expansion, Renovation"
                        required />
                    <flux:error name="project_nature" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>9. Complete Project Location <span class="text-red-500">*</span></flux:label>
                <flux:textarea wire:model="project_location" rows="2"
                    placeholder="Lot No., Block, Street, Barangay, City/Municipality" required />
                <flux:error name="project_location" />
            </flux:field>

            <div class="space-y-2 pt-1">
                <flux:label class="font-medium">10. Project Area & Measurements</flux:label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <flux:field>
                        <flux:label class="text-xs text-zinc-500">Lot Area (sq.m) <span class="text-red-500">*</span>
                        </flux:label>
                        <flux:input type="number" step="0.01" wire:model="lot_area_sqm" placeholder="0.00" required />
                        <flux:error name="lot_area_sqm" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="text-xs text-zinc-500">Building Area (sq.m)</flux:label>
                        <flux:input type="number" step="0.01" wire:model="building_area_sqm" placeholder="Optional" />
                        <flux:error name="building_area_sqm" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="text-xs text-zinc-500">Perimeter (meters)</flux:label>
                        <flux:input type="number" step="0.01" wire:model="perimeter_m" placeholder="Optional" />
                        <flux:error name="perimeter_m" />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        <!-- Section 3: Right Over Land, Land Use & Cost -->
        <flux:card class="p-4 sm:p-6 space-y-4">
            <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                <flux:heading size="lg" class="text-base sm:text-lg">Right Over Land, Land Use & Cost</flux:heading>
                <flux:subheading class="text-xs">Legal tenancy, zoning classification, and estimated investment
                </flux:subheading>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <flux:field>
                    <flux:label>11. Right Over Land <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model.live="right_over_land" required>
                        <flux:select.option value="owner">Owner</flux:select.option>
                        <flux:select.option value="heirs">Heirs</flux:select.option>
                        <flux:select.option value="lease">Lease</flux:select.option>
                        <flux:select.option value="others">Others (Specify)</flux:select.option>
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>12. Project Tenure <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="project_tenure" required>
                        <flux:select.option value="permanent">Permanent</flux:select.option>
                        <flux:select.option value="temporary">Temporary</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            @if ($right_over_land === 'others')
            <flux:field>
                <flux:label>Specify Right Over Land <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="right_over_land_specify" placeholder="Describe your legal rights" required />
                <flux:error name="right_over_land_specify" />
            </flux:field>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <flux:field>
                    <flux:label>13. Existing Land Use <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model.live="existing_land_use" required>
                        <flux:select.option value="residential">Residential</flux:select.option>
                        <flux:select.option value="commercial">Commercial</flux:select.option>
                        <flux:select.option value="institutional">Institutional</flux:select.option>
                        <flux:select.option value="industrial">Industrial</flux:select.option>
                        <flux:select.option value="vacant_idle">Vacant / Idle</flux:select.option>
                        <flux:select.option value="agriculture">Agriculture</flux:select.option>
                        <flux:select.option value="others">Others</flux:select.option>
                    </flux:select>
                </flux:field>

                <div class="flex items-center pt-2 sm:pt-6">
                    <flux:checkbox label="Is the project site currently tenanted?" wire:model="is_tenanted" />
                </div>
            </div>

            @if ($existing_land_use === 'agriculture')
            <flux:field>
                <flux:label>Specify Agricultural Crops <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="agriculture_crops_specify" placeholder="e.g. Rice, Corn, Coconut" required />
                <flux:error name="agriculture_crops_specify" />
            </flux:field>
            @endif

            @if ($existing_land_use === 'others')
            <flux:field>
                <flux:label>Specify Existing Land Use <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="existing_land_use_specify" placeholder="Describe the current land use"
                    required />
                <flux:error name="existing_land_use_specify" />
            </flux:field>
            @endif

            <div class="pt-1">
                <flux:field>
                    <flux:label>14. Project Cost (in Philippine Pesos) <span class="text-red-500">*</span></flux:label>
                    <flux:input type="number" step="0.01" wire:model="project_cost" placeholder="₱ 0.00" required />
                    <flux:error name="project_cost" />
                </flux:field>
            </div>
        </flux:card>

        <!-- Section 4: Legal & Official Proceedings -->
        <flux:card class="p-4 sm:p-6 space-y-4">
            <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                <flux:heading size="lg" class="text-base sm:text-lg">Written Notices & Related Actions</flux:heading>
                <flux:subheading class="text-xs">Prior LGU or Board administrative proceedings</flux:subheading>
            </div>

            <!-- Written Notice Question -->
            <div class="space-y-3">
                <flux:label class="text-xs sm:text-sm font-medium leading-relaxed block">
                    15. Is this project the subject of written notice(s) from the Board or LGU to apply for Locational
                    Clearance?
                </flux:label>

                <flux:radio.group wire:model.live="has_written_notice" class="flex items-center gap-4">
                    <flux:radio value="1" label="Yes" />
                    <flux:radio value="0" label="No" />
                </flux:radio.group>

                @if ((bool) $has_written_notice)
                <div
                    class="p-3.5 sm:p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/60 space-y-3 mt-2">
                    <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Written Notice
                        Information</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <flux:field>
                            <flux:label class="text-xs">Issuing Officer <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="notice_issuing_officer" placeholder="Name/Title" required />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">Order / Notice No. <span class="text-red-500">*</span>
                            </flux:label>
                            <flux:input wire:model="notice_order_number" placeholder="Reference #" required />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">Date of Notice <span class="text-red-500">*</span></flux:label>
                            <flux:input type="date" wire:model="notice_date" required />
                        </flux:field>
                    </div>
                </div>
                @endif
            </div>

            <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3"></div>

            <!-- Related Action Question -->
            <div class="space-y-3">
                <flux:label class="text-xs sm:text-sm font-medium leading-relaxed block">
                    16. Is the project applied for the subject of related action(s) with other offices of the Board/LGU?
                </flux:label>

                <flux:radio.group wire:model.live="has_related_action" class="flex items-center gap-4">
                    <flux:radio value="1" label="Yes" />
                    <flux:radio value="0" label="No" />
                </flux:radio.group>

                @if ((bool) $has_related_action)
                <div
                    class="p-3.5 sm:p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/60 space-y-3 mt-2">
                    <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Related Action Details</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <flux:field>
                            <flux:label class="text-xs">Office Filed <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="action_office_filed" placeholder="Department/Agency" required />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">Date Filed <span class="text-red-500">*</span></flux:label>
                            <flux:input type="date" wire:model="action_date_filed" required />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">Action Taken <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="action_taken" placeholder="Current resolution" required />
                        </flux:field>
                    </div>
                </div>
                @endif
            </div>
        </flux:card>

        <!-- Section 5: Preferred Mode of Release -->
        <flux:card class="p-4 sm:p-6 space-y-3">
            <flux:field>
                <flux:label class="font-medium">17. Preferred Mode of Release <span class="text-red-500">*</span>
                </flux:label>
                <flux:select wire:model="preferred_mode_of_release" required>
                    <flux:select.option value="pick_up">Personal Pick-up at Municipal Planning Office
                    </flux:select.option>
                    <flux:select.option value="mail_applicant">By Mail addressed to Applicant</flux:select.option>
                    <flux:select.option value="mail_representative">By Mail addressed to Authorized Representative
                    </flux:select.option>
                </flux:select>
            </flux:field>
        </flux:card>

        <!-- Bottom Button Bar -->
        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2">
            <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle"
                class="w-full sm:w-auto">
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary" icon-trailing="arrow-right"
                class="w-full sm:w-auto justify-center">
                Proceed to Requirements Checklist
            </flux:button>
        </div>
    </form>
    @else
    <!-- ========================================== -->
    <!-- STEP 2: Requirements Checklist & Uploads   -->
    <!-- ========================================== -->
    @php
    $c1 = count($existing_basic_docs_files) + count($basic_docs_files);
    $c2 = count($existing_land_right_files) + count($land_right_files);
    $c3 = count($existing_plans_specs_files) + count($plans_specs_files);
    $c4 = count($existing_local_clearances_files) + count($local_clearances_files);
    $c5 = count($existing_special_req_files) + count($special_req_files);
    @endphp

    <div class="space-y-5">
        <!-- Mobile Summary Header Badge Row -->
        <div
            class="flex flex-wrap items-center gap-2 p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-200/80 dark:border-zinc-800 text-xs text-zinc-500">
            <span class="font-medium text-zinc-700 dark:text-zinc-300">Current files:</span>
            <flux:badge size="sm" :color="$c1 > 0 ? 'green' : 'zinc'">Docs: {{ $c1 }}</flux:badge>
            <flux:badge size="sm" :color="$c2 > 0 ? 'green' : 'zinc'">Land: {{ $c2 }}</flux:badge>
            <flux:badge size="sm" :color="$c3 > 0 ? 'green' : 'zinc'">Plans: {{ $c3 }}</flux:badge>
            <flux:badge size="sm" :color="$c4 > 0 ? 'green' : 'zinc'">Clearance: {{ $c4 }}</flux:badge>
            <flux:badge size="sm" :color="$c5 > 0 ? 'green' : 'zinc'">Special: {{ $c5 }}</flux:badge>
        </div>

        <!-- Upload Categories Card -->
        <flux:card class="p-4 sm:p-6 space-y-6">
            <div>
                <flux:heading size="lg" class="text-base sm:text-lg">Requirements Checklist & Attachments</flux:heading>
                <flux:subheading class="text-xs">
                    Review existing documents, remove old files, or attach new scans/photos (JPG, PNG, PDF up to 10MB
                    each).
                </flux:subheading>
            </div>

            <!-- Category 1 -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">1</span>
                        <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Basic
                            Application Documents</span>
                    </div>
                    @if ($c1 > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ $c1 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                    <li>Notarized Application Form</li>
                    <li>Valid Identification Card (ID)</li>
                    <li>Special Power of Attorney (if filed by representative)</li>
                </ul>

                <div class="pl-2 sm:pl-8 space-y-2">
                    <label
                        class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                        <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional
                            basic documents</span>
                        <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                        <input type="file" wire:model="basic_docs_files" multiple accept="image/*,application/pdf"
                            class="hidden" />
                    </label>

                    @if ($c1 > 0)
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                        {{-- Existing Files --}}
                        @foreach ($existing_basic_docs_files as $index => $path)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                                class="size-full block">
                                <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                            </a>
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                            <button type="button" wire:click="removeExistingFile('basic_docs_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Delete existing file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach

                        {{-- Newly Uploaded Files --}}
                        @foreach ($basic_docs_files as $index => $file)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            @if(str_contains($file->getMimeType(), 'image'))
                            <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                            @else
                            <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                                <flux:icon name="document-text" class="size-6 text-zinc-400" />
                                <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                    $file->getClientOriginalName() }}</span>
                            </div>
                            @endif
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                            <button type="button" wire:click="removeNewFile('basic_docs_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Remove new file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <flux:separator />

            <!-- Category 2 -->
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">2</span>
                        <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Proof of Right
                            Over Land</span>
                    </div>
                    @if ($c2 > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ $c2 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                    <li>TCT (For titled properties)</li>
                    <li>Certified Tax Declaration & Real Property Tax Clearance</li>
                    <li>Notarized Contract of Lease or Authorization to Use Land</li>
                </ul>

                <div class="pl-2 sm:pl-8 space-y-2">
                    <label
                        class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                        <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional land
                            proof documents</span>
                        <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                        <input type="file" wire:model="land_right_files" multiple accept="image/*,application/pdf"
                            class="hidden" />
                    </label>

                    @if ($c2 > 0)
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                        {{-- Existing Files --}}
                        @foreach ($existing_land_right_files as $index => $path)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                                class="size-full block">
                                <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                            </a>
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                            <button type="button" wire:click="removeExistingFile('land_right_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Delete existing file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach

                        {{-- Newly Uploaded Files --}}
                        @foreach ($land_right_files as $index => $file)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            @if(str_contains($file->getMimeType(), 'image'))
                            <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                            @else
                            <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                                <flux:icon name="document-text" class="size-6 text-zinc-400" />
                                <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                    $file->getClientOriginalName() }}</span>
                            </div>
                            @endif
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                            <button type="button" wire:click="removeNewFile('land_right_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Remove new file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <flux:separator />

            <!-- Category 3 -->
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">3</span>
                        <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Plans and
                            Technical Specs</span>
                    </div>
                    @if ($c3 > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ $c3 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                    <li>Vicinity / Location Map</li>
                    <li>Site Development Plan & Architectural Plans</li>
                    <li>Bill of Materials & Cost Estimates</li>
                </ul>

                <div class="pl-2 sm:pl-8 space-y-2">
                    <label
                        class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                        <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional
                            plans and specs</span>
                        <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                        <input type="file" wire:model="plans_specs_files" multiple accept="image/*,application/pdf"
                            class="hidden" />
                    </label>

                    @if ($c3 > 0)
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                        {{-- Existing Files --}}
                        @foreach ($existing_plans_specs_files as $index => $path)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                                class="size-full block">
                                <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                            </a>
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                            <button type="button" wire:click="removeExistingFile('plans_specs_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Delete existing file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach

                        {{-- Newly Uploaded Files --}}
                        @foreach ($plans_specs_files as $index => $file)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            @if(str_contains($file->getMimeType(), 'image'))
                            <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                            @else
                            <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                                <flux:icon name="document-text" class="size-6 text-zinc-400" />
                                <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                    $file->getClientOriginalName() }}</span>
                            </div>
                            @endif
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                            <button type="button" wire:click="removeNewFile('plans_specs_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Remove new file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <flux:separator />

            <!-- Category 4 -->
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">4</span>
                        <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Supporting
                            Local Clearances</span>
                    </div>
                    @if ($c4 > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ $c4 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                    <li>Barangay Clearance for Locational Clearance</li>
                    <li>Real Property Tax Clearance / Latest Tax Receipt</li>
                </ul>

                <div class="pl-2 sm:pl-8 space-y-2">
                    <label
                        class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                        <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional
                            local clearances</span>
                        <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                        <input type="file" wire:model="local_clearances_files" multiple accept="image/*,application/pdf"
                            class="hidden" />
                    </label>

                    @if ($c4 > 0)
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                        {{-- Existing Files --}}
                        @foreach ($existing_local_clearances_files as $index => $path)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                                class="size-full block">
                                <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                            </a>
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                            <button type="button"
                                wire:click="removeExistingFile('local_clearances_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Delete existing file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach

                        {{-- Newly Uploaded Files --}}
                        @foreach ($local_clearances_files as $index => $file)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            @if(str_contains($file->getMimeType(), 'image'))
                            <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                            @else
                            <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                                <flux:icon name="document-text" class="size-6 text-zinc-400" />
                                <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                    $file->getClientOriginalName() }}</span>
                            </div>
                            @endif
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                            <button type="button" wire:click="removeNewFile('local_clearances_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Remove new file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <flux:separator />

            <!-- Category 5 -->
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">5</span>
                        <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Special
                            Requirements (If Applicable)</span>
                    </div>
                    @if ($c5 > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ $c5 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                    <li>DTI / SEC Registration, HOA Clearance, DPWH, ECC / CNC, DAR Conversion</li>
                </ul>

                <div class="pl-2 sm:pl-8 space-y-2">
                    <label
                        class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                        <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional
                            special requirements</span>
                        <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                        <input type="file" wire:model="special_req_files" multiple accept="image/*,application/pdf"
                            class="hidden" />
                    </label>

                    @if ($c5 > 0)
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                        {{-- Existing Files --}}
                        @foreach ($existing_special_req_files as $index => $path)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                                class="size-full block">
                                <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                            </a>
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                            <button type="button" wire:click="removeExistingFile('special_req_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Delete existing file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach

                        {{-- Newly Uploaded Files --}}
                        @foreach ($special_req_files as $index => $file)
                        <div
                            class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            @if(str_contains($file->getMimeType(), 'image'))
                            <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                            @else
                            <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                                <flux:icon name="document-text" class="size-6 text-zinc-400" />
                                <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                    $file->getClientOriginalName() }}</span>
                            </div>
                            @endif
                            <span
                                class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                            <button type="button" wire:click="removeNewFile('special_req_files', {{ $index }})"
                                class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition"
                                title="Remove new file">
                                <flux:icon name="x-mark" class="size-3" />
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </flux:card>

        <!-- Bottom Navigation Bar for Step 2 -->
        <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-2">
            <flux:button wire:click="goToPrevious" variant="subtle" icon="arrow-left" class="w-full sm:w-auto">
                Back to Form Details
            </flux:button>

            <flux:button wire:click="save" variant="primary" icon="check" class="w-full sm:w-auto justify-center"
                wire:loading.attr="disabled">
                Save Changes
            </flux:button>
        </div>
    </div>
    @endif
</div>