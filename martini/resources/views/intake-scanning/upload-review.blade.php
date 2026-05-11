<?php

use App\Models\IntakeScanningFile;

$items = IntakeScanningFile::with(['file', 'user', 'responseFileRecord.file'])
  ->where([["deleted", false],["accepted", false]])
  ->where('file_role', IntakeScanningFile::ROLE_IMAGE)
  ->orderByRaw('COALESCE(intake_id, 0) desc')
  ->orderBy('upload_session_id', 'desc')
  ->orderBy('sequence')
  ->get();

$grouped = [];
foreach ($items as $item) {
  $intakeKey = $item->intake_id ? (string) $item->intake_id : 'Unknown';
  $grouped[$intakeKey][] = $item;
}

uksort($grouped, static function ($left, $right) {
  return (int) $left <=> (int) $right;
});

$fields = [
  'killDate',
  'packDate',
  'bestBeforeDate',
  'storageTemperature',
  'countryOfOrigin',
  'species',
  'cuts',
  'netWeight',
  'freshFrozen',
];

$totalItems = 0;
$totalResponses = 0;
foreach ($grouped as $group) {
  foreach ($group as $item) {
    $totalItems++;
    if (!empty($item->responseFileRecord?->json_payload)) {
      $totalResponses++;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en" translate="no">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="google" content="notranslate">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Upload Review</title>
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
      position: sticky;
      top: 0;
      z-index: 10;
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
    .badge-row {
      display: flex;
      gap: 0.6rem;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
    .summary-badge {
      font-weight: 600;
      color: #111827;
      background: rgba(17, 24, 39, 0.06);
      border-radius: 999px;
      padding: 0.35rem 0.8rem;
      font-size: 0.85rem;
      white-space: nowrap;
    }
    .summary-badge.primary {
      background: rgba(37, 99, 235, 0.12);
    }
    .summary-badge.success {
      background: rgba(22, 163, 74, 0.12);
    }
    .page {
      padding: 1.5rem;
      display: grid;
      gap: 1.5rem;
    }
    .empty-state,
    .group-card,
    .pair {
      background: #fff;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 0.9rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    }
    .empty-state {
      padding: 1.5rem;
      color: #6b7280;
    }
    .group-card {
      padding: 1rem;
      display: grid;
      gap: 1rem;
    }
    .group-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1rem;
      padding-bottom: 0.25rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    .group-header h2 {
      margin: 0;
      font-size: 1.2rem;
    }
    .group-header p {
      margin: 0.25rem 0 0;
      color: #6b7280;
    }
    .pair-grid {
      display: grid;
      gap: 1rem;
    }
    .pair {
      padding: 1rem;
      display: grid;
      grid-template-columns: minmax(280px, 1fr) minmax(320px, 1.2fr);
      gap: 1rem;
    }
    .image-panel {
      display: grid;
      gap: 0.75rem;
    }
    .image-frame {
      background: #111827;
      border-radius: 0.75rem;
      overflow: hidden;
      border: 1px solid rgba(0, 0, 0, 0.14);
      min-height: 320px;
      display: grid;
      place-items: center;
    }
    .image-frame img {
      width: 100%;
      height: 100%;
      max-height: 720px;
      object-fit: contain;
      cursor: zoom-in;
      display: block;
    }
    .image-frame.empty {
      background: #f9fafb;
      color: #6b7280;
      font-weight: 600;
      padding: 1.5rem;
      text-align: center;
    }
    .meta {
      color: #6b7280;
      font-size: 0.85rem;
    }
    .fields {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.75rem;
      align-content: start;
    }
    .field {
      display: grid;
      gap: 0.25rem;
    }
    .field.wide {
      grid-column: 1 / -1;
    }
    .field label {
      font-weight: 600;
      font-size: 0.9rem;
      color: #374151;
    }
    .field input[type="text"] {
      width: 100%;
      padding: 0.65rem 0.75rem;
      border-radius: 0.6rem;
      border: 1px solid rgba(0, 0, 0, 0.14);
      font-size: 0.96rem;
      background: #fff;
      color: inherit;
    }
    .pair-actions {
      grid-column: 1 / -1;
      display: flex;
      justify-content: flex-end;
      gap: 0.6rem;
      margin-top: 0.25rem;
    }
    .pair-actions button {
      border: none;
      border-radius: 0.5rem;
      padding: 0.65rem 0.95rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      box-shadow: 0 6px 14px rgba(17, 24, 39, 0.16);
    }
    .btn-danger {
      background: #dc2626;
    }
    .btn-success {
      background: #16a34a;
    }
    .btn-success[disabled] {
      opacity: 0.65;
      cursor: default;
    }
    .image-modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.85);
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
      max-width: min(96vw, 1600px);
      max-height: 92vh;
      border-radius: 0.6rem;
      object-fit: contain;
      box-shadow: 0 12px 30px rgba(0,0,0,0.45);
      background: #111;
      transform-origin: center center;
      transition: transform 0.08s ease-out;
      cursor: zoom-in;
    }
    .image-modal .close {
      position: absolute;
      top: 12px;
      right: 12px;
      width: 44px;
      height: 44px;
      border-radius: 999px;
      border: none;
      background: rgba(255,255,255,0.2);
      color: #fff;
      font-size: 1.6rem;
      line-height: 1;
      cursor: pointer;
    }
    @media (max-width: 960px) {
      .top-bar {
        flex-direction: column;
        align-items: flex-start;
      }
      .badge-row {
        justify-content: flex-start;
      }
      .pair {
        grid-template-columns: 1fr;
      }
      .fields {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header class="top-bar">
    <div class="page-title">
      <h1>AI Upload Review</h1>
      <p>Review stored intake label images alongside the extracted field values.</p>
    </div>
    <div class="badge-row">
      <span class="summary-badge primary"><?php echo count($grouped); ?> intakes</span>
      <span class="summary-badge"><?php echo $totalItems; ?> captured items</span>
      <span class="summary-badge success"><?php echo $totalResponses; ?> AI responses</span>
    </div>
  </header>

  <main class="page">
    <?php if (empty($grouped)): ?>
      <section class="empty-state">
        No uploads were found in the review folder.
      </section>
    <?php else: ?>
      <?php foreach ($grouped as $intakeId => $group): ?>
        <section class="group-card">
          <div class="group-header">
            <div>
              <h2>Intake <?php echo htmlspecialchars($intakeId); ?></h2>
              <p><?php echo count($group); ?> image<?php echo count($group) === 1 ? '' : 's'; ?> found for this intake.</p>
            </div>
            <div class="badge-row">
              <span class="summary-badge"><?php echo count($group); ?> items</span>
            </div>
          </div>

          <div class="pair-grid">
            <?php foreach ($group as $item): ?>
              <?php
                $file = $item->file;
                $responseFileRecord = $item->responseFileRecord;
                $responseFile = $responseFileRecord?->file;
                $imageSrc = $file ? route('files.view', ['file' => $file->id]) : null;
                $data = is_array($responseFileRecord?->json_payload) ? $responseFileRecord->json_payload : [];
                $capturedBy = $item->user?->name ?? 'Unknown';
                $acceptedState = $item->accepted ? 'Accepted' : 'Pending review';
                $acceptUrl = route('intake-scanning.accept', ['intakeScanningFile' => $item->id]);
                $deleteUrl = route('intake-scanning.delete', ['intakeScanningFile' => $item->id]);
              ?>
              <section class="pair" data-item-id="<?php echo (int) $item->id; ?>">
                <div class="image-panel">
                  <?php if ($imageSrc): ?>
                    <div class="image-frame">
                      <img
                        class="preview-image"
                        src="<?php echo htmlspecialchars($imageSrc); ?>"
                        alt="<?php echo htmlspecialchars((string) ($file?->original_name ?? $item->upload_session_id)); ?>"
                      >
                    </div>
                    <div class="meta"><?php echo htmlspecialchars((string) ($file?->original_name ?? 'Stored image')); ?></div>
                  <?php else: ?>
                    <div class="image-frame empty">No image found for this record.</div>
                  <?php endif; ?>
                </div>

                <div class="fields">
                  <?php foreach ($fields as $field): ?>
                    <div class="field <?php echo $field === 'cuts' ? 'wide' : ''; ?>">
                      <label><?php echo htmlspecialchars($field); ?></label>
                      <input type="text" value="<?php echo htmlspecialchars((string) ($data[$field] ?? '')); ?>">
                    </div>
                  <?php endforeach; ?>

                  <div class="field wide">
                    <label>Stored Record</label>
                    <input type="text" value="<?php echo htmlspecialchars('Session ' . $item->upload_session_id . ' | Sequence ' . $item->sequence . ' | Image: ' . ($file?->original_name ?? 'Missing') . ' | JSON: ' . ($responseFile?->original_name ?? 'Pending') . ($item->error_message ? ' | Error: ' . $item->error_message : '')); ?>">
                  </div>

                  <div class="field">
                    <label>Captured By</label>
                    <input type="text" class="captured-by-field" value="<?php echo htmlspecialchars($capturedBy); ?>">
                  </div>

                  <div class="field">
                    <label>Accepted</label>
                    <input type="text" class="accepted-state-field" value="<?php echo htmlspecialchars($acceptedState); ?>">
                  </div>

                  <div class="pair-actions">
                    <button type="button" class="btn-danger delete-item-btn" data-delete-url="<?php echo htmlspecialchars($deleteUrl); ?>">Delete Item</button>
                    <button type="button" class="btn-success accept-item-btn" data-accept-url="<?php echo htmlspecialchars($acceptUrl); ?>" <?php echo $item->accepted ? 'disabled' : ''; ?>><?php echo $item->accepted ? 'Accepted' : 'Accept Item'; ?></button>
                  </div>
                </div>
              </section>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <div class="image-modal" id="imageModal" role="dialog" aria-modal="true" aria-label="Image preview">
    <button class="close" type="button" id="imageModalClose" aria-label="Close preview">×</button>
    <img id="imageModalImg" alt="Preview">
  </div>

  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("imageModalImg");
    const modalClose = document.getElementById("imageModalClose");
    let zoomLevel = 1;
    const minZoom = 1;
    const maxZoom = 4;

    function applyZoom() {
      modalImg.style.transform = `scale(${zoomLevel})`;
      modalImg.style.cursor = zoomLevel > 1 ? "zoom-out" : "zoom-in";
    }

    function openModal(src, altText) {
      modalImg.src = src;
      modalImg.alt = altText || "Preview";
      zoomLevel = 1;
      applyZoom();
      modal.classList.add("open");
    }

    function closeModal() {
      modal.classList.remove("open");
      modalImg.src = "";
    }

    document.querySelectorAll(".preview-image").forEach((img) => {
      img.addEventListener("click", () => openModal(img.src, img.alt));
    });

    modalClose.addEventListener("click", closeModal);
    modal.addEventListener("click", (event) => {
      if (event.target === modal) {
        closeModal();
      }
    });
    modalImg.addEventListener("click", () => {
      zoomLevel = zoomLevel === 1 ? 2 : 1;
      applyZoom();
    });
    modalImg.addEventListener("wheel", (event) => {
      event.preventDefault();
      const delta = Math.sign(event.deltaY);
      zoomLevel = Math.min(maxZoom, Math.max(minZoom, zoomLevel - delta * 0.2));
      applyZoom();
    }, { passive: false });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && modal.classList.contains("open")) {
        closeModal();
      }
    });

    document.querySelectorAll(".accept-item-btn").forEach((button) => {
      button.addEventListener("click", async () => {
        if (button.disabled) {
          return;
        }

        const url = button.getAttribute("data-accept-url");
        if (!url) {
          return;
        }

        button.disabled = true;
        const originalText = button.textContent;
        button.textContent = "Accepting...";

        try {
          const response = await fetch(url, {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": csrfToken,
              "Accept": "application/json",
            },
          });

          const data = await response.json();
          if (!response.ok || !data?.ok) {
            throw new Error(data?.error || `HTTP ${response.status}`);
          }

          const pair = button.closest(".pair");
          const acceptedField = pair?.querySelector(".accepted-state-field");
          const capturedByField = pair?.querySelector(".captured-by-field");
          if (acceptedField) {
            acceptedField.value = "Accepted";
          }
          if (capturedByField && data.userName) {
            capturedByField.value = data.userName;
          }

          button.textContent = "Accepted";
        } catch (error) {
          button.disabled = false;
          button.textContent = originalText;
          window.alert(`Accept failed: ${error.message}`);
        }
      });
    });

    document.querySelectorAll(".delete-item-btn").forEach((button) => {
      button.addEventListener("click", async () => {
        const url = button.getAttribute("data-delete-url");
        if (!url) {
          return;
        }

        if (!window.confirm("Delete this item from review?")) {
          return;
        }

        button.disabled = true;
        const originalText = button.textContent;
        button.textContent = "Deleting...";

        try {
          const response = await fetch(url, {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": csrfToken,
              "Accept": "application/json",
            },
          });

          const data = await response.json();
          if (!response.ok || !data?.ok) {
            throw new Error(data?.error || `HTTP ${response.status}`);
          }

          const pair = button.closest(".pair");
          if (pair) {
            pair.remove();
          }
        } catch (error) {
          button.disabled = false;
          button.textContent = originalText;
          window.alert(`Delete failed: ${error.message}`);
        }
      });
    });
  </script>
</body>
</html>
