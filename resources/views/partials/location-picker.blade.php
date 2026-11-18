@php
    // Variables attendues : $lat, $lng (peuvent être null)
    $hasPoint = $lat !== null && $lng !== null;
@endphp

<div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
    <p class="text-sm font-medium text-gray-700">📍 Localisation de l'entreprise</p>
    <p id="geo-status" class="mt-1 text-xs text-gray-500">
        {{ $hasPoint ? 'Position enregistrée. Vous pouvez la corriger sur la carte.' : 'Cliquez sur le bouton, ou directement sur la carte.' }}
    </p>

    <div class="mt-2 flex flex-wrap items-center gap-2">
        <button type="button" id="geo-btn" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm hover:bg-gray-50">
            Utiliser ma position
        </button>
        <a id="geo-link" href="#" target="_blank" rel="noopener" class="hidden text-sm text-emerald-700 underline">Vérifier dans Google Maps</a>
    </div>

    <div id="geo-map" class="mt-3 h-64 w-full rounded"
         data-lat="{{ $hasPoint ? $lat : '' }}" data-lng="{{ $hasPoint ? $lng : '' }}"></div>

    <input type="hidden" id="base_lat" name="base_lat" value="{{ $hasPoint ? $lat : '' }}">
    <input type="hidden" id="base_lng" name="base_lng" value="{{ $hasPoint ? $lng : '' }}">
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    (function () {
        var el = document.getElementById('geo-map');
        var latInput = document.getElementById('base_lat');
        var lngInput = document.getElementById('base_lng');
        var status = document.getElementById('geo-status');
        var link = document.getElementById('geo-link');
        var btn = document.getElementById('geo-btn');

        var startLat = parseFloat(el.dataset.lat);
        var startLng = parseFloat(el.dataset.lng);
        var hasStart = !isNaN(startLat) && !isNaN(startLng);

        // Dakar par défaut
        var map = L.map('geo-map').setView(hasStart ? [startLat, startLng] : [14.6937, -17.4441], hasStart ? 16 : 11);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        var marker = null;

        function setPoint(lat, lng, zoom) {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
            link.href = 'https://www.google.com/maps?q=' + lat + ',' + lng;
            link.classList.remove('hidden');

            if (!marker) {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function () {
                    var p = marker.getLatLng();
                    setPoint(p.lat, p.lng);
                    status.textContent = 'Position ajustée à la main.';
                });
            } else {
                marker.setLatLng([lat, lng]);
            }
            if (zoom) { map.setView([lat, lng], zoom); }
        }

        if (hasStart) { setPoint(startLat, startLng); }

        // Clic sur la carte : place le marqueur
        map.on('click', function (e) {
            setPoint(e.latlng.lat, e.latlng.lng);
            status.textContent = 'Position choisie sur la carte.';
        });

        btn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                status.textContent = "Géolocalisation indisponible : cliquez sur la carte pour placer votre entreprise.";
                return;
            }
            status.textContent = 'Localisation en cours...';
            btn.disabled = true;

            navigator.geolocation.getCurrentPosition(function (p) {
                btn.disabled = false;
                setPoint(p.coords.latitude, p.coords.longitude, 17);
                var acc = Math.round(p.coords.accuracy);
                status.textContent = 'Position trouvée (précision d\'environ ' + acc + ' m).'
                    + (acc > 500 ? ' Précision faible : déplacez le marqueur sur votre entreprise.' : ' Déplacez le marqueur si besoin.');
            }, function () {
                btn.disabled = false;
                status.textContent = "Position non obtenue (autorisation refusée ?). Cliquez sur la carte pour placer votre entreprise.";
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        });
    })();
</script>