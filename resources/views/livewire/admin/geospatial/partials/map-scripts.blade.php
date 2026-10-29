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

    // 1. Main Interactive Map Driver
    Alpine.data('mainZoneMap', (initialLocations) => ({
        map: null,
        markersGroup: null,
        locations: initialLocations,
        searchQuery: '',

        init() {
            this.$nextTick(() => {
                this.initMap();
            });

            Livewire.on('refresh-map', ([data]) => {
                if (data && data.locations) {
                    this.locations = data.locations;
                    this.renderMarkers(this.locations);
                }
            });
        },

        initMap() {
            const defaultLat = this.locations.length > 0 ? this.locations[0].latitude : DEFAULT_MAP_CENTER[0];
            const defaultLng = this.locations.length > 0 ? this.locations[0].longitude : DEFAULT_MAP_CENTER[1];

            this.map = L.map(this.$refs.mapContainer).setView([defaultLat, defaultLng], 14);

            L.tileLayer(MAPTILER_URL, {
                maxZoom: 19,
                attribution: '&copy; MapTiler &copy; OpenStreetMap'
            }).addTo(this.map);

            this.markersGroup = L.layerGroup().addTo(this.map);
            this.renderMarkers(this.locations);

            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 300);
        },

        renderMarkers(locs) {
            if (!this.markersGroup) return;
            this.markersGroup.clearLayers();

            locs.forEach(loc => {
                const marker = L.marker([loc.latitude, loc.longitude]).addTo(this.markersGroup);
                const popupContent = `
                    <div class="p-1 min-w-[190px]">
                        <h4 class="font-bold text-sm text-zinc-900">${loc.title}</h4>
                        <div class="text-[11px] font-semibold text-indigo-600 mb-1">${loc.zone_type} Zone</div>
                        <hr class="my-1 border-zinc-200" />
                        <p class="text-xs text-zinc-600 mb-0.5"><strong>Ref:</strong> ${loc.application_id || 'Direct Plot'}</p>
                        <p class="text-xs text-zinc-500">${loc.notes || 'No extra notes provided.'}</p>
                    </div>
                `;
                marker.bindPopup(popupContent);
            });
        },

        zoomTo(lat, lng) {
            if (this.map) {
                this.map.setView([lat, lng], 17, { animate: true });
            }
        },

        resetView() {
            if (this.map) {
                this.map.setView(DEFAULT_MAP_CENTER, 14, { animate: true });
            }
        },

        get filteredLocations() {
            if (!this.searchQuery || !this.searchQuery.trim()) return this.locations;
            const query = this.searchQuery.toLowerCase();
            return this.locations.filter(loc => 
                (loc.title && loc.title.toLowerCase().includes(query)) ||
                (loc.zone_type && loc.zone_type.toLowerCase().includes(query)) ||
                (loc.application_id && loc.application_id.toLowerCase().includes(query))
            );
        },

        getBadgeClasses(zoneType) {
            switch(zoneType) {
                case 'Commercial': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300';
                case 'Residential': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300';
                case 'Industrial': return 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300';
                case 'Agricultural': return 'bg-lime-100 text-lime-700 dark:bg-lime-950 dark:text-lime-300';
                default: return 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300';
            }
        }
    }));

    // 2. Modal Mini-Picker Map Driver with Livewire Coordinate Sync
    Alpine.data('pickerZoneMap', () => ({
        map: null,
        marker: null,

        init() {
            this.$watch('$wire.latitude', (val) => {
                if (val && this.$wire.longitude) {
                    const lat = parseFloat(val);
                    const lng = parseFloat(this.$wire.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        this.updateMarkerPosition(lat, lng);
                    }
                }
            });
        },

        initOrResize() {
            this.$nextTick(() => {
                setTimeout(() => {
                    const rawLat = parseFloat(this.$wire.latitude);
                    const rawLng = parseFloat(this.$wire.longitude);
                    const hasCoords = !isNaN(rawLat) && !isNaN(rawLng);

                    const centerLat = hasCoords ? rawLat : DEFAULT_MAP_CENTER[0];
                    const centerLng = hasCoords ? rawLng : DEFAULT_MAP_CENTER[1];

                    if (!this.map) {
                        this.map = L.map(this.$refs.pickerContainer).setView([centerLat, centerLng], hasCoords ? 16 : 14);

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
                    } else {
                        this.map.invalidateSize();
                        if (hasCoords) {
                            this.updateMarkerPosition(centerLat, centerLng);
                        }
                    }
                }, 250);
            });
        },

        updateMarkerPosition(lat, lng) {
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