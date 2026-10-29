<div class="space-y-5" x-data="{
        lat: @entangle('latitude'),
        lng: @entangle('longitude'),
        map: null,
        marker: null,
        hasPermission: false,
        requestingGps: false,
        statusMessage: '',

        async init() {
            await this.ensureLeafletLoaded();

            // If coordinates already exist (Edit mode with saved coordinates)
            if (this.lat && this.lng) {
                this.hasPermission = true;
                this.statusMessage = 'Saved project location loaded. You can drag the marker or tap the map to adjust.';
                setTimeout(() => {
                    this.mountMap();
                }, 150);
            } else {
                // Otherwise, request GPS from device (Create mode or unmapped)
                this.requestDeviceLocation();
            }
        },

        // 1. Dynamically load Leaflet if not yet in the browser
        ensureLeafletLoaded() {
            return new Promise((resolve) => {
                if (window.L) {
                    resolve();
                    return;
                }

                if (!document.getElementById('leaflet-css')) {
                    const link = document.createElement('link');
                    link.id = 'leaflet-css';
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);
                }

                const script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => {
                    // Fix missing default icon paths from Leaflet CDN
                    delete L.Icon.Default.prototype._getIconUrl;
                    L.Icon.Default.mergeOptions({
                        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    });
                    resolve();
                };
                document.head.appendChild(script);
            });
        },

        // 2. Strict GPS check (No fake fallback)
        requestDeviceLocation() {
            if (!navigator.geolocation) {
                this.hasPermission = false;
                this.statusMessage = 'Geolocation is not supported by your browser.';
                return;
            }

            this.requestingGps = true;
            this.statusMessage = 'Requesting GPS permission from device...';

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.requestingGps = false;
                    this.hasPermission = true;

                    const detectedLat = parseFloat(position.coords.latitude.toFixed(6));
                    const detectedLng = parseFloat(position.coords.longitude.toFixed(6));

                    // Use detected coordinates if not already adjusted
                    this.lat = this.lat ? this.lat : detectedLat;
                    this.lng = this.lng ? this.lng : detectedLng;

                    this.statusMessage = 'GPS location locked! You can drag the marker or tap the map to pinpoint your exact lot.';

                    setTimeout(() => {
                        this.mountMap();
                    }, 150);
                },
                (error) => {
                    this.requestingGps = false;
                    this.hasPermission = false;
                    this.lat = null;
                    this.lng = null;

                    if (error.code === error.PERMISSION_DENIED) {
                        this.statusMessage = 'Location permission was denied. You must allow location access on your browser to proceed.';
                    } else {
                        this.statusMessage = 'Unable to acquire GPS signal. Please turn on your device GPS/Location and try again.';
                    }
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        },

        // 3. Mount Leaflet and add the draggable pin
        mountMap() {
            if (!window.L || !this.$refs.mapBox || !this.lat || !this.lng) return;

            if (this.map) {
                this.map.remove();
                this.map = null;
            }

            this.map = L.map(this.$refs.mapBox, {
                center: [this.lat, this.lng],
                zoom: 17,
                zoomControl: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            // Draggable Pin Marker
            this.marker = L.marker([this.lat, this.lng], {
                draggable: true,
                autoPan: true
            }).addTo(this.map);

            this.marker.bindPopup('<b>Your Project Location</b><br>Drag this pin to adjust.').openPopup();

            // When marker is dragged
            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.lat = parseFloat(pos.lat.toFixed(6));
                this.lng = parseFloat(pos.lng.toFixed(6));
                this.marker.openPopup();
            });

            // When map is tapped/clicked
            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                this.lat = parseFloat(e.latlng.lat.toFixed(6));
                this.lng = parseFloat(e.latlng.lng.toFixed(6));
                this.marker.openPopup();
            });

            // Refresh map dimensions
            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 300);
        }
    }">

    <flux:card class="p-4 sm:p-6 space-y-4">
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <div>
                <flux:heading size="lg" class="text-base sm:text-lg">Pinpoint Site Coordinates</flux:heading>
                <flux:subheading class="text-xs">
                    Real-time GPS lock is required. Once loaded, drag the pin to your exact site boundary.
                </flux:subheading>
            </div>

            <flux:button type="button" @click="requestDeviceLocation()" variant="subtle" size="sm" icon="arrow-path"
                class="self-start sm:self-auto" x-bind:disabled="requestingGps">
                Re-scan GPS
            </flux:button>
        </div>

        <!-- 1. GRANTED: Map & Draggable Marker Container -->
        <div x-show="hasPermission" class="space-y-4" style="display: none;">
            <div
                class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2">
                <flux:icon name="check-circle" class="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                <span x-text="statusMessage"></span>
            </div>

            <!-- Leaflet Container -->
            <div class="relative rounded-xl overflow-hidden border border-zinc-300 dark:border-zinc-700 shadow-sm"
                style="height: 380px;">
                <div x-ref="mapBox" class="w-full h-full z-0" style="min-height: 380px; height: 100%;"></div>

                <!-- Floating Coordinates Badge -->
                <div
                    class="absolute bottom-3 left-3 z-[400] bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xs py-1.5 px-3 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-md text-[11px] font-mono text-zinc-700 dark:text-zinc-300">
                    <span>Lat: <strong x-text="lat"></strong></span>
                    <span class="mx-1.5 text-zinc-400">&bull;</span>
                    <span>Lng: <strong x-text="lng"></strong></span>
                </div>
            </div>

            <!-- Readonly Form Input Display -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <flux:field>
                    <flux:label class="text-xs">Latitude <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="latitude" readonly
                        class="font-mono text-xs bg-zinc-50 dark:bg-zinc-800/50" />
                    <flux:error name="latitude" />
                </flux:field>

                <flux:field>
                    <flux:label class="text-xs">Longitude <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="longitude" readonly
                        class="font-mono text-xs bg-zinc-50 dark:bg-zinc-800/50" />
                    <flux:error name="longitude" />
                </flux:field>
            </div>
        </div>

        <!-- 2. BLOCKED / DENIED: Locked State -->
        <div x-show="!hasPermission"
            class="p-8 sm:p-12 text-center rounded-2xl border-2 border-dashed border-red-300 dark:border-red-900/50 bg-red-50/30 dark:bg-red-950/10 space-y-4">
            <div
                class="size-12 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto">
                <flux:icon name="map-pin" class="size-6" />
            </div>

            <div class="max-w-md mx-auto space-y-1">
                <flux:heading size="base" class="font-bold text-red-700 dark:text-red-400">
                    Location Access Required
                </flux:heading>
                <flux:text class="text-xs text-red-600/90 dark:text-red-400/90 leading-relaxed"
                    x-text="statusMessage || 'You must enable device location permissions to pinpoint the site and submit your clearance application.'">
                </flux:text>
            </div>

            <flux:button type="button" @click="requestDeviceLocation()" variant="primary" size="sm" icon="map-pin"
                class="mx-auto" x-bind:disabled="requestingGps">
                <span x-show="!requestingGps">Allow Device Location</span>
                <span x-show="requestingGps" class="flex items-center gap-1.5" style="display: none;">
                    <flux:icon name="arrow-path" class="size-3.5 animate-spin" />
                    <span>Locating...</span>
                </span>
            </flux:button>
        </div>
    </flux:card>

    <!-- Bottom Navigation: Submit button remains locked until permission is given and lat/lng are set -->
    <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-2">
        <flux:button wire:click="$set('step', 2)" variant="subtle" icon="arrow-left" class="w-full sm:w-auto">
            Back to Requirements
        </flux:button>

        <flux:button wire:click="submit" variant="primary" icon="check" class="w-full sm:w-auto justify-center"
            x-bind:disabled="!hasPermission || !lat || !lng" wire:loading.attr="disabled">
            Submit Application
        </flux:button>
    </div>
</div>