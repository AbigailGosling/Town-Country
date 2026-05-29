<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
  <meta name="apple-mobile-web-app-title" content="Intake Pallet Scanner">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Intake Pallet Scanner</title>
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
      min-height: 100vh;
      background: #f6f7fb;
      color: #111827;
      overflow: hidden;
    }
    button,
    input,
    select,
    textarea {
      font: inherit;
    }
    button {
      cursor: pointer;
    }
    button:disabled {
      cursor: not-allowed;
      opacity: 0.6;
    }
    .top-bar {
      min-height: 108px;
      padding: 1rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      background: #fff;
      position: fixed;
      inset: 0 0 auto 0;
      z-index: 10;
    }
    .top-controls {
      display: flex;
      align-items: flex-end;
      gap: 1rem;
      flex-wrap: wrap;
      flex: 1 1 auto;
    }
    .control-group {
      min-width: 170px;
      flex: 1 1 170px;
      max-width: 240px;
    }
    .control-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.35rem;
      color: #374151;
    }
    .top-bar select,
    .top-bar input {
      width: 100%;
      padding: 0.5rem 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid rgba(0, 0, 0, 0.2);
      font-size: 1rem;
      background: #fff;
      color: inherit;
      min-width: 0;
    }
    .page-title {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 0.25rem;
      text-align: right;
      min-width: 240px;
    }
    .page-title h1 {
      margin: 0;
      font-size: 1.8rem;
      line-height: 1.1;
    }
    .page-title p {
      margin: 0;
      color: #6b7280;
      font-size: 0.95rem;
      max-width: 360px;
    }
    .main {
      display: grid;
      grid-template-columns: minmax(320px, 1.05fr) minmax(360px, 1fr);
      gap: 0;
      height: calc(100vh - 108px);
      margin-top: 108px;
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
    .stack {
      display: grid;
      gap: 1rem;
    }
    .panel-card {
      background: #fff;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 0.9rem;
      padding: 1rem 1.1rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    }
    .panel-card h2 {
      margin: 0 0 0.35rem;
      font-size: 1.08rem;
      color: #111827;
    }
    .panel-card p {
      margin: 0;
      color: #6b7280;
      line-height: 1.45;
    }
    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 0.75rem;
      margin-bottom: 0.9rem;
    }
    .badge-row {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
    .payload-badge,
    .total-weight,
    .summary-badge {
      font-weight: 600;
      color: #111827;
      border-radius: 999px;
      padding: 0.3rem 0.8rem;
      font-size: 0.85rem;
      white-space: nowrap;
    }
    .payload-badge {
      background: rgba(37, 99, 235, 0.12);
    }
    .total-weight {
      background: rgba(17, 24, 39, 0.06);
    }
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 0.75rem;
    }
    .summary-card {
      border-radius: 0.75rem;
      background: #f9fafb;
      border: 1px solid rgba(0, 0, 0, 0.08);
      padding: 0.85rem;
    }
    .summary-card strong {
      display: block;
      font-size: 1.45rem;
      line-height: 1;
      margin-bottom: 0.2rem;
    }
    .summary-card span {
      color: #6b7280;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .instruction-list {
      margin: 0;
      padding-left: 1.2rem;
      color: #374151;
      line-height: 1.45;
    }
    .instruction-list li + li {
      margin-top: 0.4rem;
    }
    .camera-block {
      border: 1px solid rgba(0, 0, 0, 0.12);
      padding: 1rem;
      border-radius: 0.85rem;
      background: #fdfdfd;
      position: relative;
      transition: box-shadow 0.2s;
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.5);
    }
    .camera-block.flash {
      animation: camera-flash 0.22s cubic-bezier(.4,0,.2,1);
    }
    @keyframes camera-flash {
      0% { box-shadow: 0 0 0 0 rgba(255,255,255,0.7); }
      60% { box-shadow: 0 0 0 12px rgba(255,255,255,0.7); }
      100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); }
    }
    video {
      width: 100%;
      border-radius: 0.7rem;
      border: 1px solid rgba(0, 0, 0, 0.14);
      background: #000;
      max-height: 62vh;
      aspect-ratio: 4 / 5;
      object-fit: cover;
      display: block;
    }
    .camera-actions,
    .action-row {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
    }
    .camera-actions {
      margin-top: 0.9rem;
    }
    .camera-actions button,
    .action-row button {
      border: none;
      border-radius: 0.5rem;
      padding: 0.7rem 0.95rem;
      font-weight: 600;
      color: #fff;
      box-shadow: 0 6px 14px rgba(17, 24, 39, 0.16);
      flex: 1 1 150px;
    }
    #cameraStartBtn {
      background: #111827;
    }
    #cameraSnapBtn,
    .btn-success {
      background: #16a34a;
    }
    #cameraStopBtn,
    .btn-danger {
      background: #dc2626;
    }
    .camera-status {
      display: block;
      margin-top: 0.8rem;
      font-weight: 600;
      color: #4b5563;
    }
    .preview {
      display: grid;
      gap: 0.75rem;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      margin-top: 0.25rem;
    }
    .preview figure {
      margin: 0;
      position: relative;
      background: #fff;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 0.85rem;
      padding: 0.5rem;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .preview figure.duplicate {
      border-color: rgba(234, 88, 12, 0.55);
      box-shadow: 0 10px 24px rgba(234, 88, 12, 0.14);
    }
    .preview img {
      width: 100%;
      border-radius: 0.6rem;
      border: 1px solid rgba(0, 0, 0, 0.08);
      object-fit: cover;
      aspect-ratio: 1 / 1;
      height: auto;
      cursor: zoom-in;
      background: #111827;
    }
    .preview figcaption {
      font-size: 0.85rem;
      margin-top: 0.45rem;
      color: #374151;
      font-weight: 600;
    }
    .preview button.remove {
      position: absolute;
      top: 0.85rem;
      right: 0.85rem;
      background: rgba(17, 24, 39, 0.78);
      color: #fff;
      border: none;
      padding: 0.35rem 0.6rem;
      font-size: 1rem;
      line-height: 1;
      min-width: 32px;
      min-height: 32px;
      border-radius: 999px;
      box-shadow: none;
    }
    .warning-message,
    .progress-message,
    .status-message,
    .success-message {
      margin-top: 0.75rem;
      padding: 0.9rem 1rem;
      border-radius: 0.75rem;
      font-weight: 600;
      border: 1px solid transparent;
    }
    .warning-message {
      background: rgba(234, 88, 12, 0.12);
      border-color: rgba(234, 88, 12, 0.35);
      color: #9a3412;
    }
    .progress-message {
      background: rgba(59, 130, 246, 0.12);
      border-color: rgba(59, 130, 246, 0.35);
      color: #1d4ed8;
    }
    .status-message {
      background: rgba(107, 114, 128, 0.12);
      border-color: rgba(107, 114, 128, 0.35);
      color: #374151;
    }
    .success-message {
      background: rgba(22, 163, 74, 0.12);
      border-color: rgba(22, 163, 74, 0.35);
      color: #166534;
    }
    .success-message a {
      color: #166534;
      text-decoration: underline;
      cursor: pointer;
    }
    pre {
      white-space: pre-wrap;
      background: rgba(17, 24, 39, 0.06);
      padding: 1rem;
      border-radius: 0.75rem;
      overflow-x: auto;
      border: 1px solid rgba(17, 24, 39, 0.08);
      color: #1f2937;
      min-height: 160px;
      margin: 0;
    }
    textarea {
      width: 100%;
      padding: 0.8rem 0.9rem;
      border-radius: 0.75rem;
      border: 1px solid rgba(0, 0, 0, 0.15);
      resize: vertical;
      background: #fff;
      color: inherit;
    }
    .helper-copy {
      color: #6b7280;
      font-size: 0.92rem;
      line-height: 1.45;
    }
    .image-modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      z-index: 2000;
    }
    .image-modal.open {
      display: flex;
    }
    .image-modal img {
      max-width: min(95vw, 900px);
      max-height: 85vh;
      border-radius: 0.7rem;
      object-fit: contain;
      box-shadow: 0 8px 24px rgba(0,0,0,0.35);
      background: #111;
    }
    .image-modal .close {
      position: absolute;
      top: 12px;
      right: 12px;
      width: 40px;
      height: 40px;
      border-radius: 999px;
      border: none;
      background: rgba(255,255,255,0.2);
      color: #fff;
      font-size: 1.5rem;
      line-height: 1;
      cursor: pointer;
    }
    .modal-card {
      background: #fff;
      color: #111827;
      border-radius: 0.85rem;
      padding: 1.4rem;
      max-width: 640px;
      width: 100%;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }
    .modal-card h3 {
      margin: 0 0 0.5rem;
      font-size: 1.2rem;
    }
    .modal-card p {
      margin: 0;
      color: #4b5563;
      line-height: 1.45;
    }
    @media (max-width: 1100px) {
      .top-bar {
        position: static;
      }
      .main {
        margin-top: 0;
        height: auto;
        min-height: calc(100vh - 108px);
      }
    }
    @media (max-width: 900px) {
      body {
        overflow: auto;
      }
      .top-bar {
        align-items: flex-start;
        flex-direction: column;
      }
      .page-title {
        align-items: flex-start;
        text-align: left;
        min-width: 0;
      }
      .main {
        grid-template-columns: 1fr;
      }
      .left-pane {
        border-right: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      }
      .summary-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header class="top-bar">
    <div class="top-controls">
      <div class="control-group">
        <label for="depotId">Depot</label>
        <select id="depotId" autocomplete="off"></select>
      </div>
      <div class="control-group">
        <label for="intakeId">Intake ID</label>
        <select id="intakeId" autocomplete="off" required></select>
      </div>
      <div class="control-group" id="boxesWrapper" hidden>
        <label for="boxCount">Boxes Expected</label>
        <input id="boxCount" type="number" min="1" max="100" step="1" inputmode="numeric" placeholder="1-100">
      </div>
    </div>
    <div class="page-title">
      <h1>Intake Scanner</h1>
      <p>Capture one clear label per box, review the gallery, then queue the pallet for background AI extraction.</p>
    </div>
  </header>

  <main class="main">
    <section class="pane left-pane">
      <div class="stack">
        <section class="panel-card">
          <div class="panel-header">
            <div>
              <h2>Workflow</h2>
              <p>Match the loading page structure, while keeping the scanner steps explicit for mobile use.</p>
            </div>
            <div class="badge-row">
              <span class="payload-badge" id="capturedCountBadge">0 photos</span>
              <span class="total-weight" id="selectedIntakeBadge">No intake selected</span>
            </div>
          </div>
          <div class="summary-grid">
            <div class="summary-card">
              <strong id="summaryExpectedCount">0</strong>
              <span>Expected Boxes</span>
            </div>
            <div class="summary-card">
              <strong id="summaryCapturedCount">0</strong>
              <span>Captured Labels</span>
            </div>
            <div class="summary-card">
              <strong id="summaryDuplicateCount">0</strong>
              <span>Duplicates Flagged</span>
            </div>
          </div>
        </section>

        <section class="panel-card">
          <h2>Capture Guidance</h2>
          <ol class="instruction-list">
            <li>Select the depot and intake before starting the camera.</li>
            <li>Enter the number of boxes so the scanner can warn when too many labels are captured.</li>
            <li>Take one large, in-focus photo for each delivery label.</li>
            <li>Remove duplicates or retake unclear images before sending the pallet.</li>
          </ol>
        </section>

        <section class="panel-card">
          <div class="panel-header">
            <div>
              <h2>Captured Labels</h2>
              <p>Tap any thumbnail to inspect it full size before upload.</p>
            </div>
          </div>
          <div class="preview" id="preview"></div>
          <div id="boxCountWarning" class="warning-message" hidden>
            You have scanned too many boxes, or the box count is wrong. Remove duplicate photos or change the expected box count.
          </div>
          <div class="action-row" style="margin-top: 1rem;">
            <button id="clearImagesBtn" type="button" class="btn-danger">Clear All Label Photos</button>
            <button id="sendBtn" type="button" class="btn-success" disabled>Complete Pallet Entry</button>
          </div>
        </section>
      </div>
    </section>

    <section class="pane right-pane">
      <div class="stack">
        <section class="panel-card">
          <div class="panel-header">
            <div>
              <h2>Live Camera</h2>
              <p class="helper-copy">Use the rear camera where possible. Each capture flashes the camera panel and adds a thumbnail to the gallery.</p>
            </div>
          </div>
          <div class="camera-block">
            <video id="cameraPreview" playsinline autoplay muted></video>
            <div class="camera-actions">
              <button id="cameraStartBtn" type="button">Start Scanning</button>
              <button id="cameraSnapBtn" type="button" disabled>Capture Label</button>
              <button id="cameraStopBtn" type="button" disabled>Stop Scanning</button>
            </div>
            <small id="cameraStatus" class="camera-status">Camera idle.</small>
          </div>
        </section>

        <section class="panel-card">
          <div class="panel-header">
            <div>
              <h2>Processing Notes</h2>
              <p class="helper-copy">The AI instruction block stays available here if the extraction prompt needs to be tuned later.</p>
            </div>
          </div>
          <textarea id="instructions" rows="4" hidden>Read the Delivery Label and extract the following details into a JSON string: Kill date (or slaughter date) into field killDate, pack date into field packDate, best before date (or use by date) into field bestBeforeDate, storage temperature into field storageTemperature, country of origin (or nationality) into field countryOfOrigin, species into field species, cuts into field cuts and net weight (as displayed in kg) into field netWeight. If the storage temperature is below 0 degrees C then mark it as frozen, otherwise mark it as fresh in a field called freshFrozen. If you can't find the data return ? in the field</textarea>
          <pre id="result">Waiting for pallet complete...</pre>
          <div id="uploadProgress" class="progress-message" hidden>Upload progress: 0%</div>
          <div id="jobStatus" class="status-message" hidden>Background job status: idle</div>
          <div id="successMessage" class="success-message" hidden>
            <strong>Upload complete.</strong><br>
            <span id="safeToDisconnectMsg">It is now safe to scan a different pallet or switch off your device.</span><br>
            <span>You do not need to wait for AI processing to finish.</span><br>
            <a id="newPalletLink" style="display:inline-block;margin-top:0.7em;">Start a New Pallet Intake</a>
          </div>
        </section>
      </div>
    </section>
  </main>

  <input id="apiKey" type="hidden" value="" autocomplete="off">

  <div class="image-modal" id="boxCountModal" role="dialog" aria-modal="true" aria-label="Box count warning">
    <button class="close" type="button" id="boxCountModalClose" aria-label="Close warning">×</button>
    <div class="modal-card">
      <h3>Box Count Warning</h3>
      <p>You have scanned too many boxes, or the box count is wrong. Remove duplicate photos or change the expected box count.</p>
    </div>
  </div>

  <div class="image-modal" id="duplicateModal" role="dialog" aria-modal="true" aria-label="Duplicate photo warning">
    <button class="close" type="button" id="duplicateModalClose" aria-label="Close duplicate warning">×</button>
    <div class="modal-card">
      <h3>Duplicate Photo Detected</h3>
      <p>The scanner found two photos that look the same. Review the gallery before submitting the pallet.</p>
    </div>
  </div>

  <div class="image-modal" id="imageModal" role="dialog" aria-modal="true" aria-label="Captured image preview">
    <button class="close" type="button" id="imageModalClose" aria-label="Close preview">×</button>
    <img id="imageModalImg" alt="Captured preview">
  </div>

  <script>
    const depotEndpoint = "{{ url('/depots') }}";
    const intakeEndpoint = "{{ url('/intakes') }}";
    const ocrEndpoint = "{{ url('/ocr') }}";
    const jobStatusEndpoint = "{{ url('/job-status') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

    function populateDepotId() {
      fetch(depotEndpoint)
        .then((res) => res.json())
        .then((data) => {
          const depotSelect = document.getElementById("depotId");
          if (!depotSelect) return;
          depotSelect.innerHTML = "";
          (data.depots || []).forEach((depot) => {
            const opt = document.createElement("option");
            opt.value = depot.id;
            opt.textContent = depot.name;
            if (depot.name === "Unit 11") {
              opt.selected = true;
            }
            depotSelect.appendChild(opt);
          });
        });
    }

    function populateIntakeId() {
      fetch(intakeEndpoint)
        .then((res) => res.json())
        .then((data) => {
          const intakeSelect = document.getElementById("intakeId");
          if (!intakeSelect) return;
          intakeSelect.innerHTML = "";
          const placeholder = document.createElement("option");
          placeholder.value = "";
          placeholder.textContent = "Select an Intake...";
          placeholder.disabled = true;
          placeholder.selected = true;
          intakeSelect.appendChild(placeholder);
          (data.intakes || []).forEach((id) => {
            const opt = document.createElement("option");
            opt.value = id;
            opt.textContent = id;
            intakeSelect.appendChild(opt);
          });
        });
    }

    document.addEventListener("DOMContentLoaded", populateDepotId);
    document.addEventListener("DOMContentLoaded", populateIntakeId);

    const preview = document.getElementById("preview");
    const sendBtn = document.getElementById("sendBtn");
    const result = document.getElementById("result");
    const instructions = document.getElementById("instructions");
    const apiKeyInput = document.getElementById("apiKey");
    const clearImagesBtn = document.getElementById("clearImagesBtn");
    const successMessage = document.getElementById("successMessage");
    const newPalletLink = document.getElementById("newPalletLink");
    const cameraPreview = document.getElementById("cameraPreview");
    const cameraStatus = document.getElementById("cameraStatus");
    const cameraStartBtn = document.getElementById("cameraStartBtn");
    const cameraSnapBtn = document.getElementById("cameraSnapBtn");
    const cameraStopBtn = document.getElementById("cameraStopBtn");
    const imageModal = document.getElementById("imageModal");
    const imageModalImg = document.getElementById("imageModalImg");
    const imageModalClose = document.getElementById("imageModalClose");
    const intakeSelect = document.getElementById("intakeId");
    const boxesWrapper = document.getElementById("boxesWrapper");
    const boxCountInput = document.getElementById("boxCount");
    const boxCountWarning = document.getElementById("boxCountWarning");
    const boxCountModal = document.getElementById("boxCountModal");
    const boxCountModalClose = document.getElementById("boxCountModalClose");
    const duplicateModal = document.getElementById("duplicateModal");
    const duplicateModalClose = document.getElementById("duplicateModalClose");
    const uploadProgress = document.getElementById("uploadProgress");
    const jobStatus = document.getElementById("jobStatus");
    const capturedCountBadge = document.getElementById("capturedCountBadge");
    const selectedIntakeBadge = document.getElementById("selectedIntakeBadge");
    const summaryExpectedCount = document.getElementById("summaryExpectedCount");
    const summaryCapturedCount = document.getElementById("summaryCapturedCount");
    const summaryDuplicateCount = document.getElementById("summaryDuplicateCount");
    const JOB_POLL_INTERVAL_MS = 2000;
    let boxWarningShown = false;
    let duplicateWarningShown = false;
    let capturedImages = [];
    let mediaStream;
    let isSubmitting = false;
    let isLockedAfterSubmit = false;
    let activeJobId = null;
    let jobPollTimer = null;

    let lastTouchEnd = 0;
    document.addEventListener("touchend", (event) => {
      const now = Date.now();
      if (now - lastTouchEnd <= 300) {
        event.preventDefault();
      }
      lastTouchEnd = now;
    });

    function refreshSummary() {
      const expectedBoxes = Number.parseInt(boxCountInput.value, 10);
      const duplicateCount = capturedImages.filter((img) => img.isDuplicate).length;
      capturedCountBadge.textContent = `${capturedImages.length} photo${capturedImages.length === 1 ? "" : "s"}`;
      selectedIntakeBadge.textContent = intakeSelect.value ? `Intake ${intakeSelect.value}` : "No intake selected";
      summaryExpectedCount.textContent = Number.isFinite(expectedBoxes) ? String(expectedBoxes) : "0";
      summaryCapturedCount.textContent = String(capturedImages.length);
      summaryDuplicateCount.textContent = String(duplicateCount);
    }

    function toggleBoxesField() {
      boxesWrapper.hidden = !intakeSelect.value;
      updateStartState();
      refreshSummary();
    }

    function updateStartState() {
      const value = Number.parseInt(boxCountInput.value, 10);
      const valid = Number.isFinite(value) && value >= 1 && value <= 100;
      cameraStartBtn.disabled = !valid || isSubmitting || isLockedAfterSubmit;
    }

    function updateBoxCountWarning() {
      const value = Number.parseInt(boxCountInput.value, 10);
      const valid = Number.isFinite(value) && value >= 1 && value <= 100;
      const shouldShow = valid && capturedImages.length > value;
      boxCountWarning.hidden = !shouldShow;
      if (shouldShow && !boxWarningShown) {
        boxCountModal.classList.add("open");
        boxWarningShown = true;
      }
      if (!shouldShow) {
        boxWarningShown = false;
      }
      refreshSummary();
    }

    boxCountInput.addEventListener("input", () => {
      const numericValue = Number.parseInt(boxCountInput.value, 10);
      if (Number.isNaN(numericValue)) {
        updateStartState();
        refreshSummary();
        return;
      }
      if (numericValue < 1) {
        boxCountInput.value = "1";
      } else if (numericValue > 100) {
        boxCountInput.value = "100";
      } else {
        boxCountInput.value = String(numericValue);
      }
      updateStartState();
      updateBoxCountWarning();
    });

    function isIntakeIdValid() {
      return intakeSelect.value !== "";
    }

    intakeSelect.addEventListener("change", () => {
      boxCountInput.value = "";
      toggleBoxesField();
    });
    toggleBoxesField();

    function playShutterSound() {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = "triangle";
      osc.frequency.value = 1200;
      gain.gain.setValueAtTime(0.0001, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + 0.01);
      gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.08);
      osc.connect(gain).connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.09);
      osc.onended = () => ctx.close();
    }

    function playBongSound() {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = "sine";
      osc.frequency.setValueAtTime(220, ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(110, ctx.currentTime + 0.4);
      gain.gain.setValueAtTime(0.0001, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.3, ctx.currentTime + 0.02);
      gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.5);
      osc.connect(gain).connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.55);
      osc.onended = () => ctx.close();
    }

    function updateSendState() {
      sendBtn.disabled = capturedImages.length === 0;
      refreshSummary();
    }

    function uploadBatch(url, payload, onUploadPercent) {
      return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        if (csrfToken) {
          xhr.setRequestHeader("X-CSRF-TOKEN", csrfToken);
        }

        xhr.upload.onprogress = (event) => {
          if (!event.lengthComputable) return;
          const percent = Math.min(100, Math.round((event.loaded / event.total) * 100));
          onUploadPercent(percent);
        };

        xhr.onerror = () => reject(new Error("Network error during upload."));
        xhr.onload = () => {
          if (xhr.status < 200 || xhr.status >= 300) {
            reject(new Error(`HTTP ${xhr.status}: ${xhr.responseText || "Upload failed"}`));
            return;
          }
          try {
            const data = JSON.parse(xhr.responseText || "{}");
            resolve(data);
          } catch {
            reject(new Error("Invalid JSON response from server."));
          }
        };

        xhr.send(JSON.stringify(payload));
      });
    }

    function stopJobPolling() {
      if (jobPollTimer) {
        clearInterval(jobPollTimer);
        jobPollTimer = null;
      }
    }

    function statusLineForJob(data) {
      const processed = Number.isFinite(data?.processedCount) ? data.processedCount : 0;
      const total = Number.isFinite(data?.totalImages) ? data.totalImages : 0;
      const errors = Number.isFinite(data?.errorCount) ? data.errorCount : 0;
      const pct = Number.isFinite(data?.progressPercent) ? data.progressPercent : 0;
      return `Background job ${data.jobId}: ${data.status} (${processed}/${total}, ${pct}%, errors: ${errors})`;
    }

    async function pollJobStatus(jobId) {
      const response = await fetch(`${jobStatusEndpoint}?jobId=${encodeURIComponent(jobId)}`);
      if (!response.ok) {
        throw new Error(`Status HTTP ${response.status}`);
      }
      const data = await response.json();
      if (!data?.ok) {
        throw new Error(data?.error || "Unable to read job status");
      }

      jobStatus.hidden = false;
      jobStatus.textContent = statusLineForJob(data);

      if (data.status === "completed" || data.status === "failed") {
        stopJobPolling();
      }
    }

    function startJobPolling(jobId) {
      activeJobId = jobId;
      stopJobPolling();
      jobStatus.hidden = false;
      jobStatus.textContent = `Background job ${jobId}: queued (0%)`;

      pollJobStatus(jobId).catch((error) => {
        jobStatus.hidden = false;
        jobStatus.textContent = `Background job ${jobId}: status check error (${error.message})`;
      });

      jobPollTimer = setInterval(() => {
        pollJobStatus(jobId).catch((error) => {
          jobStatus.hidden = false;
          jobStatus.textContent = `Background job ${jobId}: status check error (${error.message})`;
        });
      }, JOB_POLL_INTERVAL_MS);
    }

    function hammingDistance(a, b) {
      let dist = 0;
      for (let i = 0; i < a.length; i += 1) {
        if (a[i] !== b[i]) dist += 1;
      }
      return dist;
    }

    function updateDuplicateFlags() {
      capturedImages.forEach((img) => {
        img.isDuplicate = false;
      });
      let hasDuplicate = false;
      for (let i = 0; i < capturedImages.length; i += 1) {
        const base = capturedImages[i];
        if (!base.hash && !base.pixels) continue;
        for (let j = i + 1; j < capturedImages.length; j += 1) {
          const compare = capturedImages[j];
          if (!compare.hash && !compare.pixels) continue;
          const distance = base.hash && compare.hash ? hammingDistance(base.hash, compare.hash) : 99;
          let pixelDiff = 999;
          if (base.pixels && compare.pixels && base.pixels.length === compare.pixels.length) {
            let sum = 0;
            for (let k = 0; k < base.pixels.length; k += 1) {
              sum += Math.abs(base.pixels[k] - compare.pixels[k]);
            }
            pixelDiff = sum / base.pixels.length;
          }
          if (distance <= 12 || pixelDiff <= 8) {
            base.isDuplicate = true;
            compare.isDuplicate = true;
            hasDuplicate = true;
          }
        }
      }
      if (hasDuplicate && !duplicateWarningShown) {
        playBongSound();
        duplicateModal.classList.add("open");
        duplicateWarningShown = true;
      }
      if (!hasDuplicate) {
        duplicateWarningShown = false;
      }
      refreshSummary();
    }

    function computeFingerprint(base64Data) {
      return new Promise((resolve) => {
        const image = new Image();
        image.onload = () => {
          const canvas = document.createElement("canvas");
          const ctx = canvas.getContext("2d", { willReadFrequently: true });
          canvas.width = 16;
          canvas.height = 16;
          ctx.drawImage(image, 0, 0, 16, 16);
          const pixels16 = ctx.getImageData(0, 0, 16, 16).data;
          const grays16 = [];
          for (let i = 0; i < pixels16.length; i += 4) {
            const r = pixels16[i];
            const g = pixels16[i + 1];
            const b = pixels16[i + 2];
            grays16.push((r + g + b) / 3);
          }
          const hashCanvas = document.createElement("canvas");
          const hashCtx = hashCanvas.getContext("2d", { willReadFrequently: true });
          hashCanvas.width = 9;
          hashCanvas.height = 8;
          hashCtx.drawImage(canvas, 0, 0, 9, 8);
          const pixels = hashCtx.getImageData(0, 0, 9, 8).data;
          const grays = [];
          for (let i = 0; i < pixels.length; i += 4) {
            const r = pixels[i];
            const g = pixels[i + 1];
            const b = pixels[i + 2];
            grays.push((r + g + b) / 3);
          }
          let hash = "";
          for (let row = 0; row < 8; row += 1) {
            for (let col = 0; col < 8; col += 1) {
              const left = grays[row * 9 + col];
              const right = grays[row * 9 + col + 1];
              hash += left > right ? "1" : "0";
            }
          }
          resolve({ hash, pixels: grays16 });
        };
        image.onerror = () => resolve({ hash: "", pixels: null });
        image.src = `data:image/jpeg;base64,${base64Data}`;
      });
    }

    function renderPreview() {
      updateDuplicateFlags();
      preview.innerHTML = "";
      capturedImages.forEach((img, idx) => {
        const figure = document.createElement("figure");
        if (img.isDuplicate) {
          figure.classList.add("duplicate");
        }
        const imageEl = document.createElement("img");
        imageEl.src = `data:${img.mimeType};base64,${img.base64}`;
        imageEl.alt = img.label || `Label ${idx + 1}`;
        imageEl.addEventListener("click", () => openImageModal(imageEl.src, imageEl.alt));
        const caption = document.createElement("figcaption");
        caption.textContent = img.label || `Label ${idx + 1}`;
        const removeBtn = document.createElement("button");
        removeBtn.textContent = "×";
        removeBtn.className = "remove";
        removeBtn.disabled = isSubmitting;
        removeBtn.addEventListener("click", () => {
          capturedImages.splice(idx, 1);
          renderPreview();
          updateSendState();
        });
        figure.appendChild(imageEl);
        figure.appendChild(removeBtn);
        figure.appendChild(caption);
        preview.appendChild(figure);
      });
      if (!capturedImages.length) {
        result.textContent = "Waiting for input...";
      }
      if (mediaStream) {
        cameraStatus.textContent = `Captured ${capturedImages.length} photo(s).`;
      }
      updateBoxCountWarning();
      refreshSummary();
    }

    function openImageModal(src, altText) {
      imageModalImg.src = src;
      imageModalImg.alt = altText || "Captured preview";
      imageModal.classList.add("open");
    }

    function closeImageModal() {
      imageModal.classList.remove("open");
      imageModalImg.src = "";
    }

    async function addCapturedImage(base64Data, mimeType, label) {
      const fingerprint = await computeFingerprint(base64Data);
      capturedImages.push({
        mimeType: mimeType || "image/jpeg",
        base64: base64Data,
        label,
        hash: fingerprint.hash,
        pixels: fingerprint.pixels,
        isDuplicate: false,
      });
      const cameraBlock = document.querySelector(".camera-block");
      if (cameraBlock) {
        cameraBlock.classList.remove("flash");
        void cameraBlock.offsetWidth;
        cameraBlock.classList.add("flash");
        setTimeout(() => cameraBlock.classList.remove("flash"), 250);
      }
      renderPreview();
      updateSendState();
    }

    clearImagesBtn.addEventListener("click", () => {
      if (!confirm("Clear all captured labels?")) {
        return;
      }
      capturedImages = [];
      renderPreview();
      updateSendState();
    });

    async function startCamera() {
      if (mediaStream || isSubmitting || isLockedAfterSubmit) {
        return;
      }
      try {
        mediaStream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: { ideal: "environment" },
            width: { ideal: 1920 },
            height: { ideal: 1080 },
          },
          audio: false,
        });
        cameraPreview.srcObject = mediaStream;
        await cameraPreview.play().catch(() => {});
        cameraStatus.textContent = "Camera live. Tap Capture Label for each box.";
        cameraSnapBtn.disabled = false;
        cameraStopBtn.disabled = false;
        cameraStartBtn.disabled = true;
      } catch (error) {
        cameraStatus.textContent = `Camera error: ${error.message}`;
      }
    }

    function stopCamera() {
      if (!mediaStream) return;
      mediaStream.getTracks().forEach((track) => track.stop());
      mediaStream = null;
      cameraPreview.srcObject = null;
      cameraStatus.textContent = "Camera stopped.";
      cameraSnapBtn.disabled = true;
      cameraStopBtn.disabled = true;
      cameraStartBtn.disabled = false;
    }

    function capturePhoto() {
      if (!mediaStream) return;
      cameraSnapBtn.disabled = true;
      setTimeout(() => {
        if (mediaStream && !isSubmitting && !isLockedAfterSubmit) {
          cameraSnapBtn.disabled = false;
        }
      }, 1000);
      const canvas = document.createElement("canvas");
      const width = cameraPreview.videoWidth || 1080;
      const height = cameraPreview.videoHeight || 1920;
      canvas.width = width;
      canvas.height = height;
      const ctx = canvas.getContext("2d");
      ctx.drawImage(cameraPreview, 0, 0, width, height);
      const dataUrl = canvas.toDataURL("image/jpeg", 0.95);
      const base64Data = dataUrl.split(",")[1];
      playShutterSound();
      addCapturedImage(base64Data, "image/jpeg", `Camera ${capturedImages.length + 1}`);
      cameraStatus.textContent = `Captured ${capturedImages.length} photo(s).`;
    }

    cameraStartBtn.addEventListener("click", startCamera);
    cameraStopBtn.addEventListener("click", stopCamera);
    cameraSnapBtn.addEventListener("click", capturePhoto);
    imageModalClose.addEventListener("click", closeImageModal);
    imageModal.addEventListener("click", (event) => {
      if (event.target === imageModal) {
        closeImageModal();
      }
    });
    boxCountModalClose.addEventListener("click", () => {
      boxCountModal.classList.remove("open");
    });
    boxCountModal.addEventListener("click", (event) => {
      if (event.target === boxCountModal) {
        boxCountModal.classList.remove("open");
      }
    });
    duplicateModalClose.addEventListener("click", () => {
      duplicateModal.classList.remove("open");
    });
    duplicateModal.addEventListener("click", (event) => {
      if (event.target === duplicateModal) {
        duplicateModal.classList.remove("open");
      }
    });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && imageModal.classList.contains("open")) {
        closeImageModal();
      }
    });
    window.addEventListener("beforeunload", stopCamera);
    window.addEventListener("pagehide", stopCamera);
    window.addEventListener("beforeunload", stopJobPolling);
    updateSendState();
    refreshSummary();

    newPalletLink.addEventListener("click", (event) => {
      event.preventDefault();
      window.location.reload();
    });

    sendBtn.addEventListener("click", async () => {
      if (!capturedImages.length) {
        alert("Add at least one label photo.");
        return;
      }
      if (!isIntakeIdValid()) {
        alert("Please select an Intake ID from the list.");
        intakeSelect.focus();
        return;
      }

      const expectedBoxes = Number.parseInt(boxCountInput.value, 10);
      if (Number.isFinite(expectedBoxes) && expectedBoxes > 0 && capturedImages.length < expectedBoxes) {
        const confirmed = window.confirm(
          `You only scanned ${capturedImages.length} boxes but I am expecting ${expectedBoxes} boxes - is this correct?`
        );
        if (!confirmed) {
          return;
        }
      }

      isSubmitting = true;
      isLockedAfterSubmit = false;
      sendBtn.disabled = true;
      clearImagesBtn.disabled = true;
      cameraStartBtn.disabled = true;
      cameraSnapBtn.disabled = true;
      cameraStopBtn.disabled = true;
      stopCamera();
      renderPreview();
      successMessage.hidden = true;
      result.style.display = "block";
      result.textContent = "Sending images to server...";
      uploadProgress.hidden = false;
      uploadProgress.textContent = "Upload progress: 0%";

      const customInstruction = instructions.value.trim();

      try {
        const intakeId = intakeSelect.value;
        const uploadSessionId = `${intakeId}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
        result.textContent = `Uploading ${capturedImages.length} images...`;

        const queueResponse = await uploadBatch(
          `${ocrEndpoint}?intakeId=${encodeURIComponent(intakeId)}`,
          {
            intakeId,
            uploadSessionId,
            batchNumber: 1,
            totalBatches: 1,
            isFinalBatch: true,
            instructions: customInstruction,
            images: capturedImages,
          },
          (percent) => {
            uploadProgress.textContent = `Upload progress: ${percent}%`;
          }
        );

        uploadProgress.textContent = "Upload progress: 100%";
        if (!queueResponse?.ok) {
          throw new Error(queueResponse?.error || "Server did not confirm queue.");
        }

        activeJobId = queueResponse.jobId || uploadSessionId;
        if (activeJobId) {
          startJobPolling(activeJobId);
        }

        result.style.display = "block";
        result.textContent = "All photos uploaded successfully. Background processing has started.";
        successMessage.hidden = false;

        capturedImages = [];
        renderPreview();
        updateSendState();
      } catch (error) {
        successMessage.hidden = true;
        result.style.display = "block";
        result.textContent = `Error: ${error.message}`;
      } finally {
        isSubmitting = false;
        uploadProgress.hidden = true;
        sendBtn.disabled = false;
        clearImagesBtn.disabled = false;
        updateStartState();
        cameraSnapBtn.disabled = !mediaStream;
        cameraStopBtn.disabled = !mediaStream;
        renderPreview();
      }
    });
  </script>
</body>
</html>
