@props(['pickWeightOut' => null, 'pickerSheet' => null, 'fromPalletId' => null])

@php
    $resolvedPickerSheet = $pickerSheet ?? $pickWeightOut?->pickerSheet;
    $weightCount = $pickWeightOut ? count($pickWeightOut->getWeights()) : 0;
    $totalWeight = $pickWeightOut ? $pickWeightOut->getTotalWeight() : 0;
@endphp

<div class="border rounded p-2 mb-2 bg-white"
    draggable="{{ $pickWeightOut ? 'true' : 'false' }}"
    data-pick-weight-out-id="{{ $pickWeightOut?->id }}"
    data-pickersheet-id="{{ $resolvedPickerSheet?->id }}"
    data-weight-count="{{ $weightCount }}"
    data-total-weight="{{ $totalWeight }}"
    data-move-weight-count="{{ $weightCount > 0 ? $weightCount : 0 }}"
    @if($fromPalletId) data-from-pallet-id="{{ $fromPalletId }}" @endif>
<div><strong>Pick #{{ $resolvedPickerSheet?->id }}</strong> {{ $resolvedPickerSheet?->estimated_delivery_date }} {{ $resolvedPickerSheet?->orderReferenceNumber }}
@if($pickWeightOut)
 <strong>Weight: {{ number_format($pickWeightOut->getTotalWeight(), 3, '.', '') }} kg</strong> {{ $weightCount }} case{{ $weightCount == 1 ? '' : 's' }}</div>

    @if($weightCount > 1)
        <div class="mt-1 d-flex items-center gap-2">
            <span class="text-sm text-gray-600">Move Qty</span>
            <select class="form-control form-control-sm js-move-weight-count" style="width: 90px;">
                @for($i = 1; $i <= $weightCount; $i++)
                    <option value="{{ $i }}" @if($i === $weightCount) selected @endif>{{ $i }}</option>
                @endfor
            </select>
        </div>
    @endif
@else
</div>
    <div class="text-sm text-gray-500">No pick weight record</div>
@endif
</div>
