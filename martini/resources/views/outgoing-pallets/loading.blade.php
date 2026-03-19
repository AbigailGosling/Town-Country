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
      gap: 0;
      height: calc(100vh - 100px);
      margin-top: 100px;
    }
    .pane {
      overflow-y: auto;
      padding: 1.5rem;
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
      grid-template-columns: 1fr 110px;
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
    }
    .slot-order {
      font-size: 0.75rem;
      font-weight: 700;
      color: #111827;
      text-align: center;
    }
    .slot-subtext {
      font-size: 0.7rem;
      color: #6b7280;
      text-align: center;
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
    .map-frame {
      width: 100%;
      height: 460px;
      border: 0;
      border-radius: 0.6rem;
    }
    @media (max-width: 900px) {
      body {
        grid-template-rows: 220px 1fr;
      }
      .main {
        grid-template-columns: 1fr;
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
            <div class="total-weight" id="totalWeight">0 kg</div>
          </div>
          <div class="truck-actions">
            <button class="load-complete-btn" id="loadCompleteBtn" type="button">Load Complete</button>
            <button class="ai-plan-btn" id="aiPlanBtn" type="button" disabled>AI Plan</button>
          </div>
        </div>
        <div class="grid-wrapper">
          <div class="grid" id="palletGrid"></div>
          <div class="row-weights" id="rowWeights"></div>
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

  <script>
    let orders = [];

    const ordersContainer = document.getElementById("orders");
    const palletGrid = document.getElementById("palletGrid");
    const rowWeightsEl = document.getElementById("rowWeights");
    const totalWeightEl = document.getElementById("totalWeight");
    const payloadBadge = document.getElementById("payloadBadge");
    const sortBySelect = document.getElementById("sortBy");
    const toggleAllocatedBtn = document.getElementById("toggleAllocatedBtn");
    const depotSelect = document.getElementById("depotSelect");
    const vehicleSelect = document.getElementById("vehicleSelect");
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
    let activeDragOrderId = null;
    let hideAllocated = false;
    let selectedOrderId = null;
    let currentPayload = null;

    let vehicleInfo = null;

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
      totalWeightEl.textContent = `Total payload: ${totalWeight} kg (${tonnesText} t)`;
      payloadBadge.textContent = `Payload: ${formatPayload(currentPayload)}`;
    }

    function normalizePalletType(value) {
      if (!value) return "Euro";
      const trimmed = String(value).trim().toLowerCase();
      return trimmed.startsWith("s") ? "Standard" : "Euro";
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
        if (!outgoingPalletId || !row || !column) {
          return;
        }
        const slotIndex = (row - 1) * 3 + column;
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

    async function loadVehicles() {
      try {
        const response = await fetch("{{ route('outgoing-pallets-loading.vehicles') }}");
        if (!response.ok) {
          throw new Error("Vehicle list unavailable");
        }
        const data = await response.json();
        const vehicles = Array.isArray(data.vehicles) ? data.vehicles : [];
        if (!vehicles.length) {
          return;
        }
        vehicleSelect.innerHTML = "";
        vehicles.forEach(reg => {
          const option = document.createElement("option");
          option.value = reg;
          option.textContent = reg;
          vehicleSelect.append(option);
        });
        vehicleSelect.value = vehicles[0];
        vehiclePlate.textContent = vehicles[0];
        await loadVehicleDetails(vehicles[0]);
      } catch (error) {
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
        currentPayload = vehicleInfo.payload ?? null;
        const totalWeight = orders.reduce((sum, order) => sum + (order.allocated ? order.weightKg : 0), 0);
        updateTotalWeightDisplay(totalWeight);
        return vehicleInfo;
      } catch (error) {
        console.error(error);
        vehicleInfo = { reg };
        currentPayload = null;
        const totalWeight = orders.reduce((sum, order) => sum + (order.allocated ? order.weightKg : 0), 0);
        updateTotalWeightDisplay(totalWeight);
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
        orders = incoming.map((order, index) => {
          const allocatedToSelected = order.regAllocatedTo && order.regAllocatedTo === reg;
          const row = Number(order.row) || 0;
          const column = Number(order.column) || 0;
          const hasSlot = allocatedToSelected && row > 0 && column > 0;
          const slotId = hasSlot ? `slot-${(row - 1) * 3 + column}` : null;
          return {
          id: order.id || `order-${index + 1}`,
          outgoingPalletId: Number(order.outgoingPalletId) || null,
          title: order.title || `Order ${order.deliveryNoteNumber || index + 1}`,
          subtext: order.subtext || "",
          customerName: order.customerName || "",
          customerDeliveryAddress: order.customerDeliveryAddress || "",
          customerDeliveryPostcode: order.customerDeliveryPostcode || "",
          deliveryNoteNumber: order.deliveryNoteNumber || "",
          palletType: normalizePalletType(order.palletType),
          weightKg: Number(order.weightKg) || 0,
          freshFrozen: order.freshFrozen || "",
            allocatedReg: order.regAllocatedTo || "",
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
        depots.forEach(name => {
          const option = document.createElement("option");
          option.value = name;
          option.textContent = name;
          depotSelect.append(option);
        });
        depotSelect.value = depots.includes("WOLVES") ? "WOLVES" : depots[0];
        loadOrders();
      } catch (error) {
        console.error(error);
      }
    }

    function getSlotIndex(slotId) {
      return Number.parseInt(slotId.split("-")[1], 10);
    }

    function getRowForIndex(index) {
      return Math.ceil(index / 3);
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

    function renderOrders() {
      ordersContainer.innerHTML = "";
      orders.forEach(order => {
        const currentReg = vehicleSelect.value || vehiclePlate.textContent || "";
        if (order.allocatedReg && order.allocatedReg !== currentReg) {
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
        info.append(title, sub, weight, status);

        const palletControls = document.createElement("div");
        palletControls.className = "pallet-controls";

        const typeToggle = document.createElement("div");
        typeToggle.className = "pallet-type";
        const euroBtn = document.createElement("button");
        euroBtn.type = "button";
        euroBtn.textContent = "Euro";
        euroBtn.className = order.palletType === "Euro" ? "active" : "";
        euroBtn.disabled = !!order.allocated;
        euroBtn.addEventListener("click", () => {
          order.palletType = "Euro";
          renderOrders();
          renderGrid();
        });
        const standardBtn = document.createElement("button");
        standardBtn.type = "button";
        standardBtn.textContent = "Standard";
        standardBtn.className = order.palletType === "Standard" ? "active" : "";
        standardBtn.disabled = !!order.allocated;
        standardBtn.addEventListener("click", () => {
          order.palletType = "Standard";
          renderOrders();
          renderGrid();
        });
        typeToggle.append(euroBtn, standardBtn);

        const pallet = document.createElement("div");
        pallet.className = `pallet ${order.palletType === "Standard" ? "standard" : "euro"}`;
        pallet.textContent = order.palletType === "Standard" ? "STD" : "EU";
        pallet.draggable = !order.allocated;
        pallet.addEventListener("dragstart", () => {
          activeDragOrderId = order.id;
        });
        pallet.addEventListener("dragend", () => {
          activeDragOrderId = null;
        });

        pallet.style.display = order.allocated ? "none" : "grid";
        palletControls.append(typeToggle, pallet);
        card.append(info, palletControls);
        ordersContainer.append(card);
      });
    }

    function renderGrid() {
      palletGrid.innerHTML = "";
      rowWeightsEl.innerHTML = "";
      const totalWeight = orders.reduce((sum, order) => sum + (order.allocated ? order.weightKg : 0), 0);
      updateTotalWeightDisplay(totalWeight);
      const rowTotals = Array.from({ length: 10 }, () => 0);
      orders.forEach(order => {
        if (!order.slotId) return;
        const row = getRowForIndex(getSlotIndex(order.slotId));
        rowTotals[row - 1] += order.weightKg;
      });
      const slotMap = new Map();
      orders.forEach(order => {
        if (order.slotId) {
          slotMap.set(order.slotId, order);
        }
      });
      for (let i = 1; i <= 30; i += 1) {
        const slot = document.createElement("div");
        slot.className = "slot";
        slot.dataset.slotId = `slot-${i}`;
        if (i % 3 === 0) {
          slot.classList.add("euro-only");
        }
        const assignedOrder = slotMap.get(slot.dataset.slotId);
        if (assignedOrder) {
          slot.classList.add("occupied");
          const pallet = document.createElement("div");
          pallet.className = `pallet ${assignedOrder.palletType === "Standard" ? "standard" : "euro"}`;
          pallet.innerHTML = `<div>${assignedOrder.weightKg}kg</div><div>${assignedOrder.palletType === "Standard" ? "STD" : "EU"}</div>`;
          pallet.draggable = true;
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

          slot.append(pallet, orderText, subText);
        }
        slot.addEventListener("dragover", event => {
          if (!activeDragOrderId) return;
          const order = orders.find(item => item.id === activeDragOrderId);
          if (!order) return;
          const column = (i - 1) % 3 + 1;
          if (order.palletType === "Standard" && column === 3) {
            return;
          }
          const row = getRowForIndex(i);
          const rowCounts = getRowCounts(order.id);
          const counts = rowCounts.get(row) || { euro: 0, standard: 0 };
          const nextStandard = counts.standard + (order.palletType === "Standard" ? 1 : 0);
          const nextEuro = counts.euro + (order.palletType === "Standard" ? 0 : 1);
          const valid = nextStandard <= 2 && (nextStandard === 0 ? nextEuro <= 3 : (nextStandard === 1 ? nextEuro <= 1 : nextEuro === 0));
          if (!valid) {
            return;
          }
          if (!slot.classList.contains("occupied")) {
            event.preventDefault();
            slot.classList.add("drop-target");
          }
        });
        slot.addEventListener("dragleave", () => {
          slot.classList.remove("drop-target");
        });
        slot.addEventListener("drop", event => {
          event.preventDefault();
          slot.classList.remove("drop-target");
          if (!activeDragOrderId || slot.classList.contains("occupied")) {
            return;
          }
          const order = orders.find(item => item.id === activeDragOrderId);
          if (order) {
            const column = (i - 1) % 3 + 1;
            if (order.palletType === "Standard" && column === 3) {
              return;
            }
            const row = getRowForIndex(i);
            const rowCounts = getRowCounts(order.id);
            const counts = rowCounts.get(row) || { euro: 0, standard: 0 };
            const nextStandard = counts.standard + (order.palletType === "Standard" ? 1 : 0);
            const nextEuro = counts.euro + (order.palletType === "Standard" ? 0 : 1);
            const valid = nextStandard <= 2 && (nextStandard === 0 ? nextEuro <= 3 : (nextStandard === 1 ? nextEuro <= 1 : nextEuro === 0));
            if (!valid) {
              return;
            }
            order.allocated = true;
            order.slotId = slot.dataset.slotId;
            const reg = vehicleSelect.value || vehiclePlate.textContent || "";
            order.allocatedReg = reg;
            const palletRow = getRowForIndex(i);
            const palletColumn = column;
            updateAllocation(order.outgoingPalletId, reg, palletRow, palletColumn);
          }
          renderOrders();
          renderGrid();
        });
        palletGrid.append(slot);
      }

      rowTotals.forEach((total, idx) => {
        const weight = document.createElement("div");
        weight.className = "row-weight";
        weight.textContent = `${total} kg`;
        rowWeightsEl.append(weight);
      });

      requestAnimationFrame(() => {
        const weightCells = rowWeightsEl.querySelectorAll(".row-weight");
        weightCells.forEach((cell, index) => {
          const slotIndex = index * 3 + 1;
          const slot = palletGrid.querySelector(`[data-slot-id="slot-${slotIndex}"]`);
          if (slot) {
            cell.style.height = `${slot.offsetHeight}px`;
          }
        });
      });
    }

    ordersContainer.addEventListener("dragover", event => {
      event.preventDefault();
    });

    ordersContainer.addEventListener("drop", event => {
      event.preventDefault();
      if (!activeDragOrderId) {
        return;
      }
      const order = orders.find(item => item.id === activeDragOrderId);
      if (order) {
        order.allocated = false;
        order.slotId = null;
        order.allocatedReg = "";
        updateAllocation(order.outgoingPalletId, "", null, null);
      }
      renderOrders();
      renderGrid();
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
    depotSelect.addEventListener("change", () => {
      loadOrders();
    });

    document.getElementById("loadCompleteBtn").addEventListener("click", async () => {
      const confirmed = window.confirm("Are you sure you want to complete this Vehicle Load and generate the PODs?");
      if (!confirmed) {
        return;
      }

      const reg = vehicleSelect.value || vehiclePlate.textContent || "";
      const dueDate = document.getElementById("deliveryDate").value || "";
      const depot = depotSelect.value || "";

      try {
        const response = await fetch("{{ route('outgoing-pallets-loading.commit-allocations') }}", {
          method: "POST",
          headers: jsonHeaders(),
          body: JSON.stringify({ reg, dueDate, depot })
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
          ["Depot", vehicle.depot || ""],
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

    aiPlanClose.addEventListener("click", closeAiPlanModal);
    aiPlanModal.addEventListener("click", event => {
      if (event.target === aiPlanModal) {
        closeAiPlanModal();
      }
    });

    renderOrders();
    renderGrid();
    loadVehicles();
    loadDepots();
  </script>
</body>
</html>
