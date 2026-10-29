<flux:card class="p-4 sm:p-5 space-y-4">
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-3">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="base" class="font-bold">Municipal GIS & Geotagged Clearances Map</flux:heading>
                <flux:badge size="sm" color="blue" class="text-[10px]">Malalag, Davao del Sur</flux:badge>
            </div>
            <flux:subheading class="text-xs">Geospatial overview of approved and pending locational clearances.
            </flux:subheading>
        </div>

        <flux:button href="{{ route('admin.geospatial.index') }}" wire:navigate variant="subtle" size="xs"
            icon="arrow-top-right-on-square" class="w-full sm:w-auto justify-center">
            Full Interactive Map
        </flux:button>
    </div>

    <!-- Leaflet GIS Map Container via Alpine.js -->
    <div x-data="{
            map: null,
            locations: @js($this->mapLocations),
            
            init() {
                this.$nextTick(() => {
                    const MAPTILER_URL = 'https://api.maptiler.com/maps/hybrid-v4/256/{z}/{x}/{y}.jpg?key=Of5VhE6Hp5sKySUcOlMm';
                    
                    // Center on Malalag, Davao del Sur
                    const defaultCenter = [6.5925, 125.3944];
                    
                    this.map = L.map(this.$refs.mapContainer).setView(defaultCenter, 13);

                    L.tileLayer(MAPTILER_URL, {
                        maxZoom: 19,
                        attribution: '&copy; MapTiler'
                    }).addTo(this.map);

                    // Add pins for geotagged clearances
                    if (this.locations && this.locations.length > 0) {
                        const bounds = [];
                        this.locations.forEach(loc => {
                            if (loc.lat && loc.lng) {
                                // Draw a distinct blue circle marker for each site
                                const marker = L.circleMarker([loc.lat, loc.lng], {
                                    radius: 7,
                                    fillColor: '#3b82f6',
                                    color: '#ffffff',
                                    weight: 2,
                                    opacity: 1,
                                    fillOpacity: 0.85
                                }).addTo(this.map);

                                marker.bindPopup(`
                                    <div style='font-size: 12px; font-family: sans-serif; line-height: 1.4; min-width: 160px;'>
                                        <strong style='font-size: 13px; color: #1e293b;'>${loc.title}</strong><br>
                                        <span style='color: #4f46e5; font-weight: 600;'>${loc.zone_type} Zone</span>
                                        <hr style='margin: 4px 0; border: 0; border-top: 1px solid #e2e8f0;' />
                                        <p style='margin: 0; color: #64748b;'>${loc.notes || 'Verified location'}</p>
                                    </div>
                                `);

                                bounds.push([loc.lat, loc.lng]);
                            }
                        });

                        // Automatically zoom to fit all plotted clearances
                        if (bounds.length > 1) {
                            this.map.fitBounds(bounds, { padding: [40, 40] });
                        }
                    }

                    // Ensure map renders at correct size on load
                    setTimeout(() => {
                        this.map.invalidateSize();
                    }, 300);
                });
            }
        }"
        class="h-[400px] sm:h-[500px] w-full rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 shadow-inner relative">
        <div x-ref="mapContainer" class="h-full w-full z-0" wire:ignore></div>
    </div>
</flux:card>