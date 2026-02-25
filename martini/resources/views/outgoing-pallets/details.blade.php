<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __("Deliveries for: ".$customer->businessname. " : ".$customer->{'address'.$address_id.'_1'}. " : ".$customer->{'postcode_'.$address_id}) }}
        </h2>
    </x-slot>
    <a href="{{ route('outgoing-pallets.index') }}" class="inline-block mt-4 px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">Back to List</a>
    <div class="pl-4 pt-4">
        <div class="row">
            <div class="col-md-6 mb-4">
                <h3 class="font-semibold text-md mb-2">Unloaded Picks (Next 3 Days)</h3>
                <div id="unassigned-picks" class="border rounded p-2" style="min-height: 200px;">
                    @if($deliveries->isEmpty())
                        <div class="text-gray-600">No unloaded picks found for this customer/address in the next 3 days.</div>
                    @else
                        @foreach($deliveries as $delivery)
                            @php
                                $pickWeightOut = $delivery->pickWeightOut->first();
                            @endphp
                            <x-draggable-pick :pickWeightOut="$pickWeightOut" :pickerSheet="$delivery"/>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="font-semibold text-md">Outgoing Pallets <button id="add-pallet" class="btn btn-sm btn-primary rounded bg-green-500 hover:bg-green-700 w-6 h-6"><i class="fa fa-plus text-white" style="font-size:12pt;"></i></button></h3>
                </div>
                <div id="pallets-list">
                    @if($outgoingPallets->isEmpty())
                        <div class="text-gray-600">No outgoing pallets found for this customer/address.</div>
                    @else
                        @foreach($outgoingPallets as $pallet)
                            <div class="border rounded p-2 mb-3 pallet-card" data-outgoing-pallet-id="{{ $pallet->id }}" data-pallet-type-id="{{ $pallet->outgoing_pallet_type_id }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div><strong>Pallet #{{ $pallet->id }}</strong>
                                        <button class="rounded bg-red-500 hover:bg-red-700 w-6 h-6 btn-danger js-delete-pallet" data-outgoing-pallet-id="{{ $pallet->id }}"><i class="fas fa-trash text-red-100"></i></button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <strong>Type:</strong>
                                    <select class="form-control form-control-sm d-inline js-pallet-type-selector" style="width: auto;" data-pallet-id="{{ $pallet->id }}" data-current-type="{{ $pallet->outgoing_pallet_type_id }}">
                                        @foreach($palletTypes as $type)
                                            <option value="{{ $type->id }}" @if($type->id == $pallet->outgoing_pallet_type_id) selected @endif>
                                                {{ $type->name }} (Max: {{ $type->max_weight }} kg)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="pallet-drop-zone border rounded p-2 mt-2"
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

        function onDragStart(e) {
            const target = e.currentTarget;
            const pickWeightOutId = target?.dataset?.pickWeightOutId;
            if (!pickWeightOutId) {
                return;
            }
            const totalWeightCount = Number(target.dataset.weightCount || 0);
            const selectedMoveWeightCount = Number(target.dataset.moveWeightCount || totalWeightCount || 0);
            const payload = {
                pickWeightOutId,
                fromPalletId: target.dataset.fromPalletId || null,
                weightCount: totalWeightCount,
                moveWeightCount: selectedMoveWeightCount,
            };
            e.dataTransfer.setData('text/plain', JSON.stringify(payload));
        }

        function allowDrop(e) {
            e.preventDefault();
        }

        function renderPickCardHtml(summary) {
            const safeWeight = Number(summary.total_weight || 0).toFixed(3);
            const count = Number(summary.weight_count || 0);
            const suffix = count === 1 ? '' : 's';
            const options = Array.from({ length: count }, (_, index) => {
                const value = index + 1;
                const selected = value === count ? 'selected' : '';
                return `<option value="${value}" ${selected}>${value}</option>`;
            }).join('');
            const moveControls = count > 1
                ? `<div class="mt-1 d-flex items-center gap-2">
                        <span class="text-sm text-gray-600">Move Qty</span>
                        <select class="form-control form-control-sm js-move-weight-count" style="width: 90px;">${options}</select>
                   </div>`
                : '';

            return `
                <div><strong>Pick #${summary.pickersheet_id ?? ''}</strong> ${summary.estimated_delivery_date ?? ''} ${summary.order_reference_number ?? ''} <strong>Weight: ${safeWeight} kg</strong> ${count} case${suffix}</div>
                ${moveControls}
            `;
        }

        function applyPickSummaryToElement(element, summary) {
            element.dataset.pickWeightOutId = String(summary.id);
            element.dataset.pickersheetId = String(summary.pickersheet_id ?? '');
            element.dataset.weightCount = String(summary.weight_count ?? 0);
            element.dataset.totalWeight = String(Number(summary.total_weight || 0));
            element.dataset.moveWeightCount = String(summary.weight_count ?? 0);
            element.innerHTML = renderPickCardHtml(summary);
            bindMoveCountControls(element);
        }

        function createPickElement(summary, fromPalletId = null) {
            const el = document.createElement('div');
            el.className = 'border rounded p-2 mb-2 bg-white';
            el.draggable = true;
            el.dataset.pickWeightOutId = String(summary.id);
            el.dataset.pickersheetId = String(summary.pickersheet_id ?? '');
            el.dataset.weightCount = String(summary.weight_count ?? 0);
            el.dataset.totalWeight = String(Number(summary.total_weight || 0));
            el.dataset.moveWeightCount = String(summary.weight_count ?? 0);
            if (fromPalletId) {
                el.dataset.fromPalletId = String(fromPalletId);
            }
            el.innerHTML = renderPickCardHtml(summary);
            bindMoveCountControls(el);
            return el;
        }

        function handleMoveWeightCountChange(e) {
            const select = e.currentTarget;
            const card = select.closest('[data-pick-weight-out-id]');
            if (!card) {
                return;
            }

            const totalWeightCount = Number(card.dataset.weightCount || 0);
            const selectedValue = Number(select?.value || totalWeightCount || 0);
            if (!Number.isInteger(selectedValue) || selectedValue < 1 || selectedValue > totalWeightCount) {
                return;
            }

            card.dataset.moveWeightCount = String(selectedValue);
        }

        function bindMoveCountControls(container = document) {
            container.querySelectorAll('.js-move-weight-count').forEach((select) => {
                select.removeEventListener('change', handleMoveWeightCountChange);
                select.addEventListener('change', handleMoveWeightCountChange);
            });
        }

        async function splitPickMove({ pickWeightOutId, moveWeightCount, fromPalletId = null, targetPalletId = null }) {
            const response = await fetch('{{ route('outgoing-pallets.split-pick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    pick_weight_out_id: pickWeightOutId,
                    move_weight_count: moveWeightCount,
                    from_outgoing_pallet_id: fromPalletId,
                    target_outgoing_pallet_id: targetPalletId,
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
                    outgoing_pallet_id: outgoingPalletId,
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
                    outgoing_pallet_id: outgoingPalletId,
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

        function upsertSummaryInContainer(container, summary, fromPalletId = null) {
            if (!summary) {
                return null;
            }

            const existing = container.querySelector(`[data-pick-weight-out-id="${summary.id}"]`);
            if (existing) {
                applyPickSummaryToElement(existing, summary);
                if (fromPalletId) {
                    existing.dataset.fromPalletId = String(fromPalletId);
                } else {
                    delete existing.dataset.fromPalletId;
                }
                cleanupRedundantPickCards(container, summary.id, summary.pickersheet_id);
                return existing;
            }

            const created = createPickElement(summary, fromPalletId);
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

        function bindDraggable(container) {
            container.querySelectorAll('[draggable="true"]').forEach((el) => {
                el.removeEventListener('dragstart', onDragStart);
                el.addEventListener('dragstart', onDragStart);
            });
        }

        async function handleDropOnPallet(e) {
            e.preventDefault();
            const payload = JSON.parse(e.dataTransfer.getData('text/plain') || '{}');
            if (!payload.pickWeightOutId) {
                return;
            }

            const zone = e.currentTarget;
            const outgoingPalletId = zone.dataset.outgoingPalletId;

            const isSamePalletMove = payload.fromPalletId && String(payload.fromPalletId) === String(outgoingPalletId);
            if (isSamePalletMove) {
                return;
            }

            const dragged = document.querySelector(`[data-pick-weight-out-id="${payload.pickWeightOutId}"]`);
            const totalWeightCount = Number(payload.weightCount || dragged?.dataset?.weightCount || 0);
            const moveWeightCount = Number(payload.moveWeightCount || dragged?.dataset?.moveWeightCount || totalWeightCount || 0);
            if (!moveWeightCount || moveWeightCount < 1 || moveWeightCount > totalWeightCount) {
                return;
            }

            if (totalWeightCount > 1 && moveWeightCount < totalWeightCount) {
                const splitResult = await splitPickMove({
                    pickWeightOutId: payload.pickWeightOutId,
                    moveWeightCount,
                    fromPalletId: payload.fromPalletId || null,
                    targetPalletId: outgoingPalletId,
                });

                if (dragged) {
                    applyPickSummaryToElement(dragged, splitResult.source);
                    cleanupRedundantPickCards(dragged.parentElement, splitResult.source.id, splitResult.source.pickersheet_id);
                }

                upsertSummaryInContainer(zone, splitResult.moved, outgoingPalletId);
                bindDraggable(document);
                updatePalletWeights();
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
                        applyPickSummaryToElement(existingInZone, targetSummary);
                        dragged.remove();
                        cleanupRedundantPickCards(zone, targetSummary.id, targetSummary.pickersheet_id);
                    } else {
                        applyPickSummaryToElement(dragged, targetSummary);
                        dragged.dataset.fromPalletId = outgoingPalletId;
                        zone.appendChild(dragged);
                        cleanupRedundantPickCards(zone, targetSummary.id, targetSummary.pickersheet_id);
                    }
                } else {
                    dragged.dataset.fromPalletId = outgoingPalletId;
                    zone.appendChild(dragged);
                }
            }
        }

        async function handleDropOnUnassigned(e) {
            e.preventDefault();
            const payload = JSON.parse(e.dataTransfer.getData('text/plain') || '{}');
            if (!payload.pickWeightOutId || !payload.fromPalletId) {
                return;
            }

            const dragged = document.querySelector(`[data-pick-weight-out-id="${payload.pickWeightOutId}"]`);
            const totalWeightCount = Number(payload.weightCount || dragged?.dataset?.weightCount || 0);
            const moveWeightCount = Number(payload.moveWeightCount || dragged?.dataset?.moveWeightCount || totalWeightCount || 0);
            if (!moveWeightCount || moveWeightCount < 1 || moveWeightCount > totalWeightCount) {
                return;
            }

            if (totalWeightCount > 1 && moveWeightCount < totalWeightCount) {
                const splitResult = await splitPickMove({
                    pickWeightOutId: payload.pickWeightOutId,
                    moveWeightCount,
                    fromPalletId: payload.fromPalletId,
                    targetPalletId: null,
                });

                if (dragged) {
                    applyPickSummaryToElement(dragged, splitResult.source);
                    cleanupRedundantPickCards(dragged.parentElement, splitResult.source.id, splitResult.source.pickersheet_id);
                }

                upsertSummaryInContainer(unassigned, splitResult.moved, null);
                bindDraggable(document);
                updatePalletWeights();
                return;
            }

            const detachResult = await detachPick(payload.fromPalletId, payload.pickWeightOutId);
            if (dragged) {
                const movedSummary = detachResult?.moved_pick;
                if (!movedSummary) {
                    delete dragged.dataset.fromPalletId;
                    unassigned.appendChild(dragged);
                    return;
                }

                const existingInUnassigned = unassigned.querySelector(`[data-pick-weight-out-id="${movedSummary.id}"]`);
                if (existingInUnassigned && existingInUnassigned !== dragged) {
                    applyPickSummaryToElement(existingInUnassigned, movedSummary);
                    dragged.remove();
                    cleanupRedundantPickCards(unassigned, movedSummary.id, movedSummary.pickersheet_id);
                } else {
                    applyPickSummaryToElement(dragged, movedSummary);
                    delete dragged.dataset.fromPalletId;
                    unassigned.appendChild(dragged);
                    cleanupRedundantPickCards(unassigned, movedSummary.id, movedSummary.pickersheet_id);
                }
            }
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
                    outgoing_pallet_id: palletId,
                    outgoing_pallet_type_id: newTypeId,
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
                    outgoing_pallet_type_id: 1,
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
                    <div><strong>Pallet #${palletId}</strong></div>
                    <button class="rounded bg-red-500 hover:bg-red-700 w-6 h-6 btn-danger js-delete-pallet" data-outgoing-pallet-id="${palletId}"><i class="fas fa-trash text-red-100"></i></button>
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
                <div class="pallet-drop-zone border rounded p-2 mt-2" data-outgoing-pallet-id="${palletId}" style="min-height: 120px;"></div>
            `;

            palletsList.appendChild(card);
            bindDropZones();
            bindDeleteButtons();
            bindTypeSelectors();
            updatePalletWeights();
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
        bindMoveCountControls();
        addPalletBtn?.addEventListener('click', createPallet);
        updatePalletWeights();
</script>

