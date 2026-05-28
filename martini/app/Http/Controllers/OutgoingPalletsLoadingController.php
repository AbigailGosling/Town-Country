<?php

namespace App\Http\Controllers;

use App\Helpers\FuncHelper;
use App\Helpers\GraphHopperHelper;
use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\Customer;
use App\Models\OutgoingPallet;
use App\Models\OutgoingPalletType;
use App\Models\Site;
use App\Models\Vehicle;
use App\Models\VehicleOutgoingPalletAllocation;
use App\Models\OutgoingPalletPickWeight;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

class OutgoingPalletsLoadingController extends Controller
{
    private const PALLET_COLUMNS = 3;
    private const PLANNING_PALLET_COLUMNS = 3;
    private const DEPOT_LAT = 52.577817;
    private const DEPOT_LNG = -2.107758;

    private function normalizeMaxPalletRows($value): int
    {
        $rows = (int) $value;
        return $rows > 0 ? $rows : 5;
    }

    private function isWithinVehicleCapacity(int $row, int $column, int $maxRows): bool
    {
        return $row >= 1
            && $row <= $maxRows
            && $column >= 1
            && $column <= self::PALLET_COLUMNS;
    }

    private function isStandardPallet(OutgoingPallet $pallet): bool
    {
        $pallet->loadMissing('outgoingPalletType');
        $typeName = strtolower(trim((string) ($pallet->outgoingPalletType->name ?? '')));
        return str_starts_with($typeName, 'standard');
    }

    private function getPicksheetIdsForPallet(OutgoingPallet $pallet): array
    {
        $pallet->loadMissing('pickWeightOuts.pickWeightOut');

        return $pallet->pickWeightOuts
            ->map(function ($link) {
                return isset($link->pickWeightOut) ? trim((string) ($link->pickWeightOut->pickersheet_id ?? '')) : '';
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function getPicksheetCutSummaryLines(OutgoingPallet $pallet, int $maxLines = 3): array
    {
        $pallet->loadMissing('pickWeightOuts.pickWeightOut');

        $lines = [];
        foreach ($pallet->pickWeightOuts as $link) {
            $pickWeightOut = $link->pickWeightOut;
            if (!$pickWeightOut) {
                continue;
            }

            $picksheetId = trim((string) ($pickWeightOut->pickersheet_id ?? ''));
            foreach ($pickWeightOut->getCutQuantities() as $cutLine) {
                $cutName = trim((string) ($cutLine['cut_name'] ?? 'Unknown'));
                $quantity = (int) ($cutLine['quantity'] ?? 0);
                if ($cutName === '' || $quantity <= 0) {
                    continue;
                }

                $line = $cutName . ': ' . $quantity;
                if ($picksheetId !== '') {
                    $line .= ' • Picksheet: ' . $picksheetId;
                }

                $lines[] = $line;
                if (count($lines) >= $maxLines) {
                    return $lines;
                }
            }
        }

        return $lines;
    }

    public function view()
    {
        return view('outgoing-pallets.loading');
    }

    public function graphhopperResultsView()
    {
        return view('outgoing-pallets.graphhopper-results');
    }

    public function vehicle(Request $request):JsonResponse
    {
        $depotSiteId = (int) $request->input('depot', 0);

        // Return all vehicle registrations plus planning metadata for route assignment filtering.
        $vehiclesQuery = Vehicle::orderBy('reg', 'asc')
            ->whereNotNull('reg')
            ->where('reg', '<>', '')
            ->whereNotIn('vehicle_type_id', [1,5]);

        if ($depotSiteId > 0) {
            $vehiclesQuery->where('site_id', $depotSiteId);
        }

        $vehicles = $vehiclesQuery
            ->get()
            ->map(function (Vehicle $vehicle) {
                $reg = trim((string) ($vehicle->reg ?? ''));
                if ($reg === '') {
                    return null;
                }

                return [
                    'reg' => $reg,
                    'payloadKg' => GraphHopperHelper::planningPayloadForVehicle($vehicle),
                    'palletCapacity' => GraphHopperHelper::planningCapacityForVehicle($vehicle, self::PLANNING_PALLET_COLUMNS),
                ];
            })
            ->filter()
            ->values();

        $vehicleRegs = $vehicles
            ->pluck('reg')
            ->map(function ($reg) {
                return trim((string) $reg);
            })
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'vehicles' => $vehicleRegs,
            'vehicleOptions' => $vehicles,
        ]);
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
            'id' => (int) $vehicle->id,
            'reg' => $vehicle->reg,
            'type' => $vehicle->vehicleType ? $vehicle->vehicleType->name : null,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'grossWeight' => $vehicle->grossWeight,
            'payload' => $vehicle->payload,
            'site' => $vehicle->site ? $vehicle->site->name : null,
            'driver' => $vehicle->driver,
            'maxPalletRows' => $this->normalizeMaxPalletRows($vehicle->max_pallet_rows ?? null),
            'maxPallets' => $this->normalizeMaxPalletRows($vehicle->max_pallet_rows ?? null) * self::PALLET_COLUMNS,
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

        $maxRows = $this->normalizeMaxPalletRows($vehicle->max_pallet_rows ?? null);

        $allocations = VehicleOutgoingPalletAllocation::with([
            'outgoingPallet.pickWeightOuts',
            'outgoingPallet.customer',
            'outgoingPallet.outgoingPalletType',
        ])
            ->where('vehicle_id', $vehicle->id)
            ->get()
            ->map(function ($allocation) use ($maxRows) {
                $pallet = $allocation->outgoingPallet;
                if (!$pallet) {
                    return null;
                }

                $row = isset($allocation->row) ? (int) $allocation->row : null;
                $column = isset($allocation->column) ? (int) $allocation->column : null;
                if ($row === null || $column === null) {
                    return null;
                }
                if (!$this->isWithinVehicleCapacity($row, $column, $maxRows)) {
                    return null;
                }

                $deliveryNoteNumber = implode('-', $this->getPicksheetIdsForPallet($pallet));

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
                    'row' => $row,
                    'column' => $column,
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

        $row = (int) $palletRow;
        $column = (int) $palletColumn;
        $maxRows = $this->normalizeMaxPalletRows($vehicle->max_pallet_rows ?? null);

        if (!$this->isWithinVehicleCapacity($row, $column, $maxRows)) {
            return response()->json(['error' => 'Invalid slot for vehicle capacity'], 422);
        }

        if ($column === self::PALLET_COLUMNS && $this->isStandardPallet($pallet)) {
            return response()->json(['error' => 'Standard pallets cannot be allocated to column 3'], 422);
        }

        VehicleOutgoingPalletAllocation::where('outgoing_pallet_id', $pallet->id)
            ->where('vehicle_id', '<>', $vehicle->id)
            ->delete();

        VehicleOutgoingPalletAllocation::where('vehicle_id', $vehicle->id)
            ->where('row', $row)
            ->where('column', $column)
            ->where('outgoing_pallet_id', '<>', $pallet->id)
            ->delete();

        VehicleOutgoingPalletAllocation::updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'outgoing_pallet_id' => $pallet->id,
            ],
            [
                'row' => $row,
                'column' => $column,
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

        $pallets = OutgoingPallet::with('pickWeightOuts.pickWeightOut','customer','outgoingPalletType')->where('estimated_delivery_date', $dueDate)->orWhereNull('estimated_delivery_date')->get();

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
            $delNoteNum = implode('-', $this->getPicksheetIdsForPallet($pallet));
            $ca = ClientAddress::where('client_id', $pallet->customer_id)
                ->where('address_id', $pallet->address_id)
                ->where('client_type', ClientType::CUSTOMER->value)
                ->first();

            if (!$ca || (int) ($ca->site_id ?? 0) !== $depotSiteId) {
                continue;
            }

            $contentsPreview = implode("\n", $this->getPicksheetCutSummaryLines($pallet));

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

        $pallet = OutgoingPallet::with(['customer', 'outgoingPalletType', 'pickWeightOuts.pickWeightOut'])->find($outgoingPalletId);
        if (!$pallet) {
            return response()->json(['error' => 'Pallet not found'], 404);
        }

        $address = ClientAddress::where('client_id', $pallet->customer_id)
            ->where('address_id', $pallet->address_id)
            ->where('client_type', ClientType::CUSTOMER->value)
            ->first();

        $pickLinks = OutgoingPalletPickWeight::where('outgoing_pallet_id', $pallet->id)->get();
        $pickWeightOutIds = $pickLinks->pluck('pickWeightOut_id')->map(fn ($id) => (int) $id)->filter()->values()->all();
        $picksheetIds = $this->getPicksheetIdsForPallet($pallet);
        $contentSummaryLines = $this->getPicksheetCutSummaryLines($pallet);

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
                'picksheetIds' => $picksheetIds,
                'contentSummaryLines' => $contentSummaryLines,
                'cuts' => $cuts,
            ],
        ]);
    }
    public function aiPlan(Request $request): JsonResponse
    {
        // Backward-compatible alias for existing frontend callers.
        return $this->graphhopperMultiVehiclePlan($request);
    }

    public function graphhopperMultiVehiclePlan(Request $request): JsonResponse
    {
        $dueDate = trim((string) $request->input('dueDate', now()->format('Y-m-d')));
        $depotSiteId = (int) $request->input('depot', 0);
        $serviceDurationSeconds = max(60, (int) $request->input('serviceDurationSeconds', 1200));
        $persistSuggestions = filter_var(
            $request->input('persistSuggestions', true),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
        $persistSuggestions = $persistSuggestions === null ? true : $persistSuggestions;
        $dryRun = filter_var(
            $request->input('dryRun', false),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
        $dryRun = $dryRun === null ? false : $dryRun;
        $shouldPersistSuggestions = $persistSuggestions && !$dryRun;

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            return response()->json(['error' => 'dueDate must be in Y-m-d format'], 422);
        }
        $vehicleQuery = Vehicle::whereNotIn("vehicle_type_id", [1,5])
            ->whereNotNull('reg')
            ->where('reg', '<>', '')
            ->where('site_id', $depotSiteId);

        if ($depotSiteId > 0) {
            $vehicleQuery->where('site_id', $depotSiteId);
        }
        $vehicles = $vehicleQuery->orderBy("reg")->get();
        if ($vehicles->isEmpty()) {
            return response()->json(['error' => 'No vehicles available for planning'], 422);
        }
        $pallets = OutgoingPallet::with(['customer', 'outgoingPalletType'])
            ->whereDate('estimated_delivery_date', $dueDate)
            ->where(function ($query) {
                //$query->whereNull('dispatched')->orWhere('dispatched', 0);
            })
            ->get();
        if ($pallets->isEmpty()) {
            return response()->json([
                'error' => 'No outgoing pallets found for date',
                'dueDate' => $dueDate,
            ], 404);
        }
        $services = [];
        $skipped = [];
        $skippedAddresses = [];
        $groups = [];
        foreach ($pallets as $pallet) {
            if ($pallet->pickWeightOuts->isEmpty()) {
                $skipped[] = ['outgoingPalletId' => (int) $pallet->id, 'reason' => 'No pick weight outs linked to pallet',];
                continue;
            }
            if (in_array($pallet->customer_id.'-'.$pallet->address_id, $skippedAddresses, true)) {
                $skipped[] = ['outgoingPalletId' => (int) $pallet->id, 'reason' => 'Previously skipped due to geocoding failure for this address',];
                continue;
            }
            $address = ClientAddress::where('client_id', $pallet->customer_id)
                ->where('address_id', $pallet->address_id)
                ->where('client_type', ClientType::CUSTOMER->value)
                ->where('site_id', $depotSiteId)
                ->first();
            if (!$address) {
                $skipped[] = ['outgoingPalletId' => (int) $pallet->id, 'reason' => 'Client address missing',];
                continue;
            }
            if (strpos(trim(strtolower((string) $address->address_1)), 'collect') !== false) {
                $skipped[] = ['outgoingPalletId' => (int) $pallet->id, 'reason' => 'Ignore Collections',];
                continue;
            }
            if ($depotSiteId > 0 && (int) ($address->site_id ?? 0) !== $depotSiteId) {
                continue;
            }
            $location = null;
            $storedLat = $address->lat;
            $storedLng = $address->lng;
            if (is_numeric($storedLat) && is_numeric($storedLng)) {
                $storedLat = (float) $storedLat;
                $storedLng = (float) $storedLng;
                $location = ['lat' => $storedLat,'lon' => $storedLng,];
            }
            if ($location == null) {
                $queryAddress = GraphHopperHelper::formatAddressForGeocoding($address);
                if ($queryAddress === '') {
                    $skipped[] = ['outgoingPalletId' => (int) $pallet->id,'reason' => 'Address is empty',];
                    continue;
                }
                $location = GraphHopperHelper::geocodeAddress($queryAddress);
                if ($location === null) {
                    $skippedAddresses[] = $pallet->customer_id.'-'.$pallet->address_id;
                    $skipped[] = [
                        'outgoingPalletId' => (int) $pallet->id,
                        'reason' => 'Failed to geocode address',
                        'address' => $queryAddress,
                    ];
                    continue;
                }
                $address->lat = (float) $location['lat'];
                $address->lng = (float) $location['lon'];
                $address->save();
            }
            if (!array_key_exists($address->client_id . '-' . $address->address_id, $groups)) {
                $groups[$address->client_id . '-' . $address->address_id] = ['Fresh' => false, 'Frozen' => false,'ids' => [],];
            }
            $tempCategory = $pallet->getTemperatureCategory();
            $groups[$address->client_id . '-' . $address->address_id][$tempCategory] = true;
            $groups[$address->client_id . '-' . $address->address_id]['ids'][] = $pallet->id;
            $customer = Customer::find($pallet->customer_id);
            $services[] = [
                'id' => (string)$pallet->id,
                'name' => $customer->businessname . ' - ' . ($address->address_1 ?? '') . ' - ' . ($address->postcode ?? ''),
                'address' => [
                    'location_id' => (string)$address->client_id . '-' . $address->address_id,
                    'lat' => $location['lat'],
                    'lon' => $location['lon'],
                ],
                'setup_time' => $serviceDurationSeconds,
                'size' => [($pallet->type_id == 1 ? 1.5 : 1),(int)FuncHelper::ceilDec($pallet->getTotalWeight(), 0) ?? 0],
                'group' => $address->client_id . '-' . $address->address_id . '-' .  $tempCategory,
            ];
        }
        if (empty($services)) {
            return response()->json([
                'error' => 'No services could be created from outgoing pallets',
                'skipped' => $skipped,
            ], 422);
        }
        $generifiedVehicleTypes = GraphHopperHelper::generifyVehicleTypes($vehicles, self::PLANNING_PALLET_COLUMNS);
        $vrcVehicleTypes = [];
        $vrpVehicles = [];
        $depotLocation = [
            'location_id' => 'depot',
            'lat' => self::DEPOT_LAT,
            'lon' => self::DEPOT_LNG,
        ];
        foreach ($generifiedVehicleTypes as $type) {
            $vrcVehicleTypes[] = [
                'type_id' => $type['type_id'],
                'profile' => $type['profile'],
                'capacity' => $type['capacity'],
            ];
            for ($i = 0; $type['count']>$i; $i++) {
                if (count($vrpVehicles)==20)break 2;
                $vrpVehicles[] = [
                    'vehicle_id' => $type['type_id'] . '-' . $i,
                    'type_id' => $type['type_id'],
                    'start_address' => $depotLocation,
                    'end_address' => $depotLocation,
                    'earliest_start' => strtotime($dueDate . ' 00:00:00'),
                    'latest_end' => strtotime($dueDate . ' 23:00:00'),
                    // 'shifts' => [
                    //     [
                    //         'shift_id' => 'morning_shift',
                    //         'earliest_start' => strtotime($dueDate . ' 06:00:00'),
                    //         'latest_end' => strtotime($dueDate . ' 12:00:00'),
                    //         'start_address' => $depotLocation,
                    //         'end_address' => $depotLocation,
                    //     ],
                    //     [
                    //         'shift_id' => 'afternoon_shift',
                    //         'earliest_start' => strtotime($dueDate . ' 14:00:00'),
                    //         'latest_end' => strtotime($dueDate . ' 20:00:00'),
                    //         'start_address' => $depotLocation,
                    //         'end_address' => $depotLocation,
                    //     ],
                    // ],
                ];
            }
        }
        $freshGroups = [];
        $frozenGroups = [];
        $addressGroups = [];
        $addressIds = [];
        foreach ($groups as $groupId => $temps) {
            if ($temps['Fresh']) {
                $freshGroups[] = $groupId . '-Fresh';
            }
            if ($temps['Frozen']) {
                $frozenGroups[] = $groupId . '-Frozen';
            }
            if ($temps['Fresh'] && $temps['Frozen']) {
                $addressGroups[] = [$groupId . '-Fresh', $groupId . '-Frozen'];
                //$addressIds[] = $temps['ids'];
            }
        }
        $relations = [
            [
                'type'=> 'in_sequence',
                'groups'=> array_merge($freshGroups, $frozenGroups),
            ]
        ];
        foreach ($addressGroups as $i => $group) {
            if (in_array($group[0], $freshGroups) && in_array($group[1], $frozenGroups)) {
                // $relations[] = [
                //     'type' => 'in_same_route',
                //     'ids' => $addressIds[$i],
                // ];
                $relations[] = [
                    'type' => 'in_direct_sequence',
                    'groups' => $group,
                ];
            }
        }
        $vrpPayload = [
            'configuration' => [
                'routing' => [
                    'calc_points' => true,
                    'return_snapped_waypoints' => true,
                    'consider_traffic' => true,
                ],
            ],
            'vehicle_types' => $vrcVehicleTypes,
            'vehicles' => $vrpVehicles,
            'services' => $services,
            'relations' => $relations,
            'objectives' => [
                [
                    "type"=> "min",
                    "value"=> "vehicles",
                ]
            ],
        ];

        $graphResponse = GraphHopperHelper::vrp($vrpPayload);
        if (!$graphResponse['ok']) {
            return response()->json([
                'error' => 'GraphHopper VRP request failed',
                'detail' => $graphResponse['error'],
                'skipped' => $skipped,
            ], 502);
        }

        $persistence = [
            'enabled' => $shouldPersistSuggestions,
            'persisted' => false,
            'assignedCount' => 0,
            'skippedCount' => 0,
            'reason' => $dryRun ? 'Dry run enabled; suggestions were not persisted' : null,
        ];

        // if ($shouldPersistSuggestions) {
        //     $plannedPalletIds = array_map(static function (array $service): int {
        //         $serviceId = (string) ($service['id'] ?? '');
        //         if (preg_match('/^pallet-(\d+)$/', $serviceId, $matches) === 1) {
        //             return (int) $matches[1];
        //         }
        //         if (preg_match('/^\d+$/', $serviceId) === 1) {
        //             return (int) $serviceId;
        //         }
        //         return 0;
        //     }, $services);
        //     $plannedPalletIds = array_values(array_filter($plannedPalletIds));

        //     $persistence = $this->persistGraphHopperAllocations(
        //         $graphResponse['data'],
        //         $vehicleByGraphId,
        //         $plannedPalletIds
        //     );
        // }

        return response()->json([
            'success' => true,
            'dryRun' => $dryRun,
            'dueDate' => $dueDate,
            'vehicleCount' => count($vrpVehicles),
            'serviceCount' => count($services),
            'skipped' => $skipped,
            'persistence' => $persistence,
            'request' => $vrpPayload,
            'response' => $graphResponse['data'],
        ]);
    }
    private function persistGraphHopperAllocations(array $graphData, array $vehicleByGraphId, array $plannedPalletIds): array
    {
        $routes = data_get($graphData, 'solution.routes', data_get($graphData, 'routes', []));
        if (!is_array($routes) || empty($routes)) {
            return [
                'enabled' => true,
                'persisted' => false,
                'assignedCount' => 0,
                'skippedCount' => 0,
                'reason' => 'No routes found in GraphHopper response',
            ];
        }

        if (empty($plannedPalletIds)) {
            return [
                'enabled' => true,
                'persisted' => false,
                'assignedCount' => 0,
                'skippedCount' => 0,
                'reason' => 'No planned pallet IDs available for persistence',
            ];
        }

        $pallets = OutgoingPallet::with('outgoingPalletType')
            ->whereIn('id', $plannedPalletIds)
            ->get()
            ->keyBy('id');

        $assignedCount = 0;
        $skippedCount = 0;

        DB::connection('tandc_live')->transaction(function () use (
            $plannedPalletIds,
            $routes,
            $vehicleByGraphId,
            $pallets,
            &$assignedCount,
            &$skippedCount
        ) {
            VehicleOutgoingPalletAllocation::whereIn('outgoing_pallet_id', $plannedPalletIds)
                ->whereNull('committed_at')
                ->delete();

            $vehicleIds = array_values(array_unique(array_map(
                static fn (Vehicle $vehicle): int => (int) $vehicle->id,
                array_values($vehicleByGraphId)
            )));

            $rowTemperatureByVehicle = [];
            if (!empty($vehicleIds)) {
                $existingAllocations = VehicleOutgoingPalletAllocation::with('outgoingPallet.outgoingPalletType')
                    ->whereIn('vehicle_id', $vehicleIds)
                    ->get();

                foreach ($existingAllocations as $existingAllocation) {
                    $vehicleId = (int) ($existingAllocation->vehicle_id ?? 0);
                    $row = (int) ($existingAllocation->row ?? 0);
                    if ($vehicleId <= 0 || $row <= 0) {
                        continue;
                    }

                    $existingPallet = $existingAllocation->outgoingPallet;
                    if (!$existingPallet) {
                        continue;
                    }

                    $temperatureCategory = $this->normalizePlanningTemperatureCategory($existingPallet);
                    if ($temperatureCategory === null) {
                        continue;
                    }

                    if (!isset($rowTemperatureByVehicle[$vehicleId])) {
                        $rowTemperatureByVehicle[$vehicleId] = [];
                    }

                    if (!isset($rowTemperatureByVehicle[$vehicleId][$row])) {
                        $rowTemperatureByVehicle[$vehicleId][$row] = $temperatureCategory;
                        continue;
                    }

                    if ($rowTemperatureByVehicle[$vehicleId][$row] !== $temperatureCategory) {
                        $rowTemperatureByVehicle[$vehicleId][$row] = 'mixed';
                    }
                }
            }

            foreach ($routes as $route) {
                if (!is_array($route)) {
                    continue;
                }

                $graphVehicleId = (string) ($route['vehicle_id'] ?? $route['vehicleId'] ?? '');
                if ($graphVehicleId === '' || !array_key_exists($graphVehicleId, $vehicleByGraphId)) {
                    $skippedCount++;
                    continue;
                }

                $vehicle = $vehicleByGraphId[$graphVehicleId];
                $maxRows = $this->normalizeMaxPalletRows($vehicle->max_pallet_rows ?? null);
                $slotIndex = 1;
                $vehicleId = (int) $vehicle->id;
                $rowTemperatureMap = $rowTemperatureByVehicle[$vehicleId] ?? [];

                $routePalletIds = array_reverse($this->graphHopperServicePalletIdsForRoute($route));

                foreach ($routePalletIds as $outgoingPalletId) {
                    $pallet = $pallets->get($outgoingPalletId);
                    if (!$pallet) {
                        $skippedCount++;
                        continue;
                    }

                    $temperatureCategory = $this->normalizePlanningTemperatureCategory($pallet);
                    $slot = $this->nextSuggestedSlot(
                        $slotIndex,
                        $maxRows,
                        $this->isStandardPallet($pallet),
                        $temperatureCategory,
                        $rowTemperatureMap
                    );
                    if ($slot === null) {
                        $skippedCount++;
                        continue;
                    }

                    VehicleOutgoingPalletAllocation::updateOrCreate(
                        [
                            'vehicle_id' => (int) $vehicle->id,
                            'outgoing_pallet_id' => (int) $outgoingPalletId,
                        ],
                        [
                            'row' => $slot['row'],
                            'column' => $slot['column'],
                            'committed_by_user_id' => null,
                            'committed_by_name' => null,
                            'committed_at' => null,
                        ]
                    );

                    if ($temperatureCategory !== null) {
                        $row = (int) $slot['row'];
                        if (!isset($rowTemperatureMap[$row])) {
                            $rowTemperatureMap[$row] = $temperatureCategory;
                        } elseif ($rowTemperatureMap[$row] !== $temperatureCategory) {
                            $rowTemperatureMap[$row] = 'mixed';
                        }
                    }

                    $assignedCount++;
                }

                $rowTemperatureByVehicle[$vehicleId] = $rowTemperatureMap;
            }
        });

        return [
            'enabled' => true,
            'persisted' => true,
            'assignedCount' => $assignedCount,
            'skippedCount' => $skippedCount,
            'reason' => null,
        ];
    }

    private function graphHopperServicePalletIdsForRoute(array $route): array
    {
        $activities = $route['activities'] ?? [];
        if (!is_array($activities)) {
            return [];
        }

        $palletIds = [];
        foreach ($activities as $activity) {
            if (!is_array($activity)) {
                continue;
            }

            $type = strtolower((string) ($activity['type'] ?? ''));
            if ($type !== 'service' && $type !== 'pickup' && $type !== 'delivery') {
                continue;
            }

            $serviceId = (string) (
                $activity['id']
                ?? $activity['service_id']
                ?? ''
            );

            if (preg_match('/^pallet-(\d+)$/', $serviceId, $matches) === 1) {
                $palletIds[] = (int) $matches[1];
                continue;
            }
            if (preg_match('/^\d+$/', $serviceId) === 1) {
                $palletIds[] = (int) $serviceId;
            }
        }

        return array_values(array_unique($palletIds));
    }

    private function nextSuggestedSlot(
        int &$slotIndex,
        int $maxRows,
        bool $isStandard,
        ?string $temperatureCategory = null,
        array $rowTemperatureMap = []
    ): ?array
    {
        $maxSlots = max(0, $maxRows * self::PLANNING_PALLET_COLUMNS);

        while ($slotIndex <= $maxSlots) {
            $current = $slotIndex;
            $slotIndex++;

            $row = (int) floor(($current - 1) / self::PLANNING_PALLET_COLUMNS) + 1;
            $column = (($current - 1) % self::PLANNING_PALLET_COLUMNS) + 1;

            if ($isStandard && $column === self::PALLET_COLUMNS) {
                continue;
            }

            if (
                $temperatureCategory !== null
                && isset($rowTemperatureMap[$row])
                && $rowTemperatureMap[$row] !== $temperatureCategory
            ) {
                continue;
            }

            if (!$this->isWithinVehicleCapacity($row, $column, $maxRows)) {
                continue;
            }

            return [
                'row' => $row,
                'column' => $column,
            ];
        }

        return null;
    }

    private function normalizePlanningTemperatureCategory(OutgoingPallet $pallet): ?string
    {
        $category = strtolower(trim((string) ($pallet->getTemperatureCategory() ?? '')));

        if (str_contains($category, 'frozen')) {
            return 'frozen';
        }

        if (str_contains($category, 'fresh')) {
            return 'fresh';
        }

        return null;
    }

    public function commitAllocations(Request $request): JsonResponse
    {
        $reg = trim((string) $request->input('reg', ''));
        $dueDate = trim((string) $request->input('dueDate', ''));
        $outgoingPalletIds = $request->input('outgoingPalletIds', []);

        if ($reg === '') {
            return response()->json(['error' => 'reg is required'], 400);
        }

        if (!is_array($outgoingPalletIds) || empty($outgoingPalletIds)) {
            return response()->json(['error' => 'outgoingPalletIds must be a non-empty array'], 400);
        }

        if ($dueDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            return response()->json(['error' => 'dueDate is required and must be in Y-m-d format'], 400);
        }

        $vehicle = Vehicle::whereRaw('TRIM(reg) = ?', [$reg])->first();
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        $maxRows = $this->normalizeMaxPalletRows($vehicle->max_pallet_rows ?? null);

        // Fetch allocations for the given vehicle and pallet IDs
        $allocations = VehicleOutgoingPalletAllocation::with('outgoingPallet')
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('outgoing_pallet_id', $outgoingPalletIds)
            ->get()
            ->filter(function ($allocation) use ($maxRows) {
                $row = (int) ($allocation->row ?? 0);
                $column = (int) ($allocation->column ?? 0);
                return $this->isWithinVehicleCapacity($row, $column, $maxRows);
            })
            ->values();

        $committedAt = now();
        $authUser = $request->user();
        $committedByUserId = $authUser ? (int) $authUser->id : null;
        $committedByName = $authUser ? (string) $authUser->name : null;

        DB::connection('tandc_live')->transaction(function () use ($allocations, $committedAt, $committedByUserId, $committedByName, $dueDate) {
            foreach ($allocations as $allocation) {
                $allocation->committed_by_user_id = $committedByUserId;
                $allocation->committed_by_name = $committedByName;
                $allocation->committed_at = $committedAt;
                $allocation->save();

                $pallet = $allocation->outgoingPallet;
                if ($pallet) {
                    $pallet->dispatched = true;
                    $pallet->estimated_delivery_date = $dueDate;
                    $pallet->save();
                }
            }
        });

        return response()->json([
            'success' => true,
            'committedCount' => $allocations->count(),
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

        $maxRows = $this->normalizeMaxPalletRows($vehicle->max_pallet_rows ?? null);

        $depot = Site::find($depotSiteId);

        $query = VehicleOutgoingPalletAllocation::with([
            'outgoingPallet.pickWeightOuts.pickWeightOut',
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
            $pallet->dispatched = true;
            $pallet->save();
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

            $row = (int) ($allocation->row ?? 0);
            $column = (int) ($allocation->column ?? 0);
            if (!$this->isWithinVehicleCapacity($row, $column, $maxRows)) {
                continue;
            }

            $weightKg = (int) ($pallet->getTotalWeight() ?? 0);
            $deliveryNoteNumber = implode('-', $this->getPicksheetIdsForPallet($pallet));
            $contentsPreview = implode("\n", $this->getPicksheetCutSummaryLines($pallet));

            $loadRows[] = [
                'row' => $row,
                'column' => $column,
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
        $loadRows = array_reverse($loadRows);
        $html = view('outgoing-pallets.truck-load-pdf', [
            'generatedAt' => now(),
            'dueDate' => $dueDate,
            'vehicle' => $vehicle,
            'depotName' => $depot ? $depot->name : '',
            'maxRows' => $maxRows,
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
