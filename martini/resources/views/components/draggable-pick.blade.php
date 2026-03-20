@props(['pickWeightOut' => null, 'pickerSheet' => null, 'fromPalletId' => null, 'selectedCutId' => null, 'moveWeightCount' => null])

@php
    $resolvedPickerSheet = $pickerSheet ?? $pickWeightOut?->pickerSheet;
    $weightCount = $pickWeightOut ? count($pickWeightOut->getWeights()) : 0;
    $totalWeight = $pickWeightOut ? $pickWeightOut->getTotalWeight() : 0;
    $cutQuantities = $pickWeightOut ? $pickWeightOut->getCutQuantities() : [];
    $normalizedSelectedCutId = ($selectedCutId === null || $selectedCutId === '') ? null : (int) $selectedCutId;
    $selectedCutSummary = null;
    if ($normalizedSelectedCutId !== null) {
        foreach ($cutQuantities as $item) {
            if ((int) ($item['cut_id'] ?? 0) === $normalizedSelectedCutId) {
                $selectedCutSummary = $item;
                break;
            }
        }
    }

    $maxSelectableQty = $selectedCutSummary ? (int) ($selectedCutSummary['quantity'] ?? 0) : $weightCount;
    $maxSelectableQty = max(0, $maxSelectableQty);

    $requestedMoveQty = $moveWeightCount === null ? null : (int) $moveWeightCount;
    $defaultMoveQty = $requestedMoveQty === null
        ? $maxSelectableQty
        : min(max(1, $requestedMoveQty), max(1, $maxSelectableQty));
@endphp

<div class="border rounded p-2 mb-2 bg-white"
    draggable="{{ $pickWeightOut ? 'true' : 'false' }}"
    data-pick-weight-out-id="{{ $pickWeightOut?->id }}"
    data-pickersheet-id="{{ $resolvedPickerSheet?->id }}"
    data-weight-count="{{ $weightCount }}"
    data-total-weight="{{ $totalWeight }}"
    data-cut-quantities='@json($cutQuantities)'
    data-selected-cut-id="{{ $selectedCutSummary ? (int) $selectedCutSummary['cut_id'] : '' }}"
    data-move-weight-count="{{ $defaultMoveQty > 0 ? $defaultMoveQty : 0 }}"
    @if($fromPalletId) data-from-pallet-id="{{ $fromPalletId }}" @endif>
<div><strong>Pick #{{ $resolvedPickerSheet?->id }}</strong> {{ $resolvedPickerSheet?->estimated_delivery_date }} {{ $resolvedPickerSheet?->orderReferenceNumber }}
@if($pickWeightOut)
 <strong>Weight: {{ number_format($pickWeightOut->getTotalWeight(), 3, '.', '') }} kg</strong></div>

    @if(count($cutQuantities) > 0)
        <div class="mt-1 text-sm text-gray-700">
            @foreach($cutQuantities as $cutSummary)
                <div>
                    <strong>{{ $cutSummary['cut_name'] }}</strong>: {{ $cutSummary['quantity'] }} unit{{ (int) $cutSummary['quantity'] === 1 ? '' : 's' }}
                </div>
            @endforeach
        </div>

        <div class="mt-1 d-flex items-center gap-2">
            <span class="text-sm text-gray-600">Move Qty</span>
            <select class="form-control form-control-sm js-move-weight-count" style="width: 90px;padding-top: 2px;padding-bottom: 2px;">
                @for($i = 1; $i <= $defaultMoveQty; $i++)
                    <option value="{{ $i }}" @if($i === $defaultMoveQty) selected @endif>{{ $i }}</option>
                @endfor
            </select>
            <span class="text-sm text-gray-600">Of Cut</span>
            <select class="form-control form-control-sm js-move-cut" style="min-width: 140px;width:40%;padding-top: 2px;padding-bottom: 2px;">
                <option value="" @if(!$selectedCutSummary) selected @endif>All ({{ $weightCount }})</option>
                @foreach($cutQuantities as $cutSummary)
                    <option value="{{ $cutSummary['cut_id'] }}" @if($selectedCutSummary && (int) $cutSummary['cut_id'] === (int) $selectedCutSummary['cut_id']) selected @endif>
                        {{ $cutSummary['cut_name'] }}
                    </option>
                @endforeach
            </select>

        </div>
    @endif
@else
</div>
    <div class="text-sm text-gray-500">No pick weight record</div>
@endif
</div>
