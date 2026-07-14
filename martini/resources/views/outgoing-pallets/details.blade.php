<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __("Deliveries for: ".$customer->businessname. " : ".$customerAddress->address_1. " : ".$customerAddress->postcode) }}
        </h2>
    </x-slot>
    <div><a href="{{ route('outgoing-pallets.index') }}" class="inline-block mt-4 px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">Back to List</a></div>
    <div style="display: inline;">
        <div style="width: 50%;float: left;padding-right: 10px;" class="bg-gray-100">
            <h3 class="font-semibold text-md mb-2">Unloaded Picks (Next 3 Days)</h3>
            <div id="unassigned-picks" class="border rounded p-2 bg-blue-50" style="min-height: 200px;">
                @if($deliveries->isEmpty())
                    <div class="text-gray-600">No unloaded picks found for this customer/address in the next 3 days.</div>
                @else
                    @foreach($deliveries as $delivery)
                        @php
                            $pickWeightOut = $delivery->pickWeightOuts->first();
                        @endphp
                        <x-draggable-pick :pickWeightOut="$pickWeightOut" :pickerSheet="$delivery"/>
                    @endforeach
                @endif
            </div>
        </div>
        <div style="width: 50%;float: left;padding-left: 10px;" class="bg-gray-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="font-semibold text-md">Outgoing Pallets <button id="add-pallet" class="btn btn-sm btn-primary rounded bg-green-500 hover:bg-green-700 w-6 h-6"><i class="fa fa-plus text-white" style="font-size:12pt;"></i></button></h3>
            </div>
            <div id="pallets-list">
                @if($outgoingPallets->isEmpty())
                    <div class="text-gray-600">No outgoing pallets found for this customer/address.</div>
                @else
                    @foreach($outgoingPallets as $pallet)
                        <div class="border rounded mb-3 pallet-card" data-outgoing-pallet-id="{{ $pallet->id }}" data-pallet-type-id="{{ $pallet->transport_pallet_type_id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2 ">
                                <div class="">
                                    <strong>Pallet #{{ $pallet->id }}</strong>
                                    <button class="rounded bg-red-500 hover:bg-red-700 w-6 h-6 btn-danger js-delete-pallet" data-outgoing-pallet-id="{{ $pallet->id }}"><i class="fas fa-trash text-red-100"></i></button>
                                    <strong>Type:</strong>
                                    <select class="form-control form-control-sm d-inline js-pallet-type-selector" style="width: auto;padding-top: 2px;padding-bottom: 2px;" data-pallet-id="{{ $pallet->id }}" data-current-type="{{ $pallet->transport_pallet_type_id }}">
                                        @foreach($palletTypes as $type)
                                            <option value="{{ $type->id }}" @if($type->id == $pallet->transport_pallet_type_id) selected @endif>
                                                {{ $type->name }} (Max: {{ $type->max_weight }} kg)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="pallet-drop-zone border rounded mt-2 bg-blue-50"
                                    data-outgoing-pallet-id="{{ $pallet->id }}"
                                    style="min-height: 120px;">
                                @foreach($pallet->pickWeightOuts as $link)
                                    @php
                                        $pickWeightOut = $link->pickWeightOut;
                                        if ($pickWeightOut == null) continue;
                                    @endphp
                                    <x-draggable-pick :pickWeightOut="$pickWeightOut" :fromPalletId="$pallet->id"/>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<script>

        const csrfToken = '{{ csrf_token() }}';
        const customerId = '{{ $customer_id }}';
        const addressId = '{{ $address_id }}';
        const palletTypes = @json($palletTypes);

        const unassigned = document.getElementById('unassigned-picks');
        const palletsList = document.getElementById('pallets-list');
        const addPalletBtn = document.getElementById('add-pallet');
        let activeTouchDrag = null;
        const touchEdgeScrollThreshold = 90;
        const touchEdgeScrollMaxStep = 24;
        let unassignedHeightObserver = null;

        function createDragPayloadFromElement(target) {
            const pickWeightOutId = target?.dataset?.pickWeightOutId;
            if (!pickWeightOutId) {
                return null;
            }

            const totalWeightCount = Number(target.dataset.weightCount || 0);
            const selectedMoveWeightCount = Number(target.dataset.moveWeightCount || totalWeightCount || 0);
            const selectedCutId = Number(target.dataset.selectedCutId || 0) || null;

            return {
                pickWeightOutId,
                fromPalletId: target.dataset.fromPalletId || null,
                weightCount: totalWeightCount,
                moveWeightCount: selectedMoveWeightCount,
                moveCutId: selectedCutId,
            };
        }

        function onDragStart(e) {
            const target = e.currentTarget;
            const payload = createDragPayloadFromElement(target);
            if (!payload) {
                return;
            }
            e.dataTransfer.setData('text/plain', JSON.stringify(payload));
        }

        function syncUnassignedDropZoneHeight() {
            if (!unassigned || !palletsList) {
                return;
            }

            const palletCards = palletsList.querySelectorAll('.pallet-card');
            const measuredHeight = palletCards.length > 0
                ? Math.ceil(palletsList.scrollHeight)
                : 120;

            unassigned.style.minHeight = `${Math.max(120, measuredHeight)}px`;
        }

        function observeUnassignedDropZoneHeight() {
            if (!palletsList || typeof ResizeObserver === 'undefined' || unassignedHeightObserver) {
                return;
            }

            unassignedHeightObserver = new ResizeObserver(() => {
                syncUnassignedDropZoneHeight();
            });

            unassignedHeightObserver.observe(palletsList);
        }

        function allowDrop(e) {
            e.preventDefault();
        }

        function parseCutQuantities(rawValue) {
            if (Array.isArray(rawValue)) {
                return rawValue;
            }

            if (!rawValue) {
                return [];
            }

            try {
                const parsed = JSON.parse(rawValue);
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                return [];
            }
        }

        function getCutEntry(cutQuantities, cutId) {
            if (!Array.isArray(cutQuantities) || cutQuantities.length === 0) {
                return null;
            }

            if (cutId === null || cutId === undefined || cutId === '') {
                return null;
            }

            const numericCutId = Number(cutId || 0);
            const found = cutQuantities.find((item) => Number(item.cut_id || 0) === numericCutId);
            return found || null;
        }

        function renderMoveQtyOptions(maxQty, selectedQty) {
            const limit = Math.max(0, Number(maxQty || 0));
            const selected = Math.min(Math.max(1, Number(selectedQty || limit || 1)), limit || 1);
            return Array.from({ length: limit }, (_, index) => {
                const value = index + 1;
                const isSelected = value === selected ? 'selected' : '';
                return `<option value="${value}" ${isSelected}>${value}</option>`;
            }).join('');
        }

        function htmlToElement(html) {
            const template = document.createElement('template');
            template.innerHTML = String(html || '').trim();
            return template.content.firstElementChild;
        }

        async function fetchRenderedPickHtml({ pickWeightOutId, fromPalletId = null, selectedCutId = '', moveWeightCount = null }) {
            const response = await fetch('{{ route('outgoing-pallets.render-pick-html') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    pick_weight_out_id: pickWeightOutId,
                    from_transport_pallet_id: fromPalletId,
                    selected_cut_id: selectedCutId === '' ? null : selectedCutId,
                    move_weight_count: moveWeightCount,
                }),
            });

            if (!response.ok) {
                throw new Error('Unable to render pick HTML');
            }

            const data = await response.json();
            return data?.html || '';
        }

        async function renderPickElement(summary, fromPalletId = null, selectedCutId = '', selectedMoveWeightCount = null) {
            const html = await fetchRenderedPickHtml({
                pickWeightOutId: summary.id,
                fromPalletId,
                selectedCutId,
                moveWeightCount: selectedMoveWeightCount,
            });

            const element = htmlToElement(html);
            if (!element) {
                throw new Error('Rendered pick HTML is empty');
            }

            return element;
        }

        async function applyPickSummaryToElement(element, summary) {
            const currentSelectedCutId = element.dataset.selectedCutId ?? '';
            const currentMoveQty = Number(element.dataset.moveWeightCount || 0) || null;
            const fromPalletId = element.dataset.fromPalletId || null;
            const renderedElement = await renderPickElement(summary, fromPalletId, currentSelectedCutId, currentMoveQty);

            element.replaceWith(renderedElement);
            bindDraggable(renderedElement);
            bindMoveControls(renderedElement);
            return renderedElement;
        }

        async function createPickElement(summary, fromPalletId = null) {
            const renderedElement = await renderPickElement(summary, fromPalletId, '', Number(summary.weight_count || 0));
            bindDraggable(renderedElement);
            bindMoveControls(renderedElement);
            return renderedElement;
        }

        function updateCardMoveQtyForSelectedCut(card, selectedQty) {
            const quantity = Math.max(1, Number(selectedQty || 1));
            const qtySelect = card.querySelector('.js-move-weight-count');
            const maxLabel = card.querySelector('.js-move-max-label');

            if (qtySelect) {
                qtySelect.innerHTML = renderMoveQtyOptions(quantity, quantity);
            }

            if (maxLabel) {
                maxLabel.textContent = `max ${quantity} case${quantity === 1 ? '' : 's'}`;
            }

            card.dataset.moveWeightCount = String(quantity);
        }

        function handleMoveCutChange(e) {
            const select = e.currentTarget;
            const card = select.closest('[data-pick-weight-out-id]');
            if (!card) {
                return;
            }

            if (!select?.value) {
                card.dataset.selectedCutId = '';
                updateCardMoveQtyForSelectedCut(card, Number(card.dataset.weightCount || 0));
                return;
            }

            const cutQuantities = parseCutQuantities(card.dataset.cutQuantities);
            const selectedCut = getCutEntry(cutQuantities, select?.value || null);
            if (!selectedCut) {
                return;
            }

            const selectedCutId = Number(selectedCut.cut_id || 0);
            const selectedQty = Number(selectedCut.quantity || 0);
            if (!selectedCutId || selectedQty < 1) {
                return;
            }

            card.dataset.selectedCutId = String(selectedCutId);
            updateCardMoveQtyForSelectedCut(card, selectedQty);
        }

        function handleMoveWeightCountChange(e) {
            const select = e.currentTarget;
            const card = select.closest('[data-pick-weight-out-id]');
            if (!card) {
                return;
            }

            const cutQuantities = parseCutQuantities(card.dataset.cutQuantities);
            const selectedCut = getCutEntry(cutQuantities, card.dataset.selectedCutId || null);
            const maxAllowed = selectedCut
                ? Number(selectedCut.quantity || 0)
                : Number(card.dataset.weightCount || 0);
            const selectedValue = Number(select?.value || maxAllowed || 0);
            if (!Number.isInteger(selectedValue) || selectedValue < 1 || selectedValue > maxAllowed) {
                return;
            }

            card.dataset.moveWeightCount = String(selectedValue);
        }

        function bindMoveControls(container = document) {
            container.querySelectorAll('.js-move-cut').forEach((select) => {
                select.removeEventListener('change', handleMoveCutChange);
                select.addEventListener('change', handleMoveCutChange);
            });
            container.querySelectorAll('.js-move-weight-count').forEach((select) => {
                select.removeEventListener('change', handleMoveWeightCountChange);
                select.addEventListener('change', handleMoveWeightCountChange);
            });
        }

        async function splitPickMove({ pickWeightOutId, moveWeightCount, moveCutId = null, fromPalletId = null, targetPalletId = null }) {
            const response = await fetch('{{ route('outgoing-pallets.split-pick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    pick_weight_out_id: pickWeightOutId,
                    move_weight_count: moveWeightCount,
                    move_cut_id: moveCutId,
                    from_transport_pallet_id: fromPalletId,
                    target_transport_pallet_id: targetPalletId,
                }),
            });

            if (!response.ok) {
                throw new Error('Unable to split pick');
            }

            return response.json();
        }

        async function attachPick(outgoingPalletId, pickWeightOutId) {
            const response = await fetch('{{ route('outgoing-pallets.attach-pick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    transport_pallet_id: outgoingPalletId,
                    pick_weight_out_id: pickWeightOutId,
                }),
            });

            if (!response.ok) {
                return null;
            }

            const data = await response.json();
            updatePalletWeights();
            return data;
        }

        async function detachPick(outgoingPalletId, pickWeightOutId, recombineUnloaded = true) {
            const response = await fetch('{{ route('outgoing-pallets.detach-pick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    transport_pallet_id: outgoingPalletId,
                    pick_weight_out_id: pickWeightOutId,
                    recombine_unloaded: recombineUnloaded,
                }),
            });

            if (!response.ok) {
                return null;
            }

            const data = await response.json();
            updatePalletWeights();
            return data;
        }

        async function upsertSummaryInContainer(container, summary, fromPalletId = null) {
            if (!summary) {
                return null;
            }

            const existing = container.querySelector(`[data-pick-weight-out-id="${summary.id}"]`);
            if (existing) {
                const rendered = await applyPickSummaryToElement(existing, summary);
                if (fromPalletId) {
                    rendered.dataset.fromPalletId = String(fromPalletId);
                }
                cleanupRedundantPickCards(container, summary.id, summary.pickersheet_id);
                return rendered;
            }

            const created = await createPickElement(summary, fromPalletId);
            container.appendChild(created);
            cleanupRedundantPickCards(container, summary.id, summary.pickersheet_id);
            return created;
        }

        function cleanupRedundantPickCards(container, keeperPickWeightOutId, pickerSheetId) {
            if (!container || !keeperPickWeightOutId || !pickerSheetId) {
                return;
            }

            container.querySelectorAll('[data-pick-weight-out-id]').forEach((card) => {
                const cardPickWeightOutId = Number(card.dataset.pickWeightOutId || 0);
                const cardPickerSheetId = Number(card.dataset.pickersheetId || 0);
                if (cardPickerSheetId === Number(pickerSheetId) && cardPickWeightOutId !== Number(keeperPickWeightOutId)) {
                    card.remove();
                }
            });
        }

        async function processDropOnPallet(payload, zone) {
            if (!payload.pickWeightOutId) {
                return;
            }

            const outgoingPalletId = zone.dataset.outgoingPalletId;

            const isSamePalletMove = payload.fromPalletId && String(payload.fromPalletId) === String(outgoingPalletId);
            if (isSamePalletMove) {
                return;
            }

            const dragged = document.querySelector(`[data-pick-weight-out-id="${payload.pickWeightOutId}"]`);
            const totalWeightCount = Number(payload.weightCount || dragged?.dataset?.weightCount || 0);
            const moveWeightCount = Number(payload.moveWeightCount || dragged?.dataset?.moveWeightCount || totalWeightCount || 0);
            const moveCutId = Number(payload.moveCutId || dragged?.dataset?.selectedCutId || 0) || null;
            const cutQuantities = parseCutQuantities(dragged?.dataset?.cutQuantities);
            const selectedCut = getCutEntry(cutQuantities, moveCutId);
            const maxMovableCount = moveCutId ? Number(selectedCut?.quantity || 0) : totalWeightCount;
            if (!moveWeightCount || moveWeightCount < 1 || moveWeightCount > maxMovableCount) {
                return;
            }

            const shouldSplit = totalWeightCount > 1 && (moveWeightCount < totalWeightCount || (moveCutId && maxMovableCount < totalWeightCount));
            if (shouldSplit) {
                const splitResult = await splitPickMove({
                    pickWeightOutId: payload.pickWeightOutId,
                    moveWeightCount,
                    moveCutId,
                    fromPalletId: payload.fromPalletId || null,
                    targetPalletId: outgoingPalletId,
                });

                if (dragged) {
                    const updatedDragged = await applyPickSummaryToElement(dragged, splitResult.source);
                    cleanupRedundantPickCards(updatedDragged.parentElement, splitResult.source.id, splitResult.source.pickersheet_id);
                }

                await upsertSummaryInContainer(zone, splitResult.moved, outgoingPalletId);
                bindDraggable(document);
                updatePalletWeights();
                syncUnassignedDropZoneHeight();
                return;
            }

            if (payload.fromPalletId && payload.fromPalletId !== outgoingPalletId) {
                await detachPick(payload.fromPalletId, payload.pickWeightOutId, false);
            }
            const attachResult = await attachPick(outgoingPalletId, payload.pickWeightOutId);

            if (dragged) {
                const targetSummary = attachResult?.target_pick;
                if (targetSummary) {
                    const existingInZone = zone.querySelector(`[data-pick-weight-out-id="${targetSummary.id}"]`);
                    if (existingInZone && existingInZone !== dragged) {
                        await applyPickSummaryToElement(existingInZone, targetSummary);
                        dragged.remove();
                        cleanupRedundantPickCards(zone, targetSummary.id, targetSummary.pickersheet_id);
                    } else {
                        const updatedDragged = await applyPickSummaryToElement(dragged, targetSummary);
                        updatedDragged.dataset.fromPalletId = outgoingPalletId;
                        zone.appendChild(updatedDragged);
                        cleanupRedundantPickCards(zone, targetSummary.id, targetSummary.pickersheet_id);
                    }
                } else {
                    dragged.dataset.fromPalletId = outgoingPalletId;
                    zone.appendChild(dragged);
                }
            }

            updatePalletWeights();
            syncUnassignedDropZoneHeight();
        }

        async function handleDropOnPallet(e) {
            e.preventDefault();
            const payload = JSON.parse(e.dataTransfer.getData('text/plain') || '{}');
            await processDropOnPallet(payload, e.currentTarget);
        }

        async function processDropOnUnassigned(payload) {
            if (!payload.pickWeightOutId || !payload.fromPalletId) {
                return;
            }

            const dragged = document.querySelector(`[data-pick-weight-out-id="${payload.pickWeightOutId}"]`);
            const totalWeightCount = Number(payload.weightCount || dragged?.dataset?.weightCount || 0);
            const moveWeightCount = Number(payload.moveWeightCount || dragged?.dataset?.moveWeightCount || totalWeightCount || 0);
            const moveCutId = Number(payload.moveCutId || dragged?.dataset?.selectedCutId || 0) || null;
            const cutQuantities = parseCutQuantities(dragged?.dataset?.cutQuantities);
            const selectedCut = getCutEntry(cutQuantities, moveCutId);
            const maxMovableCount = moveCutId ? Number(selectedCut?.quantity || 0) : totalWeightCount;
            if (!moveWeightCount || moveWeightCount < 1 || moveWeightCount > maxMovableCount) {
                return;
            }

            const shouldSplit = totalWeightCount > 1 && (moveWeightCount < totalWeightCount || (moveCutId && maxMovableCount < totalWeightCount));
            if (shouldSplit) {
                const splitResult = await splitPickMove({
                    pickWeightOutId: payload.pickWeightOutId,
                    moveWeightCount,
                    moveCutId,
                    fromPalletId: payload.fromPalletId,
                    targetPalletId: null,
                });

                if (dragged) {
                    const updatedDragged = await applyPickSummaryToElement(dragged, splitResult.source);
                    cleanupRedundantPickCards(updatedDragged.parentElement, splitResult.source.id, splitResult.source.pickersheet_id);
                }

                await upsertSummaryInContainer(unassigned, splitResult.moved, null);
                bindDraggable(document);
                updatePalletWeights();
                syncUnassignedDropZoneHeight();
                return;
            }

            const detachResult = await detachPick(payload.fromPalletId, payload.pickWeightOutId);
            if (dragged) {
                const movedSummary = detachResult?.moved_pick;
                if (!movedSummary) {
                    delete dragged.dataset.fromPalletId;
                    unassigned.appendChild(dragged);
                    updatePalletWeights();
                    return;
                }

                const existingInUnassigned = unassigned.querySelector(`[data-pick-weight-out-id="${movedSummary.id}"]`);
                if (existingInUnassigned && existingInUnassigned !== dragged) {
                    await applyPickSummaryToElement(existingInUnassigned, movedSummary);
                    dragged.remove();
                    cleanupRedundantPickCards(unassigned, movedSummary.id, movedSummary.pickersheet_id);
                } else {
                    const updatedDragged = await applyPickSummaryToElement(dragged, movedSummary);
                    delete updatedDragged.dataset.fromPalletId;
                    unassigned.appendChild(updatedDragged);
                    cleanupRedundantPickCards(unassigned, movedSummary.id, movedSummary.pickersheet_id);
                }
            }

            updatePalletWeights();
            syncUnassignedDropZoneHeight();
        }

        async function handleDropOnUnassigned(e) {
            e.preventDefault();
            const payload = JSON.parse(e.dataTransfer.getData('text/plain') || '{}');
            await processDropOnUnassigned(payload);
        }

        function removeTouchDragGhost(state = activeTouchDrag) {
            if (state?.ghostElement?.parentNode) {
                state.ghostElement.parentNode.removeChild(state.ghostElement);
            }
        }

        function clearTouchDragHover(state = activeTouchDrag) {
            if (state?.hoverZone) {
                state.hoverZone.classList.remove('ring-2', 'ring-blue-400');
            }

            if (state?.hoverUnassigned) {
                unassigned.classList.remove('ring-2', 'ring-blue-400');
            }
        }

        function updateTouchDropTarget(clientX, clientY) {
            if (!activeTouchDrag) {
                return;
            }

            clearTouchDragHover(activeTouchDrag);
            activeTouchDrag.hoverZone = null;
            activeTouchDrag.hoverUnassigned = false;

            const elementAtPoint = document.elementFromPoint(clientX, clientY);
            const zone = elementAtPoint?.closest?.('.pallet-drop-zone');
            if (zone) {
                activeTouchDrag.hoverZone = zone;
                zone.classList.add('ring-2', 'ring-blue-400');
                return;
            }

            if (elementAtPoint?.closest?.('#unassigned-picks')) {
                activeTouchDrag.hoverUnassigned = true;
                unassigned.classList.add('ring-2', 'ring-blue-400');
            }
        }

        function shouldIgnoreTouchDragStart(eventTarget) {
            return !!eventTarget?.closest?.('select, option, input, button, a, textarea, label');
        }

        function runTouchAutoScroll() {
            if (!activeTouchDrag) {
                return;
            }

            const speed = Number(activeTouchDrag.autoScrollSpeed || 0);
            if (speed !== 0) {
                window.scrollBy(0, speed);
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
        }

        function updateTouchAutoScroll(clientY) {
            if (!activeTouchDrag) {
                return;
            }

            const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
            const topZone = touchEdgeScrollThreshold;
            const bottomZone = Math.max(0, viewportHeight - touchEdgeScrollThreshold);

            let speed = 0;
            if (clientY < topZone) {
                speed = -Math.ceil((topZone - clientY) / 4);
            } else if (clientY > bottomZone) {
                speed = Math.ceil((clientY - bottomZone) / 4);
            }

            speed = Math.max(-touchEdgeScrollMaxStep, Math.min(touchEdgeScrollMaxStep, speed));
            activeTouchDrag.autoScrollSpeed = speed;
        }

        function onTouchStart(e) {
            if (!e.touches || e.touches.length !== 1) {
                return;
            }

            if (shouldIgnoreTouchDragStart(e.target)) {
                return;
            }

            const card = e.currentTarget;
            const payload = createDragPayloadFromElement(card);
            if (!payload) {
                return;
            }

            const touch = e.touches[0];
            const rect = card.getBoundingClientRect();
            const ghost = card.cloneNode(true);
            ghost.style.position = 'fixed';
            ghost.style.left = `${touch.clientX - (rect.width / 2)}px`;
            ghost.style.top = `${touch.clientY - 25}px`;
            ghost.style.width = `${rect.width}px`;
            ghost.style.pointerEvents = 'none';
            ghost.style.opacity = '0.85';
            ghost.style.zIndex = '9999';
            ghost.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.2)';
            ghost.classList.add('ring-2', 'ring-blue-300');
            document.body.appendChild(ghost);

            activeTouchDrag = {
                payload,
                sourceElement: card,
                ghostElement: ghost,
                hoverZone: null,
                hoverUnassigned: false,
                autoScrollFrame: null,
                autoScrollSpeed: 0,
                lastTouchClientX: touch.clientX,
                lastTouchClientY: touch.clientY,
            };

            activeTouchDrag.autoScrollFrame = window.requestAnimationFrame(runTouchAutoScroll);

            card.style.opacity = '0.5';
            updateTouchDropTarget(touch.clientX, touch.clientY);
            updateTouchAutoScroll(touch.clientY);
            e.preventDefault();
        }

        function onTouchMove(e) {
            if (!activeTouchDrag || !e.touches || e.touches.length !== 1) {
                return;
            }

            const touch = e.touches[0];
            const ghost = activeTouchDrag.ghostElement;
            if (ghost) {
                ghost.style.left = `${touch.clientX - (ghost.offsetWidth / 2)}px`;
                ghost.style.top = `${touch.clientY - 25}px`;
            }

            activeTouchDrag.lastTouchClientX = touch.clientX;
            activeTouchDrag.lastTouchClientY = touch.clientY;

            updateTouchDropTarget(touch.clientX, touch.clientY);
            updateTouchAutoScroll(touch.clientY);
            e.preventDefault();
        }

        async function onTouchEnd() {
            if (!activeTouchDrag) {
                return;
            }

            const state = activeTouchDrag;
            activeTouchDrag = null;

            stopTouchAutoScroll(state);
            clearTouchDragHover(state);
            removeTouchDragGhost(state);

            if (state.sourceElement) {
                state.sourceElement.style.opacity = '';
            }

            if (state.hoverZone) {
                await processDropOnPallet(state.payload, state.hoverZone);
                return;
            }

            if (state.hoverUnassigned) {
                await processDropOnUnassigned(state.payload);
            }
        }

        function bindTouchDraggable(container) {
            const elements = [];
            if (container?.matches?.('[draggable="true"]')) {
                elements.push(container);
            }

            if (container?.querySelectorAll) {
                container.querySelectorAll('[draggable="true"]').forEach((el) => {
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

        function bindDraggable(container) {
            const elements = [];
            if (container?.matches?.('[draggable="true"]')) {
                elements.push(container);
            }

            if (container?.querySelectorAll) {
                container.querySelectorAll('[draggable="true"]').forEach((el) => {
                    elements.push(el);
                });
            }

            elements.forEach((el) => {
                el.removeEventListener('dragstart', onDragStart);
                el.addEventListener('dragstart', onDragStart);
            });

            bindTouchDraggable(container);
        }

        function bindDropZones() {
            document.querySelectorAll('.pallet-drop-zone').forEach((zone) => {
                zone.removeEventListener('dragover', allowDrop);
                zone.removeEventListener('drop', handleDropOnPallet);
                zone.addEventListener('dragover', allowDrop);
                zone.addEventListener('drop', handleDropOnPallet);
            });
            unassigned.removeEventListener('dragover', allowDrop);
            unassigned.removeEventListener('drop', handleDropOnUnassigned);
            unassigned.addEventListener('dragover', allowDrop);
            unassigned.addEventListener('drop', handleDropOnUnassigned);
        }

        let isUpdatingWeights = false;
        function updatePalletWeights() {
            if (isUpdatingWeights) {
                return;
            }
            isUpdatingWeights = true;

            try {
                document.querySelectorAll('.pallet-card').forEach((card) => {
                    const palletId = card.dataset.outgoingPalletId;
                    const dropZone = card.querySelector('.pallet-drop-zone');
                    let totalWeight = 0;

                    dropZone.querySelectorAll('[data-pick-weight-out-id]').forEach((pick) => {
                        const weight = Number(pick.dataset.totalWeight || 0);
                        if (!Number.isNaN(weight)) {
                            totalWeight += weight;
                        }
                    });

                    const typeSelector = card.querySelector('.js-pallet-type-selector');
                    const typeId = typeSelector ? typeSelector.value : card.dataset.palletTypeId || 1;
                    card.dataset.palletTypeId = typeId;

                    const type = palletTypes.find(t => t.id == typeId);
                    const maxWeight = type ? type.max_weight : 300;
                    const isOverweight = totalWeight > maxWeight;

                    let weightDisplay = card.querySelector('.current-weight-display');
                    if (!weightDisplay) {
                        weightDisplay = document.createElement('div');
                        weightDisplay.className = 'current-weight-display mb-2';
                        const typeDiv = card.querySelector('.js-pallet-type-selector')?.parentNode;
                        if (typeDiv) {
                            typeDiv.parentNode.insertBefore(weightDisplay, typeDiv.nextSibling);
                        }
                    }

                    weightDisplay.innerHTML = `
                        <strong>Current Weight:</strong>
                        <span class="${isOverweight ? 'text-danger font-weight-bold' : 'text-gray-700'}">
                            ${totalWeight.toFixed(3)} kg
                            ${isOverweight ? '<i class="fas fa-exclamation-triangle"></i> OVERWEIGHT' : ''}
                        </span>
                    `;
                });
            } finally {
                isUpdatingWeights = false;
            }
        }

        async function handlePalletTypeChange(e) {
            const typeSelector = e.target;
            const palletId = typeSelector.dataset.palletId;
            const newTypeId = typeSelector.value;

            const response = await fetch('{{ route('outgoing-pallets.update-type') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    transport_pallet_id: palletId,
                    transport_pallet_type_id: newTypeId,
                }),
            });

            if (response.ok) {
                updatePalletWeights();
            }
        }

        function bindTypeSelectors() {
            document.querySelectorAll('.js-pallet-type-selector').forEach((selector) => {
                selector.removeEventListener('change', handlePalletTypeChange);
                selector.addEventListener('change', handlePalletTypeChange);
            });
        }

        async function createPallet() {
            const response = await fetch('{{ route('outgoing-pallets.create') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    customer_id: customerId,
                    address_id: addressId,
                    transport_pallet_type_id: 1,
                }),
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const palletId = data.id;
            const selectedTypeId = 1;
            const palletType = palletTypes.find(t => t.id == selectedTypeId);

            const card = document.createElement('div');
            card.className = 'border rounded p-2 mb-3 pallet-card';
            card.dataset.outgoingPalletId = palletId;
            card.dataset.palletTypeId = selectedTypeId;
            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div><strong>Pallet #${palletId}</strong>
                    <button class="rounded bg-red-500 hover:bg-red-700 w-6 h-6 btn-danger js-delete-pallet" data-outgoing-pallet-id="${palletId}"><i class="fas fa-trash text-red-100"></i></button></div>
                </div>
                <div class="mb-2">
                    <strong>Type:</strong>
                    <select class="form-control form-control-sm d-inline js-pallet-type-selector" style="width: auto;" data-pallet-id="${palletId}">
                        ${palletTypes.map(t => `<option value="${t.id}" ${t.id == selectedTypeId ? 'selected' : ''}>${t.name} (Max: ${t.max_weight} kg)</option>`).join('')}
                    </select>
                </div>
                <div class="current-weight-display mb-2">
                    <strong>Current Weight:</strong> <span class="text-gray-700">0 kg</span>
                </div>
                <div class="pallet-drop-zone border rounded bg-blue-50" data-outgoing-pallet-id="${palletId}" style="min-height: 120px;"></div>
            `;

            palletsList.appendChild(card);
            bindDropZones();
            bindDeleteButtons();
            bindTypeSelectors();
            updatePalletWeights();
            syncUnassignedDropZoneHeight();
        }
        async function deletePallet(outgoingPalletId) {
            const response = await fetch(`/outgoing-pallets/${outgoingPalletId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (!response.ok) {
                return;
            }

            const card = document.querySelector(`.pallet-card[data-outgoing-pallet-id="${outgoingPalletId}"]`);
            if (card) {
                card.querySelectorAll('[data-pick-weight-out-id]').forEach((item) => {
                    delete item.dataset.fromPalletId;
                    unassigned.appendChild(item);
                });
                card.remove();
            }

            syncUnassignedDropZoneHeight();
        }

        function bindDeleteButtons() {
            document.querySelectorAll('.js-delete-pallet').forEach((btn) => {
                btn.onclick = () => deletePallet(btn.dataset.outgoingPalletId);
            });
        }

        bindDraggable(document);
        bindDropZones();
        bindDeleteButtons();
        bindTypeSelectors();
        bindMoveControls();
        observeUnassignedDropZoneHeight();
        window.addEventListener('resize', syncUnassignedDropZoneHeight);
        addPalletBtn?.addEventListener('click', createPallet);
        updatePalletWeights();
        syncUnassignedDropZoneHeight();
</script>

