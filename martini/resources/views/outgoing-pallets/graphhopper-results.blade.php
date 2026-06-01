<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Route Planning</title>
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >
    <link
        rel="stylesheet"
        href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css"
    >
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f8fb;
            --surface: #ffffff;
            --border: #d9e1ec;
            --text: #0f172a;
            --muted: #475569;
            --primary: #0f766e;
            --primary-dark: #115e59;
            --danger: #b91c1c;
            --success: #166534;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top right, #e0f2fe 0%, var(--bg) 45%);
            color: var(--text);
        }

        .container {
            max-width: min(1920px, calc(100vw - 32px));
            margin: 0 auto;
            padding: 16px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
            margin-bottom: 16px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--muted);
        }

        input, select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.95rem;
            color: var(--text);
            background: #fff;
        }

        .actions {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        button {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
            background: var(--primary);
            color: #fff;
        }

        button:hover { background: var(--primary-dark); }
        button:disabled { opacity: 0.65; cursor: wait; }

        .status {
            margin: 0;
            font-weight: 600;
            color: var(--muted);
        }

        .status.error { color: var(--danger); }
        .status.success { color: var(--success); }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
        }

        .metric {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
        }

        .metric .label {
            margin: 0 0 4px 0;
            color: var(--muted);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .metric .value {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .section-title {
            margin: 0 0 8px 0;
            font-size: 1rem;
        }

        .list {
            margin: 0;
            padding-left: 18px;
        }

        pre {
            margin: 0;
            padding: 14px;
            border-radius: 10px;
            background: #0f172a;
            color: #e2e8f0;
            overflow-x: auto;
            font-size: 0.85rem;
            line-height: 1.45;
        }

        #routeMap {
            width: 100%;
            height: 650px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .results-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 10px;
            align-items: start;
        }

        .overview-panel {
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #f8fafc;
            padding: 10px;
            max-height: 680px;
            overflow: auto;
        }

        .overview-title {
            margin: 0 0 10px 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text);
        }

        .map-panel {
            min-width: 0;
        }

        .map-note {
            margin: 8px 0 0 0;
            color: var(--muted);
            font-size: 0.85rem;
        }

        .route-legend {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .route-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 0.82rem;
            color: var(--text);
            font-weight: 600;
        }

        .route-legend-swatch {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
            border: 1px solid rgba(0, 0, 0, 0.18);
        }

        .route-breakdown {
            margin-top: 0;
            display: grid;
            gap: 10px;
        }

        .route-breakdown-card {
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #f8fafc;
            padding: 12px;
        }

        .route-breakdown-head {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 8px;
        }

        .route-breakdown-vehicle {
            min-width: 220px;
            max-width: 320px;
            margin-left: auto;
        }

        .route-breakdown-vehicle-controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .route-breakdown-vehicle select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 0.82rem;
            color: var(--text);
            background: #fff;
        }

        .route-breakdown-commit {
            white-space: nowrap;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
        }

        .route-breakdown-commit:disabled {
            cursor: not-allowed;
        }

        .route-breakdown-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            flex: 0 0 auto;
        }

        .route-breakdown-metrics {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            flex: 1 1 280px;
        }

        .route-breakdown-badge {
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 999px;
            padding: 3px 9px;
            font-size: 0.78rem;
            color: var(--muted);
            font-weight: 600;
        }

        .route-breakdown-stops {
            margin: 0;
            padding-left: 18px;
            display: grid;
            gap: 3px;
            color: var(--text);
            font-size: 0.87rem;
        }

        .route-breakdown-empty {
            margin: 0;
            font-size: 0.9rem;
            color: var(--muted);
        }

        .hidden { display: none; }

        @media (max-width: 980px) {
            .form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .results-layout { grid-template-columns: 1fr; }
            .overview-panel { max-height: none; }
        }

        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Automated Route Plan</h1>
        <a href="{{ route('outgoing-pallets-loading.view') }}">Back to Loading</a>
    </div>

    <div class="card">
        <form id="plannerForm">
            <div class="form-grid">
                <div class="field">
                    <label for="dueDate">Due Date</label>
                    <input type="date" id="dueDate" name="dueDate" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="field">
                    <label for="depot">Depot</label>
                    <select id="depot" name="depot" required>
                        <option value="">Loading depots...</option>
                    </select>
                </div>
                <div class="field">
                    <label for="serviceDurationSeconds">Service Duration (seconds)</label>
                    <input type="number" id="serviceDurationSeconds" name="serviceDurationSeconds" min="60" value="1800" required>
                </div>
                <div class="field">
                    <label for="dryRun">Mode</label>
                    <select id="dryRun" name="dryRun">
                        <option value="false" selected>Persist Suggestions</option>
                        <option value="true" selected>Dry Run (No Persist)</option>
                    </select>
                </div>
            </div>

            <div class="actions">
                <button type="submit" id="runBtn">Run Planner</button>
                <p id="status" class="status">Choose parameters and run the planner.</p>
            </div>
        </form>
    </div>

    <div id="results" class="hidden">
        <div class="card">
            <div class="results-layout">
                <aside class="overview-panel" aria-label="Route overview panel">
                    <h2 class="overview-title">Route Overview</h2>
                    <div id="routeBreakdown" class="route-breakdown" aria-label="Route breakdown"></div>
                </aside>
                <section class="map-panel" aria-label="Route map panel">
                    <div id="routeMap"></div>
                    <div id="routeLegend" class="route-legend" aria-label="Route legend"></div>
                    <p id="mapNote" class="map-note hidden" aria-live="polite"></p>
                </section>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<script src="https://unpkg.com/@maplibre/maplibre-gl-leaflet@0.1.3/leaflet-maplibre-gl.js"></script>
<script src="https://unpkg.com/gh-routing-api/dist/gh-routing-api.min.js"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const graphHopperApiKey = @json(config('services.graphhopper.key', ''));
    const form = document.getElementById('plannerForm');
    const depotSelect = document.getElementById('depot');
    const runBtn = document.getElementById('runBtn');
    const statusEl = document.getElementById('status');
    const resultsEl = document.getElementById('results');
    const mapNoteEl = document.getElementById('mapNote');
    const routeLegendEl = document.getElementById('routeLegend');
    const routeBreakdownEl = document.getElementById('routeBreakdown');

    let routeMap = null;
    let routeLayer = null;
    let markerLayer = null;
    let graphHopperRateLimitedUntil = 0;
    let availableVehicles = [];
    let latestPlanPayload = null;
    const selectedVehicleByRoute = new Map();
    const committingRouteKeys = new Set();

    function ensureMap() {
        if (routeMap) {
            return;
        }

        routeMap = L.map('routeMap', {
            center: [52.577817, -2.107758],
            zoom: 8,
        });

        L.maplibreGL({
            style: 'https://tiles.openfreemap.org/styles/bright',
        }).addTo(routeMap);

        routeLayer = L.layerGroup().addTo(routeMap);
        markerLayer = L.layerGroup().addTo(routeMap);
    }

    function clearMapLayers() {
        ensureMap();
        routeLayer.clearLayers();
        markerLayer.clearLayers();
        routeLegendEl.innerHTML = '';
    }

    function resetRouteDisplayForNewPlan() {
        resultsEl.classList.remove('hidden');
        clearMapLayers();
        routeBreakdownEl.innerHTML = '';
        mapNoteEl.textContent = '';
        mapNoteEl.classList.add('hidden');
    }

    function routeColor(index) {
        const colors = [
            '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#f032e6',
            '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000', '#aaffc3',
            '#808000', '#ffd8b1', '#000075', '#808080', '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728',
            '#9467bd', '#8c564b', '#e377c2', '#17becf', '#393b79', '#637939', '#8c6d31', '#843c39'
        ];

        if (index < colors.length) {
            return colors[index];
        }

        const hue = Math.round((index * 137.508) % 360);
        return `hsl(${hue}, 82%, 46%)`;
    }

    function isGraphHopperRateLimitedError(error) {
        const message = String(error?.message || error || '').toLowerCase();
        return message.includes('429')
            || message.includes('rate limit')
            || message.includes('api limit')
            || message.includes('minutely api limit');
    }

    function osrmProfileFromRoutingProfile(profile) {
        const value = String(profile || '').toLowerCase();
        if (value === 'bike') {
            return 'cycling';
        }

        if (value === 'foot' || value === 'hike') {
            return 'foot';
        }

        return 'driving';
    }

    function getRoutesFromPayload(payload) {
        const responseRoutes = payload?.response?.solution?.routes ?? payload?.response?.routes ?? [];
        const requestServices = payload?.request?.services ?? [];
        const requestVehicles = payload?.request?.vehicles ?? [];

        const serviceById = new Map();
        requestServices.forEach(service => {
            if (service?.id && service?.address?.lat != null && service?.address?.lon != null) {
                serviceById.set(String(service.id), {
                    id: String(service.id),
                    name: String(service.name ?? service.id),
                    lat: Number(service.address.lat),
                    lon: Number(service.address.lon),
                });
            }
        });

        const vehicleById = new Map();
        requestVehicles.forEach(vehicle => {
            if (vehicle?.vehicle_id) {
                vehicleById.set(String(vehicle.vehicle_id), vehicle);
            }
        });

        const results = [];
        responseRoutes.forEach(route => {
            const vehicleId = String(route?.vehicle_id ?? route?.vehicleId ?? '');
            if (!vehicleId) {
                return;
            }

            const requestVehicle = vehicleById.get(vehicleId);
            if (!requestVehicle || !requestVehicle.start_address || !requestVehicle.end_address) {
                return;
            }

            const points = [];
            points.push({
                kind: 'start',
                id: String(requestVehicle.start_address.location_id ?? vehicleId + '-start'),
                lat: Number(requestVehicle.start_address.lat),
                lon: Number(requestVehicle.start_address.lon),
            });

            const activities = Array.isArray(route?.activities) ? route.activities : [];
            activities.forEach(activity => {
                const type = String(activity?.type ?? '').toLowerCase();
                if (type !== 'service' && type !== 'pickup' && type !== 'delivery') {
                    return;
                }

                const serviceId = String(activity?.id ?? activity?.service_id ?? activity?.address?.location_id ?? '');
                if (!serviceId || !serviceById.has(serviceId)) {
                    return;
                }

                const service = serviceById.get(serviceId);
                points.push({
                    kind: 'service',
                    id: service.id,
                    name: service.name,
                    lat: service.lat,
                    lon: service.lon,
                });
            });

            points.push({
                kind: 'end',
                id: String(requestVehicle.end_address.location_id ?? vehicleId + '-end'),
                lat: Number(requestVehicle.end_address.lat),
                lon: Number(requestVehicle.end_address.lon),
            });

            const validPoints = points.filter(point =>
                Number.isFinite(point.lat)
                && Number.isFinite(point.lon)
                && point.lat >= -90
                && point.lat <= 90
                && point.lon >= -180
                && point.lon <= 180
            );

            if (validPoints.length >= 2) {
                results.push({
                    vehicleId,
                    points: validPoints,
                });
            }
        });

        return results;
    }

    function renderRouteLegend(routes) {
        routeLegendEl.innerHTML = '';

        routes.forEach((route, index) => {
            const color = routeColor(index);
            const item = document.createElement('div');
            item.className = 'route-legend-item';
            item.innerHTML = `<span class="route-legend-swatch" style="background:${color}"></span><span>${route.vehicleId}</span>`;
            routeLegendEl.appendChild(item);
        });
    }

    function formatSeconds(seconds) {
        const total = Number(seconds || 0);
        if (!Number.isFinite(total) || total <= 0) {
            return 'n/a';
        }

        const whole = Math.round(total);
        const hrs = Math.floor(whole / 3600);
        const mins = Math.floor((whole % 3600) / 60);
        if (hrs > 0) {
            return `${hrs}h ${mins}m`;
        }

        return `${mins}m`;
    }

    function formatMeters(meters) {
        const value = Number(meters || 0);
        if (!Number.isFinite(value) || value <= 0) {
            return 'n/a';
        }

        if (value >= 1000) {
            return `${(value / 1000).toFixed(1)} km`;
        }

        return `${Math.round(value)} m`;
    }

    function formatUnixTimestamp(ts) {
        const value = Number(ts || 0);
        if (!Number.isFinite(value) || value <= 0) {
            return '';
        }

        const dt = new Date(value * 1000);
        if (Number.isNaN(dt.getTime())) {
            return '';
        }

        return dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function parseRoutePalletId(activity) {
        const serviceId = String(activity?.id ?? activity?.service_id ?? activity?.address?.location_id ?? '').trim();
        if (!serviceId) {
            return null;
        }

        const prefixed = serviceId.match(/^pallet-(\d+)$/i);
        if (prefixed) {
            return Number(prefixed[1]);
        }

        if (/^\d+$/.test(serviceId)) {
            return Number(serviceId);
        }

        return null;
    }

    function extractRoutePalletIds(stops) {
        const ids = [];
        const seen = new Set();

        stops.forEach(activity => {
            const palletId = parseRoutePalletId(activity);
            if (!Number.isFinite(palletId) || palletId <= 0) {
                return;
            }

            if (!seen.has(palletId)) {
                seen.add(palletId);
                ids.push(palletId);
            }
        });

        return ids;
    }

    function renderRouteBreakdown(payload) {
        routeBreakdownEl.innerHTML = '';

        const responseRoutes = payload?.response?.solution?.routes ?? payload?.response?.routes ?? [];
        const requestServices = payload?.request?.services ?? [];

        if (!Array.isArray(responseRoutes) || !responseRoutes.length) {
            routeBreakdownEl.innerHTML = '<p class="route-breakdown-empty">No routes available for breakdown.</p>';
            return;
        }

        const serviceMeta = new Map();
        const serviceWeightByServiceId = new Map();
        requestServices.forEach(service => {
            const id = String(service?.id ?? '');
            if (!id) {
                return;
            }

            const weightKg = Number(service?.size?.[1] ?? 0);

            if (Number.isFinite(weightKg)) {
                serviceWeightByServiceId.set(id, weightKg);
            }

            serviceMeta.set(id, {
                name: String(service?.name ?? id),
                group: String(service?.group ?? '').trim(),
                weightKg: Number.isFinite(weightKg) ? weightKg : 0,
            });
        });

        function computeRouteTravelMetrics(route) {
            const fallbackDistanceMeters = Number(route?.distance ?? 0);
            const fallbackDriveSeconds = Number(route?.transport_time ?? 0);
            const activities = Array.isArray(route?.activities) ? route.activities : [];

            const hasStart = activities.some(activity => String(activity?.type ?? '').toLowerCase() === 'start');
            const hasEnd = activities.some(activity => String(activity?.type ?? '').toLowerCase() === 'end');
            const hasTerminalActivities = hasStart && hasEnd;

            if (activities.length < 2 || !hasTerminalActivities) {
                return {
                    distanceMeters: Number.isFinite(fallbackDistanceMeters) ? fallbackDistanceMeters : 0,
                    driveSeconds: Number.isFinite(fallbackDriveSeconds) ? fallbackDriveSeconds : 0,
                };
            }

            let driveSeconds = 0;
            let distanceMeters = 0;
            let usedTimeDeltas = false;
            let usedDistanceDeltas = false;

            for (let i = 0; i < activities.length - 1; i++) {
                const current = activities[i] ?? {};
                const next = activities[i + 1] ?? {};

                const currentEnd = Number(current?.end_time ?? current?.arr_time);
                const nextArrival = Number(next?.arr_time ?? next?.end_time);
                if (Number.isFinite(currentEnd) && Number.isFinite(nextArrival)) {
                    const deltaSeconds = nextArrival - currentEnd;
                    if (deltaSeconds >= 0) {
                        driveSeconds += deltaSeconds;
                        usedTimeDeltas = true;
                    }
                }

                const currentDistance = Number(current?.distance);
                const nextDistance = Number(next?.distance);
                if (Number.isFinite(currentDistance) && Number.isFinite(nextDistance)) {
                    const deltaMeters = nextDistance - currentDistance;
                    if (deltaMeters >= 0) {
                        distanceMeters += deltaMeters;
                        usedDistanceDeltas = true;
                    }
                }
            }

            if (!usedTimeDeltas) {
                driveSeconds = Number.isFinite(fallbackDriveSeconds) ? fallbackDriveSeconds : 0;
            }

            if (!usedDistanceDeltas) {
                distanceMeters = Number.isFinite(fallbackDistanceMeters) ? fallbackDistanceMeters : 0;
            }

            return { distanceMeters, driveSeconds };
        }

        responseRoutes.forEach((route, index) => {
            const vehicleId = String(route?.vehicle_id ?? `Vehicle ${index + 1}`);
            const routeKey = `${index}:${vehicleId}`;
            const color = routeColor(index);
            const activities = Array.isArray(route?.activities) ? route.activities : [];
            const stops = activities.filter(activity => {
                const type = String(activity?.type ?? '').toLowerCase();
                return type === 'service' || type === 'pickup' || type === 'delivery';
            });
            const travelMetrics = computeRouteTravelMetrics(route);
            const routePalletIds = extractRoutePalletIds(stops);

            const requiredPalletCount = stops.length;
            const requiredPayloadKg = stops.reduce((sum, activity) => {
                const serviceId = String(activity?.id ?? activity?.service_id ?? activity?.address?.location_id ?? '');
                const meta = serviceMeta.get(serviceId);

                if (meta && Number.isFinite(meta.weightKg)) {
                    return sum + meta.weightKg;
                }

                if (serviceWeightByServiceId.has(serviceId)) {
                    return sum + Number(serviceWeightByServiceId.get(serviceId));
                }

                return sum;
            }, 0);

            const card = document.createElement('article');
            card.className = 'route-breakdown-card';

            const head = document.createElement('div');
            head.className = 'route-breakdown-head';

            const title = document.createElement('div');
            title.className = 'route-breakdown-title';
            title.innerHTML = `<span class="route-legend-swatch" style="background:${color}"></span><span>${vehicleId}</span>`;

            const vehicleControl = document.createElement('div');
            vehicleControl.className = 'route-breakdown-vehicle';

            const selector = document.createElement('select');
            selector.setAttribute('aria-label', `Assign actual vehicle for ${vehicleId}`);

            const commitButton = document.createElement('button');
            commitButton.type = 'button';
            commitButton.className = 'route-breakdown-commit';
            commitButton.textContent = 'Commit';

            const compatibleVehicles = availableVehicles.filter(vehicle => {
                const payloadKg = Number(vehicle?.payloadKg);
                const palletCapacity = Number(vehicle?.palletCapacity);

                if (!Number.isFinite(payloadKg) || payloadKg < requiredPayloadKg) {
                    return false;
                }

                if (!Number.isFinite(palletCapacity) || palletCapacity < requiredPalletCount) {
                    return false;
                }

                return true;
            });

            const compatibleRegs = compatibleVehicles
                .map(vehicle => String(vehicle?.reg || '').trim())
                .filter(Boolean);

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = compatibleRegs.length
                ? 'Select actual vehicle...'
                : 'No vehicles for selected depot';
            selector.appendChild(defaultOption);

            compatibleRegs.forEach(reg => {
                const option = document.createElement('option');
                option.value = reg;
                option.textContent = reg;
                selector.appendChild(option);
            });

            const storedSelection = selectedVehicleByRoute.get(routeKey) || '';
            const inferredSelection = compatibleRegs.includes(vehicleId) ? vehicleId : '';
            selector.value = storedSelection || inferredSelection;

            const syncCommitButtonState = () => {
                const selectedReg = String(selector.value || '').trim();
                const isBusy = committingRouteKeys.has(routeKey);
                commitButton.disabled = isBusy || !selectedReg || !routePalletIds.length;
            };

            selector.addEventListener('change', event => {
                selectedVehicleByRoute.set(routeKey, String(event.target.value || ''));
                syncCommitButtonState();
            });

            commitButton.addEventListener('click', async () => {
                const selectedReg = String(selector.value || '').trim();
                const dueDate = String(document.getElementById('dueDate')?.value || '').trim();

                if (!selectedReg) {
                    setStatus('Select a vehicle before committing a route.', 'error');
                    return;
                }

                if (!dueDate) {
                    setStatus('Due date is required to commit route allocations.', 'error');
                    return;
                }

                if (!routePalletIds.length) {
                    setStatus('No outgoing pallets were found in this route.', 'error');
                    return;
                }

                const isConfirmed = window.confirm(
                    `Commit ${routePalletIds.length} pallet(s) from ${vehicleId} to ${selectedReg}?`
                );
                if (!isConfirmed) {
                    return;
                }

                committingRouteKeys.add(routeKey);
                const previousLabel = commitButton.textContent;
                commitButton.textContent = 'Committing...';
                syncCommitButtonState();

                try {
                    setStatus(`Committing ${routePalletIds.length} pallets to ${selectedReg}...`, null);
                    const response = await fetch("{{ route('outgoing-pallets-loading.commit-allocations') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            reg: selectedReg,
                            dueDate,
                            outgoingPalletIds: routePalletIds,
                        }),
                    });

                    let payload = null;
                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = null;
                    }

                    if (!response.ok) {
                        const message = payload?.error || payload?.message || 'Failed to commit route allocations';
                        throw new Error(message);
                    }

                    const committedCount = Number(payload?.committedCount ?? payload?.assignedCount ?? 0);
                    const skippedCount = Number(payload?.skippedCount ?? 0);
                    const suffix = skippedCount > 0 ? ` (${skippedCount} skipped)` : '';
                    setStatus(`Committed ${committedCount} pallet(s) to ${selectedReg}${suffix}.`, 'success');

                    selectedVehicleByRoute.set(routeKey, selectedReg);
                    if (latestPlanPayload) {
                        renderRouteBreakdown(latestPlanPayload);
                    }
                } catch (error) {
                    setStatus(error.message || 'Failed to commit route allocations.', 'error');
                } finally {
                    commitButton.textContent = previousLabel;
                    committingRouteKeys.delete(routeKey);
                    syncCommitButtonState();
                }
            });

            if (!compatibleRegs.length) {
                selector.disabled = true;
            }

            const controls = document.createElement('div');
            controls.className = 'route-breakdown-vehicle-controls';
            controls.appendChild(selector);
            controls.appendChild(commitButton);
            vehicleControl.appendChild(controls);
            syncCommitButtonState();

            const metrics = document.createElement('div');
            metrics.className = 'route-breakdown-metrics';
            metrics.innerHTML = [
                `<span class="route-breakdown-badge">Pallets: ${stops.length}</span>`,
                `<span class="route-breakdown-badge">Distance: ${formatMeters(travelMetrics.distanceMeters)}</span>`,
                `<span class="route-breakdown-badge">Drive: ${formatSeconds(travelMetrics.driveSeconds)}</span>`,
                `<span class="route-breakdown-badge">Total: ${formatSeconds(route?.completion_time ?? route?.time)}</span>`,
            ].join('');

            head.appendChild(title);
            head.appendChild(metrics);
            head.appendChild(vehicleControl);
            card.appendChild(head);

            if (!stops.length) {
                const empty = document.createElement('p');
                empty.className = 'route-breakdown-empty';
                empty.textContent = 'No pallets in this route.';
                card.appendChild(empty);
                routeBreakdownEl.appendChild(card);
                return;
            }

            const list = document.createElement('ol');
            list.className = 'route-breakdown-stops';

            stops.forEach(activity => {
                const serviceId = String(activity?.id ?? activity?.service_id ?? activity?.address?.location_id ?? 'Unknown');
                const meta = serviceMeta.get(serviceId);
                const when = formatUnixTimestamp(activity?.arr_time ?? activity?.end_time ?? activity?.start_time);
                const group = meta?.group ? ` - ${meta.group}` : '';
                const timePart = when ? ` (${when})` : '';

                const li = document.createElement('li');
                li.textContent = `${meta?.name ?? serviceId}${group}${timePart}`;
                list.appendChild(li);
            });

            card.appendChild(list);
            routeBreakdownEl.appendChild(card);
        });
    }

    function requestGraphHopperPath(ghRouting, routingRequest) {
        return new Promise((resolve, reject) => {
            let settled = false;

            const finish = (error, data) => {
                if (settled) {
                    return;
                }
                settled = true;
                if (error) {
                    reject(error);
                    return;
                }
                resolve(data);
            };

            const runLegacySignature = () => {
                try {
                    const maybePromise = ghRouting.doRequest(
                        routingRequest,
                        response => finish(null, response),
                        error => finish(error || new Error('GraphHopper route request failed'))
                    );

                    if (maybePromise && typeof maybePromise.then === 'function') {
                        maybePromise.then(response => finish(null, response)).catch(error => finish(error));
                    }
                } catch (error) {
                    finish(error);
                }
            };

            try {
                // Newer clients use promise-based doRequest(request).
                const maybePromise = ghRouting.doRequest(routingRequest);

                if (maybePromise && typeof maybePromise.then === 'function') {
                    maybePromise.then(response => finish(null, response)).catch(() => {
                        // Fall back to older callback signature if promise call path fails.
                        runLegacySignature();
                    });
                    return;
                }

                // If no promise was returned, try legacy callback signature.
                runLegacySignature();
            } catch (error) {
                // If direct call fails (signature mismatch), try legacy callback signature.
                try {
                    runLegacySignature();
                } catch (legacyError) {
                    finish(legacyError || error);
                }
            }

            setTimeout(() => {
                finish(new Error('GraphHopper route request timed out'));
            }, 12000);
        });
    }

    async function requestRouteGeometryViaServer(points, profile) {
        const response = await fetch('/api/graphhopper/route', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                points: points.map(point => ({
                    lat: point.lat,
                    lng: point.lon,
                    lon: point.lon,
                })),
                profile,
                calc_points: true,
                points_encoded: false,
                instructions: false,
            }),
        });

        let payload = null;
        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (!response.ok) {
            const message = payload?.message || payload?.error || `Route API request failed (${response.status})`;
            throw new Error(message);
        }

        return payload;
    }

    async function requestRouteGeometryViaOsrm(points, profile) {
        const coords = points
            .map(point => `${Number(point.lon)},${Number(point.lat)}`)
            .join(';');

        const osrmProfile = osrmProfileFromRoutingProfile(profile);
        const url = `https://router.project-osrm.org/route/v1/${osrmProfile}/${coords}?overview=full&geometries=geojson&steps=false`;
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        });

        let payload = null;
        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (!response.ok) {
            const message = payload?.message || `OSRM route request failed (${response.status})`;
            throw new Error(message);
        }

        const coordinates = payload?.routes?.[0]?.geometry?.coordinates ?? [];
        if (!Array.isArray(coordinates) || !coordinates.length) {
            throw new Error('OSRM returned no route geometry');
        }

        return coordinates
            .map(coord => [Number(coord[1]), Number(coord[0])])
            .filter(point => Number.isFinite(point[0]) && Number.isFinite(point[1]));
    }

    function extractLatLngsFromRouteResponse(routeResponse) {
        const path = routeResponse?.paths?.[0] ?? null;
        if (!path) {
            return [];
        }

        let latLngs = [];
        const geoJsonCoordinates = path?.points?.coordinates ?? [];
        if (Array.isArray(geoJsonCoordinates) && geoJsonCoordinates.length) {
            latLngs = geoJsonCoordinates
                .map(coord => [Number(coord[1]), Number(coord[0])])
                .filter(point => Number.isFinite(point[0]) && Number.isFinite(point[1]));
        } else if (typeof path?.points === 'string' && window?.L?.PolylineUtil?.decode) {
            latLngs = L.PolylineUtil.decode(path.points, 5)
                .map(point => [Number(point[0]), Number(point[1])])
                .filter(point => Number.isFinite(point[0]) && Number.isFinite(point[1]));
        }

        return latLngs;
    }

    async function buildRoadLatLngsViaServerSegments(route, profile) {
        const points = route.points;
        if (!Array.isArray(points) || points.length < 2) {
            return [];
        }

        const stitched = [];

        for (let i = 0; i < points.length - 1; i++) {
            const segmentPoints = [points[i], points[i + 1]];
            const segmentResponse = await requestRouteGeometryViaServer(segmentPoints, profile);
            const segmentLatLngs = extractLatLngsFromRouteResponse(segmentResponse);

            if (!segmentLatLngs.length) {
                throw new Error(`no geometry points returned for segment ${i + 1}`);
            }

            if (stitched.length && segmentLatLngs.length) {
                const [prevLat, prevLng] = stitched[stitched.length - 1];
                const [firstLat, firstLng] = segmentLatLngs[0];
                if (prevLat === firstLat && prevLng === firstLng) {
                    segmentLatLngs.shift();
                }
            }

            stitched.push(...segmentLatLngs);
        }

        return stitched;
    }

    async function drawRoutesWithGraphHopperJs(payload) {
        clearMapLayers();

        const routes = getRoutesFromPayload(payload);
        if (!routes.length) {
            mapNoteEl.textContent = 'No route geometry could be assembled from the response.';
            return;
        }

        renderRouteLegend(routes);

        if (!graphHopperApiKey) {
            mapNoteEl.textContent = 'GraphHopper API key is missing, cannot render map routes.';

            const fallbackBounds = [];
            routes.forEach((route, index) => {
                const color = routeColor(index);
                const fallbackLine = route.points.map(point => [point.lat, point.lon]);
                L.polyline(fallbackLine, {
                    color,
                    weight: 3,
                    opacity: 0.7,
                    dashArray: '6,6',
                })
                    .bindPopup(`Vehicle: ${route.vehicleId} (fallback line)`)
                    .addTo(routeLayer);
                fallbackBounds.push(...fallbackLine);
            });

            if (fallbackBounds.length) {
                routeMap.fitBounds(fallbackBounds, {padding: [24, 24]});
            }
            return;
        }

        const bounds = [];
        const routeErrors = [];
        const profile = "truck";

        const graphHopperClientAvailable = !!(window.GraphHopper && window.GraphHopper.Routing);
        let graphHopperRateLimited = Date.now() < graphHopperRateLimitedUntil;

        const drawTasks = routes.map(async (route, index) => {
            const color = routeColor(index);

            route.points.forEach((point, pointIndex) => {
                const label = point.kind === 'service'
                    ? String(point.name ?? point.id ?? 'Unknown stop')
                        .split(' - ')
                        .join('<br/>')
                    : `${point.kind.toUpperCase()} (${route.vehicleId})`;
                L.circleMarker([point.lat, point.lon], {
                    radius: point.kind === 'service' ? 4 : 6,
                    color,
                    weight: 2,
                    fillColor: '#ffffff',
                    fillOpacity: 0.9,
                })
                    .bindPopup(label)
                    .addTo(markerLayer);
            });

            // Draw a fallback polyline first so a route line is always visible.
            const fallbackLine = route.points.map(point => [point.lat, point.lon]);
            const fallbackPolyline = L.polyline(fallbackLine, {
                color,
                weight: 3,
                opacity: 0.7,
                dashArray: '6,6',
            })
                .bindPopup(`Vehicle: ${route.vehicleId} (fallback line)`)
                .addTo(routeLayer);
            bounds.push(...fallbackLine);

            try {
                let latLngs = [];

                if (graphHopperClientAvailable && !graphHopperRateLimited) {
                    const ghRouting = new window.GraphHopper.Routing(
                        { key: graphHopperApiKey },
                        { profile, elevation: false }
                    );

                    const routingRequest = {
                        profile,
                        points: route.points.map(point => [point.lon, point.lat]),
                        points_encoded: false,
                        instructions: false,
                        calc_points: true,
                        return_snapped_waypoints: true,
                    };

                    try {
                        const routeResponse = await requestGraphHopperPath(ghRouting, routingRequest);
                        latLngs = extractLatLngsFromRouteResponse(routeResponse);
                    } catch (clientError) {
                        if (isGraphHopperRateLimitedError(clientError)) {
                            graphHopperRateLimited = true;
                            graphHopperRateLimitedUntil = Date.now() + 60 * 1000;
                        }
                    }
                }

                if (!latLngs.length && !graphHopperRateLimited) {
                    try {
                        const routeResponse = await requestRouteGeometryViaServer(route.points, profile);
                        latLngs = extractLatLngsFromRouteResponse(routeResponse);
                    } catch (serverRouteError) {
                        if (isGraphHopperRateLimitedError(serverRouteError)) {
                            graphHopperRateLimited = true;
                            graphHopperRateLimitedUntil = Date.now() + 60 * 1000;
                        }
                    }
                }

                if (!latLngs.length && !graphHopperRateLimited) {
                    // Fallback: request each leg separately and stitch road geometry.
                    try {
                        latLngs = await buildRoadLatLngsViaServerSegments(route, profile);
                    } catch (segmentError) {
                        if (isGraphHopperRateLimitedError(segmentError)) {
                            graphHopperRateLimited = true;
                            graphHopperRateLimitedUntil = Date.now() + 60 * 1000;
                        }
                    }
                }

                if (!latLngs.length) {
                    latLngs = await requestRouteGeometryViaOsrm(route.points, profile);
                }

                if (latLngs.length) {
                    fallbackPolyline.remove();
                    L.polyline(latLngs, {
                        color,
                        weight: 4,
                        opacity: 0.85,
                    })
                        .bindPopup(`Vehicle: ${route.vehicleId}`)
                        .addTo(routeLayer);
                } else {
                    routeErrors.push(`${route.vehicleId}: no geometry points returned`);
                }
            } catch (error) {
                // Keep fallback polyline for this route.
                const message = error && error.message ? error.message : String(error || 'unknown routing error');
                routeErrors.push(`${route.vehicleId}: ${message}`);
            }
        });

        await Promise.all(drawTasks);

        if (bounds.length) {
            routeMap.fitBounds(bounds, {padding: [24, 24]});
        }
    }

    function setStatus(message, mode) {
        statusEl.textContent = message;
        statusEl.classList.remove('error', 'success');
        if (mode) {
            statusEl.classList.add(mode);
        }
    }

    async function renderSummary(payload) {
        resultsEl.classList.remove('hidden');
        ensureMap();
        setTimeout(() => routeMap.invalidateSize(), 0);
        renderRouteBreakdown(payload);
        await drawRoutesWithGraphHopperJs(payload);
    }

    async function loadDepots() {
        try {
            const response = await fetch("{{ route('outgoing-pallets-loading.depots') }}");
            if (!response.ok) {
                throw new Error('Failed to load depots');
            }

            const payload = await response.json();
            const depots = Array.isArray(payload.depots) ? payload.depots : [];
            depotSelect.innerHTML = '';

            if (!depots.length) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No depots available';
                depotSelect.appendChild(option);
                return;
            }

            depots.forEach((depot, idx) => {
                const option = document.createElement('option');
                option.value = String(depot.id);
                option.textContent = depot.name;
                if (depot.id === 1) {
                    option.selected = true;
                }
                depotSelect.appendChild(option);
            });

            await loadVehiclesForDepot(depotSelect.value);
        } catch (error) {
            depotSelect.innerHTML = '<option value="">Unable to load depots</option>';
            setStatus(error.message || 'Unable to load depots', 'error');
        }
    }

    async function loadVehiclesForDepot(depotId) {
        const value = String(depotId || '').trim();
        if (!value) {
            availableVehicles = [];
            return;
        }

        try {
            const response = await fetch(`{{ route('outgoing-pallets-loading.vehicles') }}?depot=${encodeURIComponent(value)}`, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load vehicles');
            }

            const payload = await response.json();
            if (Array.isArray(payload?.vehicleOptions)) {
                availableVehicles = payload.vehicleOptions
                    .map(vehicle => ({
                        reg: String(vehicle?.reg || '').trim(),
                        payloadKg: Number(vehicle?.payloadKg),
                        palletCapacity: Number(vehicle?.palletCapacity),
                    }))
                    .filter(vehicle => vehicle.reg !== '');
            } else if (Array.isArray(payload?.vehicles)) {
                // Backward-compatible fallback if metadata is unavailable.
                availableVehicles = payload.vehicles
                    .map(reg => ({ reg: String(reg || '').trim(), payloadKg: null, palletCapacity: null }))
                    .filter(vehicle => vehicle.reg !== '');
            } else {
                availableVehicles = [];
            }

            if (latestPlanPayload) {
                renderRouteBreakdown(latestPlanPayload);
            }
        } catch (error) {
            availableVehicles = [];
        }
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();

        const dueDate = document.getElementById('dueDate').value;
        const depot = depotSelect.value;
        const serviceDurationSeconds = Number(document.getElementById('serviceDurationSeconds').value || 1200);
        const dryRun = document.getElementById('dryRun').value === 'true';

        if (!dueDate || !depot) {
            setStatus('Due date and depot are required.', 'error');
            return;
        }

        await loadVehiclesForDepot(depot);

        resetRouteDisplayForNewPlan();

        runBtn.disabled = true;
        setStatus('Running GraphHopper plan...', null);

        try {
            const response = await fetch("{{ route('outgoing-pallets-loading.graphhopper-multi-vehicle') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    dueDate,
                    depot: Number(depot),
                    serviceDurationSeconds,
                    dryRun,
                    persistSuggestions: !dryRun
                })
            });

            let payload = null;
            try {
                payload = await response.json();
            } catch (jsonError) {
                payload = { error: 'Unable to parse response JSON' };
            }

            if (!response.ok) {
                const message = payload.error || payload.detail || 'GraphHopper planning request failed';
                throw new Error(message);
            }

            latestPlanPayload = payload;
            await renderSummary(payload);
            setStatus('Plan complete. Results displayed below.', 'success');
        } catch (error) {
            setStatus(error.message || 'GraphHopper planning failed.', 'error');
        } finally {
            runBtn.disabled = false;
        }
    });

    depotSelect.addEventListener('change', async () => {
        await loadVehiclesForDepot(depotSelect.value);
    });

    loadDepots();
</script>
</body>
</html>
