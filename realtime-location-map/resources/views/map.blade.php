<!DOCTYPE html>
<html>

<head>
    <title>Real-Time Location Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 600px;
            width: 100%;
        }

        .leaflet-marker-icon {
            transition: transform 0.5s ease-out, opacity 0.5s ease-out;
        }

        #clearTrail {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
        }

        .filter-container {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        input[type="datetime-local"] {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

    </style>
</head>

<body>
    <div class="filter-container" style="margin: 10px 0;">
        <input type="datetime-local" id="startTime" placeholder="Start Time">
        <input type="datetime-local" id="endTime" placeholder="End Time">
        <button onclick="applyTimeFilter()">Apply Filter</button>
        <button onclick="clearFilter()">Clear Filter</button>
    </div>
    <div id="map"></div>
    <button id="clearTrail">Clear Trail</button>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Socket.io Client -->
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script>
        // Initialize map (same as before)
        const map = L.map('map').setView([40.712776, -74.005974], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Initialize map and polyline
        let currentMarker = null;
        const polyline = L.polyline([], {
            color: 'blue'
        }).addTo(map);

        // Load historical data on page load
        fetch('/api/locations')
            .then(response => response.json())
            .then(locations => {
                locations.forEach(location => {
                    const pos = [location.latitude, location.longitude];

                    // Add to polyline
                    polyline.addLatLng(pos);

                    // Add initial marker for first point
                    if (!currentMarker) {
                        currentMarker = L.marker(pos).addTo(map);
                        map.setView(pos, 13);
                    }
                });

                polyline.redraw();
            });

        // Connect to Socket.io
        const socket = io('http://localhost:3000');

        socket.on('location-update', (location) => {
            const newPos = [location.latitude, location.longitude];

            // Update or create marker
            if (currentMarker) {
                currentMarker.setLatLng(newPos);
            } else {
                currentMarker = L.marker(newPos).addTo(map);
            }

            // Add position to polyline
            polyline.addLatLng(newPos).redraw();

            // Smoothly pan map to new position
            map.panTo(newPos, {
                animate: true
                , duration: 1
            });
        });

    </script>
    <script>
        document.getElementById('clearTrail').addEventListener('click', () => {
            polyline.setLatLngs([]).redraw();
            if (currentMarker) map.removeLayer(currentMarker);
            currentMarker = null;
        });

    </script>
    <script>
        function applyTimeFilter() {
            const start = document.getElementById('startTime').value;
            const end = document.getElementById('endTime').value;

            if (!start || !end) {
                alert('Please select both start and end times');
                return;
            }

            // Convert to ISO format for proper timezone handling
            const startISO = new Date(start).toISOString();
            const endISO = new Date(end).toISOString();

            fetch(`/api/locations?start=${startISO}&end=${endISO}`)
                .then(response => response.json())
                .then(locations => {
                    // Clear existing visualization
                    polyline.setLatLngs([]);
                    if (currentMarker) map.removeLayer(currentMarker);
                    currentMarker = null;

                    // Redraw with filtered data
                    locations.forEach(location => {
                        const pos = [location.latitude, location.longitude];
                        polyline.addLatLng(pos);

                        if (!currentMarker) {
                            currentMarker = L.marker(pos).addTo(map);
                            map.setView(pos, 13);
                        }
                    });

                    polyline.redraw();
                });
        }

        function clearFilter() {
            document.getElementById('startTime').value = '';
            document.getElementById('endTime').value = '';
            fetch('/api/locations')
                .then(response => response.json())
                .then(locations => {
                    // Same redraw logic as page load
                });
        }

    </script>

</body>

</html>
