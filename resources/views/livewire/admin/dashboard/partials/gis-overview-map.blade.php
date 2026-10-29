<flux:card class="p-5 space-y-4">
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="base" class="font-bold">Municipal GIS & Geotagged Clearances Map</flux:heading>
                <flux:badge size="sm" color="blue" class="text-[10px]">Malalag, Davao del Sur</flux:badge>
            </div>
            <flux:subheading class="text-xs">Geospatial overview of approved and pending locational clearances.
            </flux:subheading>
        </div>

        <flux:button href="{{ route('admin.geospatial.index') }}" wire:navigate variant="subtle" size="xs"
            icon="arrow-top-right-on-square">
            Full Interactive Map
        </flux:button>
    </div>

    <!-- Leaflet GIS Map Container via Alpine.js -->
    <div x-data="{
            map: null,
            locations: @js($this->mapLocations),
            init() {
                this.$nextTick(() => {
                    // Center on Malalag, Davao del Sur: 6.5925° N, 125.3944° E
                    const defaultCenter = [6.5925, 125.3944];
                    
                    this.map = L.map(this.$refs.mapContainer).setView(defaultCenter, 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    // Add pins for geotagged clearances
                    if (this.locations.length > 0) {
                        const bounds = [];
                        this.locations.forEach(loc => {
                            if (loc.lat && loc.lng) {
                                const marker = L.circleMarker([loc.lat, loc.lng], {
                                    radius: 7,
                                    fillColor: '#3b82f6',
                                    color: '#ffffff',
                                    weight: 2,
                                    opacity: 1,
                                    fillOpacity: 0.85
                                }).addTo(this.map);

                                marker.bindPopup(`
                                    <div style='font-size: 12px; font-family: sans-serif; line-height: 1.4;'>
                                        <strong style='font-size: 13px; color: #1e293b;'>${loc.title}</strong><br>
                                        <span style='color: #64748b;'>Zone: ${loc.zone_type}</span><br>
                                        <p style='margin: 4px 0 0 0; color: #334155;'>${loc.notes || 'Verified location'}</p>
                                    </div>
                                `);

                                bounds.push([loc.lat, loc.lng]);
                            }
                        });

                        if (bounds.length > 1) {
                            this.map.fitBounds(bounds, { padding: [30, 30] });
                        }
                    }
                });
            }
        }"
        class="h-150 w-full rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-inner relative">
        <div x-ref="mapContainer" class="h-full w-full z-0" wire:ignore></div>
    </div>
</flux:card>