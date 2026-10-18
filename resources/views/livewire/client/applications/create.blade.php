<?php

use function Livewire\Volt\{layout, title, state, usesFileUploads};
use App\Models\LocClearance;
use Flux\Flux;

usesFileUploads();

layout('components.layouts.client');
title('New Locational Clearance Application');

state([
    // Step state tracking (1 = Form, 2 = Requirements Checklist)
    'step' => 1,

    // Image uploads state for 5 requirement categories
    'basic_docs_files' => [],
    'land_right_files' => [],
    'plans_specs_files' => [],
    'local_clearances_files' => [],
    'special_req_files' => [],

    // 1-6. Applicant & Representation Details
    'applicant_name' => fn() => trim((auth('client')->user()->first_name ?? '') . ' ' . (auth('client')->user()->middle_name ? auth('client')->user()->middle_name . ' ' : '') . (auth('client')->user()->last_name ?? '')),
    'corporation_name' => '',
    'representative_address_tel' => fn() => auth('client')->user()->address ?? '',
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

$removeFile = function ($category, $index) {
    if (isset($this->{$category}[$index])) {
        unset($this->{$category}[$index]);
        $this->{$category} = array_values($this->{$category});
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

$submit = function () {
    // Validate optional uploads in step 2
    $this->validate([
        'basic_docs_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'land_right_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'plans_specs_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'local_clearances_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        'special_req_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
    ]);

    // Store uploaded files and store paths grouped inside $requirements array
    $uploadCategories = ['basic_docs_files', 'land_right_files', 'plans_specs_files', 'local_clearances_files', 'special_req_files'];

    $requirements = [];

    foreach ($uploadCategories as $category) {
        $requirements[$category] = array_map(function ($file) use ($category) {
            return $file->store("locational-clearances/{$category}", 'public');
        }, $this->{$category} ?? []);
    }

    LocClearance::create([
        'client_id' => auth('client')->id(),
        'status' => 'pending',

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

        // Save all requirement file paths into the JSON 'requirements' column
        'requirements' => $requirements,
    ]);

    Flux::toast(text: 'Application submitted successfully!', variant: 'success');

    return redirect()->route('client.applications.index');
};

?>

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl" level="1">Application for Locational Clearance</flux:heading>
            <flux:subheading>
                @if ($step === 1)
                    Please fill out all the required details according to the official form.
                @else
                    Review the required documents and attach optional supporting photos.
                @endif
            </flux:subheading>
        </div>
        <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle" icon="arrow-left">
            Back to Applications
        </flux:button>
    </div>

    @if ($step === 1)
        <!-- STEP 1: Application Form Details -->
        <form wire:submit.prevent="goToNext" class="space-y-6">
            <!-- Section 1: Applicant & Representation Information (Items 1 - 6) -->
            <flux:card class="space-y-4">
                <flux:heading size="lg">Applicant & Representative Information</flux:heading>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input label="1. Name of Applicant (Last, First, Middle)" wire:model="applicant_name"
                        required />
                    <flux:input label="2. Name of Corporation" wire:model="corporation_name"
                        placeholder="If applicable" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input label="3. Address / Tel. No. of Applicant" wire:model="representative_address_tel"
                        placeholder="Address / Contact No." required />
                    <flux:input label="4. Address / Tel. No. of Corporation" wire:model="corporation_address_tel"
                        placeholder="If applicable" />
                </div>

                <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 space-y-4">
                    <flux:subheading>Authorized Representative (If Applicable)</flux:subheading>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input label="5. Name of Authorized Representative"
                            wire:model="authorized_representative_name" />
                        <flux:input label="6. Address / Tel. No. of Authorized Representative"
                            wire:model="authorized_representative_address_tel" />
                    </div>
                </div>
            </flux:card>

            <!-- Section 2: Project Specifications (Items 7 - 10) -->
            <flux:card class="space-y-4">
                <flux:heading size="lg">Project Details & Measurements</flux:heading>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input label="7. Project Type" wire:model="project_type"
                        placeholder="e.g. Residential, Commercial, Industrial" required />
                    <flux:input label="8. Project Nature" wire:model="project_nature"
                        placeholder="e.g. New Construction, Expansion, Renovation" required />
                </div>

                <flux:textarea label="9. Project Location" wire:model="project_location" rows="2"
                    placeholder="Barangay, Street, Municipality" required />

                <div>
                    <flux:subheading class="mb-2">10. Project Area (in square meters)</flux:subheading>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <flux:input label="Lot Area (sq.m)" type="number" step="0.01" wire:model="lot_area_sqm"
                            required />
                        <flux:input label="Building Area (sq.m)" type="number" step="0.01"
                            wire:model="building_area_sqm" />
                        <flux:input label="Perimeter (meters)" type="number" step="0.01" wire:model="perimeter_m" />
                    </div>
                </div>
            </flux:card>

            <!-- Section 3: Land Ownership, Land Use & Project Cost (Items 11 - 14) -->
            <flux:card class="space-y-4">
                <flux:heading size="lg">Right Over Land, Land Use & Cost</flux:heading>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:select label="11. Right Over Land" wire:model.live="right_over_land" required>
                        <flux:select.option value="owner">Owner</flux:select.option>
                        <flux:select.option value="heirs">Heirs</flux:select.option>
                        <flux:select.option value="lease">Lease</flux:select.option>
                        <flux:select.option value="others">Others (Specify)</flux:select.option>
                    </flux:select>

                    <flux:select label="12. Project Tenure" wire:model="project_tenure" required>
                        <flux:select.option value="permanent">Permanent</flux:select.option>
                        <flux:select.option value="temporary">Temporary</flux:select.option>
                    </flux:select>
                </div>

                @if ($right_over_land === 'others')
                    <flux:input label="Specify Right Over Land" wire:model="right_over_land_specify" required />
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:select label="13. Existing Land Use of Project Site" wire:model.live="existing_land_use"
                        required>
                        <flux:select.option value="residential">Residential</flux:select.option>
                        <flux:select.option value="commercial">Commercial</flux:select.option>
                        <flux:select.option value="institutional">Institutional</flux:select.option>
                        <flux:select.option value="industrial">Industrial</flux:select.option>
                        <flux:select.option value="vacant_idle">Vacant / Idle</flux:select.option>
                        <flux:select.option value="agriculture">Agriculture</flux:select.option>
                        <flux:select.option value="others">Others</flux:select.option>
                    </flux:select>

                    <div class="flex items-center pt-6">
                        <flux:checkbox label="Is the project site currently Tenanted?" wire:model="is_tenanted" />
                    </div>
                </div>

                @if ($existing_land_use === 'agriculture')
                    <flux:input label="Specify Agriculture Crops" wire:model="agriculture_crops_specify"
                        placeholder="e.g. Rice, Corn, Coconut" required />
                @endif

                @if ($existing_land_use === 'others')
                    <flux:input label="Specify Other Existing Land Use" wire:model="existing_land_use_specify"
                        required />
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <flux:input label="14. Project Cost (in pesos)" type="number" step="0.01"
                        wire:model="project_cost" placeholder="0.00" required />
                </div>
            </flux:card>

            <!-- Section 4: Legal & Official Proceedings (Items 15 & 16) -->
            <flux:card class="space-y-4">
                <flux:heading size="lg">Written Notices & Related Actions</flux:heading>

                <div class="space-y-3">
                    <flux:subheading>15. Is the project applied for the subject of written notice(s) from this Board or
                        LGU to present or apply for Locational Clearance (LC)?</flux:subheading>
                    <div class="flex items-center gap-4">
                        <flux:radio.group wire:model.live="has_written_notice">
                            <flux:radio value="1" label="Yes" />
                            <flux:radio value="0" label="No" />
                        </flux:radio.group>
                    </div>

                    @if ((bool) $has_written_notice)
                        <div
                            class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 space-y-3 mt-2 border border-zinc-200 dark:border-zinc-700">
                            <flux:subheading>Written Notice Information</flux:subheading>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <flux:input label="Issuing Officer" wire:model="notice_issuing_officer" required />
                                <flux:input label="Order in the Notice" wire:model="notice_order_number" required />
                                <flux:input label="Date of Notice" type="date" wire:model="notice_date"
                                    required />
                            </div>
                        </div>
                    @endif
                </div>

                <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4"></div>

                <div class="space-y-3">
                    <flux:subheading>16. Is the project applied for the subject of related action(s) with other offices
                        of the Board and/or LGU?</flux:subheading>
                    <div class="flex items-center gap-4">
                        <flux:radio.group wire:model.live="has_related_action">
                            <flux:radio value="1" label="Yes" />
                            <flux:radio value="0" label="No" />
                        </flux:radio.group>
                    </div>

                    @if ((bool) $has_related_action)
                        <div
                            class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 space-y-3 mt-2 border border-zinc-200 dark:border-zinc-700">
                            <flux:subheading>Related Action Information</flux:subheading>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <flux:input label="Office where Action was filed" wire:model="action_office_filed"
                                    required />
                                <flux:input label="Date Filed" type="date" wire:model="action_date_filed"
                                    required />
                                <flux:input label="Action Taken" wire:model="action_taken" required />
                            </div>
                        </div>
                    @endif
                </div>
            </flux:card>

            <!-- Section 5: Preferred Mode of Release -->
            <flux:card class="space-y-4">
                <flux:heading size="lg">Preferred Mode of Release</flux:heading>

                <flux:select label="17. Preferred Mode of Release of Decision" wire:model="preferred_mode_of_release"
                    required>
                    <flux:select.option value="pick_up">Pick-up</flux:select.option>
                    <flux:select.option value="mail_applicant">By mail addressed to Applicant</flux:select.option>
                    <flux:select.option value="mail_representative">By mail addressed to Authorized Representative
                    </flux:select.option>
                </flux:select>
            </flux:card>

            <div class="flex justify-end gap-3 pt-4">
                <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" icon-trailing="arrow-right">
                    Next: Requirements Checklist
                </flux:button>
            </div>
        </form>
    @else
        <!-- STEP 2: Requirements Checklist Overview & Photo Uploads -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Mobile: Top Timeline Overview | Desktop: Right Timeline Overview (col-span-1) -->
            <div class="order-1 lg:order-2 lg:col-span-1 space-y-6">
                <flux:card class="space-y-4">
                    <flux:heading size="lg">Overview Timeline</flux:heading>

                    <!-- Responsive Pure Tailwind Timeline -->
                    <div class="overflow-x-auto pb-2 sm:pb-0">
                        <div class="flex sm:flex-col gap-4 sm:gap-6 min-w-max sm:min-w-0 relative">

                            <!-- Timeline Item 1 -->
                            <div class="flex items-center sm:items-start gap-3 relative">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs {{ count($basic_docs_files) > 0 ? 'bg-emerald-600' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300' }}">
                                    @if (count($basic_docs_files) > 0)
                                        <flux:icon.check variant="micro" class="w-4 h-4 text-white" />
                                    @else
                                        1
                                    @endif
                                </div>
                                <div>
                                    <flux:heading class="text-xs sm:text-sm">1. Basic Docs</flux:heading>
                                    @if (count($basic_docs_files) > 0)
                                        <flux:badge size="sm" color="green">{{ count($basic_docs_files) }}
                                            photo(s)</flux:badge>
                                    @else
                                        <span class="text-xs text-zinc-400">Pending</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Timeline Item 2 -->
                            <div class="flex items-center sm:items-start gap-3 relative">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs {{ count($land_right_files) > 0 ? 'bg-emerald-600' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300' }}">
                                    @if (count($land_right_files) > 0)
                                        <flux:icon.check variant="micro" class="w-4 h-4 text-white" />
                                    @else
                                        2
                                    @endif
                                </div>
                                <div>
                                    <flux:heading class="text-xs sm:text-sm">2. Proof of Land</flux:heading>
                                    @if (count($land_right_files) > 0)
                                        <flux:badge size="sm" color="green">{{ count($land_right_files) }}
                                            photo(s)</flux:badge>
                                    @else
                                        <span class="text-xs text-zinc-400">Pending</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Timeline Item 3 -->
                            <div class="flex items-center sm:items-start gap-3 relative">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs {{ count($plans_specs_files) > 0 ? 'bg-emerald-600' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300' }}">
                                    @if (count($plans_specs_files) > 0)
                                        <flux:icon.check variant="micro" class="w-4 h-4 text-white" />
                                    @else
                                        3
                                    @endif
                                </div>
                                <div>
                                    <flux:heading class="text-xs sm:text-sm">3. Plans & Specs</flux:heading>
                                    @if (count($plans_specs_files) > 0)
                                        <flux:badge size="sm" color="green">{{ count($plans_specs_files) }}
                                            photo(s)</flux:badge>
                                    @else
                                        <span class="text-xs text-zinc-400">Pending</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Timeline Item 4 -->
                            <div class="flex items-center sm:items-start gap-3 relative">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs {{ count($local_clearances_files) > 0 ? 'bg-emerald-600' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300' }}">
                                    @if (count($local_clearances_files) > 0)
                                        <flux:icon.check variant="micro" class="w-4 h-4 text-white" />
                                    @else
                                        4
                                    @endif
                                </div>
                                <div>
                                    <flux:heading class="text-xs sm:text-sm">4. Local Clearances</flux:heading>
                                    @if (count($local_clearances_files) > 0)
                                        <flux:badge size="sm" color="green">{{ count($local_clearances_files) }}
                                            photo(s)</flux:badge>
                                    @else
                                        <span class="text-xs text-zinc-400">Pending</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Timeline Item 5 -->
                            <div class="flex items-center sm:items-start gap-3 relative">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs {{ count($special_req_files) > 0 ? 'bg-emerald-600' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300' }}">
                                    @if (count($special_req_files) > 0)
                                        <flux:icon.check variant="micro" class="w-4 h-4 text-white" />
                                    @else
                                        5
                                    @endif
                                </div>
                                <div>
                                    <flux:heading class="text-xs sm:text-sm">5. Special Reqs</flux:heading>
                                    @if (count($special_req_files) > 0)
                                        <flux:badge size="sm" color="green">{{ count($special_req_files) }}
                                            photo(s)</flux:badge>
                                    @else
                                        <span class="text-xs text-zinc-400">Pending</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </flux:card>

                <!-- Navigation Action Buttons -->
                <div class="flex flex-col gap-3">
                    <flux:button wire:click="goToPrevious" variant="subtle" icon="arrow-left" class="w-full">
                        Back to Form Details
                    </flux:button>
                    <flux:button wire:click="submit" variant="primary" icon="check" class="w-full">
                        Submit Application
                    </flux:button>
                </div>
            </div>

            <!-- Mobile: Bottom Checklist | Desktop: Left Checklist (col-span-2) -->
            <div class="order-2 lg:order-1 lg:col-span-2 space-y-6">
                <flux:card class="space-y-6">
                    <div class="border-b border-zinc-200 dark:border-zinc-800 pb-4">
                        <flux:heading size="lg">Requirements Checklist & Attachments</flux:heading>
                        <flux:subheading>Attach optional photos (0 or more) for each category to fulfill requirements.
                        </flux:subheading>
                    </div>

                    <!-- Category 1 -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <flux:heading size="base"
                                class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                <flux:badge size="sm" color="zinc">1</flux:badge> Basic Application Documents
                            </flux:heading>
                            @if (count($basic_docs_files) > 0)
                                <flux:badge size="sm" color="green" icon="check">Attached</flux:badge>
                            @endif
                        </div>
                        <ul class="list-disc list-inside text-sm text-zinc-600 dark:text-zinc-400 space-y-1 pl-4">
                            <li>Notarized Application Form</li>
                            <li>Valid ID</li>
                            <li>Special Power of Attorney <span class="text-xs text-zinc-400">(Optional if filed by
                                    representative)</span></li>
                        </ul>
                        <div class="pt-2">
                            <input type="file" wire:model="basic_docs_files" multiple accept="image/*"
                                class="text-xs text-zinc-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300" />
                            @if ($basic_docs_files)
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach ($basic_docs_files as $index => $file)
                                        <div class="relative group">
                                            <img src="{{ $file->temporaryUrl() }}"
                                                class="h-16 w-16 object-cover rounded-md border border-zinc-300 dark:border-zinc-700" />
                                            <button type="button"
                                                wire:click="removeFile('basic_docs_files', {{ $index }})"
                                                class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full p-0.5 shadow-md hover:bg-red-700">
                                                <flux:icon.x-mark variant="micro" class="w-3 h-3" />
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
                        <div class="flex justify-between items-center">
                            <flux:heading size="base"
                                class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                <flux:badge size="sm" color="zinc">2</flux:badge> Proof of Right Over Land
                            </flux:heading>
                            @if (count($land_right_files) > 0)
                                <flux:badge size="sm" color="green" icon="check">Attached</flux:badge>
                            @endif
                        </div>
                        <ul class="list-disc list-inside text-sm text-zinc-600 dark:text-zinc-400 space-y-1 pl-4">
                            <li>TCT <span class="text-xs text-zinc-400">(For titled properties)</span></li>
                            <li>Certified True Copy of Tax Declaration & Real Property Tax Clearance/Receipts from
                                City/Municipal Treasurer's Office <span class="text-xs text-zinc-400">(For untitled
                                    properties)</span></li>
                            <li>Notarized Contract of Lease + copy of Owner’s Title or Tax Declaration <span
                                    class="text-xs text-zinc-400">(For rented/leased properties)</span></li>
                            <li>Notarized Affidavit of Consent / Authorization to Use Land or Deed of Conveyance <span
                                    class="text-xs text-zinc-400">(If granted consent by owner)</span></li>
                        </ul>
                        <div class="pt-2">
                            <input type="file" wire:model="land_right_files" multiple accept="image/*"
                                class="text-xs text-zinc-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300" />
                            @if ($land_right_files)
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach ($land_right_files as $index => $file)
                                        <div class="relative group">
                                            <img src="{{ $file->temporaryUrl() }}"
                                                class="h-16 w-16 object-cover rounded-md border border-zinc-300 dark:border-zinc-700" />
                                            <button type="button"
                                                wire:click="removeFile('land_right_files', {{ $index }})"
                                                class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full p-0.5 shadow-md hover:bg-red-700">
                                                <flux:icon.x-mark variant="micro" class="w-3 h-3" />
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
                        <div class="flex justify-between items-center">
                            <flux:heading size="base"
                                class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                <flux:badge size="sm" color="zinc">3</flux:badge> Plans and Technical
                                Specifications
                            </flux:heading>
                            @if (count($plans_specs_files) > 0)
                                <flux:badge size="sm" color="green" icon="check">Attached</flux:badge>
                            @endif
                        </div>
                        <ul class="list-disc list-inside text-sm text-zinc-600 dark:text-zinc-400 space-y-1 pl-4">
                            <li>Vicinity / Location Map</li>
                            <li>Site Development Plan</li>
                            <li>Architectural / Engineering Plans</li>
                            <li>Bill of Materials and Cost Estimates</li>
                        </ul>
                        <div class="pt-2">
                            <input type="file" wire:model="plans_specs_files" multiple accept="image/*"
                                class="text-xs text-zinc-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300" />
                            @if ($plans_specs_files)
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach ($plans_specs_files as $index => $file)
                                        <div class="relative group">
                                            <img src="{{ $file->temporaryUrl() }}"
                                                class="h-16 w-16 object-cover rounded-md border border-zinc-300 dark:border-zinc-700" />
                                            <button type="button"
                                                wire:click="removeFile('plans_specs_files', {{ $index }})"
                                                class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full p-0.5 shadow-md hover:bg-red-700">
                                                <flux:icon.x-mark variant="micro" class="w-3 h-3" />
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
                        <div class="flex justify-between items-center">
                            <flux:heading size="base"
                                class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                <flux:badge size="sm" color="zinc">4</flux:badge> Supporting Local Clearances
                            </flux:heading>
                            @if (count($local_clearances_files) > 0)
                                <flux:badge size="sm" color="green" icon="check">Attached</flux:badge>
                            @endif
                        </div>
                        <ul class="list-disc list-inside text-sm text-zinc-600 dark:text-zinc-400 space-y-1 pl-4">
                            <li>Barangay Clearance</li>
                            <li>Real Property Tax Clearance / Latest Tax Receipt</li>
                        </ul>
                        <div class="pt-2">
                            <input type="file" wire:model="local_clearances_files" multiple accept="image/*"
                                class="text-xs text-zinc-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300" />
                            @if ($local_clearances_files)
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach ($local_clearances_files as $index => $file)
                                        <div class="relative group">
                                            <img src="{{ $file->temporaryUrl() }}"
                                                class="h-16 w-16 object-cover rounded-md border border-zinc-300 dark:border-zinc-700" />
                                            <button type="button"
                                                wire:click="removeFile('local_clearances_files', {{ $index }})"
                                                class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full p-0.5 shadow-md hover:bg-red-700">
                                                <flux:icon.x-mark variant="micro" class="w-3 h-3" />
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
                        <div class="flex justify-between items-center">
                            <flux:heading size="base"
                                class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                <flux:badge size="sm" color="zinc">5</flux:badge> Special / Conditional
                                Requirements (Project-Specific)
                            </flux:heading>
                            @if (count($special_req_files) > 0)
                                <flux:badge size="sm" color="green" icon="check">Attached</flux:badge>
                            @endif
                        </div>
                        <ul class="list-disc list-inside text-sm text-zinc-600 dark:text-zinc-400 space-y-1 pl-4">
                            <li>Business Registration</li>
                            <li>Homeowners Association Clearance</li>
                            <li>DPWH Clearance</li>
                            <li>Environmental Clearance (ECC / CNC)</li>
                            <li>DAR Conversion Clearance</li>
                        </ul>
                        <div class="pt-2">
                            <input type="file" wire:model="special_req_files" multiple accept="image/*"
                                class="text-xs text-zinc-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300" />
                            @if ($special_req_files)
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach ($special_req_files as $index => $file)
                                        <div class="relative group">
                                            <img src="{{ $file->temporaryUrl() }}"
                                                class="h-16 w-16 object-cover rounded-md border border-zinc-300 dark:border-zinc-700" />
                                            <button type="button"
                                                wire:click="removeFile('special_req_files', {{ $index }})"
                                                class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full p-0.5 shadow-md hover:bg-red-700">
                                                <flux:icon.x-mark variant="micro" class="w-3 h-3" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </flux:card>
            </div>

        </div>
    @endif
</div>
