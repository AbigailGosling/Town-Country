<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Truck Load</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            color: #000000;
            font-size: 11px;
            background: #ffffff;
        }
        .page {
            padding: 16px;
        }
        .top-bar {
            background: #ffffff;
            border: 1px solid #000000;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }
        .title {
            margin: 0 0 8px 0;
            font-size: 20px;
            font-weight: 700;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
        }
        .meta td {
            padding: 3px 0;
            vertical-align: top;
        }
        .meta .label {
            width: 120px;
            font-weight: 700;
            color: #000000;
        }
        .truck {
            background: #ffffff;
            border: 1px solid #000000;
            border-radius: 8px;
            padding: 10px;
        }
        .truck-title {
            text-align: center;
            font-weight: 700;
            color: #000000;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .truck-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .truck-header td {
            vertical-align: middle;
        }
        .plate {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #000000;
            background: #ffffff;
            color: #000000;
            font-weight: 700;
            letter-spacing: 0.06em;
            margin-right: 6px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-weight: 600;
            margin-right: 6px;
        }
        .payload-badge {
            background: #ffffff;
            color: #000000;
            border: 1px solid #000000;
        }
        .weight-badge {
            background: #ffffff;
            color: #000000;
            border: 1px solid #000000;
        }
        .layout {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }
        .slot {
            border: 1px dashed #000000;
            border-radius: 6px;
            background: #ffffff;
            min-height: 72px;
            padding: 6px;
            vertical-align: top;
        }
        .slot.euro-only {
            background: #ffffff;
            border-color: #000000;
        }
        .slot.occupied {
            border-style: solid;
            border-color: #000000;
            background: #ffffff;
        }
        .slot-head {
            font-size: 10px;
            color: #000000;
            margin-bottom: 3px;
        }
        .slot-order {
            font-weight: 700;
            font-size: 10px;
            margin-bottom: 2px;
        }
        .slot-sub {
            color: #000000;
            font-size: 9px;
            margin-bottom: 2px;
        }
        .slot-meta {
            color: #000000;
            font-size: 9px;
            font-weight: 600;
        }
        .slot-frozen-badge {
            display: inline-block;
            padding: 1px 6px;
            border: 1px solid #000000;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        .row-weight {
            text-align: center;
            background: #ffffff;
            border: 1px solid #000000;
            border-radius: 6px;
            font-weight: 700;
            color: #000000;
            width: 72px;
            padding: 6px 4px;
            font-size: 10px;
        }
        .manifest {
            margin-top: 12px;
            width: 100%;
            border-collapse: collapse;
        }
        .manifest th, .manifest td {
            border: 1px solid #000000;
            padding: 5px 6px;
            vertical-align: top;
        }
        .manifest th {
            background: #ffffff;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
        }
        .right {
            text-align: right;
        }
        .empty {
            margin-top: 14px;
            font-weight: 600;
            color: #000000;
        }
    </style>
</head>
<body>
    @php
        $maxRows = max(1, (int) ($maxRows ?? 5));
        $maxSlots = $maxRows * 3;
        $slotMap = [];
        $rowTotals = array_fill(1, $maxRows, 0);
        foreach ($rows as $row) {
            $slotIndex = ((int)$row['row'] - 1) * 3 + (int)$row['column'];
            if ($slotIndex >= 1 && $slotIndex <= $maxSlots) {
                $slotMap[$slotIndex] = $row;
                $rowTotals[(int)$row['row']] += (int)$row['weightKg'];
            }
        }
        $payloadValue = $vehicle->payload ?? '—';
    @endphp

    <div class="page">
        <div class="top-bar">
            <h1 class="title">Pallet Loader - Truck Load</h1>
            <table class="meta">
                <tr>
                    <td class="label">Vehicle</td>
                    <td>{{ $vehicle->reg }}</td>
                    <td class="label">Depot</td>
                    <td>{{ $depotName ?: ($vehicle->site->name ?? '') }}</td>
                </tr>
                <tr>
                    <td class="label">Delivery Date</td>
                    <td>{{ $dueDate ?: 'All dates' }}</td>
                    <td class="label">Generated</td>
                    <td>{{ $generatedAt->format('Y-m-d H:i') }}</td>
                </tr>
            </table>
        </div>

        <div class="truck">
            <div class="truck-title">Bulkhead / front of vehicle</div>
            <table class="truck-header">
                <tr>
                    <td>
                        <span class="plate">{{ $vehicle->reg }}</span>
                        <span class="badge payload-badge">Payload: {{ $payloadValue }}kg</span>
                        <span class="badge weight-badge">Total payload: {{ $totalWeight }} kg</span>
                    </td>
                </tr>
            </table>

            <table class="layout">
                @for ($r = 1; $r <= $maxRows; $r++)
                    <tr>
                        @for ($c = 1; $c <= 3; $c++)
                            @php
                                $slotIndex = ($r - 1) * 3 + $c;
                                $slot = $slotMap[$slotIndex] ?? null;
                                $slotClass = 'slot';
                                if ($c === 3) {
                                    $slotClass .= ' euro-only';
                                }
                                if ($slot) {
                                    $slotClass .= ' occupied';
                                }
                            @endphp
                            <td class="{{ $slotClass }}">
                                <div class="slot-head">Slot {{ $slotIndex }} (R{{ $r }} C{{ $c }})</div>
                                @if ($slot)
                                    <div class="slot-order">{{ $slot['customerName'] }}</div>
                                    <div class="slot-sub">{{ $slot['address'] }} {{ $slot['postcode'] }}</div>
                                    <div class="slot-meta">{{ $slot['weightKg'] }}kg • {{ $slot['palletType'] }} • {{ strtoupper((string) $slot['freshFrozen']) }}</div>
                                    @if (strtoupper((string) ($slot['freshFrozen'] ?? '')) === 'FROZEN')
                                        <div class="slot-frozen-badge">Frozen</div>
                                    @endif
                                    @if (!empty($slot['contentsPreview']))
                                        <div class="slot-sub">{{ $slot['contentsPreview'] }}</div>
                                    @endif
                                    <div class="slot-sub">DN: {{ $slot['deliveryNoteNumber'] }}</div>
                                @else
                                    <div class="slot-sub">{{ $c === 3 ? 'Euro pallet only' : 'Empty slot' }}</div>
                                @endif
                            </td>
                        @endfor
                        <td class="row-weight">{{ $rowTotals[$r] }} kg</td>
                    </tr>
                @endfor
            </table>
        </div>

        @if (count($rows) === 0)
            <div class="empty">No pallets allocated for this truck and filter.</div>
        @else
            <table class="manifest">
                <thead>
                    <tr>
                        <th>Slot</th>
                        <th>Pallet</th>
                        <th>Delivery Note</th>
                        <th>Customer</th>
                        <th>Postcode</th>
                        <th>Type</th>
                        <th>Contents</th>
                        <th>Temp</th>
                        <th class="right">Weight (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>R{{ $row['row'] }} C{{ $row['column'] }}</td>
                            <td>{{ $row['palletId'] }}</td>
                            <td>{{ $row['deliveryNoteNumber'] }}</td>
                            <td>{{ $row['customerName'] }}</td>
                            <td>{{ $row['postcode'] }}</td>
                            <td>{{ $row['palletType'] }}</td>
                            <td>{{ $row['contentsPreview'] ?? '' }}</td>
                            <td>{{ $row['freshFrozen'] }}</td>
                            <td class="right">{{ $row['weightKg'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <div>
            <table style="margin-top: 12px; width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="border: 1px solid #000000; padding: 6px; font-weight: 700;">Please collect any empty pallets and trays from customers. Please ensure that lorries are returned at the end of the day with a full tank of fuel and AdBlue is filled up on return to the yard.</td>
                </tr>
            </table>
        </div>
        <div>
            <table style="margin-top: 12px; width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th></th>
                        <th style="width:70%">Additional Notes</th>
                    </tr>
                </thead>
                <tr>
                    <td style="border: 1px solid #000000; padding: 6px; font-weight: 700; height: 100px; vertical-align: top; text-align: left;">Loaded By</td>
                    <td style="border: 1px solid #000000; padding: 6px;" rowspan="2"></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000000; padding: 6px; font-weight: 700; height: 100px; vertical-align: top; text-align: left;">Checked By</td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
