<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<div id="sos-map" class="mt-4 h-64 w-full rounded"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    (function () {
        var map = L.map('sos-map').setView([{{ $lat }}, {{ $lng }}], 15);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        L.marker([{{ $lat }}, {{ $lng }}]).addTo(map);
    })();
</script>