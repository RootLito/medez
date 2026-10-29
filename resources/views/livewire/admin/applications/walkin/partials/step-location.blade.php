<div class="h-full w-full space-y-4" x-data="walkinLocationPicker()" wire:ignore>
    <!-- Desktop Split Workstation -->
    <div class="flex flex-col lg:flex-row gap-5 sm:gap-6 h-full">

        <div
            class="flex-1 rounded-2xl overflow-hidden border border-zinc-300 dark:border-zinc-700 shadow-sm relative min-h-[460px] lg:min-h-[560px]">
            <div x-ref="mapContainer" class="h-full w-full z-0"></div>

            <div
                class="absolute bottom-3 left-3 z-[400] bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md py-2 px-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-md text-xs font-mono text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                <div class="size-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span>Lat: <strong
                        x-text="$wire.latitude ? Number($wire.latitude).toFixed(6) : 'Click Map'"></strong></span>
                <span class="text-zinc-400">&bull;</span>
                <span>Lng: <strong
                        x-text="$wire.longitude ? Number($wire.longitude).toFixed(6) : 'Click Map'"></strong></span>
            </div>

            <div
                class="absolute top-3 right-3 z-[400] bg-zinc-900/80 text-white backdrop-blur-xs text-[11px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm hidden sm:flex items-center gap-1.5">
                <flux:icon name="map-pin" class="size-3.5 text-zinc-300 shrink-0" />
                <span>Click map to place pin &bull; Drag marker to adjust</span>
            </div>
        </div>

        <div
            class="w-full lg:w-96 xl:w-[400px] shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between space-y-4">
            <div class="space-y-4">
                <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3 flex items-center justify-between">
                    <div>
                        <flux:heading size="lg" class="text-base sm:text-lg">Location Specs</flux:heading>
                        <flux:subheading class="text-xs">GPS pinning for this walk-in</flux:subheading>
                    </div>
                    <flux:button type="button" @click="recenterMap()" variant="subtle" size="xs"
                        icon="arrows-pointing-out">
                        Recenter
                    </flux:button>
                </div>

                <div
                    class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/80 dark:border-zinc-700/60 text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $applicant_name ?:
                            'Applicant Name' }}</span>
                        <flux:badge size="sm" color="indigo" class="capitalize font-semibold text-[10px]">
                            {{ $existing_land_use }}
                        </flux:badge>
                    </div>
                    <div class="text-[11px] text-zinc-500 space-y-0.5">
                        <div><span class="text-zinc-400">Type:</span> {{ $project_type ?: 'N/A' }}</div>
                        <div class="truncate"><span class="text-zinc-400">Address:</span> {{ $project_location ?: 'N/A'
                            }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label class="text-xs font-semibold">Latitude <span class="text-red-500">*</span>
                        </flux:label>
                        <flux:input wire:model="latitude" readonly placeholder="6.5902"
                            class="font-mono text-xs bg-zinc-50 dark:bg-zinc-800/40" />
                        <flux:error name="latitude" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="text-xs font-semibold">Longitude <span class="text-red-500">*</span>
                        </flux:label>
                        <flux:input wire:model="longitude" readonly placeholder="125.3970"
                            class="font-mono text-xs bg-zinc-50 dark:bg-zinc-800/40" />
                        <flux:error name="longitude" />
                    </flux:field>
                </div>

                <div
                    class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-700/40 text-[11px] text-zinc-500 flex items-start gap-2">
                    <flux:icon icon="light-bulb" class="size-4 shrink-0 text-amber-500 dark:text-amber-400 mt-0.5" />
                    <span>Click anywhere on the map or drag the pin directly onto the applicant's lot to lock
                        coordinates.</span>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex flex-col gap-2">
                <flux:button wire:click="submit" variant="primary" icon="check" class="w-full justify-center"
                    x-bind:disabled="!$wire.latitude || !$wire.longitude" wire:loading.attr="disabled">
                    Save & Encode Application
                </flux:button>
                <flux:button wire:click="$set('step', 2)" variant="subtle" size="sm" icon="arrow-left"
                    class="w-full justify-center">
                    Back to Requirements
                </flux:button>
            </div>
        </div>

    </div>
</div>

@script
<script>
    const DEFAULT_MAP_CENTER = [6.5902, 125.3970]; // Malalag Center
    const MAPTILER_URL = 'https://api.maptiler.com/maps/hybrid-v4/256/{z}/{x}/{y}.jpg?key=Of5VhE6Hp5sKySUcOlMm';

    if (window.L) {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });
    }

    Alpine.data('walkinLocationPicker', () => ({
        map: null,
        marker: null,

        init() {
            this.$nextTick(() => {
                this.initMap();
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

        recenterMap() {
            if (this.map) {
                this.map.setView(DEFAULT_MAP_CENTER, 14, { animate: true });
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