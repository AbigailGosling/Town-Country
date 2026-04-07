<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Pallet Allocator</title>
  <style>
    :root {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color-scheme: light dark;
    }
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      height: 100vh;
      background: #f6f7fb;
      color: #111827;
      overflow: hidden;
    }
    .top-bar {
      height: 100px;
      padding: 1rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      background: #fff;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 10;
    }
    .top-controls {
      display: flex;
      align-items: flex-end;
      gap: 1rem;
    }
    .toggle-btn {
      padding: 0.5rem 0.9rem;
      border-radius: 0.5rem;
      border: 1px solid rgba(0, 0, 0, 0.2);
      background: #fff;
      color: #111827;
      font-weight: 600;
      cursor: pointer;
    }
    .toggle-btn.active {
      background: #111827;
      color: #fff;
      border-color: #111827;
    }
    .top-bar h1 {
      margin: 0;
      font-size: 1.8rem;
    }
    .top-bar label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.35rem;
    }
    .top-bar select {
      padding: 0.5rem 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid rgba(0, 0, 0, 0.2);
      font-size: 1rem;
      background: #fff;
      color: inherit;
      min-width: 200px;
    }
    .field-hint {
      display: none;
      margin-top: 0.35rem;
      color: #b91c1c;
      font-size: 0.8rem;
      font-weight: 600;
    }
    .field-hint.visible {
      display: block;
    }
    .top-bar input[type="date"] {
      padding: 0.5rem 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid rgba(0, 0, 0, 0.2);
      font-size: 1rem;
      background: #fff;
      color: inherit;
      min-width: 200px;
    }
    .main {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-template-rows: minmax(0, 1fr);
      gap: 0;
      height: calc(100vh - 100px);
      margin-top: 100px;
    }
    .pane {
      overflow-y: auto;
      padding: 1.5rem;
      min-height: 0;
    }
    .left-pane {
      background: #f9fafb;
      border-right: 1px solid rgba(0, 0, 0, 0.08);
    }
    .right-pane {
      background: #fff;
    }
    .truck {
      border: 2px solid rgba(0, 0, 0, 0.2);
      border-radius: 0.9rem;
      padding: 1rem;
      margin: 0.5rem 2rem 2rem;
      background: #fdfdfd;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    }
    .truck-header {
      display: grid;
      grid-template-columns: 1fr 1fr;
      align-items: center;
      margin-bottom: 0.5rem;
    }
    .truck-actions {
      justify-self: end;
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }
    .load-complete-btn {
      justify-self: end;
      padding: 0.5rem 0.9rem;
      border-radius: 0.5rem;
      border: none;
      background: #16a34a;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 6px 14px rgba(22, 163, 74, 0.25);
    }
    .load-complete-btn:hover {
      background: #15803d;
    }
    .print-load-btn {
      justify-self: end;
      padding: 0.5rem 0.9rem;
      border-radius: 0.5rem;
      border: none;
      background: #111827;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 6px 14px rgba(17, 24, 39, 0.25);
    }
    .print-load-btn:hover {
      background: #030712;
    }
    .ai-plan-btn {
      justify-self: end;
      padding: 0.5rem 0.9rem;
      border-radius: 0.5rem;
      border: none;
      background: #2563eb;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
      margin-left: 0.6rem;
    }
    .ai-plan-btn:hover {
      background: #1d4ed8;
    }
    .truck-title {
      font-weight: 700;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: #374151;
      margin: 0 0 0.35rem;
      text-align: center;
    }
    .plate {
      justify-self: start;
      background: #f5d547;
      color: #111;
      border: 1px solid rgba(0, 0, 0, 0.35);
      border-radius: 0.4rem;
      padding: 0.35rem 0.65rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      font-size: 0.95rem;
      box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.12);
      cursor: pointer;
    }
    .plate-wrap {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex-wrap: wrap;
    }
    .payload-badge {
      font-weight: 600;
      color: #111827;
      background: rgba(37, 99, 235, 0.12);
      border-radius: 999px;
      padding: 0.25rem 0.7rem;
      font-size: 0.85rem;
      flex: 1 1 160px;
      white-space: nowrap;
      text-align: center;
    }
    .total-weight {
      font-weight: 600;
      color: #111827;
      background: rgba(17, 24, 39, 0.06);
      border-radius: 999px;
      padding: 0.25rem 0.7rem;
      font-size: 0.85rem;
      flex: 1 1 160px;
      white-space: nowrap;
      text-align: center;
    }
    .order-card {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #fff;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 0.75rem;
      padding: 1rem 1.25rem;
      margin-bottom: 1rem;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
      cursor: pointer;
    }
    .frozen-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.15rem 0.5rem;
      border-radius: 999px;
      background: rgba(14, 165, 233, 0.12);
      color: #0ea5e9;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }
    .order-card.selected {
      border-color: #2563eb;
      box-shadow: 0 10px 24px rgba(37, 99, 235, 0.2);
      background: rgba(37, 99, 235, 0.05);
    }
    .order-info h3 {
      margin: 0 0 0.35rem;
      font-size: 1.1rem;
    }
    .order-title {
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }
    .order-info p {
      margin: 0;
      color: #6b7280;
      font-size: 0.95rem;
    }
    .postcode-link {
      color: #2563eb;
      text-decoration: underline;
      cursor: pointer;
      font-weight: 600;
    }
    .postcode-pin {
      margin-left: 0.35rem;
      font-size: 0.95rem;
      vertical-align: middle;
      color: #2563eb;
    }
    .order-status {
      margin-top: 0.5rem;
      font-size: 0.85rem;
      font-weight: 600;
      color: #16a34a;
      display: none;
    }
    .order-status .reg-plate {
      display: inline-block;
      margin-left: 0.4rem;
      padding: 0.1rem 0.45rem;
      border-radius: 0.35rem;
      background: #f5d547;
      color: #111;
      border: 1px solid rgba(0, 0, 0, 0.35);
      font-weight: 700;
      letter-spacing: 0.08em;
      font-size: 0.75rem;
      box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.12);
    }
    .order-status.visible {
      display: block;
    }
    .pallet-controls {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .pallet-type {
      display: inline-flex;
      border: 1px solid rgba(0, 0, 0, 0.2);
      border-radius: 999px;
      overflow: hidden;
      background: #fff;
    }
    .pallet-type button {
      border: none;
      background: transparent;
      padding: 0.35rem 0.7rem;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      color: #6b7280;
    }
    .pallet-type button.active {
      background: #111827;
      color: #fff;
    }
    .contents-btn {
      border: 1px solid rgba(0, 0, 0, 0.2);
      background: #fff;
      color: #111827;
      border-radius: 0.45rem;
      padding: 0.35rem 0.6rem;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
    }
    .contents-list {
      margin: 0;
      padding-left: 1rem;
      line-height: 1.45;
    }
    .pallet {
      width: 64px;
      height: 64px;
      border-radius: 0.6rem;
      border: 2px dashed #4b5563;
      background: #f3f4f6;
      display: grid;
      place-items: center;
      font-weight: 700;
      color: #374151;
      cursor: grab;
      user-select: none;
      text-align: center;
      padding: 0.2rem;
      font-size: 0.7rem;
      line-height: 1.1;
    }
    .pallet.euro {
      border-color: #2563eb;
      background: rgba(37, 99, 235, 0.12);
      color: #1e3a8a;
    }
    .pallet.standard {
      border-color: #f59e0b;
      background: rgba(245, 158, 11, 0.15);
      color: #92400e;
    }
    .pallet:active {
      cursor: grabbing;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(90px, 1fr));
      gap: 0.75rem;
    }
    .grid-wrapper {
      display: grid;
      /*grid-template-columns: 1fr 110px;*/
      gap: 1rem;
      align-items: start;
    }
    .row-weights {
      display: grid;
      grid-template-rows: repeat(10, minmax(110px, 1fr));
      gap: 0.75rem;
    }
    .row-weight {
      display: grid;
      place-items: center;
      font-weight: 600;
      color: #374151;
      background: rgba(17, 24, 39, 0.06);
      border-radius: 0.6rem;
      min-height: 110px;
    }
    .slot {
      border: 2px dashed rgba(0, 0, 0, 0.2);
      border-radius: 0.75rem;
      min-height: 110px;
      aspect-ratio: 1 / 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0.25rem;
      font-weight: 600;
      color: #6b7280;
      background: #f9fafb;
      padding: 0.45rem;
      overflow: hidden;
    }
    .slot.drop-target {
      border-color: #2563eb;
      background: rgba(37, 99, 235, 0.08);
    }
    .slot.occupied {
      border-style: solid;
      border-color: #16a34a;
      background: rgba(22, 163, 74, 0.12);
      color: #166534;
      justify-content: flex-start;
    }
    .slot-content {
      width: 100%;
      height: 100%;
      min-height: 0;
      display: grid;
      grid-template-rows: auto auto auto auto;
      justify-items: center;
      align-content: start;
      gap: 0.25rem;
    }
    .slot-order {
      font-size: 0.75rem;
      font-weight: 700;
      color: #111827;
      text-align: center;
      width: 100%;
      line-height: 1.15;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
    .slot-subtext {
      font-size: 0.7rem;
      color: #6b7280;
      text-align: center;
      width: 100%;
      line-height: 1.15;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      word-break: break-word;
    }
    .slot-frozen-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.1rem 0.45rem;
      border-radius: 999px;
      background: rgba(14, 165, 233, 0.12);
      color: #0ea5e9;
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      text-transform: uppercase;
      margin-top: 0.2rem;
      max-width: 100%;
      flex-shrink: 0;
    }
    .slot-contents {
      font-size: 0.65rem;
      font-weight: 500;
      color: #4b5563;
      text-align: center;
      width: 100%;
      line-height: 1.15;
      overflow: hidden;
      display: block;
      white-space: pre-line;
      max-height: 3.45em;
      word-break: break-word;
    }
    .order-contents-summary {
      margin: 0;
      color: #4b5563;
      font-size: 0.9rem;
      line-height: 1.25;
      overflow: hidden;
      display: block;
      white-space: pre-line;
      max-height: 3.75em;
      word-break: break-word;
    }
    .slot.euro-only:not(.occupied) {
      background: rgba(37, 99, 235, 0.08);
      border-color: rgba(37, 99, 235, 0.35);
      color: rgba(37, 99, 235, 0.55);
      font-size: 0.8rem;
      font-weight: 500;
    }
    .slot.euro-only:not(.occupied)::before {
      content: "Euro Pallet Only";
    }
    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      z-index: 2000;
    }
    .modal.open {
      display: flex;
    }
    .modal-content {
      background: #fff;
      color: #111827;
      border-radius: 0.8rem;
      padding: 1.5rem;
      max-width: 520px;
      width: 100%;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }
    .modal-content.map-modal {
      max-width: 820px;
      width: 80vw;
    }
    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
    }
    .modal-header h2 {
      margin: 0;
      font-size: 1.3rem;
    }
    .modal-close {
      border: none;
      background: rgba(0, 0, 0, 0.08);
      width: 36px;
      height: 36px;
      border-radius: 999px;
      font-size: 1.2rem;
      cursor: pointer;
    }
    .modal-grid {
      display: grid;
      gap: 0.6rem;
    }
    .modal-row {
      display: grid;
      grid-template-columns: 140px 1fr;
      gap: 0.5rem;
      font-size: 0.95rem;
    }
    .modal-row strong {
      color: #374151;
    }
    .modal-summary-text {
      line-height: 1.25;
      overflow: hidden;
      display: block;
      white-space: pre-line;
      max-height: 3.75em;
      word-break: break-word;
    }
    .map-frame {
      width: 100%;
      height: 460px;
      border: 0;
      border-radius: 0.6rem;
    }
    @media (max-width: 900px) {
      .main {
        grid-template-columns: 1fr;
        grid-template-rows: 25% 75%;
      }
      .left-pane {
        border-right: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      }
    }
  </style>
</head>
<body>
  <header class="top-bar">
    <div class="top-controls">
      <div>
        <label for="deliveryDate">Delivery Date</label>
        <input id="deliveryDate" type="date" />
      </div>
      <div>
        <label for="depotSelect">Depot</label>
        <select id="depotSelect">
          <option>...</option>
        </select>
      </div>
      <div>
        <label for="sortBy">Sort By</label>
        <select id="sortBy">
          <option value="postcode">Postcode</option>
          <option value="name">Customer Name</option>
        </select>
      </div>
      <div>
        <label for="vehicleSelect">Vehicle</label>
        <select id="vehicleSelect">
          <option>...</option>
        </select>
        <div class="field-hint" id="vehicleHint">No vehicles available for selected depot.</div>
      </div>
      <div>
        <label>&nbsp;</label>
        <button id="toggleAllocatedBtn" class="toggle-btn" type="button">Hide Allocated</button>
      </div>
    </div>
    <h1>Pallet Loader</h1>
  </header>

  <main class="main">
    <section class="pane left-pane" id="orders"></section>
    <section class="pane right-pane">
      <div class="truck">
        <div class="truck-title">Bulkhead / front of vehicle</div>
        <div class="truck-header">
          <div class="plate-wrap">
            <div class="plate" id="vehiclePlate">...</div>
            <div class="payload-badge" id="payloadBadge">Payload: —</div>
            <!--<div class="total-weight" id="totalWeight">0 kg</div>-->
          </div>
          <div class="truck-actions">
            <button class="print-load-btn" id="printLoadBtn" type="button">Print Load</button>
            <button class="load-complete-btn" id="loadCompleteBtn" type="button">Load Complete</button>
            <!--<button class="ai-plan-btn" id="aiPlanBtn" type="button" disabled>AI Plan</button>-->
          </div>
        </div>
        <div class="grid-wrapper">
          <div class="grid" id="palletGrid"></div>
          <!--<div class="row-weights" id="rowWeights"></div>-->
        </div>
      </div>
    </section>
  </main>

  <div class="modal" id="vehicleModal" role="dialog" aria-modal="true" aria-label="Vehicle details">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Vehicle Details</h2>
        <button class="modal-close" type="button" id="vehicleModalClose">×</button>
      </div>
      <div class="modal-grid" id="vehicleModalBody"></div>
    </div>
  </div>

  <div class="modal" id="mapModal" role="dialog" aria-modal="true" aria-label="Postcode map">
    <div class="modal-content map-modal">
      <div class="modal-header">
        <h2 id="mapModalTitle">Location</h2>
        <button class="modal-close" type="button" id="mapModalClose">×</button>
      </div>
      <iframe id="mapFrame" class="map-frame" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>

  <div class="modal" id="aiPlanModal" role="dialog" aria-modal="true" aria-label="AI delivery plan">
    <div class="modal-content">
      <div class="modal-header">
        <h2>AI Delivery Plan</h2>
        <button class="modal-close" type="button" id="aiPlanClose">×</button>
      </div>
      <div class="modal-grid" id="aiPlanBody"></div>
    </div>
  </div>

  <div class="modal" id="contentsModal" role="dialog" aria-modal="true" aria-label="Pallet contents overview">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="contentsModalTitle">Pallet Contents</h2>
        <button class="modal-close" type="button" id="contentsModalClose">×</button>
      </div>
      <div class="modal-grid" id="contentsModalBody"></div>
    </div>
  </div>

  <script>
    let orders = [];

    const ordersContainer = document.getElementById("orders");
    const palletGrid = document.getElementById("palletGrid");
    const rowWeightsEl = document.getElementById("rowWeights");
    const totalWeightEl = document.getElementById("totalWeight");
    const payloadBadge = document.getElementById("payloadBadge");
    const printLoadBtn = document.getElementById("printLoadBtn");
    const sortBySelect = document.getElementById("sortBy");
    const toggleAllocatedBtn = document.getElementById("toggleAllocatedBtn");
    const depotSelect = document.getElementById("depotSelect");
    const vehicleSelect = document.getElementById("vehicleSelect");
    const vehicleHint = document.getElementById("vehicleHint");
    const vehiclePlate = document.getElementById("vehiclePlate");
    const vehicleModal = document.getElementById("vehicleModal");
    const vehicleModalClose = document.getElementById("vehicleModalClose");
    const vehicleModalBody = document.getElementById("vehicleModalBody");
    const mapModal = document.getElementById("mapModal");
    const mapModalClose = document.getElementById("mapModalClose");
    const mapFrame = document.getElementById("mapFrame");
    const mapModalTitle = document.getElementById("mapModalTitle");
    const aiPlanBtn = document.getElementById("aiPlanBtn");
    const aiPlanModal = document.getElementById("aiPlanModal");
    const aiPlanClose = document.getElementById("aiPlanClose");
    const aiPlanBody = document.getElementById("aiPlanBody");
    const contentsModal = document.getElementById("contentsModal");
    const contentsModalClose = document.getElementById("contentsModalClose");
    const contentsModalTitle = document.getElementById("contentsModalTitle");
    const contentsModalBody = document.getElementById("contentsModalBody");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
    let activeDragOrderId = null;
    let hideAllocated = false;
    let selectedOrderId = null;
    let currentPayload = null;
    let activeTouchDrag = null;
    const touchEdgeScrollThreshold = 90;
    const touchEdgeScrollMaxStep = 22;
    const PALLET_COLUMNS = 3;
    const DEFAULT_MAX_PALLET_ROWS = 5;

    let vehicleInfo = null;
    let vehicleMaxPalletRows = DEFAULT_MAX_PALLET_ROWS;

    function normalizeMaxPalletRows(value) {
      const rows = Number(value);
      if (!Number.isFinite(rows) || rows <= 0) {
        return DEFAULT_MAX_PALLET_ROWS;
      }
      return Math.floor(rows);
    }

    function getMaxPalletRows() {
      return normalizeMaxPalletRows(vehicleInfo?.maxPalletRows ?? vehicleMaxPalletRows);
    }

    function getMaxSlotCount() {
      return getMaxPalletRows() * PALLET_COLUMNS;
    }

    function isSlotWithinCapacity(row, column) {
      return row >= 1 && row <= getMaxPalletRows() && column >= 1 && column <= PALLET_COLUMNS;
    }

    function getOrderById(orderId) {
      return orders.find(item => item.id === orderId) || null;
    }

    function createDragPayloadFromOrder(order) {
      if (!order) {
        return null;
      }

      return {
        orderId: order.id,
        fromSlotId: order.slotId || null,
        allocated: !!order.allocated,
        palletType: order.palletType,
        outgoingPalletId: order.outgoingPalletId || null,
      };
    }

    function formatPayload(payload) {
      if (payload === null || payload === undefined || payload === "") {
        return "—";
      }
      const numeric = Number(payload);
      if (!Number.isNaN(numeric)) {
        return `${numeric}kg`;
      }
      return String(payload);
    }

    function updateTotalWeightDisplay(totalWeight) {
      const tonnes = Number.isFinite(totalWeight) ? (totalWeight / 1000) : 0;
      const tonnesText = tonnes.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
      //totalWeightEl.textContent = `Total payload: ${totalWeight} kg (${tonnesText} t)`;
      payloadBadge.textContent = `Payload: ${formatPayload(currentPayload)}`;
    }

    function normalizePalletType(value) {
      if (!value) return "Euro";
      const trimmed = String(value).trim().toLowerCase();
      return trimmed.startsWith("s") ? "Standard" : "Euro";
    }

    function normalizeReg(value) {
      return String(value || "").trim().toUpperCase();
    }

    function buildContentSummary(order) {
      return String(order?.contentsPreview || "").trim();
    }

    function buildOverviewContentSummary(overview) {
      return Array.isArray(overview?.contentSummaryLines)
        ? overview.contentSummaryLines.map(line => String(line || "").trim()).filter(Boolean).join("\n")
        : "";
    }

    function jsonHeaders() {
      const headers = {
        "Content-Type": "application/json"
      };
      if (csrfToken) {
        headers["X-CSRF-TOKEN"] = csrfToken;
      }
      return headers;
    }

    function updateVehicleHint(message) {
      if (!vehicleHint) {
        return;
      }
      if (!message) {
        vehicleHint.classList.remove("visible");
        vehicleHint.textContent = "";
        return;
      }
      vehicleHint.textContent = message;
      vehicleHint.classList.add("visible");
    }

    async function updateAllocation(outgoingPalletId, regAllocatedTo, palletRow, palletColumn) {
      if (!outgoingPalletId) {
        return;
      }
      try {
        const response = await fetch("{{ route('outgoing-pallets-loading.update-allocation') }}", {
          method: "POST",
          headers: jsonHeaders(),
          body: JSON.stringify({
            outgoingPalletId,
            regAllocatedTo,
            palletRow,
            palletColumn
          })
        });
        if (!response.ok) {
          const errorText = await response.text();
          console.error("Allocation update failed", errorText);
        }
      } catch (error) {
        console.error("Allocation update error", error);
      }
    }

    async function updatePalletType(outgoingPalletId, palletType) {
      if (!outgoingPalletId) {
        return false;
      }

      try {
        const response = await fetch("{{ route('outgoing-pallets-loading.update-pallet-type') }}", {
          method: "POST",
          headers: jsonHeaders(),
          body: JSON.stringify({
            outgoingPalletId,
            palletType
          })
        });

        if (!response.ok) {
          throw new Error("Unable to update pallet type");
        }

        return true;
      } catch (error) {
        console.error("Pallet type update error", error);
        return false;
      }
    }

    async function openContentsModal(order) {
      if (!order || !order.outgoingPalletId) {
        return;
      }

      contentsModalTitle.textContent = `Pallet ${order.outgoingPalletId} Contents`;
      contentsModalBody.innerHTML = "";

      try {
        const response = await fetch(`{{ route('outgoing-pallets-loading.pallet-overview') }}?outgoingPalletId=${encodeURIComponent(order.outgoingPalletId)}`);
        if (!response.ok) {
          throw new Error("Pallet contents unavailable");
        }
        const data = await response.json();
        const overview = data.overview || {};
        const modalContentSummary = buildOverviewContentSummary(overview);

        const rows = [
          ["Customer", overview.customerName || ""],
          ["Address", [overview.address || "", overview.postcode || ""].filter(Boolean).join(" • ")],
          ["Type", overview.palletType || ""],
          ["Temperature", overview.temperature || ""],
          ["Total Weight", `${Number(overview.totalWeightKg || 0)} kg`],
          ["Content Summary", modalContentSummary || "No cut/picksheet summary available."],
          ["PickWeightOuts", `${Number(overview.pickWeightOutCount || 0)}`],
        ];

        rows.forEach(([label, value]) => {
          const row = document.createElement("div");
          row.className = "modal-row";
          if (label === "Content Summary") {
            row.innerHTML = `<strong>${label}</strong><div class="modal-summary-text">${value}</div>`;
          } else {
            row.innerHTML = `<strong>${label}</strong><div>${value}</div>`;
          }
          contentsModalBody.append(row);
        });
      } catch (error) {
        const row = document.createElement("div");
        row.className = "modal-row";
        row.innerHTML = `<strong>Error</strong><div>${error.message}</div>`;
        contentsModalBody.append(row);
      }

      contentsModal.classList.add("open");
    }

    function closeContentsModal() {
      contentsModal.classList.remove("open");
    }

    async function loadVehicleAllocations(reg) {
      if (!reg) {
        return [];
      }
      try {
        const response = await fetch(`{{ route('outgoing-pallets-loading.vehicle-allocations') }}?reg=${encodeURIComponent(reg)}`);
        if (!response.ok) {
          throw new Error("Vehicle allocations unavailable");
        }
        const data = await response.json();
        return Array.isArray(data.allocations) ? data.allocations : [];
      } catch (error) {
        console.error(error);
        return [];
      }
    }

    async function applyVehicleAllocations(reg) {
      orders.forEach(order => {
        order.allocated = false;
        order.slotId = null;
        order.allocatedReg = "";
      });

      const allocations = await loadVehicleAllocations(reg);
      const selectedDueDate = document.getElementById("deliveryDate").value;
      const dateMatchedAllocations = allocations.filter(alloc => {
        if (!selectedDueDate) {
          return true;
        }
        return String(alloc.dueDate || "") === selectedDueDate;
      });
      if (!dateMatchedAllocations.length) {
        const sortValue = sortBySelect.value || "postcode";
        if (sortValue === "name") {
          orders.sort((a, b) => a.customerName.localeCompare(b.customerName));
        } else {
          orders.sort((a, b) => a.customerDeliveryPostcode.localeCompare(b.customerDeliveryPostcode));
        }
        renderOrders();
        renderGrid();
        return;
      }

      const orderMap = new Map();
      orders.forEach(order => {
        if (order.outgoingPalletId) {
          orderMap.set(Number(order.outgoingPalletId), order);
        }
      });

      dateMatchedAllocations.forEach((alloc, index) => {
        const outgoingPalletId = Number(alloc.outgoingPalletId || 0);
        const row = Number(alloc.row) || 0;
        const column = Number(alloc.column) || 0;
        if (!outgoingPalletId || !row || !column || !isSlotWithinCapacity(row, column)) {
          return;
        }
        const slotIndex = (row - 1) * PALLET_COLUMNS + column;
        const slotId = `slot-${slotIndex}`;
        let order = orderMap.get(outgoingPalletId);
        if (!order) {
          const deliveryNoteNumber = String(alloc.deliveryNoteNumber || "");
          const customerName = alloc.customerName || "";
          const customerDeliveryPostcode = alloc.customerDeliveryPostcode || "";
          const subtext = [customerName, customerDeliveryPostcode].filter(Boolean).join(" • ");
          order = {
            id: `alloc-${deliveryNoteNumber || index + 1}`,
            outgoingPalletId,
            title: `Order ${deliveryNoteNumber}`,
            subtext,
            contentsPreview: "",
            customerName,
            customerDeliveryPostcode,
            deliveryNoteNumber,
            palletType: normalizePalletType(alloc.palletType),
            weightKg: Number(alloc.palletWeight) || 0,
            freshFrozen: alloc.freshFrozen || "",
            allocatedReg: reg,
            allocated: true,
            slotId
          };
          orders.push(order);
          orderMap.set(outgoingPalletId, order);
        } else {
          order.palletType = normalizePalletType(alloc.palletType || order.palletType);
          order.weightKg = Number(alloc.palletWeight) || order.weightKg;
          order.freshFrozen = alloc.freshFrozen ?? order.freshFrozen;
          order.allocatedReg = reg;
          order.allocated = true;
          order.slotId = slotId;
        }
      });

      const sortValue = sortBySelect.value || "postcode";
      if (sortValue === "name") {
        orders.sort((a, b) => a.customerName.localeCompare(b.customerName));
      } else {
        orders.sort((a, b) => a.customerDeliveryPostcode.localeCompare(b.customerDeliveryPostcode));
      }
      renderOrders();
      renderGrid();
    }

    async function loadVehicles(selectedReg = "") {
      try {
        const depot = depotSelect.value || "";
        const url = `{{ route('outgoing-pallets-loading.vehicles') }}?depot=${encodeURIComponent(depot)}`;
        const response = await fetch(url);
        if (!response.ok) {
          throw new Error("Vehicle list unavailable");
        }
        const data = await response.json();
        const vehicles = Array.isArray(data.vehicles) ? data.vehicles : [];
        vehicleSelect.innerHTML = "";
        if (!vehicles.length) {
          const selectedDepotName = depotSelect.options[depotSelect.selectedIndex]?.text || "selected depot";
          updateVehicleHint(`No vehicles available for ${selectedDepotName}.`);
          vehiclePlate.textContent = "...";
          currentPayload = null;
          updateTotalWeightDisplay(0);
          vehicleSelect.disabled = true;
          renderOrders();
          renderGrid();
          return;
        }
        updateVehicleHint("");
        vehicleSelect.disabled = false;
        vehicles.forEach(reg => {
          const normalizedReg = String(reg || "").trim();
          if (!normalizedReg) {
            return;
          }
          const option = document.createElement("option");
          option.value = normalizedReg;
          option.textContent = normalizedReg;
          vehicleSelect.append(option);
        });

        const optionValues = Array.from(vehicleSelect.options).map(option => option.value);
        const normalizedSelectedReg = normalizeReg(selectedReg);
        const nextReg = optionValues.find(value => normalizeReg(value) === normalizedSelectedReg) || optionValues[0];
        vehicleSelect.value = nextReg;
        vehiclePlate.textContent = nextReg;
        await loadVehicleDetails(nextReg);
        await loadOrders();
      } catch (error) {
        updateVehicleHint("Vehicle list unavailable. Please try again.");
        console.error(error);
      }
    }

    async function loadVehicleDetails(reg) {
      try {
        const response = await fetch(`{{ route('outgoing-pallets-loading.vehicle-details') }}?reg=${encodeURIComponent(reg)}`);
        if (!response.ok) {
          throw new Error("Vehicle details unavailable");
        }
        const data = await response.json();
        vehicleInfo = data.vehicle || { reg };
        vehicleMaxPalletRows = normalizeMaxPalletRows(vehicleInfo.maxPalletRows);
        currentPayload = vehicleInfo.payload ?? null;
        const totalWeight = orders.reduce((sum, order) => sum + (order.allocated ? order.weightKg : 0), 0);
        updateTotalWeightDisplay(totalWeight);
        renderGrid();
        return vehicleInfo;
      } catch (error) {
        console.error(error);
        vehicleInfo = { reg };
        vehicleMaxPalletRows = DEFAULT_MAX_PALLET_ROWS;
        currentPayload = null;
        const totalWeight = orders.reduce((sum, order) => sum + (order.allocated ? order.weightKg : 0), 0);
        updateTotalWeightDisplay(totalWeight);
        renderGrid();
        return vehicleInfo;
      }
    }

    async function loadOrders() {
      try {
        const dueDate = document.getElementById("deliveryDate").value;
        const depot = depotSelect.value || "";
        const reg = vehicleSelect.value || vehiclePlate.textContent || "";
        const response = await fetch(`{{ route('outgoing-pallets-loading.pallet-selection') }}?dueDate=${encodeURIComponent(dueDate)}&depot=${encodeURIComponent(depot)}&reg=${encodeURIComponent(reg)}`);
        if (!response.ok) {
          throw new Error("Order list unavailable");
        }
        const data = await response.json();
        const incoming = Array.isArray(data.orders) ? data.orders : [];
        const normalizedCurrentReg = normalizeReg(reg);
        orders = incoming.map((order, index) => {
          const allocatedToSelected = normalizeReg(order.regAllocatedTo) === normalizedCurrentReg;
          const row = Number(order.row) || 0;
          const column = Number(order.column) || 0;
          const hasSlot = allocatedToSelected && isSlotWithinCapacity(row, column);
          const slotId = hasSlot ? `slot-${(row - 1) * PALLET_COLUMNS + column}` : null;
          return {
          id: order.id || `order-${index + 1}`,
          outgoingPalletId: Number(order.outgoingPalletId) || null,
          title: order.title || `Order ${order.deliveryNoteNumber || index + 1}`,
          subtext: order.subtext || "",
          contentsPreview: order.contentsPreview || "",
          customerName: order.customerName || "",
          customerDeliveryAddress: order.customerDeliveryAddress || "",
          customerDeliveryPostcode: order.customerDeliveryPostcode || "",
          deliveryNoteNumber: order.deliveryNoteNumber || "",
          palletType: normalizePalletType(order.palletType),
          weightKg: Number(order.weightKg) || 0,
          freshFrozen: order.freshFrozen || "",
            allocatedReg: String(order.regAllocatedTo || "").trim(),
            allocated: hasSlot,
            slotId
          };
        });
        const sortValue = sortBySelect.value || "postcode";
        if (sortValue === "name") {
          orders.sort((a, b) => a.customerName.localeCompare(b.customerName));
        } else {
          orders.sort((a, b) => a.customerDeliveryPostcode.localeCompare(b.customerDeliveryPostcode));
        }
        renderOrders();
        renderGrid();
      } catch (error) {
        console.error(error);
      }
    }

    async function loadDepots() {
      try {
        const response = await fetch("{{ route('outgoing-pallets-loading.depots') }}");
        if (!response.ok) {
          throw new Error("Depot list unavailable");
        }
        const data = await response.json();
        const depots = Array.isArray(data.depots) ? data.depots : [];
        if (!depots.length) {
          return;
        }
        depotSelect.innerHTML = "";
        depots.forEach(depot => {
          const depotId = typeof depot === "object" && depot !== null ? Number(depot.id) : Number(depot);
          const depotName = typeof depot === "object" && depot !== null ? String(depot.name || "") : String(depot || "");
          if (!depotId || !depotName) {
            return;
          }
          const option = document.createElement("option");
          option.value = String(depotId);
          option.textContent = depotName;
          depotSelect.append(option);
        });

        const options = Array.from(depotSelect.options);
        if (!options.length) {
          return;
        }

        const wolverhamptonOption = options.find(option => {
          const text = String(option.textContent || "").trim().toLowerCase();
          return text === "wolverhampton" || text.includes("wolverhampton");
        });

        depotSelect.value = wolverhamptonOption ? wolverhamptonOption.value : options[0].value;
        await loadVehicles();
      } catch (error) {
        console.error(error);
      }
    }

    function getSlotIndex(slotId) {
      return Number.parseInt(slotId.split("-")[1], 10);
    }

    function getRowForIndex(index) {
      return Math.ceil(index / PALLET_COLUMNS);
    }

    function getColumnForIndex(index) {
      return ((index - 1) % PALLET_COLUMNS) + 1;
    }

    function getRowCounts(excludeOrderId) {
      const rowCounts = new Map();
      orders.forEach(order => {
        if (!order.slotId || order.id === excludeOrderId) return;
        const row = getRowForIndex(getSlotIndex(order.slotId));
        const counts = rowCounts.get(row) || { euro: 0, standard: 0 };
        if (order.palletType === "Standard") {
          counts.standard += 1;
        } else {
          counts.euro += 1;
        }
        rowCounts.set(row, counts);
      });
      return rowCounts;
    }

    function canAllocateOrderToSlot(order, slotIndex, slotElement = null) {
      if (!order || !slotIndex) {
        return false;
      }

      const column = getColumnForIndex(slotIndex);
      if (order.palletType === "Standard" && column === PALLET_COLUMNS) {
        return false;
      }

      const slot = slotElement || palletGrid.querySelector(`[data-slot-id="slot-${slotIndex}"]`);
      if (slot?.classList.contains("occupied")) {
        return false;
      }

      const row = getRowForIndex(slotIndex);
      const rowCounts = getRowCounts(order.id);
      const counts = rowCounts.get(row) || { euro: 0, standard: 0 };
      const nextStandard = counts.standard + (order.palletType === "Standard" ? 1 : 0);
      const nextEuro = counts.euro + (order.palletType === "Standard" ? 0 : 1);
      return nextStandard <= 2 && (nextStandard === 0 ? nextEuro <= 3 : (nextStandard === 1 ? nextEuro <= 1 : nextEuro === 0));
    }

    function processOrderDropToSlot(orderId, slotId) {
      if (!orderId || !slotId) {
        return false;
      }

      const order = getOrderById(orderId);
      if (!order) {
        return false;
      }

      const slotIndex = getSlotIndex(slotId);
      if (!canAllocateOrderToSlot(order, slotIndex)) {
        return false;
      }

      order.allocated = true;
      order.slotId = slotId;
      const reg = vehicleSelect.value || vehiclePlate.textContent || "";
      order.allocatedReg = String(reg).trim();
      const palletRow = getRowForIndex(slotIndex);
      const palletColumn = getColumnForIndex(slotIndex);
      updateAllocation(order.outgoingPalletId, reg, palletRow, palletColumn);
      renderOrders();
      renderGrid();
      return true;
    }

    function processOrderDropToOrders(orderId) {
      if (!orderId) {
        return false;
      }

      const order = getOrderById(orderId);
      if (!order) {
        return false;
      }

      order.allocated = false;
      order.slotId = null;
      order.allocatedReg = "";
      updateAllocation(order.outgoingPalletId, "", null, null);
      renderOrders();
      renderGrid();
      return true;
    }

    function clearTouchDropHighlights(state = activeTouchDrag) {
      if (state?.hoverSlot) {
        state.hoverSlot.classList.remove("drop-target");
      }

      if (state?.hoverOrders) {
        ordersContainer.style.outline = "";
        ordersContainer.style.outlineOffset = "";
      }
    }

    function removeTouchDragGhost(state = activeTouchDrag) {
      if (state?.ghostElement?.parentNode) {
        state.ghostElement.parentNode.removeChild(state.ghostElement);
      }
    }

    function resolveTouchScrollContainer(clientX, clientY) {
      const elementAtPoint = document.elementFromPoint(clientX, clientY);
      const pane = elementAtPoint?.closest?.('.pane');
      if (pane) {
        return pane;
      }

      const panes = Array.from(document.querySelectorAll('.pane'));
      return panes.find((candidate) => {
        const rect = candidate.getBoundingClientRect();
        return clientX >= rect.left && clientX <= rect.right;
      }) || panes[0] || null;
    }

    function updateTouchAutoScroll(clientX, clientY) {
      if (!activeTouchDrag) {
        return;
      }

      const scrollContainer = resolveTouchScrollContainer(clientX, clientY);
      activeTouchDrag.scrollContainer = scrollContainer;
      if (!scrollContainer) {
        activeTouchDrag.autoScrollSpeed = 0;
        return;
      }

      const rect = scrollContainer.getBoundingClientRect();
      let speed = 0;

      if (clientY < rect.top + touchEdgeScrollThreshold) {
        speed = -Math.ceil(((rect.top + touchEdgeScrollThreshold) - clientY) / 4);
      } else if (clientY > rect.bottom - touchEdgeScrollThreshold) {
        speed = Math.ceil((clientY - (rect.bottom - touchEdgeScrollThreshold)) / 4);
      }

      activeTouchDrag.autoScrollSpeed = Math.max(-touchEdgeScrollMaxStep, Math.min(touchEdgeScrollMaxStep, speed));
    }

    function updateTouchDropTarget(clientX, clientY) {
      if (!activeTouchDrag) {
        return;
      }

      clearTouchDropHighlights(activeTouchDrag);
      activeTouchDrag.hoverSlot = null;
      activeTouchDrag.hoverOrders = false;

      const elementAtPoint = document.elementFromPoint(clientX, clientY);
      const slot = elementAtPoint?.closest?.('.slot');
      const order = getOrderById(activeTouchDrag.payload?.orderId);

      if (slot && order) {
        const slotId = slot.dataset.slotId || "";
        const slotIndex = slotId ? getSlotIndex(slotId) : 0;
        if (slotIndex && canAllocateOrderToSlot(order, slotIndex, slot)) {
          activeTouchDrag.hoverSlot = slot;
          slot.classList.add('drop-target');
          return;
        }
      }

      if (elementAtPoint?.closest?.('#orders')) {
        activeTouchDrag.hoverOrders = true;
        ordersContainer.style.outline = '2px solid #2563eb';
        ordersContainer.style.outlineOffset = '-2px';
      }
    }

    function runTouchAutoScroll() {
      if (!activeTouchDrag) {
        return;
      }

      const speed = Number(activeTouchDrag.autoScrollSpeed || 0);
      const container = activeTouchDrag.scrollContainer;
      if (container && speed !== 0) {
        container.scrollTop += speed;
        updateTouchDropTarget(activeTouchDrag.lastTouchClientX, activeTouchDrag.lastTouchClientY);
      }

      activeTouchDrag.autoScrollFrame = window.requestAnimationFrame(runTouchAutoScroll);
    }

    function stopTouchAutoScroll(state = activeTouchDrag) {
      if (!state) {
        return;
      }

      if (state.autoScrollFrame) {
        window.cancelAnimationFrame(state.autoScrollFrame);
      }

      state.autoScrollFrame = null;
      state.autoScrollSpeed = 0;
      state.scrollContainer = null;
    }

    function shouldIgnoreTouchDragStart(eventTarget) {
      return !!eventTarget?.closest?.('button, select, option, input, textarea, a, label');
    }

    function onTouchStart(e) {
      if (!e.touches || e.touches.length !== 1) {
        return;
      }

      if (shouldIgnoreTouchDragStart(e.target)) {
        return;
      }

      const orderId = e.currentTarget?.dataset?.orderId;
      const order = getOrderById(orderId);
      const payload = createDragPayloadFromOrder(order);
      if (!payload) {
        return;
      }

      activeDragOrderId = orderId;
      const touch = e.touches[0];
      const source = e.currentTarget;
      const rect = source.getBoundingClientRect();
      const ghost = source.cloneNode(true);
      ghost.style.position = 'fixed';
      ghost.style.left = `${touch.clientX - (rect.width / 2)}px`;
      ghost.style.top = `${touch.clientY - Math.min(40, rect.height / 2)}px`;
      ghost.style.width = `${rect.width}px`;
      ghost.style.pointerEvents = 'none';
      ghost.style.opacity = '0.88';
      ghost.style.zIndex = '9999';
      ghost.style.boxShadow = '0 12px 28px rgba(0, 0, 0, 0.18)';
      document.body.appendChild(ghost);

      activeTouchDrag = {
        payload,
        sourceElement: source,
        ghostElement: ghost,
        hoverSlot: null,
        hoverOrders: false,
        autoScrollFrame: null,
        autoScrollSpeed: 0,
        scrollContainer: resolveTouchScrollContainer(touch.clientX, touch.clientY),
        lastTouchClientX: touch.clientX,
        lastTouchClientY: touch.clientY,
      };

      source.style.opacity = '0.45';
      updateTouchDropTarget(touch.clientX, touch.clientY);
      updateTouchAutoScroll(touch.clientX, touch.clientY);
      activeTouchDrag.autoScrollFrame = window.requestAnimationFrame(runTouchAutoScroll);
      e.preventDefault();
    }

    function onTouchMove(e) {
      if (!activeTouchDrag || !e.touches || e.touches.length !== 1) {
        return;
      }

      const touch = e.touches[0];
      activeTouchDrag.lastTouchClientX = touch.clientX;
      activeTouchDrag.lastTouchClientY = touch.clientY;

      const ghost = activeTouchDrag.ghostElement;
      if (ghost) {
        ghost.style.left = `${touch.clientX - (ghost.offsetWidth / 2)}px`;
        ghost.style.top = `${touch.clientY - Math.min(40, ghost.offsetHeight / 2)}px`;
      }

      updateTouchDropTarget(touch.clientX, touch.clientY);
      updateTouchAutoScroll(touch.clientX, touch.clientY);
      e.preventDefault();
    }

    function onTouchEnd() {
      if (!activeTouchDrag) {
        return;
      }

      const state = activeTouchDrag;
      activeTouchDrag = null;
      activeDragOrderId = null;

      stopTouchAutoScroll(state);
      clearTouchDropHighlights(state);
      removeTouchDragGhost(state);

      if (state.sourceElement) {
        state.sourceElement.style.opacity = '';
      }

      if (state.hoverSlot?.dataset?.slotId) {
        processOrderDropToSlot(state.payload?.orderId, state.hoverSlot.dataset.slotId);
        return;
      }

      if (state.hoverOrders) {
        processOrderDropToOrders(state.payload?.orderId);
      }
    }

    function bindTouchDraggable(container = document) {
      const elements = [];
      if (container?.matches?.('.pallet[data-order-id][draggable="true"]')) {
        elements.push(container);
      }

      if (container?.querySelectorAll) {
        container.querySelectorAll('.pallet[data-order-id][draggable="true"]').forEach((el) => {
          elements.push(el);
        });
      }

      elements.forEach((el) => {
        el.removeEventListener('touchstart', onTouchStart);
        el.removeEventListener('touchmove', onTouchMove);
        el.removeEventListener('touchend', onTouchEnd);
        el.removeEventListener('touchcancel', onTouchEnd);
        el.addEventListener('touchstart', onTouchStart, { passive: false });
        el.addEventListener('touchmove', onTouchMove, { passive: false });
        el.addEventListener('touchend', onTouchEnd);
        el.addEventListener('touchcancel', onTouchEnd);
      });
    }

    function renderOrders() {
      ordersContainer.innerHTML = "";
      orders.forEach(order => {
        const currentReg = normalizeReg(vehicleSelect.value || vehiclePlate.textContent || "");
        if (normalizeReg(order.allocatedReg) && normalizeReg(order.allocatedReg) !== currentReg) {
          return;
        }
        if (hideAllocated && order.allocated) {
          return;
        }
        const card = document.createElement("div");
        card.className = "order-card";
        const isFrozen = String(order.freshFrozen || "").trim().toUpperCase() === "FROZEN";
        card.dataset.orderId = order.id;
        if (order.id === selectedOrderId) {
          card.classList.add("selected");
        }
        card.addEventListener("click", (event) => {
          if (event.target.closest("button") || event.target.closest(".postcode-link")) {
            return;
          }
          selectedOrderId = order.id;
          renderOrders();
        });

        const info = document.createElement("div");
        info.className = "order-info";
        const title = document.createElement("h3");
        title.className = "order-title";
        title.textContent = order.title;
        if (isFrozen) {
          const frozenBadge = document.createElement("span");
          frozenBadge.className = "frozen-badge";
          frozenBadge.textContent = "Frozen";
          title.append(frozenBadge);
        }
        const sub = document.createElement("p");
        const customer = (order.customerName || "").trim();
        const address = (order.customerDeliveryAddress || "").trim();
        const postcode = (order.customerDeliveryPostcode || "").trim();
        if (postcode) {
          const customerSpan = document.createElement("span");
          customerSpan.textContent = customer ? `${customer} • ` : "";
          const addressSpan = document.createElement("span");
          addressSpan.textContent = address ? `${address} • ` : "";
          const postcodeSpan = document.createElement("span");
          postcodeSpan.className = "postcode-link";
          postcodeSpan.textContent = postcode;
          postcodeSpan.addEventListener("click", () => openMapModal(postcode));
          const pinSpan = document.createElement("span");
          pinSpan.className = "postcode-pin";
          pinSpan.textContent = "📍";
          sub.append(customerSpan, addressSpan, postcodeSpan, pinSpan);
        } else {
          sub.textContent = order.subtext;
        }
        const weight = document.createElement("p");
        weight.textContent = `Weight: ${order.weightKg} kg`;
        const contentsPreview = document.createElement("p");
        const contentSummary = buildContentSummary(order);
        if (contentSummary) {
          contentsPreview.className = "order-contents-summary";
          contentsPreview.textContent = contentSummary;
        }
        const status = document.createElement("div");
        status.className = "order-status";
        if (order.allocatedReg) {
          const regPlate = document.createElement("span");
          regPlate.className = "reg-plate";
          regPlate.textContent = order.allocatedReg;
          status.textContent = "Pallet Allocated";
          status.append(regPlate);
        } else {
          status.textContent = "Pallet Allocated";
        }
        if (order.allocated) {
          status.classList.add("visible");
        }
        info.append(title, sub, weight);
        if (contentSummary) {
          info.append(contentsPreview);
        }
        info.append(status);

        const palletControls = document.createElement("div");
        palletControls.className = "pallet-controls";

        const contentsBtn = document.createElement("button");
        contentsBtn.type = "button";
        contentsBtn.className = "contents-btn";
        contentsBtn.textContent = "Contents";
        contentsBtn.addEventListener("click", event => {
          event.stopPropagation();
          openContentsModal(order);
        });

        const typeToggle = document.createElement("div");
        typeToggle.className = "pallet-type";
        const euroBtn = document.createElement("button");
        euroBtn.type = "button";
        euroBtn.textContent = "Euro";
        euroBtn.className = order.palletType === "Euro" ? "active" : "";
        euroBtn.disabled = !!order.allocated;
        euroBtn.addEventListener("click", async () => {
          if (order.palletType === "Euro") {
            return;
          }
          const previousType = order.palletType;
          order.palletType = "Euro";
          renderOrders();
          renderGrid();

          const updated = await updatePalletType(order.outgoingPalletId, "Euro");
          if (!updated) {
            order.palletType = previousType;
            renderOrders();
            renderGrid();
            window.alert("Unable to update pallet type. Please try again.");
          }
        });
        const standardBtn = document.createElement("button");
        standardBtn.type = "button";
        standardBtn.textContent = "Standard";
        standardBtn.className = order.palletType === "Standard" ? "active" : "";
        standardBtn.disabled = !!order.allocated;
        standardBtn.addEventListener("click", async () => {
          if (order.palletType === "Standard") {
            return;
          }
          const previousType = order.palletType;
          order.palletType = "Standard";
          renderOrders();
          renderGrid();

          const updated = await updatePalletType(order.outgoingPalletId, "Standard");
          if (!updated) {
            order.palletType = previousType;
            renderOrders();
            renderGrid();
            window.alert("Unable to update pallet type. Please try again.");
          }
        });
        typeToggle.append(euroBtn, standardBtn);

        const pallet = document.createElement("div");
        pallet.className = `pallet ${order.palletType === "Standard" ? "standard" : "euro"}`;
        pallet.textContent = order.palletType === "Standard" ? "STD" : "EU";
        pallet.draggable = !order.allocated;
        pallet.dataset.orderId = order.id;
        pallet.addEventListener("dragstart", () => {
          activeDragOrderId = order.id;
        });
        pallet.addEventListener("dragend", () => {
          activeDragOrderId = null;
        });

        pallet.style.display = order.allocated ? "none" : "grid";
        palletControls.append(contentsBtn, typeToggle, pallet);
        card.append(info, palletControls);
        ordersContainer.append(card);
      });

      bindTouchDraggable(ordersContainer);
    }

    function renderGrid() {
      palletGrid.innerHTML = "";
      //rowWeightsEl.innerHTML = "";
      const maxRows = getMaxPalletRows();
      const maxSlots = getMaxSlotCount();
      //rowWeightsEl.style.gridTemplateRows = `repeat(${maxRows}, minmax(110px, 1fr))`;
      const totalWeight = orders.reduce((sum, order) => sum + (order.allocated ? order.weightKg : 0), 0);
      updateTotalWeightDisplay(totalWeight);
      const rowTotals = Array.from({ length: maxRows }, () => 0);
      orders.forEach(order => {
        if (!order.slotId) return;
        const slotIndex = getSlotIndex(order.slotId);
        const row = getRowForIndex(slotIndex);
        const column = getColumnForIndex(slotIndex);
        if (!isSlotWithinCapacity(row, column)) {
          return;
        }
        rowTotals[row - 1] += order.weightKg;
      });
      const slotMap = new Map();
      orders.forEach(order => {
        if (order.slotId) {
          const slotIndex = getSlotIndex(order.slotId);
          const row = getRowForIndex(slotIndex);
          const column = getColumnForIndex(slotIndex);
          if (isSlotWithinCapacity(row, column)) {
            slotMap.set(order.slotId, order);
          }
        }
      });
      for (let i = 1; i <= maxSlots; i += 1) {
        const slot = document.createElement("div");
        slot.className = "slot";
        slot.dataset.slotId = `slot-${i}`;
        if (i % PALLET_COLUMNS === 0) {
          slot.classList.add("euro-only");
        }
        const assignedOrder = slotMap.get(slot.dataset.slotId);
        if (assignedOrder) {
          slot.classList.add("occupied");
          const isFrozen = String(assignedOrder.freshFrozen || "").trim().toUpperCase() === "FROZEN";
          const slotContent = document.createElement("div");
          slotContent.className = "slot-content";

          const pallet = document.createElement("div");
          pallet.className = `pallet ${assignedOrder.palletType === "Standard" ? "standard" : "euro"}`;
          pallet.innerHTML = `<div>${assignedOrder.weightKg}kg</div><div>${assignedOrder.palletType === "Standard" ? "STD" : "EU"}</div>`;
          pallet.draggable = true;
          pallet.dataset.orderId = assignedOrder.id;
          pallet.addEventListener("dragstart", () => {
            activeDragOrderId = assignedOrder.id;
          });
          pallet.addEventListener("dragend", () => {
            activeDragOrderId = null;
          });
          const orderText = document.createElement("div");
          orderText.className = "slot-order";
          orderText.textContent = assignedOrder.title;

          const subText = document.createElement("div");
          subText.className = "slot-subtext";
          subText.textContent = assignedOrder.subtext;

          slotContent.append(pallet, orderText, subText);

          if (isFrozen) {
            const frozenBadge = document.createElement("div");
            frozenBadge.className = "slot-frozen-badge";
            frozenBadge.textContent = "Frozen";
            slotContent.append(frozenBadge);
          }

          const slotContentSummary = buildContentSummary(assignedOrder);
          if (slotContentSummary) {
            const contentsDiv = document.createElement("div");
            contentsDiv.className = "slot-contents";
            contentsDiv.textContent = slotContentSummary;
            slotContent.append(contentsDiv);
          }

          slot.append(slotContent);
        }
        slot.addEventListener("dragover", event => {
          if (!activeDragOrderId) return;
          const order = orders.find(item => item.id === activeDragOrderId);
          if (!order) return;
          if (!canAllocateOrderToSlot(order, i, slot)) {
            return;
          }
          event.preventDefault();
          slot.classList.add("drop-target");
        });
        slot.addEventListener("dragleave", () => {
          slot.classList.remove("drop-target");
        });
        slot.addEventListener("drop", event => {
          event.preventDefault();
          slot.classList.remove("drop-target");
          if (!activeDragOrderId) {
            return;
          }
          processOrderDropToSlot(activeDragOrderId, slot.dataset.slotId);
        });
        palletGrid.append(slot);
      }

      rowTotals.forEach((total, idx) => {
        const weight = document.createElement("div");
        weight.className = "row-weight";
        weight.textContent = `${total} kg`;
        //rowWeightsEl.append(weight);
      });

      requestAnimationFrame(() => {
        const weightCells = rowWeightsEl.querySelectorAll(".row-weight");
        weightCells.forEach((cell, index) => {
          const slotIndex = index * PALLET_COLUMNS + 1;
          const slot = palletGrid.querySelector(`[data-slot-id="slot-${slotIndex}"]`);
          if (slot) {
            cell.style.height = `${slot.offsetHeight}px`;
          }
        });
      });

      bindTouchDraggable(palletGrid);
    }

    ordersContainer.addEventListener("dragover", event => {
      event.preventDefault();
    });

    ordersContainer.addEventListener("drop", event => {
      event.preventDefault();
      if (!activeDragOrderId) {
        return;
      }
      processOrderDropToOrders(activeDragOrderId);
    });

    sortBySelect.value = "postcode";
    sortBySelect.addEventListener("change", (event) => {
      const value = event.target.value;
      if (value === "name") {
        orders.sort((a, b) => a.customerName.localeCompare(b.customerName));
      } else {
        orders.sort((a, b) => a.customerDeliveryPostcode.localeCompare(b.customerDeliveryPostcode));
      }
      renderOrders();
    });

    toggleAllocatedBtn.addEventListener("click", () => {
      hideAllocated = !hideAllocated;
      toggleAllocatedBtn.classList.toggle("active", hideAllocated);
      toggleAllocatedBtn.textContent = hideAllocated ? "Show Allocated" : "Hide Allocated";
      renderOrders();
    });

    vehicleSelect.addEventListener("change", async () => {
      vehiclePlate.textContent = vehicleSelect.value;
      await loadVehicleDetails(vehicleSelect.value);
      await loadOrders();
    });

    const deliveryDateInput = document.getElementById("deliveryDate");
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    deliveryDateInput.value = tomorrow.toISOString().split("T")[0];
    deliveryDateInput.addEventListener("change", () => {
      loadOrders();
    });
    depotSelect.addEventListener("change", async () => {
      await loadVehicles();
    });

    document.getElementById("loadCompleteBtn").addEventListener("click", async () => {
      const confirmed = window.confirm("Are you sure you want to complete this Vehicle Load and generate the PODs?");
      if (!confirmed) {
        return;
      }

      const reg = vehicleSelect.value || vehiclePlate.textContent || "";
      const dueDate = document.getElementById("deliveryDate").value || "";

      if (!dueDate) {
        window.alert("Select a delivery date before completing the load.");
        return;
      }

      // Collect outgoing pallet IDs from allocated orders
      const outgoingPalletIds = orders
        .filter(order => order.allocated)
        .map(order => order.outgoingPalletId)
        .filter(id => id > 0);

      if (!outgoingPalletIds.length) {
        window.alert("No pallets allocated to this vehicle.");
        return;
      }

      try {
        const response = await fetch("{{ route('outgoing-pallets-loading.commit-allocations') }}", {
          method: "POST",
          headers: jsonHeaders(),
          body: JSON.stringify({ reg, outgoingPalletIds, dueDate })
        });
        if (!response.ok) {
          const errorText = await response.text();
          throw new Error(errorText || "Commit failed");
        }
        const data = await response.json();
        window.alert(`Load committed (${data.committedCount || 0} allocations).`);
      } catch (error) {
        window.alert("Load commit failed. Check server logs.");
        console.error(error);
      }
    });

    printLoadBtn.addEventListener("click", () => {
      const reg = vehicleSelect.value || vehiclePlate.textContent || "";
      const dueDate = document.getElementById("deliveryDate").value || "";
      const depot = depotSelect.value || "";

      if (!reg || !depot) {
        window.alert("Select a depot and vehicle before printing.");
        return;
      }

      const url = `{{ route('outgoing-pallets-loading.print-truck-load') }}?reg=${encodeURIComponent(reg)}&dueDate=${encodeURIComponent(dueDate)}&depot=${encodeURIComponent(depot)}`;
      window.open(url, "_blank", "noopener");
    });

    let resizeTimer;
    window.addEventListener("resize", () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        renderGrid();
      }, 100);
    });

    async function openVehicleModal() {
      const reg = vehicleSelect.value || vehiclePlate.textContent;
      vehicleModalBody.innerHTML = "";
      try {
        const vehicle = await loadVehicleDetails(reg);
        const rows = [
          ["Registration", vehicle.reg || reg],
          ["Type", vehicle.type || ""],
          ["Make", vehicle.make || ""],
          ["Model", vehicle.model || ""],
          ["Gross Weight", vehicle.grossWeight || ""],
          ["Payload", vehicle.payload || ""],
          ["Max Pallet Rows", `${normalizeMaxPalletRows(vehicle.maxPalletRows)}`],
          ["Max Pallets", `${normalizeMaxPalletRows(vehicle.maxPalletRows) * PALLET_COLUMNS}`],
          ["Depot", vehicle.site || ""],
          ["Driver", vehicle.driver || ""]
        ];
        rows.forEach(([label, value]) => {
          const row = document.createElement("div");
          row.className = "modal-row";
          row.innerHTML = `<strong>${label}</strong><div>${value}</div>`;
          vehicleModalBody.append(row);
        });
      } catch (error) {
        const row = document.createElement("div");
        row.className = "modal-row";
        row.innerHTML = `<strong>Error</strong><div>${error.message}</div>`;
        vehicleModalBody.append(row);
      }
      vehicleModal.classList.add("open");
    }

    function closeVehicleModal() {
      vehicleModal.classList.remove("open");
    }

    function openMapModal(postcode) {
      mapModalTitle.textContent = `Location: ${postcode}`;
      mapFrame.src = `https://www.google.com/maps?q=${encodeURIComponent(postcode)}&output=embed`;
      mapModal.classList.add("open");
    }

    function closeMapModal() {
      mapModal.classList.remove("open");
      mapFrame.src = "";
    }

    function openAiPlanModal(content) {
      aiPlanBody.innerHTML = "";
      const row = document.createElement("div");
      row.className = "modal-row";
      row.innerHTML = `<strong>Itinerary</strong><div style="white-space: pre-wrap;">${content}</div>`;
      aiPlanBody.append(row);
      aiPlanModal.classList.add("open");
    }

    function closeAiPlanModal() {
      aiPlanModal.classList.remove("open");
    }

    vehiclePlate.addEventListener("click", openVehicleModal);
    vehicleModalClose.addEventListener("click", closeVehicleModal);
    vehicleModal.addEventListener("click", event => {
      if (event.target === vehicleModal) {
        closeVehicleModal();
      }
    });

    mapModalClose.addEventListener("click", closeMapModal);
    mapModal.addEventListener("click", event => {
      if (event.target === mapModal) {
        closeMapModal();
      }
    });

    if (aiPlanBtn) {
      aiPlanBtn.addEventListener("click", async () => {
      const allocatedOrders = orders.filter(order => order.allocated);
      const postcodes = Array.from(new Set(allocatedOrders
        .map(order => (order.customerDeliveryPostcode || "").trim())
        .filter(Boolean)));

      if (!postcodes.length) {
        window.alert("No allocated postcodes to plan.");
        return;
      }

      aiPlanBtn.disabled = true;
      aiPlanBtn.textContent = "Planning...";
      try {
        const response = await fetch("{{ route('outgoing-pallets-loading.ai-plan') }}", {
          method: "POST",
          headers: jsonHeaders(),
          body: JSON.stringify({
            startPostcode: "WV2 2QJ",
            stopMinutes: 20,
            postcodes
          })
        });
        if (!response.ok) {
          const errorText = await response.text();
          throw new Error(errorText || "AI plan unavailable");
        }
        const data = await response.json();
        openAiPlanModal(data.itinerary || "No itinerary returned.");
      } catch (error) {
        window.alert("AI plan failed. Check server logs.");
        console.error(error);
      } finally {
        aiPlanBtn.disabled = false;
        aiPlanBtn.textContent = "AI Plan";
      }
      });
    }

    if (aiPlanClose) {
      aiPlanClose.addEventListener("click", closeAiPlanModal);
    }
    if (aiPlanModal) {
      aiPlanModal.addEventListener("click", event => {
        if (event.target === aiPlanModal) {
          closeAiPlanModal();
        }
      });
    }

    if (contentsModalClose) {
      contentsModalClose.addEventListener("click", closeContentsModal);
    }
    if (contentsModal) {
      contentsModal.addEventListener("click", event => {
        if (event.target === contentsModal) {
          closeContentsModal();
        }
      });
    }

    renderOrders();
    renderGrid();
    loadDepots();
  </script>
</body>
</html>
