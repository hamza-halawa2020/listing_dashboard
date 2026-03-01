<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: $wire.entangle('{{ $getStatePath() }}'),
            map: null,
            marker: null,
            searchQuery: '',
            searching: false,
            init() {
                // Initialize Leaflet map
                this.map = L.map(this.$refs.map).setView([{{ $getDefaultLatitude() }}, {{ $getDefaultLongitude() }}], {{ $getDefaultZoom() }});
                
                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(this.map);

                // Check if we have latitude/longitude from the form data
                this.$nextTick(() => {
                    const lat = @this.get('data.latitude');
                    const lng = @this.get('data.longitude');
                    
                    if (lat && lng) {
                        console.log('Loading existing coordinates:', lat, lng);
                        this.setMarker(lat, lng);
                        this.map.setView([lat, lng], 15);
                    } else {
                        // Parse existing coordinates from state if available
                        if (this.state) {
                            try {
                                const coords = JSON.parse(this.state);
                                if (coords.latitude && coords.longitude) {
                                    this.setMarker(coords.latitude, coords.longitude);
                                    this.map.setView([coords.latitude, coords.longitude], 13);
                                }
                            } catch (e) {
                                console.log('No existing coordinates');
                            }
                        }
                    }
                });

                // Add click event to map
                this.map.on('click', (e) => {
                    this.setMarker(e.latlng.lat, e.latlng.lng);
                });
            },
            getUserLocation() {
                if (!navigator.geolocation) {
                    alert('⚠️ Geolocation is not supported by your browser');
                    return;
                }

                // Show loading state
                const btn = event?.target;
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '⏳ Getting location...';
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        console.log('Got location:', lat, lng);
                        
                        // Center map on user location
                        this.map.setView([lat, lng], 15);
                        
                        // Optionally set marker at user location
                        this.setMarker(lat, lng);
                        
                        // Reset button
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '📍 My Location';
                        }
                    },
                    (error) => {
                        console.error('Geolocation error:', error);
                        let message = '';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                message = '⚠️ Location permission denied. Please enable location access in your browser settings.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                message = '⚠️ Location information is unavailable.';
                                break;
                            case error.TIMEOUT:
                                message = '⚠️ Location request timed out.';
                                break;
                            default:
                                message = '⚠️ An unknown error occurred while getting location.';
                        }
                        alert(message);
                        
                        // Reset button
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '📍 My Location';
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            },
            async searchAddress() {
                if (!this.searchQuery.trim()) return;
                
                this.searching = true;
                try {
                    // Using Nominatim (OpenStreetMap) geocoding API
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}&limit=1`
                    );
                    const data = await response.json();
                    
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        this.map.setView([lat, lng], 15);
                        this.setMarker(lat, lng);
                    } else {
                        alert('Location not found. Try a different search term.');
                    }
                } catch (error) {
                    console.error('Search error:', error);
                    alert('Error searching for location.');
                } finally {
                    this.searching = false;
                }
            },
            setMarker(lat, lng) {
                // Remove existing marker
                if (this.marker) {
                    this.map.removeLayer(this.marker);
                }

                // Add new marker
                this.marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(this.map);

                // Update state
                this.updateCoordinates(lat, lng);

                // Handle marker drag
                this.marker.on('dragend', (e) => {
                    const position = e.target.getLatLng();
                    this.updateCoordinates(position.lat, position.lng);
                });
            },
            updateCoordinates(lat, lng) {
                this.state = JSON.stringify({
                    latitude: parseFloat(lat.toFixed(8)),
                    longitude: parseFloat(lng.toFixed(8))
                });
                
                // Update individual fields if they exist
                if (window.Livewire) {
                    @this.set('data.latitude', parseFloat(lat.toFixed(8)));
                    @this.set('data.longitude', parseFloat(lng.toFixed(8)));
                }
            }
        }"
        wire:ignore
    >
        <!-- Search Box -->
        <div class="mb-3 flex gap-2">
            <input 
                type="text" 
                x-model="searchQuery"
                @keydown.enter.prevent="searchAddress()"
                placeholder="Search for address... (e.g., Cairo, Egypt)"
                class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
            />
            <button 
                type="button"
                @click="searchAddress()"
                :disabled="searching"
                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span x-show="!searching">🔍 Search</span>
                <span x-show="searching">Searching...</span>
            </button>
            <button 
                type="button"
                @click="getUserLocation()"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                title="Get my current location"
            >
                📍 My Location
            </button>
        </div>

        <div 
            x-ref="map" 
            style="height: 400px; width: 100%; border-radius: 0.5rem; border: 1px solid #e5e7eb;"
            class="mb-2"
        ></div>
        
        <div class="text-sm text-gray-600 dark:text-gray-400">
            <p>🗺️ Click on the map or drag the marker to select location</p>
            <p>📍 Use "My Location" button to center map on your current location</p>
            <p>🔍 Search for any address to quickly find a location</p>
            <p x-show="state" class="mt-2 font-semibold" x-text="'Selected: ' + (state ? JSON.parse(state).latitude : '') + ', ' + (state ? JSON.parse(state).longitude : '')"></p>
        </div>
    </div>

    @once
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
                crossorigin=""/>
        @endpush

        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                crossorigin=""></script>
        @endpush
    @endonce
</x-dynamic-component>
