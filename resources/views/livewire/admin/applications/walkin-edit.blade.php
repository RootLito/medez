<?php

use function Livewire\Volt\{layout, title, state, mount};
use App\Models\LocClearance;
use Flux\Flux;

layout('components.layouts.admin');
title('Edit Walk-In Application - Admin Portal');

state([
    'clearance' => null,

    // Official / Header Information
    'application_no' => '',
    'or_number' => '',
    'amount_paid' => '',
    'date_of_receipt' => '',

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

    // 17. Preferred Mode of Release & Status
    'preferred_mode_of_release' => 'pick_up',
    'status' => 'pending',
]);

mount(function (LocClearance $clearance) {
    $this->clearance = $clearance;

    // Populate state from model
    $this->application_no = $clearance->application_no;
    $this->or_number = $clearance->or_number;
    $this->amount_paid = $clearance->amount_paid;
    $this->date_of_receipt = $clearance->date_of_receipt?->format('Y-m-d');

    $this->applicant_name = $clearance->applicant_name;
    $this->corporation_name = $clearance->corporation_name;
    $this->representative_address_tel = $clearance->representative_address_tel;
    $this->corporation_address_tel = $clearance->corporation_address_tel;
    $this->authorized_representative_name = $clearance->authorized_representative_name;
    $this->authorized_representative_address_tel = $clearance->authorized_representative_address_tel;

    $this->project_type = $clearance->project_type;
    $this->project_nature = $clearance->project_nature;
    $this->project_location = $clearance->project_location;
    $this->lot_area_sqm = $clearance->lot_area_sqm;
    $this->building_area_sqm = $clearance->building_area_sqm;
    $this->perimeter_m = $clearance->perimeter_m;

    $this->right_over_land = $clearance->right_over_land ?? 'owner';
    $this->right_over_land_specify = $clearance->right_over_land_specify;
    $this->project_tenure = $clearance->project_tenure ?? 'permanent';

    $this->existing_land_use = $clearance->existing_land_use ?? 'residential';
    $this->existing_land_use_specify = $clearance->existing_land_use_specify;
    $this->agriculture_crops_specify = $clearance->agriculture_crops_specify;
    $this->is_tenanted = (bool) $clearance->is_tenanted;

    $this->project_cost = $clearance->project_cost;

    $this->has_written_notice = (bool) $clearance->has_written_notice;
    $this->notice_issuing_officer = $clearance->notice_issuing_officer;
    $this->notice_order_number = $clearance->notice_order_number;
    $this->notice_date = $clearance->notice_date?->format('Y-m-d');

    $this->has_related_action = (bool) $clearance->has_related_action;
    $this->action_office_filed = $clearance->action_office_filed;
    $this->action_date_filed = $clearance->action_date_filed?->format('Y-m-d');
    $this->action_taken = $clearance->action_taken;

    $this->preferred_mode_of_release = $clearance->preferred_mode_of_release ?? 'pick_up';
    $this->status = $clearance->status ?? 'pending';
});

$submit = function () {
    $validated = $this->validate([
        // Header
        'application_no' => ['nullable', 'string', 'max:255'],
        'or_number' => ['nullable', 'string', 'max:255'],
        'amount_paid' => ['nullable', 'numeric', 'min:0'],
        'date_of_receipt' => ['required', 'date'],

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

        // Item 17 & Status
        'preferred_mode_of_release' => ['required', 'string', 'max:255'],
        'status' => ['required', 'string', 'in:pending,approved,rejected'],
    ]);

    $this->clearance->update($validated);

    Flux::toast(text: 'Walk-in application updated successfully!', variant: 'success');

    return redirect()->route('admin.applications.walkin-index');
};

?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl" level="1">Edit Walk-In Application</flux:heading>
            <flux:subheading>Update details for Application No. {{ $clearance->application_no ?? '#' . $clearance->id }}
            </flux:subheading>
        </div>
        <flux:button href="{{ route('admin.applications.walkin-index') }}" wire:navigate variant="subtle" icon="arrow-left">
            Back to Walk-Ins
        </flux:button>
    </div>

    <form wire:submit="submit" class="space-y-6">
        <!-- Section 0: Official Processing Details (Admin Only) -->
        <flux:card class="space-y-4 border-l-4 border-indigo-500 dark:border-indigo-400">
            <flux:heading size="lg">Official Receipt & Processing Information</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <flux:input label="Application No." wire:model="application_no" placeholder="e.g. LC-2026-0001" />
                <flux:input label="O.R. Number" wire:model="or_number" placeholder="Official Receipt No." />
                <flux:input label="Amount Paid (₱)" type="number" step="0.01" wire:model="amount_paid"
                    placeholder="0.00" />
                <flux:input label="Date Received" type="date" wire:model="date_of_receipt" required />
            </div>

            <div class="pt-2">
                <flux:select label="Application Status" wire:model="status" required>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="approved">Approved</flux:select.option>
                    <flux:select.option value="rejected">Rejected</flux:select.option>
                </flux:select>
            </div>
        </flux:card>

        <!-- Section 1: Applicant & Representation Information -->
        <flux:card class="space-y-4">
            <flux:heading size="lg">Applicant & Representative Information</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input label="1. Name of Applicant (Last, First, Middle)" wire:model="applicant_name" required />
                <flux:input label="2. Name of Corporation" wire:model="corporation_name" placeholder="If applicable" />
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

        <!-- Section 2: Project Specifications -->
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

        <!-- Section 3: Right Over Land, Tenure & Project Cost -->
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
                <flux:select label="13. Existing Land Use of Project Site" wire:model.live="existing_land_use" required>
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
                <flux:input label="Specify Other Existing Land Use" wire:model="existing_land_use_specify" required />
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <flux:input label="14. Project Cost (in pesos)" type="number" step="0.01"
                    wire:model="project_cost" placeholder="0.00" required />
            </div>
        </flux:card>

        <!-- Section 4: Legal & Official Proceedings -->
        <flux:card class="space-y-4">
            <flux:heading size="lg">Written Notices & Related Actions</flux:heading>

            <!-- Item 15 -->
            <div class="space-y-3">
                <flux:subheading>15. Is the project applied for the subject of written notice(s) from this Board or LGU
                    to present or apply for Locational Clearance (LC)?</flux:subheading>
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
                            <flux:input label="Date of Notice" type="date" wire:model="notice_date" required />
                        </div>
                    </div>
                @endif
            </div>

            <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4"></div>

            <!-- Item 16 -->
            <div class="space-y-3">
                <flux:subheading>16. Is the project applied for the subject of related action(s) with other offices of
                    the Board and/or LGU?</flux:subheading>
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
                            <flux:input label="Date Filed" type="date" wire:model="action_date_filed" required />
                            <flux:input label="Action Taken" wire:model="action_taken" required />
                        </div>
                    </div>
                @endif
            </div>
        </flux:card>

        <!-- Section 5: Release Preference -->
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

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-3 pt-4">
            <flux:button href="{{ route('admin.applications.walkin-index') }}" wire:navigate variant="subtle">
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                Update Record
            </flux:button>
        </div>
    </form>
</div>
