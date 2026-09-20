<?php

use App\Models\ZoneLocation;
use function Livewire\Volt\{layout, state, title, mount};

layout('components.layouts.admin');
title('Interactive Zone Map');

state([
    'locations' => [],
    'showModal' => false,

    'application_id' => null,
    'title_input' => '',
    'zone_type' => 'Commercial',
    'latitude' => '',
    'longitude' => '',
    'notes' => '',
]);

$loadLocations = function () {$this->locations = ZoneLocation::latest()->get()->toArray();

    if (empty($this->locations)) {$this->locations = [
            [
                'id' => 1,
                'application_id' => 'APP-2026-0001',
                'title' => 'Malalag Municipal Hall',
                'zone_type' => 'Institutional',
                'latitude' => 6.5902,
                'longitude' => 125.3970,
                'notes' => 'Local government unit headquarters and administrative center of Malalag.',
            ],
            [
                'id' => 2,
                'application_id' => 'APP-2026-0891',
                'title' => 'Bolton Commercial Center',
                'zone_type' => 'Commercial',
                'latitude' => 6.7845,
                'longitude' => 125.3582,
                'notes' => 'Commercial clearance approved for multi-story development along Bolton.',
            ],
            [
                'id' => 3,
                'application_id' => 'APP-2026-0890',
                'title' => 'Pito Residential Complex',
                'zone_type' => 'Residential',
                'latitude' => 6.7214,
                'longitude' => 125.3210,
                'notes' => 'High-density residential housing project in Barangay Pito.',
            ],
            [
                'id' => 4,
                'application_id' => null,
                'title' => 'Poblacion Malalag Eco Industrial Zone',
                'zone_type' => 'Industrial',
                'latitude' => 6.5921,
                'longitude' => 125.3985,
                'notes' => 'Port-adjacent zone reserved for light manufacturing and warehouses in Malalag.',
            ]
        ];
    }
};

mount(function () {
    $this->loadLocations();
});

$saveLocation = function () {$this->validate([
        'application_id' => 'nullable|string|max:50',
        'title_input' => 'required|string|max:255',
        'zone_type' => 'required|string',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'notes' => 'nullable|string',
    ]);

    ZoneLocation::create([
        'application_id' => $this->application_id ?: null,
        'title' => $this->title_input,
        'zone_type' => $this->zone_type,
        'latitude' => (float) $this->latitude,
        'longitude' => (float) $this->longitude,
        'notes' => $this->notes,
    ]);

    $this->reset(['application_id', 'title_input', 'latitude', 'longitude', 'notes']);
    $this->zone_type = 'Commercial';$this->showModal = false;

    $this->loadLocations();
    $this->dispatch('refresh-map', locations:$this->locations);
};

?>

<div class="space-y-6">
    {{-- Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Interactive Zone Map</flux:heading>
            <flux:subheading>GIS mapping, zone classifications, and geotagged applications.</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="primary" icon="plus" size="sm" wire:click="$set('showModal', true)">
                Add Location
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- Main View Container --}}
    <div 
        x-data="mainZoneMap(@js($locations))"
        class="grid grid-cols-1 lg:grid-cols-4 gap-6"
    >
        {{-- Interactive Map --}}
        <div class="lg:col-span-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
            <div x-ref="mapContainer" class="h-[600px] w-full z-0" wire:ignore></div>
        </div>

        {{-- Sidebar Locations List --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg">Zone Places</flux:heading>
                    <flux:badge size="sm" variant="subtle">{{ count($locations) }} Locations</flux:badge>
                </div>

                <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                    @foreach ($locations as $loc)
                        <div 
                            class="p-3 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/80 dark:border-zinc-700/60 rounded-lg hover:border-indigo-500 cursor-pointer transition-all"
                            @click="zoomTo({{ $loc['latitude'] }}, {{$loc['longitude'] }})"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $loc['title'] }}
                                </span>
                                @php
                                    $badgeColor = match($loc['zone_type']) {
                                        'Commercial' => 'indigo',
                                        'Residential' => 'emerald',
                                        'Industrial' => 'warning',
                                        'Agricultural' => 'lime',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge size="sm" color="{{ $badgeColor }}">{{ $loc['zone_type'] }}</flux:badge>
                            </div>

                            <div class="mt-2 text-xs text-zinc-500 space-y-1">
                                <div><strong>App ID:</strong> {{ $loc['application_id'] ?? 'N/A' }}</div>
                                <div><strong>Coords:</strong> {{ number_format($loc['latitude'], 4) }}, {{ number_format($loc['longitude'], 4) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Add Location Modal --}}
    <flux:modal name="add-location-modal" wire:model="showModal" class="md:w-[550px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add Zone Location</flux:heading>
                <flux:subheading size="sm">Enter coordinates manually or click anywhere on the picker map below.</flux:subheading>
            </div>

            <form wire:submit="saveLocation" class="space-y-4">
                <flux:input label="Title / Landmark Name" wire:model="title_input" placeholder="e.g. South Commercial Plaza" required />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Application ID (Optional)" wire:model="application_id" placeholder="APP-2026-XXXX" />

                    <flux:select label="Zone Type" wire:model="zone_type">
                        <flux:select.option value="Commercial">Commercial</flux:select.option>
                        <flux:select.option value="Residential">Residential</flux:select.option>
                        <flux:select.option value="Industrial">Industrial</flux:select.option>
                        <flux:select.option value="Agricultural">Agricultural</flux:select.option>
                        <flux:select.option value="Institutional">Institutional</flux:select.option>
                    </flux:select>
                </div>

                {{-- Interactive Picker Map via Alpine --}}
                <div 
                    x-data="pickerZoneMap()"
                    x-effect="if ($wire.showModal) initOrResize()"
                    wire:ignore
                >
                    <flux:label class="mb-1">Click Map to Pick Coordinates</flux:label>
                    <div x-ref="pickerContainer" class="h-44 w-full rounded-lg border border-zinc-200 dark:border-zinc-700 z-0"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Latitude" wire:model="latitude" placeholder="14.5995" required />
                    <flux:input label="Longitude" wire:model="longitude" placeholder="120.9842" required />
                </div>

                <flux:textarea label="Notes / Description" wire:model="notes" rows="2" placeholder="Specific zoning rules or comments..." />

                <div class="flex justify-end gap-2 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Save Location</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Encapsulated Alpine Component Definitions --}}
    <script>
        const MAPTILER_URL = 'https://api.maptiler.com/maps/hybrid-v4/256/{z}/{x}/{y}.jpg?key=Of5VhE6Hp5sKySUcOlMm';

        document.addEventListener('alpine:init', () => {
            // Main Map Component
            Alpine.data('mainZoneMap', (initialLocations) => ({
                map: null,
                markersGroup: null,
                locations: initialLocations,

                init() {
                    const defaultLat = this.locations.length > 0 ? this.locations[0].latitude : 6.5902;
                    const defaultLng = this.locations.length > 0 ? this.locations[0].longitude : 125.3970;

                    this.map = L.map(this.$refs.mapContainer).setView([defaultLat, defaultLng], 13);

                    L.tileLayer(MAPTILER_URL, {
                        maxZoom: 19,
                        attribution: '&copy; MapTiler &copy; OpenStreetMap'
                    }).addTo(this.map);

                    this.markersGroup = L.layerGroup().addTo(this.map);
                    this.renderMarkers(this.locations);

                    // Listen for updates dispatched from Livewire
                    Livewire.on('refresh-map', ([data]) => {
                        if (data && data.locations) {
                            this.locations = data.locations;
                            this.renderMarkers(this.locations);
                        }
                    });
                },

                renderMarkers(locs) {
                    if (!this.markersGroup) return;
                    this.markersGroup.clearLayers();

                    locs.forEach(loc => {
                        const marker = L.marker([loc.latitude, loc.longitude]).addTo(this.markersGroup);
                        const popupContent = `
                            <div class="p-1 min-w-[200px]">
                                <h4 class="font-bold text-sm text-zinc-900">${loc.title}</h4>
                                <div class="text-xs text-indigo-600 font-semibold mb-1">${loc.zone_type} Zone</div>
                                <hr class="my-1 border-zinc-200" />
                                <p class="text-xs text-zinc-600 mb-1"><strong>App ID:</strong> ${loc.application_id || 'N/A'}</p>
                                <p class="text-xs text-zinc-500">${loc.notes || 'No extra notes provided.'}</p>
                            </div>
                        `;
                        marker.bindPopup(popupContent);
                    });
                },

                zoomTo(lat, lng) {
                    if (this.map) {
                        this.map.setView([lat, lng], 16, { animate: true });
                    }
                }
            }));

            // Picker Map Component (Modal)
            Alpine.data('pickerZoneMap', () => ({
                map: null,
                marker: null,

                initOrResize() {
                    // Wait for the modal animation to finish displaying
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (!this.map) {
                                this.map = L.map(this.$refs.pickerContainer).setView([14.5995, 120.9842], 12);

                                L.tileLayer(MAPTILER_URL, {
                                    maxZoom: 19,
                                    attribution: '&copy; MapTiler'
                                }).addTo(this.map);

                                this.map.on('click', (e) => {
                                    const lat = e.latlng.lat.toFixed(7);
                                    const lng = e.latlng.lng.toFixed(7);

                                    this.$wire.set('latitude', lat);
                                    this.$wire.set('longitude', lng);

                                    if (this.marker) {
                                        this.marker.setLatLng(e.latlng);
                                    } else {
                                        this.marker = L.marker(e.latlng).addTo(this.map);
                                    }
                                });
                            } else {
                                this.map.invalidateSize();
                            }
                        }, 200);
                    });
                }
            }));
        });
    </script>
</div>