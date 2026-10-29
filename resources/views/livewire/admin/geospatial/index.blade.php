<?php

use App\Models\ZoneLocation;
use App\Models\LocClearance;
use function Livewire\Volt\{layout, state, title, mount, computed, updated};
use Flux\Flux;

layout('components.layouts.admin');
title('Interactive Zone Map');

state([
    'locations' => [],
    'showModal' => false,

    // Form inputs (initialized with empty string)
    'selected_clearance_id' => '',
    'latitude' => '',
    'longitude' => '',
    'notes' => '',
]);

// Computed: Always available across Livewire updates
$walkInClearances = computed(function () {
    return LocClearance::where('is_walk_in', true)
        ->latest()
        ->get();
});

// Computed: Automatically computes the selected model
$selectedClearance = computed(function () {
    return $this->selected_clearance_id 
        ? LocClearance::find($this->selected_clearance_id) 
        : null;
});

// Watcher: Auto-populates notes & existing coordinates if already mapped
updated(['selected_clearance_id' => function ($value) {
    if ($value) {
        $clearance = LocClearance::find($value);
        if ($clearance) {
            $this->notes = $clearance->project_location ?? '';

            $existing = ZoneLocation::where('application_id', $clearance->id)->first();
            if ($existing) {
                $this->latitude = (string) $existing->latitude;
                $this->longitude = (string) $existing->longitude;
                $this->notes = $existing->notes ?: $this->notes;
            }
        }
    } else {
        $this->notes = '';
        $this->latitude = '';
        $this->longitude = '';
    }
}]);

$loadLocations = function () {
    $this->locations = ZoneLocation::latest()->get()->map(function ($loc) {
        $clearance = null;
        if ($loc->application_id) {
            $clearance = is_numeric($loc->application_id)
                ? LocClearance::find($loc->application_id)
                : LocClearance::where('application_no', $loc->application_id)->first();
        }

        return [
            'id' => $loc->id,
            'application_id' => $clearance?->application_no ?? ($loc->application_id ? '#' . $loc->application_id : null),
            'clearance_id' => $clearance?->id ?? (is_numeric($loc->application_id) ? (int)$loc->application_id : null),
            'is_walk_in' => $clearance ? (bool) $clearance->is_walk_in : false,
            'title' => $loc->title,
            'zone_type' => $loc->zone_type,
            'latitude' => (float) $loc->latitude,
            'longitude' => (float) $loc->longitude,
            'notes' => $loc->notes,
        ];
    })->toArray();
};

mount(function () {
    $this->loadLocations();
});



// Edit action for sidebar walk-in entries
$editWalkInLocation = function ($clearanceId) {
    $this->selected_clearance_id = (string) $clearanceId;
    $clearance = LocClearance::findOrFail($clearanceId);
    $this->notes = $clearance->project_location ?? '';

    $existing = ZoneLocation::where('application_id', $clearance->id)->first();
    if ($existing) {
        $this->latitude = (string) $existing->latitude;
        $this->longitude = (string) $existing->longitude;
        $this->notes = $existing->notes ?: $this->notes;
    }

    $this->showModal = true;
};

$saveLocation = function () {
    $this->validate([
        'selected_clearance_id' => ['required', 'exists:loc_clearances,id'],
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
        'notes' => ['nullable', 'string'],
    ], [
        'selected_clearance_id.required' => 'Please select a walk-in clearance application.',
        'latitude.required' => 'Please pick coordinates on the map.',
        'longitude.required' => 'Please pick coordinates on the map.',
    ]);

    $clearance = LocClearance::findOrFail($this->selected_clearance_id);

    $title = trim(($clearance->project_type ?? 'Clearance') . ' - ' . $clearance->applicant_name);
    $zoneType = ucfirst($clearance->existing_land_use ?? 'Commercial');

    ZoneLocation::updateOrCreate(
        ['application_id' => $clearance->id],
        [
            'title' => $title,
            'zone_type' => $zoneType,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'notes' => $this->notes ?: $clearance->project_location,
        ]
    );

    $this->reset(['selected_clearance_id', 'latitude', 'longitude', 'notes']);
    $this->showModal = false;

    $this->loadLocations();
    $this->dispatch('refresh-map', locations: $this->locations);

    Flux::toast(text: 'Walk-in location saved successfully!', variant: 'success');
};

?>

<div class="flex flex-col w-full h-full gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Interactive Zone Map</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">GIS mapping, zone classifications, and geotagged clearances.
            </flux:subheading>
        </div>
    </div>

    <div x-data="mainZoneMap(@js($locations))" class="flex-1 flex gap-6">
        <div class="flex-1 rounded-xl overflow-hidden">
            <div x-ref="mapContainer" class="h-full w-full z-0" wire:ignore></div>
        </div>

        @include('livewire.admin.geospatial.partials.map-sidebar')
    </div>

    @include('livewire.admin.geospatial.partials.map-scripts')
</div>