<x-filament-widgets::widget>
    <x-filament::section heading="Peta Area Parkir">

        {{-- Legenda --}}
        <div class="flex flex-wrap gap-4 mb-4 text-xs font-medium">
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-gray-600 dark:text-gray-400">Tersedia (&lt;80%)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-full bg-yellow-400"></span>
                <span class="text-gray-600 dark:text-gray-400">Hampir Penuh (80–99%)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-gray-600 dark:text-gray-400">Penuh (100%)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-full bg-gray-400"></span>
                <span class="text-gray-600 dark:text-gray-400">Tidak ada data</span>
            </div>
        </div>

        {{-- Map container --}}
        <div
            id="area-parkir-map"
            style="height: 480px; width: 100%; border-radius: 10px; z-index: 0; overflow: hidden;"
        ></div>

    </x-filament::section>

    {{-- Leaflet CSS & JS --}}
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"
    />
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"
    />

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLs="
        crossorigin=""
    ></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
    (function () {
        const areas = @json($areas);

        // Inisialisasi map
        const map = L.map('area-parkir-map', {
            zoomControl: true,
            scrollWheelZoom: true,
        });

        // Layer tile — OpenStreetMap
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        });

        // Layer tile — Satelit (Esri)
        const satelliteLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                attribution: 'Tiles © Esri',
                maxZoom: 19,
            }
        );

        osmLayer.addTo(map);

        // Layer control (toggle peta/satelit)
        L.control.layers(
            { 'Peta': osmLayer, 'Satelit': satelliteLayer },
            {},
            { position: 'topright' }
        ).addTo(map);

        // Skala
        L.control.scale({ imperial: false }).addTo(map);

        // Fungsi warna marker berdasarkan okupansi
        function markerColor(rate) {
            if (rate >= 100) return '#ef4444';  // merah
            if (rate >= 80)  return '#facc15';  // kuning
            return '#22c55e';                   // hijau
        }

        // Custom circle marker
        function makeIcon(rate) {
            const color = markerColor(rate);
            const svg = `
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="42" viewBox="0 0 36 42">
                    <path d="M18 0C8.06 0 0 8.06 0 18c0 13.5 18 24 18 24S36 31.5 36 18C36 8.06 27.94 0 18 0z"
                          fill="${color}" stroke="white" stroke-width="2"/>
                    <circle cx="18" cy="18" r="9" fill="white" opacity="0.9"/>
                    <text x="18" y="22" text-anchor="middle" font-size="9"
                          font-family="monospace" font-weight="bold" fill="${color}">
                        ${rate}%
                    </text>
                </svg>`;
            return L.divIcon({
                html: svg,
                className: '',
                iconSize: [36, 42],
                iconAnchor: [18, 42],
                popupAnchor: [0, -42],
            });
        }

        // Cluster group
        const markers = L.markerClusterGroup({
            maxClusterRadius: 60,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: true,
        });

        const bounds = [];

        areas.forEach(area => {
            const icon   = makeIcon(area.rate);
            const marker = L.marker([area.lat, area.lng], { icon });

            // Bar progress okupansi
            const barColor = markerColor(area.rate);
            const popup = `
                <div style="font-family:sans-serif;min-width:200px;padding:4px">
                    <div style="font-weight:700;font-size:14px;margin-bottom:6px;color:#111">
                        📍 ${area.nama}
                    </div>
                    <div style="font-size:12px;color:#555;margin-bottom:8px">${area.alamat}</div>
                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                        <span>Kapasitas</span><b>${area.kapasitas} slot</b>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                        <span>Terisi</span><b>${area.terisi} slot</b>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px">
                        <span>Tersisa</span><b style="color:${barColor}">${area.sisa} slot</b>
                    </div>
                    <div style="background:#e5e7eb;border-radius:999px;height:8px;overflow:hidden">
                        <div style="
                            width:${area.rate}%;
                            height:100%;
                            background:${barColor};
                            border-radius:999px;
                            transition:width 0.4s
                        "></div>
                    </div>
                    <div style="text-align:right;font-size:11px;margin-top:3px;color:#666">
                        ${area.rate}% terisi
                    </div>
                </div>`;

            marker.bindPopup(popup, { maxWidth: 260 });

            // Tooltip hover
            marker.bindTooltip(area.nama, {
                permanent: false,
                direction: 'top',
                offset: [0, -42],
            });

            markers.addLayer(marker);
            bounds.push([area.lat, area.lng]);
        });

        map.addLayer(markers);

        // Fit ke semua marker, atau default ke Indonesia
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40] });
        } else {
            map.setView([-2.5489, 118.0149], 5); // center Indonesia
        }

        // Tombol "Tampilkan Semua" custom control
        const fitControl = L.Control.extend({
            options: { position: 'topleft' },
            onAdd: function () {
                const btn = L.DomUtil.create('button', '');
                btn.innerHTML = '⊞ Tampilkan Semua';
                btn.style.cssText = `
                    background:white;
                    border:2px solid rgba(0,0,0,0.2);
                    border-radius:4px;
                    padding:5px 8px;
                    font-size:12px;
                    cursor:pointer;
                    font-family:sans-serif;
                `;
                L.DomEvent.on(btn, 'click', function () {
                    if (bounds.length > 0) {
                        map.fitBounds(bounds, { padding: [40, 40] });
                    }
                });
                return btn;
            }
        });
        map.addControl(new fitControl());

    })();
    </script>

</x-filament-widgets::widget>
