<?php

use function Livewire\Volt\{layout, title, state, mount, usesFileUploads};
use App\Models\LocClearance;
use App\Models\ZoneLocation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

usesFileUploads();

layout('components.layouts.admin');
title('Edit Walk-In Application - Admin Portal');

state([
    'clearance' => null,

    // Step state tracking (1 = Form, 2 = Requirements, 3 = Location Map)
    'step' => 1,

    // Official / Header Information
    'application_no' => '',
    'or_number' => '',
    'amount_paid' => '',
    'date_of_receipt' => '',
    'status' => 'pending',

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

    // New image uploads state
    'basic_docs_files' => [],
    'land_right_files' => [],
    'plans_specs_files' => [],
    'local_clearances_files' => [],
    'special_req_files' => [],

    // Existing stored files (guaranteed arrays)
    'existing_basic_docs_files' => [],
    'existing_land_right_files' => [],
    'existing_plans_specs_files' => [],
    'existing_local_clearances_files' => [],
    'existing_special_req_files' => [],

    // Phase 3: Coordinates
    'latitude' => null,
    'longitude' => null,
]);

mount(function (LocClearance $clearance) {
    if (! $clearance->is_walk_in) {
        abort(404);
    }

    $this->clearance = $clearance;

    // Hydrate Header/Receipt
    $this->application_no = $clearance->application_no ?? '';
    $this->or_number = $clearance->or_number ?? '';
    $this->amount_paid = $clearance->amount_paid ?? '';
    $this->date_of_receipt = $clearance->date_of_receipt ? $clearance->date_of_receipt->format('Y-m-d') : now()->format('Y-m-d');
    $this->status = $clearance->status ?? 'pending';

    // Hydrate Form Specs
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

    // Hydrate existing requirements safely with array fallbacks
    $requirements = is_array($clearance->requirements) ? $clearance->requirements : (json_decode($clearance->requirements, true) ?? []);
    $this->existing_basic_docs_files = is_array($requirements['basic_docs_files'] ?? null) ? $requirements['basic_docs_files'] : [];
    $this->existing_land_right_files = is_array($requirements['land_right_files'] ?? null) ? $requirements['land_right_files'] : [];
    $this->existing_plans_specs_files = is_array($requirements['plans_specs_files'] ?? null) ? $requirements['plans_specs_files'] : [];
    $this->existing_local_clearances_files = is_array($requirements['local_clearances_files'] ?? null) ? $requirements['local_clearances_files'] : [];
    $this->existing_special_req_files = is_array($requirements['special_req_files'] ?? null) ? $requirements['special_req_files'] : [];

    // Hydrate ZoneLocation Coordinates
    $zoneLocation = ZoneLocation::where('application_id', $clearance->id)->first();
    if ($zoneLocation) {
        $this->latitude = $zoneLocation->latitude;
        $this->longitude = $zoneLocation->longitude;
    }
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
        $existing = is_array($this->{"existing_{$category}"}) ? $this->{"existing_{$category}"} : [];
        $newFiles = array_map(function ($file) use ($category) {
            return $file->store("locational-clearances/{$category}", 'public');
        }, is_array($this->{$category}) ? $this->{$category} : []);

        $requirements[$category] = array_values(array_merge($existing, $newFiles));
    }

    DB::transaction(function () use ($requirements) {
        $this->clearance->update([
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

        ZoneLocation::updateOrCreate(
            ['application_id' => $this->clearance->id],
            [
                'title' => $this->project_type . ' - ' . $this->applicant_name,
                'zone_type' => ucfirst($this->existing_land_use ?? 'Commercial'),
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
                'notes' => $this->project_location,
            ]
        );
    });

    Flux::toast(text: 'Walk-in application updated successfully!', variant: 'success');

    return redirect()->route('admin.applications.walkin-index');
};

?>

<div class="h-full w-full flex flex-col space-y-6">
    <!-- Header with Back Button -->
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-200/80 dark:border-zinc-800 pb-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">
                    Edit Walk-In Application
                </flux:heading>
                <span
                    class="font-mono text-xs font-semibold px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                    {{ $clearance->application_no ?? '#' . $clearance->id }}
                </span>
            </div>
            <flux:subheading class="text-xs sm:text-sm mt-0.5">
                @if ($step === 1) Phase 1 of 3: Edit applicant identification & specifications.
                @elseif ($step === 2) Phase 2 of 3: Manage attached requirement scans.
                @else Phase 3 of 3: Adjust project pin on the location map. @endif
            </flux:subheading>
        </div>
    </div>

    <!-- Node-Based Order Tracking Stepper -->
    <div class="py-5 px-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs">
        <div class="flex items-center justify-between relative max-w-xl mx-auto">

            <!-- Step 1 -->
            <button type="button" wire:click="$set('step', 1)"
                class="relative z-10 flex flex-col items-center group cursor-pointer focus:outline-hidden">
                <div
                    class="size-10 rounded-full flex items-center justify-center border-2 transition-all shadow-md {{ $step > 1 ? 'bg-emerald-600 text-white border-emerald-600' : ($step === 1 ? 'bg-white text-zinc-900 border-white ring-4 ring-white/20' : 'bg-zinc-900 text-zinc-400 border-zinc-700') }}">
                    @if($step > 1)
                    <flux:icon name="check" class="size-5" /> @else
                    <flux:icon name="document-text" class="size-5" /> @endif
                </div>
                <span
                    class="mt-2 text-xs font-medium {{ $step === 1 ? 'text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-400' }}">Form
                    Details</span>
            </button>

            <!-- Step 2 -->
            <button type="button" @if($step> 1) wire:click="$set('step', 2)" @endif class="relative z-10 flex flex-col
                items-center group {{ $step > 1 ? 'cursor-pointer' : 'cursor-default' }} focus:outline-hidden">
                <div
                    class="size-10 rounded-full flex items-center justify-center border-2 transition-all shadow-md {{ $step > 2 ? 'bg-emerald-600 text-white border-emerald-600' : ($step === 2 ? 'bg-white text-zinc-900 border-white ring-4 ring-white/20' : 'bg-zinc-900 text-zinc-500 border-zinc-700') }}">
                    @if($step > 2)
                    <flux:icon name="check" class="size-5" /> @else
                    <flux:icon name="folder-open" class="size-5" /> @endif
                </div>
                <span
                    class="mt-2 text-xs font-medium {{ $step === 2 ? 'text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-400' }}">Requirements</span>
            </button>

            <!-- Step 3 -->
            <button type="button" @if($step> 2) wire:click="$set('step', 3)" @endif class="relative z-10 flex flex-col
                items-center group {{ $step > 2 ? 'cursor-pointer' : 'cursor-default' }} focus:outline-hidden">
                <div
                    class="size-10 rounded-full flex items-center justify-center border-2 transition-all shadow-md {{ $step === 3 ? 'bg-white text-zinc-900 border-white ring-4 ring-white/20' : 'bg-zinc-900 text-zinc-500 border-zinc-700' }}">
                    <flux:icon name="map-pin" class="size-5" />
                </div>
                <span
                    class="mt-2 text-xs font-medium {{ $step === 3 ? 'text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-400' }}">Location
                    Map</span>
            </button>
        </div>
    </div>


    @if ($step === 1)
    <div wire:key="walkin-edit-step-1" class="flex-1">
        @include('livewire.admin.applications.walkin.partials.step-form')
    </div>
    @elseif ($step === 2)
    <div wire:key="walkin-edit-step-2" class="flex-1">
        @include('livewire.admin.applications.walkin.partials.edit-step-requirements')
    </div>
    @elseif ($step === 3)
    <div wire:key="walkin-edit-step-3" class="flex-1">
        @include('livewire.admin.applications.walkin.partials.step-location')
    </div>
    @endif
</div>