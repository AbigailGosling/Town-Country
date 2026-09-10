<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Truck Load Contents</title>
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
        .manifest {
            width: 100%;
            border-collapse: collapse;
        }
        .manifest th,
        .manifest td {
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
        .manifest-preline {
            white-space: pre-line;
            line-height: 1.25;
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
    <div class="page">
        <div class="top-bar">
            <h1 class="title">Pallet Loader - Truck Load Contents</h1>
            <table class="meta">
                <tr>
                    <td class="label">Vehicle</td>
                    <td>{{ $vehicle->reg }}</td>
                    <td class="label">Depot</td>
                    <td>{{ $depotName ?: ($vehicle->site->name ?? '') }}</td>
                    <td class="label">Load Sheet</td>
                    <td>{{ $loadSheetId ? '#'.$loadSheetId : '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Delivery Date</td>
                    <td>{{ $dueDate ?: 'All dates' }}</td>
                    <td class="label">Generated</td>
                    <td>{{ $generatedAt->format('Y-m-d H:i') }}</td>
                    <td class="label">Total Weight</td>
                    <td>{{ (int) $totalWeight }} kg</td>
                </tr>
            </table>
        </div>

        @if (count($rows) === 0)
            <div class="empty">No pallets allocated for this truck and filter.</div>
        @else
            <table class="manifest">
                <thead>
                    <tr>
                        <th>Pallet</th>
                        <th>Customer</th>
                        <th>Pallet Details</th>
                        <th>Delivery</th>
                        <th>Weight (kg)</th>
                        <th>Contents</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php
                            $breakdown = $row['picksheetBreakdown'] ?? [];
                            if (empty($breakdown)) {
                                $breakdown = [[
                                    'picksheetId' => '',
                                    'contents' => $row['contentsFull'] ?? '',
                                    'weightKg' => $row['weightKg'] ?? 0,
                                ]];
                            }
                            $rowspan = count($breakdown);
                        @endphp

                        @foreach ($breakdown as $index => $detail)
                            <tr>
                                @if ($index === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $row['palletId'] }}</td>
                                    <td rowspan="{{ $rowspan }}" class="manifest-preline">{!! e($row['customerName'] ?? '') !!}<br>{!! e($row['address'] ?? '') !!}<br>{!! e($row['postcode'] ?? '') !!}</td>
                                    <td rowspan="{{ $rowspan }}" class="manifest-preline">Type: {!! e($row['palletType'] ?? '') !!}<br>Temp: {!! e($row['freshFrozen'] ?? '') !!}<br>Weight: {{ number_format((float) ($row['weightKg'] ?? 0), 3, '.', '') }} kg</td>
                                @endif
                                <td>{{ $detail['picksheetId'] !== '' ? $detail['picksheetId'] : '—' }}</td>
                                <td class="right">{{ number_format((float) ($detail['weightKg'] ?? 0), 3, '.', '') }}</td>
                                <td class="manifest-preline">{!! nl2br(e($detail['contents'] ?? '')) !!}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
