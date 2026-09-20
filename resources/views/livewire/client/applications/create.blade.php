<?php

use function Livewire\Volt\{layout, title, state};
use App\Models\LocClearance;
use Flux\Flux;

layout('components.layouts.client');
title('New Locational Clearance Application');

state([
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

$submit = function () {
    $validated = $this->validate([
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
        'notice_issuing_officer' => ['nullable', 'required_if:has_written_notice,true', 'string', 'max:255'],
        'notice_order_number' => ['nullable', 'required_if:has_written_notice,true', 'string', 'max:255'],
        'notice_date' => ['nullable', 'required_if:has_written_notice,true', 'date'],

        // Item 16: Related Action
        'has_related_action' => ['boolean'],
        'action_office_filed' => ['nullable', 'required_if:has_related_action,true', 'string', 'max:255'],
        'action_date_filed' => ['nullable', 'required_if:has_related_action,true', 'date'],
        'action_taken' => ['nullable', 'required_if:has_related_action,true', 'string', 'max:255'],

        // Item 17: Preferred Release
        'preferred_mode_of_release' => ['required', 'string', 'max:255'],
    ]);

    LocClearance::create(array_merge($validated, [
        'client_id' => auth('client')->id(),
        'status' => 'pending',
    ]));

    Flux::toast(
        text: 'Application submitted successfully!',
        variant: 'success',
    );

    return redirect()->route('client.applications.index');
};

?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl" level="1">Application for Locational Clearance</flux:heading>
            <flux:subheading>Please fill out all the required details according to the official form.</flux:subheading>
        </div>
        <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle" icon="arrow-left">
            Back to Applications
        </flux:button>
    </div>

    <form wire:submit="submit" class="space-y-6">
        <!-- Section 1: Applicant & Representation Information (Items 1 - 6) -->
        <flux:card class="space-y-4">
            <flux:heading size="lg">Applicant & Representative Information</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input label="1. Name of Applicant (Last, First, Middle)" wire:model="applicant_name" required />
                <flux:input label="2. Name of Corporation" wire:model="corporation_name" placeholder="If applicable" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input label="3. Address / Tel. No. of Applicant" wire:model="representative_address_tel" placeholder="Address / Contact No." required />
                <flux:input label="4. Address / Tel. No. of Corporation" wire:model="corporation_address_tel" placeholder="If applicable" />
            </div>

            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 space-y-4">
                <flux:subheading>Authorized Representative (If Applicable)</flux:subheading>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input label="5. Name of Authorized Representative" wire:model="authorized_representative_name" />
                    <flux:input label="6. Address / Tel. No. of Authorized Representative" wire:model="authorized_representative_address_tel" />
                </div>
            </div>
        </flux:card>

        <!-- Section 2: Project Specifications (Items 7 - 10) -->
        <flux:card class="space-y-4">
            <flux:heading size="lg">Project Details & Measurements</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input label="7. Project Type" wire:model="project_type" placeholder="e.g. Residential, Commercial, Industrial" required />
                <flux:input label="8. Project Nature" wire:model="project_nature" placeholder="e.g. New Construction, Expansion, Renovation" required />
            </div>

            <flux:textarea label="9. Project Location" wire:model="project_location" rows="2" placeholder="Barangay, Street, Municipality" required />

            <div>
                <flux:subheading class="mb-2">10. Project Area (in square meters)</flux:subheading>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <flux:input label="Lot Area (sq.m)" type="number" step="0.01" wire:model="lot_area_sqm" required />
                    <flux:input label="Building Area (sq.m)" type="number" step="0.01" wire:model="building_area_sqm" />
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

            @if($right_over_land === 'others')
            <flux:input label="Specify Right Over Land" wire:model="right_over_land_specify" required />
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Dropdown for Existing Land Use -->
                <flux:select label="13. Existing Land Use of Project Site" wire:model.live="existing_land_use" required>
                    <flux:select.option value="residential">Residential</flux:select.option>
                    <flux:select.option value="commercial">Commercial</flux:select.option>
                    <flux:select.option value="institutional">Institutional</flux:select.option>
                    <flux:select.option value="industrial">Industrial</flux:select.option>
                    <flux:select.option value="vacant_idle">Vacant / Idle</flux:select.option>
                    <flux:select.option value="agriculture">Agriculture</flux:select.option>
                    <flux:select.option value="others">Others</flux:select.option>
                </flux:select>

                <!-- Tenanted Status Switch/Checkbox -->
                <div class="flex items-center pt-6">
                    <flux:checkbox label="Is the project site currently Tenanted?" wire:model="is_tenanted" />
                </div>
            </div>

            @if($existing_land_use === 'agriculture')
            <flux:input label="Specify Agriculture Crops" wire:model="agriculture_crops_specify" placeholder="e.g. Rice, Corn, Coconut" required />
            @endif

            @if($existing_land_use === 'others')
            <flux:input label="Specify Other Existing Land Use" wire:model="existing_land_use_specify" required />
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <flux:input label="14. Project Cost (in pesos)" type="number" step="0.01" wire:model="project_cost" placeholder="0.00" required />
            </div>
        </flux:card>

        <!-- Section 4: Legal & Official Proceedings (Items 15 & 16) -->
        <flux:card class="space-y-4">
            <flux:heading size="lg">Written Notices & Related Actions</flux:heading>

            <!-- Item 15 -->
            <div class="space-y-3">
                <flux:subheading>15. Is the project applied for the subject of written notice(s) from this Board or LGU to present or apply for Locational Clearance (LC)?</flux:subheading>
                <div class="flex items-center gap-4">
                    <flux:radio.group wire:model.live="has_written_notice">
                        <flux:radio value="1" label="Yes" />
                        <flux:radio value="0" label="No" />
                    </flux:radio.group>
                </div>

                @if((bool)$has_written_notice)
                <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 space-y-3 mt-2 border border-zinc-200 dark:border-zinc-700">
                    <flux:subheading>Written Notice Information</flux:subheading>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <flux:input label="Issuing Officer" wire:model="notice_issuing_officer" required />
                        <flux:input label="Order in the Notice" wire:model="notice_order_number" required />
                        <flux:input label="Date of Notice" type="date" wire:model="notice_date" required />
                    </div>
                </div>
                @endif
            </div>

            <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4"></div>

            <!-- Item 16 -->
            <div class="space-y-3">
                <flux:subheading>16. Is the project applied for the subject of related action(s) with other offices of the Board and/or LGU?</flux:subheading>
                <div class="flex items-center gap-4">
                    <flux:radio.group wire:model.live="has_related_action">
                        <flux:radio value="1" label="Yes" />
                        <flux:radio value="0" label="No" />
                    </flux:radio.group>
                </div>

                @if((bool)$has_related_action)
                <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 space-y-3 mt-2 border border-zinc-200 dark:border-zinc-700">
                    <flux:subheading>Related Action Information</flux:subheading>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <flux:input label="Office where Action was filed" wire:model="action_office_filed" required />
                        <flux:input label="Date Filed" type="date" wire:model="action_date_filed" required />
                        <flux:input label="Action Taken" wire:model="action_taken" required />
                    </div>
                </div>
                @endif
            </div>
        </flux:card>

        <!-- Section 5: Preferences (Item 17) -->
        <flux:card class="space-y-4">
            <flux:heading size="lg">Preferred Mode of Release</flux:heading>

            <flux:select label="17. Preferred Mode of Release of Decision" wire:model="preferred_mode_of_release" required>
                <flux:select.option value="pick_up">Pick-up</flux:select.option>
                <flux:select.option value="mail_applicant">By mail addressed to Applicant</flux:select.option>
                <flux:select.option value="mail_representative">By mail addressed to Authorized Representative</flux:select.option>
            </flux:select>
        </flux:card>

        <!-- Submit Button Row -->
        <div class="flex justify-end gap-3 pt-4">
            <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle">
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                Submit Application
            </flux:button>
        </div>
    </form>
</div>