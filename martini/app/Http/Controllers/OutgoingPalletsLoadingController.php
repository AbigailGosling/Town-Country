<?php

namespace App\Http\Controllers;

use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\OutgoingPallet;
use App\Models\OutgoingPalletType;
use App\Models\Site;
use App\Models\Vehicle;
use App\Models\VehicleOutgoingPalletAllocation;
use App\Models\OutgoingPalletPickWeight;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class OutgoingPalletsLoadingController extends Controller
{
    public function view()
    {
        return view('outgoing-pallets.loading');
    }
    public function vehicle(Request $request):JsonResponse
    {
        $depotSiteId = (int) $request->input('depot', 0);

        // Return all vehicle registrations as JSON using Eloquent
        $vehiclesQuery = Vehicle::orderBy('reg', 'asc')
            ->whereNotNull('reg')
            ->where('reg', '<>', '');

        if ($depotSiteId > 0) {
            $vehiclesQuery->where('site_id', $depotSiteId);
        }

        $vehicles = $vehiclesQuery
            ->get(['reg'])
            ->pluck('reg')
            ->map(function ($reg) {
                return trim((string) $reg);
            })
            ->filter()
            ->unique()
            ->values();

        return response()->json(['vehicles' => $vehicles]);
    }
    public function vehicleDetails(Request $request): JsonResponse
    {
        $reg = trim((string) $request->input('reg', ''));
        if (!$reg) {
            return response()->json(['error' => 'Missing reg parameter'], 400);
        }

        // Adjust fields to match your new schema (type, depot removed, vehicle_type_id, site_id added)
        $vehicle = Vehicle::with(['vehicleType', 'site'])
            ->whereRaw('TRIM(reg) = ?', [$reg])
            ->first();

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        // Compose response with related info
        $data = [
            'reg' => $vehicle->reg,
            'type' => $vehicle->vehicleType ? $vehicle->vehicleType->name : null,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'grossWeight' => $vehicle->grossWeight,
            'payload' => $vehicle->payload,
            'site' => $vehicle->site ? $vehicle->site->name : null,
            'driver' => $vehicle->driver,
        ];
        return response()->json(['vehicle' => $data]);
    }
    public function vehicleAllocations(Request $request): JsonResponse
    {
        $reg = trim((string) $request->input('reg', ''));
        if ($reg === '') {
            return response()->json(['allocations' => []]);
        }

        $vehicle = Vehicle::whereRaw('TRIM(reg) = ?', [$reg])->first();
        if (!$vehicle) {
            return response()->json(['allocations' => []]);
        }

        $allocations = VehicleOutgoingPalletAllocation::with([
            'outgoingPallet.pickWeightOuts',
            'outgoingPallet.customer',
            'outgoingPallet.outgoingPalletType',
        ])
            ->where('vehicle_id', $vehicle->id)
            ->get()
            ->map(function ($allocation) {
                $pallet = $allocation->outgoingPallet;
                if (!$pallet) {
                    return null;
                }

                $deliveryNoteNumber = implode('-', $pallet->pickWeightOuts
                    ->pluck('pickersheet_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all());

                $ca = ClientAddress::where('client_id', $pallet->customer_id)
                    ->where('address_id', $pallet->address_id)
                    ->where('client_type', ClientType::CUSTOMER->value)
                    ->first();

                return [
                    'outgoingPalletId' => (int) $pallet->id,
                    'deliveryNoteNumber' => $deliveryNoteNumber,
                    'customerName' => $pallet->customer->businessname ?? '',
                    'customerDeliveryPostcode' => $ca->postcode ?? '',
                    'palletWeight' => (int)($pallet->getTotalWeight() ?? 0),
                    'palletType' => $pallet->outgoingPalletType->name ?? 'Euro',
                    'freshFrozen' => $pallet->getTemperatureCategory() ?? '',
                    'dueDate' => $pallet->estimated_delivery_date ? date('Y-m-d', strtotime((string)$pallet->estimated_delivery_date)) : '',
                    'row' => isset($allocation->row) ? (int)$allocation->row : null,
                    'column' => isset($allocation->column) ? (int)$allocation->column : null,
                    'committedByUserId' => $allocation->committed_by_user_id ? (int) $allocation->committed_by_user_id : null,
                    'committedByName' => $allocation->committed_by_name,
                    'committedAt' => $allocation->committed_at,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        return response()->json(['allocations' => $allocations]);
    }
    public function updateAllocation(Request $request): JsonResponse
    {
        $outgoingPalletId = (int) $request->input('outgoingPalletId', 0);
        $regAllocatedTo = (string) $request->input('regAllocatedTo', '');
        $palletRow = $request->input('palletRow');
        $palletColumn = $request->input('palletColumn');

        if ($outgoingPalletId <= 0) {
            return response()->json(['error' => 'outgoingPalletId is required'], 400);
        }

        $pallet = OutgoingPallet::find($outgoingPalletId);
        if (!$pallet) {
            return response()->json(['error' => 'Pallet not found'], 404);
        }

        $regAllocatedTo = trim($regAllocatedTo);

        if ($regAllocatedTo === '' || $palletRow === null || $palletColumn === null) {
            $deleted = VehicleOutgoingPalletAllocation::where('outgoing_pallet_id', $pallet->id)->delete();
            return response()->json(['success' => true, 'affectedRows' => $deleted]);
        }

        $vehicle = Vehicle::whereRaw('TRIM(reg) = ?', [trim($regAllocatedTo)])->first();
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        VehicleOutgoingPalletAllocation::where('outgoing_pallet_id', $pallet->id)
            ->where('vehicle_id', '<>', $vehicle->id)
            ->delete();

        VehicleOutgoingPalletAllocation::where('vehicle_id', $vehicle->id)
            ->where('row', (int)$palletRow)
            ->where('column', (int)$palletColumn)
            ->where('outgoing_pallet_id', '<>', $pallet->id)
            ->delete();

        VehicleOutgoingPalletAllocation::updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'outgoing_pallet_id' => $pallet->id,
            ],
            [
                'row' => (int)$palletRow,
                'column' => (int)$palletColumn,
                'committed_by_user_id' => null,
                'committed_by_name' => null,
                'committed_at' => null,
            ]
        );

        return response()->json(['success' => true, 'affectedRows' => 1]);
    }

    public function updatePalletType(Request $request): JsonResponse
    {
        $outgoingPalletId = (int) $request->input('outgoingPalletId', 0);
        $palletType = trim((string) $request->input('palletType', ''));

        if ($outgoingPalletId <= 0 || $palletType === '') {
            return response()->json(['error' => 'outgoingPalletId and palletType are required'], 400);
        }

        $normalizedType = strtolower($palletType);
        if (!in_array($normalizedType, ['standard', 'euro'], true)) {
            return response()->json(['error' => 'Invalid palletType'], 400);
        }

        $pallet = OutgoingPallet::find($outgoingPalletId);
        if (!$pallet) {
            return response()->json(['error' => 'Pallet not found'], 404);
        }

        $type = OutgoingPalletType::query()
            ->whereRaw('LOWER(name) LIKE ?', [$normalizedType . '%'])
            ->orderBy('id')
            ->first();

        if (!$type) {
            return response()->json(['error' => 'Pallet type not found'], 404);
        }

        $pallet->outgoing_pallet_type_id = (int) $type->id;
        $pallet->save();

        return response()->json([
            'success' => true,
            'outgoingPalletId' => (int) $pallet->id,
            'outgoingPalletTypeId' => (int) $pallet->outgoing_pallet_type_id,
            'palletType' => $type->name,
        ]);
    }
    public function palletSelection(Request $request): JsonResponse
    {
        $dueDate = trim((string) $request->input('dueDate', ''));
        $depotSiteId = (int) $request->input('depot', 0);
        $reg = trim((string) $request->input('reg', ''));

        if ($dueDate === '' || $depotSiteId <= 0 || $reg === '') {
                return response()->json(['orders' => []]);
        }

        $pallets = OutgoingPallet::with('pickWeightOuts','customer','outgoingPalletType')->where('estimated_delivery_date', $dueDate)->orWhereNull('estimated_delivery_date')->get();

        $allocations = VehicleOutgoingPalletAllocation::with('vehicle')
            ->get()
            ->keyBy('outgoing_pallet_id');

        $orders = [];
        foreach ($pallets as $pallet)
        {
            $allocation = $allocations->get($pallet->id);
            if ($allocation) {
                $regAllocatedTo = $allocation->vehicle ? trim((string)$allocation->vehicle->reg) : '';
            } else {
                $regAllocatedTo = '';
            }
            $delNoteNum = implode('-', $pallet->pickWeightOuts->pluck('pickersheet_id')->filter()->unique()->values()->all());
            $ca = ClientAddress::where('client_id', $pallet->customer_id)
                ->where('address_id', $pallet->address_id)
                ->where('client_type', ClientType::CUSTOMER->value)
                ->first();

            if (!$ca || (int) ($ca->site_id ?? 0) !== $depotSiteId) {
                continue;
            }

            $cutTotals = [];
            $pickLinks = OutgoingPalletPickWeight::where('outgoing_pallet_id', $pallet->id)->get();
            foreach ($pickLinks as $link) {
                $pickWeightOut = $link->pickWeightOut;
                if (!$pickWeightOut) {
                    continue;
                }
                foreach ($pickWeightOut->getCutQuantities() as $cutLine) {
                    $cutName = (string) ($cutLine['cut_name'] ?? 'Unknown');
                    $quantity = (int) ($cutLine['quantity'] ?? 0);
                    if (!isset($cutTotals[$cutName])) {
                        $cutTotals[$cutName] = 0;
                    }
                    $cutTotals[$cutName] += $quantity;
                }
            }

            arsort($cutTotals);
            $topCuts = array_slice($cutTotals, 0, 3, true);
            $contentsPreview = '';
            if (!empty($topCuts)) {
                $parts = [];
                foreach ($topCuts as $cutName => $qty) {
                    $parts[] = $cutName . ': ' . $qty;
                }
                $contentsPreview = implode(' • ', $parts);
            }

            $orders[] = [
                    'id' => 'order-' . $pallet->id,
                    'outgoingPalletId' => (int) $pallet->id,
                    'deliveryNoteNumber' => $delNoteNum ?? '',
                    'title' => 'Pallet ' . ($pallet->id ?? ''),
                    'subtext' => trim(($pallet->customer->businessname ?? '') . ' • ' . ($ca->address_1 ?? '') . ' • ' . ($ca->postcode ?? '')),
                    'customerName' => $pallet->customer->businessname ?? '',
                    'customerDeliveryAddress' => $ca->address_1 ?? '',
                    'customerDeliveryPostcode' => $ca->postcode ?? '',
                    'palletType' => $pallet->outgoingPalletType->name,
                    'weightKg' => (int)($pallet->getTotalWeight() ?? 0),
                    'contentsPreview' => $contentsPreview,
                    'freshFrozen' => $pallet->getTemperatureCategory() ?? '',
                    'regAllocatedTo' => $regAllocatedTo ?? '',
                    'row' => $allocation ? (int) $allocation->row : null,
                    'column' => $allocation ? (int) $allocation->column : null,
            ];
        }

        return response()->json(['orders' => $orders]);
    }
    public function orders(Request $request): JsonResponse
    {
        $dueDate = trim((string) $request->input('dueDate', ''));
        $depotSiteId = (int) $request->input('depot', 0);

        if ($depotSiteId <= 0) {
            return response()->json(['orders' => []]);
        }

        $query = OutgoingPallet::query();
        if ($dueDate) {
            $query->where('estimated_delivery_date', $dueDate);
        }

        $pallets = $query->orderBy('customerName')
            ->orderBy('customerDeliveryPostcode')
            ->get([
                'customer_id',
                'address_id',
                'deliveryNoteNumber',
                'customerName',
                'palletWeight',
                'palletType',
                'customerDeliveryPostcode',
                'freshFrozen',
                'regAllocatedTo',
            ]);

        $orders = [];
        $idx = 1;
        foreach ($pallets as $row) {
            $ca = ClientAddress::where('client_id', $row->customer_id)
                ->where('address_id', $row->address_id)
                ->where('client_type', ClientType::CUSTOMER->value)
                ->first();

            if (!$ca || (int) ($ca->site_id ?? 0) !== $depotSiteId) {
                continue;
            }

            $orders[] = [
                'id' => 'order-' . $idx,
                'title' => 'Order ' . ($row->deliveryNoteNumber ?? ''),
                'subtext' => trim(($row->customerName ?? '') . ' • ' . ($row->customerDeliveryPostcode ?? '')),
                'customerName' => $row->customerName ?? '',
                'customerDeliveryPostcode' => $row->customerDeliveryPostcode ?? '',
                'palletType' => $row->palletType ?? 'Euro',
                'weightKg' => (int)($row->palletWeight ?? 0),
                'freshFrozen' => $row->freshFrozen ?? '',
                'regAllocatedTo' => $row->regAllocatedTo ?? '',
                'deliveryNoteNumber' => $row->deliveryNoteNumber ?? ''
            ];
            $idx++;
        }

        return response()->json(['orders' => $orders]);
    }
    public function depots(): JsonResponse
    {
        $depots = Site::query()
            ->select('id', 'name')
            ->where('disabled', false)
            ->orderBy('name')
            ->get()
            ->map(function ($site) {
                return [
                    'id' => (int) $site->id,
                    'name' => (string) ($site->name ?? ''),
                ];
            })
            ->filter(function ($site) {
                return $site['id'] > 0 && $site['name'] !== '';
            })
            ->values();

        return response()->json(['depots' => $depots]);
    }

    public function palletOverview(Request $request): JsonResponse
    {
        $outgoingPalletId = (int) $request->input('outgoingPalletId', 0);
        if ($outgoingPalletId <= 0) {
            return response()->json(['error' => 'outgoingPalletId is required'], 400);
        }

        $pallet = OutgoingPallet::with(['customer', 'outgoingPalletType'])->find($outgoingPalletId);
        if (!$pallet) {
            return response()->json(['error' => 'Pallet not found'], 404);
        }

        $address = ClientAddress::where('client_id', $pallet->customer_id)
            ->where('address_id', $pallet->address_id)
            ->where('client_type', ClientType::CUSTOMER->value)
            ->first();

        $pickLinks = OutgoingPalletPickWeight::where('outgoing_pallet_id', $pallet->id)->get();
        $pickWeightOutIds = $pickLinks->pluck('pickWeightOut_id')->map(fn ($id) => (int) $id)->filter()->values()->all();

        $cutTotals = [];
        foreach ($pickLinks as $link) {
            $pickWeightOut = $link->pickWeightOut;
            if (!$pickWeightOut) {
                continue;
            }

            foreach ($pickWeightOut->getCutQuantities() as $cutLine) {
                $cutName = (string) ($cutLine['cut_name'] ?? 'Unknown');
                if (!isset($cutTotals[$cutName])) {
                    $cutTotals[$cutName] = [
                        'cutName' => $cutName,
                        'quantity' => 0,
                        'totalWeight' => 0.0,
                    ];
                }

                $cutTotals[$cutName]['quantity'] += (int) ($cutLine['quantity'] ?? 0);
                $cutTotals[$cutName]['totalWeight'] += (float) ($cutLine['total_weight'] ?? 0);
            }
        }

        ksort($cutTotals);
        $cuts = array_values(array_map(function (array $line) {
            $line['totalWeight'] = round((float) $line['totalWeight'], 3);
            return $line;
        }, $cutTotals));

        return response()->json([
            'overview' => [
                'outgoingPalletId' => (int) $pallet->id,
                'customerName' => $pallet->customer->businessname ?? '',
                'address' => $address->address_1 ?? '',
                'postcode' => $address->postcode ?? '',
                'palletType' => $pallet->outgoingPalletType->name ?? '',
                'temperature' => $pallet->getTemperatureCategory() ?? '',
                'totalWeightKg' => (int) ($pallet->getTotalWeight() ?? 0),
                'pickWeightOutCount' => count($pickWeightOutIds),
                'pickWeightOutIds' => $pickWeightOutIds,
                'cuts' => $cuts,
            ],
        ]);
    }
    public function aiPlan(Request $request): JsonResponse
    {
        $startPostcode = trim((string) $request->input('startPostcode', 'WV2 2QJ'));
        $stopMinutes = (int) $request->input('stopMinutes', 20);
        $postcodes = $request->input('postcodes', []);
        if (!is_array($postcodes)) {
                $postcodes = [];
        }
        $postcodes = array_values(array_filter(array_map('trim', $postcodes)));

        if (!$postcodes) {
                return response()->json(['error' => 'No postcodes provided'], 400);
        }

        $apiKey = env('OPENAI_API_KEY','');
        if (!$apiKey) {
                return response()->json(['error' => 'OPENAI_API_KEY is not set'], 500);
        }

        $prompt = "You are a logistics planner. Create the most cost-effective delivery order for the following UK postcodes, starting from {$startPostcode}. Assume a {$stopMinutes} minute stop at each postcode before moving to the next. Provide a clear itinerary list with numbered stops and any timing assumptions. Calculate the average time to travel between postcodes.\n\nPostcodes:\n- " . implode("\n- ", $postcodes);

        $payload = [
                'model' => 'gpt-4o-mini',
                'temperature' => 0.2,
                'messages' => [
                        ['role' => 'system', 'content' => 'Return a concise itinerary only.'],
                        ['role' => 'user', 'content' => $prompt]
                ]
        ];

        try {
                $client = new \GuzzleHttp\Client([
                        'verify' => 'C:\\inetpub\\intakemaster\\certs\\cacert.pem',
                ]);
                $response = $client->post('https://api.openai.com/v1/chat/completions', [
                        'headers' => [
                                'Content-Type' => 'application/json',
                                'Authorization' => 'Bearer ' . $apiKey,
                        ],
                        'json' => $payload,
                ]);
                $data = json_decode($response->getBody(), true);
                $itinerary = $data['choices'][0]['message']['content'] ?? '';
                return response()->json(['itinerary' => $itinerary]);
        } catch (\Exception $e) {
                return response()->json([
                        'error' => 'OpenAI request failed',
                        'detail' => $e->getMessage(),
                ], 500);
        }
    }

    public function commitAllocations(Request $request): JsonResponse
    {
        $reg = trim((string) $request->input('reg', ''));
        $dueDate = trim((string) $request->input('dueDate', ''));

        if ($reg === '') {
            return response()->json(['error' => 'reg is required'], 400);
        }

        $vehicle = Vehicle::whereRaw('TRIM(reg) = ?', [$reg])->first();
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        $query = VehicleOutgoingPalletAllocation::with('outgoingPallet')
            ->where('vehicle_id', $vehicle->id);

        if ($dueDate !== '') {
            $query->whereHas('outgoingPallet', function ($q) use ($dueDate) {
                $q->where('estimated_delivery_date', $dueDate);
            });
        }

        $allocations = $query->get();
        $allocatedPalletIds = [];
        $committedAt = now();
        $authUser = $request->user();
        $committedByUserId = $authUser ? (int) $authUser->id : null;
        $committedByName = $authUser ? (string) $authUser->name : null;

        DB::connection('tandc_live')->transaction(function () use ($allocations, $reg, $dueDate, &$allocatedPalletIds, $committedAt, $committedByUserId, $committedByName) {
            foreach ($allocations as $allocation) {
                $pallet = $allocation->outgoingPallet;
                if (!$pallet) {
                    continue;
                }

                $allocation->committed_by_user_id = $committedByUserId;
                $allocation->committed_by_name = $committedByName;
                $allocation->committed_at = $committedAt;
                $allocation->save();

                $pallet->regAllocatedTo = $reg;
                $pallet->row = $allocation->row;
                $pallet->column = $allocation->column;
                $pallet->save();

                $allocatedPalletIds[] = (int) $pallet->id;
            }

            $clearQuery = OutgoingPallet::where('regAllocatedTo', $reg);
            if ($dueDate !== '') {
                $clearQuery->where('estimated_delivery_date', $dueDate);
            }
            if (!empty($allocatedPalletIds)) {
                $clearQuery->whereNotIn('id', $allocatedPalletIds);
            }

            $clearQuery->update([
                'regAllocatedTo' => '',
                'row' => null,
                'column' => null,
            ]);
        });

        return response()->json([
            'success' => true,
            'committedCount' => count($allocatedPalletIds),
            'committedAt' => $committedAt,
            'committedByUserId' => $committedByUserId,
            'committedByName' => $committedByName,
        ]);
    }

    public function printTruckLoad(Request $request)
    {
        $reg = trim((string) $request->input('reg', ''));
        $dueDate = trim((string) $request->input('dueDate', ''));
        $depotSiteId = (int) $request->input('depot', 0);

        if ($reg === '' || $depotSiteId <= 0) {
            return response('Missing reg or depot parameter', 400);
        }

        $vehicle = Vehicle::with('site')->whereRaw('TRIM(reg) = ?', [$reg])->first();
        if (!$vehicle) {
            return response('Vehicle not found', 404);
        }

        $depot = Site::find($depotSiteId);

        $query = VehicleOutgoingPalletAllocation::with([
            'outgoingPallet.pickWeightOuts',
            'outgoingPallet.customer',
            'outgoingPallet.outgoingPalletType',
        ])->where('vehicle_id', $vehicle->id);

        if ($dueDate !== '') {
            $query->whereHas('outgoingPallet', function ($q) use ($dueDate) {
                $q->where('estimated_delivery_date', $dueDate);
            });
        }

        $allocations = $query->get();
        $loadRows = [];
        $totalWeight = 0;

        foreach ($allocations as $allocation) {
            $pallet = $allocation->outgoingPallet;
            if (!$pallet) {
                continue;
            }

            $ca = ClientAddress::where('client_id', $pallet->customer_id)
                ->where('address_id', $pallet->address_id)
                ->where('client_type', ClientType::CUSTOMER->value)
                ->first();

            if (!$ca || (int) ($ca->site_id ?? 0) !== $depotSiteId) {
                continue;
            }

            $weightKg = (int) ($pallet->getTotalWeight() ?? 0);
            $deliveryNoteNumber = implode('-', $pallet->pickWeightOuts
                ->pluck('pickersheet_id')
                ->filter()
                ->unique()
                ->values()
                ->all());

            $cutTotals = [];
            $pickLinks = OutgoingPalletPickWeight::where('outgoing_pallet_id', $pallet->id)->get();
            foreach ($pickLinks as $link) {
                $pickWeightOut = $link->pickWeightOut;
                if (!$pickWeightOut) {
                    continue;
                }
                foreach ($pickWeightOut->getCutQuantities() as $cutLine) {
                    $cutName = (string) ($cutLine['cut_name'] ?? 'Unknown');
                    $quantity = (int) ($cutLine['quantity'] ?? 0);
                    if (!isset($cutTotals[$cutName])) {
                        $cutTotals[$cutName] = 0;
                    }
                    $cutTotals[$cutName] += $quantity;
                }
            }

            arsort($cutTotals);
            $topCuts = array_slice($cutTotals, 0, 3, true);
            $contentsPreview = '';
            if (!empty($topCuts)) {
                $parts = [];
                foreach ($topCuts as $cutName => $qty) {
                    $parts[] = $cutName . ': ' . $qty;
                }
                $contentsPreview = implode(' • ', $parts);
            }

            $loadRows[] = [
                'row' => (int) ($allocation->row ?? 0),
                'column' => (int) ($allocation->column ?? 0),
                'palletId' => (int) $pallet->id,
                'deliveryNoteNumber' => $deliveryNoteNumber,
                'customerName' => $pallet->customer->businessname ?? '',
                'address' => $ca->address_1 ?? '',
                'postcode' => $ca->postcode ?? '',
                'palletType' => $pallet->outgoingPalletType->name ?? 'Euro',
                'freshFrozen' => $pallet->getTemperatureCategory() ?? '',
                'contentsPreview' => $contentsPreview,
                'weightKg' => $weightKg,
            ];

            $totalWeight += $weightKg;
        }

        usort($loadRows, function ($a, $b) {
            if ($a['row'] === $b['row']) {
                return $a['column'] <=> $b['column'];
            }
            return $a['row'] <=> $b['row'];
        });

        $html = view('outgoing-pallets.truck-load-pdf', [
            'generatedAt' => now(),
            'dueDate' => $dueDate,
            'vehicle' => $vehicle,
            'depotName' => $depot ? $depot->name : '',
            'rows' => $loadRows,
            'totalWeight' => $totalWeight,
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);
        $mpdf->WriteHTML($html);

        $filenameDate = $dueDate !== '' ? $dueDate : now()->format('Y-m-d');
        $filename = 'truck-load-' . preg_replace('/[^A-Za-z0-9\-]/', '-', $reg) . '-' . $filenameDate . '.pdf';
        $pdfBinary = $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
