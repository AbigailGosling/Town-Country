<?php

namespace App\Http\Controllers;

use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\OutgoingPallet;
use App\Models\Site;
use App\Models\Vehicle;
use App\Models\VehicleOutgoingPalletAllocation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OutgoingPalletsLoadingController extends Controller
{
    public function view()
    {
        return view('outgoing-pallets.loading');
    }
    public function vehicle():JsonResponse
    {
        // Return all vehicle registrations as JSON using Eloquent
        $vehicles = Vehicle::orderBy('reg', 'asc')
            ->whereNotNull('reg')
            ->pluck('reg')
            ->filter()
            ->values();
        return response()->json(['vehicles' => $vehicles]);
    }
    public function vehicleDetails(Request $request): JsonResponse
    {
        $reg = $request->input('reg');
        if (!$reg) {
            return response()->json(['error' => 'Missing reg parameter'], 400);
        }

        // Adjust fields to match your new schema (type, depot removed, vehicle_type_id, site_id added)
        $vehicle = Vehicle::with(['vehicleType', 'site'])
            ->where('reg', $reg)
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

        $vehicle = Vehicle::where('reg', $reg)->first();
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

        $vehicle = Vehicle::where('reg', $regAllocatedTo)->first();
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
    public function palletSelection(Request $request): JsonResponse
    {
        $dueDate = trim((string) $request->input('dueDate', ''));
        $depot = trim((string) $request->input('depot', ''));
        $reg = trim((string) $request->input('reg', ''));

        if ($dueDate === '' || $depot === '' || $reg === '') {
                return response()->json(['orders' => []]);
        }

        $pallets = OutgoingPallet::with('pickWeightOuts','customer','outgoingPalletType')->where('estimated_delivery_date', $dueDate)->orWhereNull('estimated_delivery_date')->get();

        $allocations = VehicleOutgoingPalletAllocation::with('vehicle')
            ->whereHas('vehicle', function ($query) use ($reg) {
                $query->where('reg', $reg);
            })
            ->get()
            ->keyBy('outgoing_pallet_id');

        $orders = [];
        foreach ($pallets as $pallet)
        {
            $allocation = $allocations->get($pallet->id);
            if ($allocation) {
                $regAllocatedTo = $allocation->vehicle ? $allocation->vehicle->reg : '';
            } else {
                $regAllocatedTo = '';
            }
            $delNoteNum = implode('-', $pallet->pickWeightOuts->pluck('pickersheet_id')->filter()->unique()->values()->all());
            $ca = ClientAddress::where('client_id', $pallet->customer_id)
                ->where('address_id', $pallet->address_id)
                ->where('client_type', ClientType::CUSTOMER->value)
                ->first();
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
        $dueDate = $request->input('dueDate');
        $depot = $request->input('depot');

        $query = OutgoingPallet::query();
        if ($dueDate) {
            $query->where('estimated_delivery_date', $dueDate);
        }
        if ($depot) {
            $query->where('depot', $depot);
        }
        $pallets = $query->orderBy('customerName')
            ->orderBy('customerDeliveryPostcode')
            ->get([
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
        $depots = Site::select('name')
            ->distinct()
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->where('disabled', false)
            ->orderBy('name')
            ->pluck('name');

        return response()->json(['depots' => $depots]);
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

        $vehicle = Vehicle::where('reg', $reg)->first();
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
}
