<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Intake Job Monitor</title>
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
    .top-bar {
      min-height: 104px;
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
      gap: 0.75rem;
      flex-wrap: wrap;
      flex: 1 1 auto;
    }
    .top-controls input {
      min-width: 300px;
      flex: 1 1 340px;
      padding: 0.6rem 0.75rem;
      border-radius: 0.5rem;
      border: 1px solid rgba(0, 0, 0, 0.2);
      font-size: 1rem;
      background: #fff;
      color: inherit;
    }
    .top-controls button {
      padding: 0.65rem 0.95rem;
      border-radius: 0.5rem;
      border: none;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 6px 14px rgba(17, 24, 39, 0.16);
    }
    #watchBtn {
      background: #2563eb;
    }
    #stopBtn {
      background: #111827;
    }
    .page-title {
      min-width: 260px;
      text-align: right;
    }
    .page-title h1 {
      margin: 0;
      font-size: 1.8rem;
    }
    .page-title p {
      margin: 0.25rem 0 0;
      color: #6b7280;
      font-size: 0.95rem;
    }
    .main {
      display: grid;
      grid-template-columns: minmax(320px, 0.95fr) minmax(420px, 1.4fr);
      gap: 0;
      height: calc(100vh - 104px);
      margin-top: 104px;
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
    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: 0.85rem;
    }
    .panel-card h2 {
      margin: 0 0 0.25rem;
      font-size: 1.08rem;
    }
    .panel-card p {
      margin: 0;
      color: #6b7280;
      line-height: 1.45;
    }
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
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
    .status {
      padding: 0.9rem 1rem;
      border-radius: 0.75rem;
      border: 1px solid rgba(128, 128, 128, 0.2);
      background: rgba(17, 24, 39, 0.04);
      margin-bottom: 1rem;
      font-weight: 600;
      color: #374151;
    }
    .status.queued {
      color: #1d4ed8;
      background: rgba(37, 99, 235, 0.12);
      border-color: rgba(37, 99, 235, 0.3);
    }
    .status.processing {
      color: #7c3aed;
      background: rgba(124, 58, 237, 0.12);
      border-color: rgba(124, 58, 237, 0.28);
    }
    .status.completed {
      color: #166534;
      background: rgba(22, 163, 74, 0.12);
      border-color: rgba(22, 163, 74, 0.3);
    }
    .status.failed {
      color: #991b1b;
      background: rgba(220, 38, 38, 0.12);
      border-color: rgba(220, 38, 38, 0.3);
    }
    .muted {
      color: #6b7280;
      font-size: 0.92rem;
    }
    .jobs-table-wrap {
      overflow: auto;
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 0.85rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.95rem;
      background: #fff;
    }
    th,
    td {
      border-bottom: 1px solid rgba(128, 128, 128, 0.18);
      text-align: left;
      padding: 0.75rem;
      vertical-align: top;
      white-space: nowrap;
    }
    th {
      position: sticky;
      top: 0;
      background: #f9fafb;
      color: #374151;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      z-index: 1;
    }
    td:last-child,
    th:last-child {
      white-space: normal;
    }
    .job-link {
      color: #2563eb;
      text-decoration: underline;
      font-weight: 600;
    }
    .job-status-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.2rem 0.55rem;
      border-radius: 999px;
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .job-status-badge.queued {
      background: rgba(37, 99, 235, 0.12);
      color: #1d4ed8;
    }
    .job-status-badge.processing {
      background: rgba(124, 58, 237, 0.12);
      color: #7c3aed;
    }
    .job-status-badge.completed {
      background: rgba(22, 163, 74, 0.12);
      color: #166534;
    }
    .job-status-badge.failed {
      background: rgba(220, 38, 38, 0.12);
      color: #991b1b;
    }
    .job-status-badge.uploading {
      background: rgba(245, 158, 11, 0.15);
      color: #92400e;
    }
    .progress-bar {
      width: 100%;
      height: 10px;
      background: rgba(17, 24, 39, 0.08);
      border-radius: 999px;
      overflow: hidden;
      margin-top: 0.35rem;
    }
    .progress-bar span {
      display: block;
      height: 100%;
      background: linear-gradient(90deg, #2563eb, #16a34a);
      border-radius: inherit;
    }
    .watch-grid {
      display: grid;
      gap: 0.7rem;
    }
    .watch-row {
      display: grid;
      grid-template-columns: 120px 1fr;
      gap: 0.6rem;
      font-size: 0.95rem;
    }
    .watch-errors {
      display: grid;
      gap: 0.45rem;
      margin-top: 0.4rem;
    }
    .watch-error {
      padding: 0.6rem 0.75rem;
      border-radius: 0.65rem;
      border: 1px solid rgba(220, 38, 38, 0.18);
      background: rgba(220, 38, 38, 0.06);
      color: #991b1b;
      font-size: 0.9rem;
      line-height: 1.35;
    }
    .watch-row strong {
      color: #374151;
    }
    @media (max-width: 1100px) {
      .top-bar {
        position: static;
      }
      .main {
        margin-top: 0;
        height: auto;
      }
    }
    @media (max-width: 900px) {
      body {
        overflow: auto;
      }
      .top-bar {
        flex-direction: column;
        align-items: flex-start;
      }
      .page-title {
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
        grid-template-columns: 1fr 1fr;
      }
      .watch-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header class="top-bar">
    <div class="top-controls">
      <input id="jobIdInput" type="text" placeholder="Paste Job ID to monitor a specific job" autocomplete="off">
      <button id="watchBtn" type="button">Watch Job</button>
      <button id="stopBtn" type="button">Stop Watching</button>
    </div>
    <div class="page-title">
      <h1>Intake Job Monitor</h1>
      <p>Live view for queued, processing, completed, and failed label extraction jobs.</p>
    </div>
  </header>

  <main class="main">
    <section class="pane left-pane">
      <div class="stack">
        <section class="panel-card">
          <div class="panel-header">
            <div>
              <h2>Watched Job</h2>
              <p>Paste a job ID or click one from the recent list to keep polling it.</p>
            </div>
          </div>
          <div id="watchStatus" class="status">No job selected.</div>
          <div class="watch-grid" id="watchDetails">
            <div class="watch-row"><strong>Job ID</strong><span>Not watching a job.</span></div>
          </div>
        </section>

        <section class="panel-card">
          <div class="panel-header">
            <div>
              <h2>Queue Summary</h2>
              <p>Recent jobs refresh every 3 seconds.</p>
            </div>
          </div>
          <div class="summary-grid">
            <div class="summary-card">
              <strong id="summaryTotalJobs">0</strong>
              <span>Total Jobs</span>
            </div>
            <div class="summary-card">
              <strong id="summaryActiveJobs">0</strong>
              <span>Queued or Processing</span>
            </div>
            <div class="summary-card">
              <strong id="summaryCompletedJobs">0</strong>
              <span>Completed</span>
            </div>
            <div class="summary-card">
              <strong id="summaryFailedJobs">0</strong>
              <span>Failed</span>
            </div>
          </div>
          <p class="muted" style="margin-top: 0.85rem;">Selecting a job from the table starts the detailed watcher automatically.</p>
        </section>
      </div>
    </section>

    <section class="pane right-pane">
      <section class="panel-card">
        <div class="panel-header">
          <div>
            <h2>Recent Jobs</h2>
            <p>Newest updates appear first.</p>
          </div>
        </div>
        <div class="jobs-table-wrap">
          <table>
            <thead>
              <tr>
                <th>Job ID</th>
                <th>Intake</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Stored</th>
                <th>Errors</th>
                <th>Updated</th>
              </tr>
            </thead>
            <tbody id="jobsBody">
              <tr><td colspan="7">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </section>
    </section>
  </main>

  <script>
    const jobsListEndpoint = "{{ url('/jobs-list') }}";
    const jobStatusEndpoint = "{{ url('/job-status') }}";

    const jobIdInput = document.getElementById("jobIdInput");
    const watchBtn = document.getElementById("watchBtn");
    const stopBtn = document.getElementById("stopBtn");
    const watchStatus = document.getElementById("watchStatus");
    const watchDetails = document.getElementById("watchDetails");
    const jobsBody = document.getElementById("jobsBody");
    const summaryTotalJobs = document.getElementById("summaryTotalJobs");
    const summaryActiveJobs = document.getElementById("summaryActiveJobs");
    const summaryCompletedJobs = document.getElementById("summaryCompletedJobs");
    const summaryFailedJobs = document.getElementById("summaryFailedJobs");

    let watchJobId = "";
    let jobsTimer = null;
    let watchTimer = null;

    function esc(text) {
      return String(text ?? "").replace(/[&<>"']/g, (ch) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[ch]));
    }

    function statusClass(status) {
      return String(status || "unknown").toLowerCase();
    }

    function renderWatchDetails(job) {
      if (!job) {
        watchDetails.innerHTML = '<div class="watch-row"><strong>Job ID</strong><span>Not watching a job.</span></div>';
        return;
      }

      const errorMessages = Array.isArray(job.errorMessages) ? job.errorMessages : [];
      const renderedErrors = errorMessages.length
        ? `<div class="watch-errors">${errorMessages.map((message) => `<div class="watch-error">${esc(message)}</div>`).join("")}</div>`
        : '<span>-</span>';

      watchDetails.innerHTML = `
        <div class="watch-row"><strong>Job ID</strong><span>${esc(job.jobId)}</span></div>
        <div class="watch-row"><strong>Intake</strong><span>${esc(job.intakeId || "-")}</span></div>
        <div class="watch-row"><strong>Status</strong><span>${esc(job.status)}</span></div>
        <div class="watch-row"><strong>Progress</strong><span>${esc(job.processedCount)}/${esc(job.totalImages)} (${esc(job.progressPercent)}%)</span></div>
        <div class="watch-row"><strong>Stored Images</strong><span>${esc(job.storedImageCount || 0)}</span></div>
        <div class="watch-row"><strong>OCR Success</strong><span>${esc(job.processedImageCount || 0)}</span></div>
        <div class="watch-row"><strong>Pending</strong><span>${esc(job.pendingImageCount || 0)}</span></div>
        <div class="watch-row"><strong>Errors</strong><span>${esc(job.errorCount)}</span></div>
        <div class="watch-row"><strong>Latest Error</strong><span>${esc(job.latestError || "-")}</span></div>
        <div class="watch-row"><strong>Error Detail</strong>${renderedErrors}</div>
        <div class="watch-row"><strong>Updated</strong><span>${esc(job.updatedAt || "-")}</span></div>
      `;
    }

    function applyWatchStatus(job) {
      const status = statusClass(job?.status);
      watchStatus.className = `status${status ? ` ${status}` : ""}`;
    }

    function updateSummary(jobs) {
      const totalJobs = jobs.length;
      const activeJobs = jobs.filter((job) => ["queued", "processing", "uploading"].includes(statusClass(job.status))).length;
      const completedJobs = jobs.filter((job) => statusClass(job.status) === "completed").length;
      const failedJobs = jobs.filter((job) => statusClass(job.status) === "failed").length;

      summaryTotalJobs.textContent = String(totalJobs);
      summaryActiveJobs.textContent = String(activeJobs);
      summaryCompletedJobs.textContent = String(completedJobs);
      summaryFailedJobs.textContent = String(failedJobs);
    }

    function renderProgressCell(job) {
      const percent = Number(job.progressPercent || 0);
      return `
        <div>${esc(job.processedCount)}/${esc(job.totalImages)} (${esc(job.progressPercent)}%)</div>
        <div class="progress-bar"><span style="width:${Math.max(0, Math.min(100, percent))}%"></span></div>
      `;
    }

    async function loadJobs() {
      try {
        const response = await fetch(`${jobsListEndpoint}?limit=30`);
        const data = await response.json();
        if (!data?.ok || !Array.isArray(data.jobs)) {
          jobsBody.innerHTML = '<tr><td colspan="7">Unable to load jobs.</td></tr>';
          updateSummary([]);
          return;
        }
        if (!data.jobs.length) {
          jobsBody.innerHTML = '<tr><td colspan="7">No jobs yet.</td></tr>';
          updateSummary([]);
          return;
        }

        updateSummary(data.jobs);
        jobsBody.innerHTML = data.jobs.map((job) => `
          <tr>
            <td><a href="#" data-jobid="${esc(job.jobId)}" class="job-link">${esc(job.jobId)}</a></td>
            <td>${esc(job.intakeId || "-")}</td>
            <td><span class="job-status-badge ${statusClass(job.status)}">${esc(job.status)}</span></td>
            <td>${renderProgressCell(job)}</td>
            <td>${esc(job.storedImageCount || job.totalImages || 0)}</td>
            <td title="${esc(job.latestError || "")}">${esc(job.errorCount)}</td>
            <td>${esc(job.updatedAt || "")}</td>
          </tr>
        `).join("");
      } catch {
        jobsBody.innerHTML = '<tr><td colspan="7">Error loading jobs list.</td></tr>';
        updateSummary([]);
      }
    }

    async function loadWatchedJob() {
      if (!watchJobId) {
        watchStatus.textContent = "No job selected.";
        watchStatus.className = "status";
        renderWatchDetails(null);
        return;
      }
      try {
        const response = await fetch(`${jobStatusEndpoint}?jobId=${encodeURIComponent(watchJobId)}`);
        const data = await response.json();
        if (!data?.ok) {
          watchStatus.textContent = `Job ${watchJobId}: ${data?.error || "not found"}`;
          watchStatus.className = "status failed";
          renderWatchDetails(null);
          return;
        }

        watchStatus.textContent = `Job ${data.jobId}: ${data.status} | stored: ${data.storedImageCount || 0} | resolved: ${data.processedCount}/${data.totalImages} (${data.progressPercent}%) | errors: ${data.errorCount}`;
        applyWatchStatus(data);
        renderWatchDetails(data);
      } catch {
        watchStatus.textContent = `Job ${watchJobId}: status check failed.`;
        watchStatus.className = "status failed";
      }
    }

    function startWatching(jobId) {
      watchJobId = (jobId || "").trim();
      jobIdInput.value = watchJobId;
      if (!watchJobId) {
        watchStatus.textContent = "No job selected.";
        watchStatus.className = "status";
        renderWatchDetails(null);
        return;
      }
      if (watchTimer) clearInterval(watchTimer);
      loadWatchedJob();
      watchTimer = setInterval(loadWatchedJob, 2000);
    }

    function stopWatching() {
      watchJobId = "";
      if (watchTimer) {
        clearInterval(watchTimer);
        watchTimer = null;
      }
      watchStatus.textContent = "No job selected.";
      watchStatus.className = "status";
      renderWatchDetails(null);
    }

    watchBtn.addEventListener("click", () => startWatching(jobIdInput.value));
    stopBtn.addEventListener("click", stopWatching);

    jobsBody.addEventListener("click", (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;
      if (target.classList.contains("job-link")) {
        event.preventDefault();
        startWatching(target.getAttribute("data-jobid") || "");
      }
    });

    const params = new URLSearchParams(window.location.search);
    const fromQuery = params.get("jobId") || "";
    if (fromQuery) {
      startWatching(fromQuery);
    } else {
      renderWatchDetails(null);
    }

    loadJobs();
    jobsTimer = setInterval(loadJobs, 3000);
    window.addEventListener("beforeunload", () => {
      if (jobsTimer) clearInterval(jobsTimer);
      if (watchTimer) clearInterval(watchTimer);
    });
  </script>
</body>
</html>
