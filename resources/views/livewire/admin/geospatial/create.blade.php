<?php

use App\Models\ZoneLocation;
use App\Models\LocClearance;
use function Livewire\Volt\{layout, title, state, mount, computed, updated};
use Flux\Flux;

layout('components.layouts.admin');
title('Geotag Walk-In Application');

state([
    'selected_clearance_id' => '',
    'filter_tag' => 'untagged', // 'untagged', 'all', 'tagged'
    'latitude' => '',
    'longitude' => '',
    'notes' => '',
]);

// Computed: Filtered applications (Untagged by default)
$walkInClearances = computed(function () {
    $taggedIds = ZoneLocation::whereNotNull('application_id')->pluck('application_id')->toArray();

    return LocClearance::query()
        ->when($this->filter_tag === 'untagged', function ($q) use ($taggedIds) {
            $q->whereNotIn('id', $taggedIds);
            // Always keep currently selected application in view even if filter is untagged
            if ($this->selected_clearance_id) {
                $q->orWhere('id', $this->selected_clearance_id);
            }
        })
        ->when($this->filter_tag === 'tagged', function ($q) use ($taggedIds) {
            $q->whereIn('id', $taggedIds);
        })
        ->latest()
        ->get();
});

// Computed: Selected model
$selectedClearance = computed(function () {
    return $this->selected_clearance_id 
        ? LocClearance::find($this->selected_clearance_id) 
        : null;
});

// Computed: Checks if the currently selected clearance is already mapped
$existingZoneLocation = computed(function () {
    return $this->selected_clearance_id
        ? ZoneLocation::where('application_id', $this->selected_clearance_id)->first()
        : null;
});

// Watcher: Preload existing coordinates & address notes if already mapped
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
            } else {
                $this->latitude = '';
                $this->longitude = '';
            }
        }
    } else {
        $this->notes = '';
        $this->latitude = '';
        $this->longitude = '';
    }
}]);

mount(function () {
    // If a clearance_id was passed via query parameter (?clearance_id=1)
    $clearanceId = request()->query('clearance_id');

    if ($clearanceId) {
        $this->selected_clearance_id = (string) $clearanceId;
        $this->filter_tag = 'all'; // Switch filter to 'all' so selected record is visible

        $clearance = LocClearance::find($clearanceId);
        if ($clearance) {
            $this->notes = $clearance->project_location ?? '';

            $existing = ZoneLocation::where('application_id', $clearance->id)->first();
            if ($existing) {
                $this->latitude = (string) $existing->latitude;
                $this->longitude = (string) $existing->longitude;
                $this->notes = $existing->notes ?: $this->notes;
            }
        }
    }
});

// 1. Initial save attempt: checks if record already exists
$handleSaveAttempt = function () {
    $this->validate([
        'selected_clearance_id' => ['required', 'exists:loc_clearances,id'],
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
        'notes' => ['nullable', 'string'],
    ], [
        'selected_clearance_id.required' => 'Please select a clearance application.',
        'latitude.required' => 'Please pinpoint the location on the map.',
        'longitude.required' => 'Please pinpoint the location on the map.',
    ]);

    // Check if location already exists for this application
    $alreadyZoned = ZoneLocation::where('application_id', $this->selected_clearance_id)->exists();

    if ($alreadyZoned) {
        Flux::modal('overwrite-confirmation-modal')->show();
        return;
    }

    $this->saveLocation();
};

// 2. Perform the actual save / update
$saveLocation = function () {
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

    Flux::modal('overwrite-confirmation-modal')->close();

    Flux::toast(text: 'Application geotagged successfully!', variant: 'success');

    return redirect()->route('admin.geospatial.index');
};

?>

<div class="flex flex-col w-full h-full min-h-[calc(100vh-8rem)] gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold flex items-center gap-2">
                <flux:icon name="map-pin" class="size-6 text-indigo-600 dark:text-indigo-400" />
                Geotag Walk-In Application
            </flux:heading>
            <flux:subheading class="text-xs sm:text-sm">
                Assign real-world coordinates and boundary points to an encoded clearance application.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button href="{{ route('admin.geospatial.index') }}" wire:navigate variant="subtle" size="sm"
                icon="arrow-left" class="w-full sm:w-auto justify-center">
                Back to Zone Map
            </flux:button>
        </div>
    </div>

    <!-- Main Map & Control Sidebar Flex Body -->
    <div x-data="geotagPickerMap()" class="flex-1 flex flex-col lg:flex-row gap-6 min-h-0">

        <!-- Left: Full-Height Interactive Map Canvas -->
        <div
            class="flex-1 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-xs relative min-h-[450px]">
            <div x-ref="mapContainer" class="h-full w-full z-0" wire:ignore></div>

            <!-- Floating Coordinates Badge (Bottom-Left) -->
            <div
                class="absolute bottom-3 left-3 z-[400] bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md py-1.5 px-3 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-md text-xs font-mono text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                <div class="size-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span>Lat: <strong
                        x-text="$wire.latitude ? Number($wire.latitude).toFixed(6) : 'Click Map'"></strong></span>
                <span class="text-zinc-400">&bull;</span>
                <span>Lng: <strong
                        x-text="$wire.longitude ? Number($wire.longitude).toFixed(6) : 'Click Map'"></strong></span>
            </div>

            <div
                class="absolute top-3 right-3 z-[400] bg-zinc-900/80 text-white backdrop-blur-xs text-[11px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm hidden sm:flex items-center gap-1.5">
                <flux:icon name="map-pin" class="w-3.5 h-3.5 text-zinc-300 shrink-0" />
                <span>Click map to drop pin &bull; Drag marker to adjust</span>
            </div>
        </div>

        <!-- Right: Control Sidebar Panel -->
        <div
            class="w-full lg:w-96 shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col justify-between overflow-y-auto space-y-4">
            <form wire:submit="handleSaveAttempt" class="flex flex-col h-full justify-between space-y-4">
                <div class="space-y-4">
                    <!-- Sidebar Header -->
                    <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                        <flux:heading size="lg" class="text-base sm:text-lg">Location Details</flux:heading>
                        <flux:subheading class="text-xs">Application linking & coordinates</flux:subheading>
                    </div>

                    <!-- Filter Pill Bar: Untagged vs All vs Mapped -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <flux:label class="text-xs font-semibold">Select Application <span
                                    class="text-red-500">*</span></flux:label>

                            <!-- Filter Segmented Tabs -->
                            <div
                                class="inline-flex items-center p-0.5 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-[10px] font-medium border border-zinc-200/60 dark:border-zinc-700/60">
                                <button type="button" wire:click="$set('filter_tag', 'untagged')"
                                    class="px-2 py-0.5 rounded-md transition {{ $filter_tag === 'untagged' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 font-bold shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                                    Untagged
                                </button>
                                <button type="button" wire:click="$set('filter_tag', 'all')"
                                    class="px-2 py-0.5 rounded-md transition {{ $filter_tag === 'all' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 font-bold shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                                    All
                                </button>
                                <button type="button" wire:click="$set('filter_tag', 'tagged')"
                                    class="px-2 py-0.5 rounded-md transition {{ $filter_tag === 'tagged' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 font-bold shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                                    Mapped
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown Select -->
                        <flux:select wire:model.live="selected_clearance_id" placeholder="Choose an application..."
                            class="w-full">
                            @forelse($this->walkInClearances as $walkIn)
                            <flux:select.option :value="(string)$walkIn->id">
                                {{ $walkIn->application_no ?? '#' . $walkIn->id }} — {{ $walkIn->applicant_name }} ({{
                                $walkIn->project_type ?? 'N/A' }})
                            </flux:select.option>
                            @empty
                            <flux:select.option disabled value="">
                                No {{ $filter_tag }} applications available
                            </flux:select.option>
                            @endforelse
                        </flux:select>
                        <flux:error name="selected_clearance_id" />
                    </div>

                    <!-- Already Mapped Callout Notice -->
                    @if ($this->existingZoneLocation)
                    <div
                        class="p-2.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300 text-xs flex items-center gap-2">
                        <flux:icon name="information-circle"
                            class="size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                        <span>Already mapped. Saving will overwrite its existing location.</span>
                    </div>
                    @endif

                    <!-- Auto-Populated Summary Card -->
                    @if ($this->selectedClearance)
                    <div
                        class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 text-xs space-y-2 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 truncate">{{
                                $this->selectedClearance->applicant_name }}</span>
                            <flux:badge size="sm" color="indigo" class="capitalize font-semibold text-[10px]">
                                {{ $this->selectedClearance->existing_land_use ?? 'Commercial' }}
                            </flux:badge>
                        </div>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 space-y-0.5">
                            <div><span class="text-zinc-400">Type:</span> {{ $this->selectedClearance->project_type ??
                                'N/A' }}</div>
                            <div class="truncate"><span class="text-zinc-400">Address:</span> {{
                                $this->selectedClearance->project_location ?? 'N/A' }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- Coordinates Inputs -->
                    <div class="grid grid-cols-2 gap-3">
                        <flux:field>
                            <flux:label class="text-xs">Latitude <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="latitude" placeholder="e.g. 6.5902"
                                class="font-mono text-xs bg-zinc-50 dark:bg-zinc-800/40" required />
                            <flux:error name="latitude" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="text-xs">Longitude <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="longitude" placeholder="e.g. 125.3970"
                                class="font-mono text-xs bg-zinc-50 dark:bg-zinc-800/40" required />
                            <flux:error name="longitude" />
                        </flux:field>
                    </div>

                    <!-- Location Notes -->
                    <flux:field>
                        <flux:label class="text-xs">Location Notes / Landmarks</flux:label>
                        <flux:textarea wire:model="notes" rows="3"
                            placeholder="e.g. Near Barangay Hall, along highway boundary..." class="text-xs" />
                    </flux:field>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex flex-col gap-2">
                    <flux:button variant="primary" type="submit" icon="check" class="w-full justify-center"
                        wire:loading.attr="disabled">
                        Save & Geotag Location
                    </flux:button>
                    <flux:button href="{{ route('admin.geospatial.index') }}" wire:navigate variant="subtle" size="sm"
                        class="w-full justify-center">
                        Cancel
                    </flux:button>
                </div>
            </form>
        </div>

    </div>

    <!-- Overwrite Confirmation Modal -->
    <flux:modal name="overwrite-confirmation-modal" class="w-full max-w-sm sm:max-w-md mx-4" backdrop="blur">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <flux:icon name="exclamation-triangle" class="size-6" />
                </div>
                <div>
                    <flux:heading size="lg">Overwrite Location?</flux:heading>
                    <flux:subheading class="text-xs">Existing zone location found</flux:subheading>
                </div>
            </div>

            <flux:text class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 leading-relaxed">
                This clearance application is already plotted on the map. Saving will <strong>overwrite</strong> its
                existing GPS coordinates and landmark notes.
            </flux:text>

            @if ($this->existingZoneLocation)
            <div
                class="p-3 rounded-lg bg-zinc-100 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700 text-xs font-mono space-y-1">
                <div class="text-zinc-400 text-[11px] font-sans">Current Plotted Location:</div>
                <div>Lat: {{ number_format($this->existingZoneLocation->latitude, 6) }} &bull; Lng: {{
                    number_format($this->existingZoneLocation->longitude, 6) }}</div>
            </div>
            @endif

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" size="sm" wire:click="saveLocation" wire:loading.attr="disabled">
                    Yes, Overwrite & Save
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

@script
<script>
    const DEFAULT_MAP_CENTER = [6.5902, 125.3970]; // Malalag Center
    const MAPTILER_URL = 'https://api.maptiler.com/maps/hybrid-v4/256/{z}/{x}/{y}.jpg?key=Of5VhE6Hp5sKySUcOlMm';

    // Fix default Leaflet marker pin icons
    if (window.L) {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });
    }

    Alpine.data('geotagPickerMap', () => ({
        map: null,
        marker: null,

        init() {
            this.$nextTick(() => {
                this.initMap();
            });

            // Watch for changes when an application is chosen in the dropdown
            this.$watch('$wire.latitude', (val) => {
                if (val && this.$wire.longitude) {
                    const lat = parseFloat(val);
                    const lng = parseFloat(this.$wire.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        this.updateMarker(lat, lng);
                    }
                }
            });
        },

        initMap() {
            const rawLat = parseFloat(this.$wire.latitude);
            const rawLng = parseFloat(this.$wire.longitude);
            const hasCoords = !isNaN(rawLat) && !isNaN(rawLng);

            const centerLat = hasCoords ? rawLat : DEFAULT_MAP_CENTER[0];
            const centerLng = hasCoords ? rawLng : DEFAULT_MAP_CENTER[1];

            this.map = L.map(this.$refs.mapContainer).setView([centerLat, centerLng], hasCoords ? 16 : 14);

            L.tileLayer(MAPTILER_URL, {
                maxZoom: 19,
                attribution: '&copy; MapTiler'
            }).addTo(this.map);

            if (hasCoords) {
                this.marker = L.marker([centerLat, centerLng], { draggable: true }).addTo(this.map);
                this.bindMarkerEvents();
            }

            // Click map to drop or move the pin
            this.map.on('click', (e) => {
                const lat = parseFloat(e.latlng.lat.toFixed(6));
                const lng = parseFloat(e.latlng.lng.toFixed(6));

                this.$wire.set('latitude', lat, false);
                this.$wire.set('longitude', lng, false);

                if (this.marker) {
                    this.marker.setLatLng(e.latlng);
                } else {
                    this.marker = L.marker(e.latlng, { draggable: true }).addTo(this.map);
                    this.bindMarkerEvents();
                }
            });

            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 300);
        },

        updateMarker(lat, lng) {
            if (!this.map) return;
            this.map.setView([lat, lng], 16, { animate: true });
            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
            } else {
                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                this.bindMarkerEvents();
            }
        },

        bindMarkerEvents() {
            if (!this.marker) return;
            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.$wire.set('latitude', parseFloat(pos.lat.toFixed(6)), false);
                this.$wire.set('longitude', parseFloat(pos.lng.toFixed(6)), false);
            });
        }
    }));
</script>
@endscript