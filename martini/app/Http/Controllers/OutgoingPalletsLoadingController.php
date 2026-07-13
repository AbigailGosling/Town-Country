<?php

namespace App\Http\Controllers;

use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\Customer;
use App\Models\LoadSheet;
use App\Models\TransportPallet;
use App\Models\TransportPalletType;
use App\Models\Site;
use App\Models\Vehicle;
use App\Models\VehicleTransportPalletAllocation;
use App\Models\TransportPalletPickWeight;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class OutgoingPalletsLoadingController extends Controller
{
    private const DEFAULT_MAX_PALLET_ROWS = 5;
    private const PALLET_COLUMNS = 3;

    private function normalizeMaxPalletRows($value): int
    {
        $rows = (int) $value;
        return $rows > 0 ? $rows : self::DEFAULT_MAX_PALLET_ROWS;
    }

    private function isWithinVehicleCapacity(int $row, int $column, int $maxRows): bool
    {
        return $row >= 1
            && $row <= $maxRows
            && $column >= 1
            && $column <= self::PALLET_COLUMNS;
    }

    private function isStandardPallet(TransportPallet $pallet): bool
    {
        $pallet->loadMissing('transportPalletType');
        $typeName = strtolower(trim((string) ($pallet->transportPalletType->name ?? '')));
        return str_starts_with($typeName, 'standard');
    }

    private function getPicksheetIdsForPallet(TransportPallet $pallet): array
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

    private function getPicksheetCutSummaryLines(TransportPallet $pallet, int $maxLines = 3): array
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
    private function getLegacyCreditCheckForCustomer(int $customerId): array
    {
        if ($customerId <= 0) {
            return [];
        }

        $request = Request::create(
            route('legacy', ['path' => 'legacy/ajax/customer_credit_check.php']),
            'GET',
            ['customer_id' => $customerId]
        );
        $response = app()->handle($request);

        if (is_array($response)) {
            return $response;
        }

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            return is_array($data) ? $data : [];
        }

        if (is_object($response) && method_exists($response, 'getContent')) {
            $decoded = json_decode((string) $response->getContent(), true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function canTransportPalletBeLoaded(int $customerId): bool
    {
        $response = $this->getLegacyCreditCheckForCustomer($customerId);
        $customer = Customer::find($customerId);
        // Keep the legacy eligibility formula exactly as provided.
        return !(($response['overcredit'] || $response['printblock']) && !$customer->allowPrint);
    }

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
            ->where('reg', '<>', '')
            ->whereNotIn('vehicle_type_id', [1, 5]);

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
        $dueDate = trim((string) $request->input('dueDate', ''));
        $loadSheetId = (int) $request->input('loadSheetId', 0);
        if ($reg === '') {
            return response()->json(['allocations' => []]);
        }

        $vehicle = Vehicle::whereRaw('TRIM(reg) = ?', [$reg])->first();
        if (!$vehicle) {
            return response()->json(['allocations' => []]);
        }

        if ($loadSheetId <= 0) {
            return response()->json(['allocations' => []]);
        }

        $maxRows = $this->normalizeMaxPalletRows($vehicle->max_pallet_rows ?? null);

        $allocationsQuery = VehicleTransportPalletAllocation::with([
            'outgoingPallet.pickWeightOuts',
            'outgoingPallet.customer',
            'outgoingPallet.transportPalletType',
        ])
            ->where('vehicle_id', $vehicle->id)
            ->where('load_sheet_id', $loadSheetId);

        if ($dueDate !== '') {
            $allocationsQuery->whereHas('outgoingPallet', function ($query) use ($dueDate) {
                $query->whereDate('estimated_delivery_date', $dueDate);
            });
        }

        $allocations = $allocationsQuery
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
                    'palletType' => $pallet->transportPalletType->name ?? 'Euro',
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

    public function loadSheets(Request $request): JsonResponse
    {
        $reg = trim((string) $request->input('reg', ''));
        $dueDate = trim((string) $request->input('dueDate', ''));

        if ($reg === '' || $dueDate === '') {
            return response()->json(['loadSheets' => []]);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            return response()->json(['error' => 'dueDate must be in Y-m-d format'], 422);
        }

        $vehicle = Vehicle::whereRaw('TRIM(reg) = ?', [$reg])->first();
        if (!$vehicle) {
            return response()->json(['loadSheets' => []]);
        }

        $sheets = LoadSheet::query()
            ->where('vehicle_id', (int) $vehicle->id)
            ->whereDate('date', $dueDate)
            ->orderByDesc('created_at')
            ->get(['id', 'vehicle_id', 'date', 'created_at']);

        if ($sheets->isEmpty()) {
            return response()->json(['loadSheets' => []]);
        }

        $allocationCounts = VehicleTransportPalletAllocation::query()
            ->selectRaw('load_sheet_id, COUNT(*) as pallet_count')
            ->whereIn('load_sheet_id', $sheets->pluck('id')->all())
            ->groupBy('load_sheet_id')
            ->pluck('pallet_count', 'load_sheet_id');

        $loadSheets = $sheets->map(function ($sheet) use ($allocationCounts) {
            $sheetId = (int) $sheet->id;
            $count = (int) ($allocationCounts[$sheetId] ?? 0);
            $createdAt = $sheet->created_at ? $sheet->created_at->format('H:i') : '';
            $label = 'Sheet #' . $sheetId;
            if ($createdAt !== '') {
                $label .= ' • ' . $createdAt;
            }
            $label .= ' • ' . $count . ' pallet' . ($count === 1 ? '' : 's');

            return [
                'id' => $sheetId,
                'label' => $label,
                'date' => (string) ($sheet->date ? $sheet->date->format('Y-m-d') : ''),
                'createdAt' => $sheet->created_at,
                'palletCount' => $count,
            ];
        })->values();

        return response()->json(['loadSheets' => $loadSheets]);
    }
    public function updateAllocation(Request $request): JsonResponse
    {
        $outgoingPalletId = (int) $request->input('outgoingPalletId', 0);
        $regAllocatedTo = (string) $request->input('regAllocatedTo', '');
        $palletRow = $request->input('palletRow');
        $palletColumn = $request->input('palletColumn');
        $dueDate = trim((string) $request->input('dueDate', ''));
        $requestedLoadSheetId = (int) $request->input('loadSheetId', 0);

        if ($outgoingPalletId <= 0) {
            return response()->json(['error' => 'outgoingPalletId is required'], 400);
        }

        $pallet = TransportPallet::find($outgoingPalletId);
        if (!$pallet) {
            return response()->json(['error' => 'Pallet not found'], 404);
        }

        $regAllocatedTo = trim($regAllocatedTo);

        if ($regAllocatedTo === '' || $palletRow === null || $palletColumn === null) {
            $deleteQuery = VehicleTransportPalletAllocation::where('transport_pallet_id', $pallet->id);
            if ($requestedLoadSheetId > 0) {
                $deleteQuery->where('load_sheet_id', $requestedLoadSheetId);
            }

            $deleted = $deleteQuery->delete();
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

        $loadSheetId = null;
        $createdNewLoadSheet = false;

        if ($requestedLoadSheetId > 0) {
            $existingLoadSheet = LoadSheet::query()
                ->where('id', $requestedLoadSheetId)
                ->where('vehicle_id', $vehicle->id)
                ->first();

            if (!$existingLoadSheet) {
                return response()->json(['error' => 'Load sheet not found for vehicle'], 422);
            }

            if ($dueDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
                $sheetDate = $existingLoadSheet->date ? $existingLoadSheet->date->format('Y-m-d') : '';
                if ($sheetDate !== '' && $sheetDate !== $dueDate) {
                    return response()->json(['error' => 'Load sheet date does not match selected due date'], 422);
                }
            }

            $loadSheetId = (int) $existingLoadSheet->id;
        } else {
            if ($dueDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
                return response()->json(['error' => 'dueDate is required and must be in Y-m-d format when creating a load sheet'], 422);
            }

            $authUser = $request->user();
            $loadSheet = LoadSheet::create([
                'user_id' => $authUser ? (int) $authUser->id : null,
                'vehicle_id' => (int) $vehicle->id,
                'date' => $dueDate,
            ]);

            $loadSheetId = (int) $loadSheet->id;
            $createdNewLoadSheet = true;
        }

        VehicleTransportPalletAllocation::where('transport_pallet_id', $pallet->id)
            ->where('load_sheet_id', $loadSheetId)
            ->where('vehicle_id', '<>', $vehicle->id)
            ->delete();

        VehicleTransportPalletAllocation::where('vehicle_id', $vehicle->id)
            ->where('load_sheet_id', $loadSheetId)
            ->where('row', $row)
            ->where('column', $column)
            ->where('transport_pallet_id', '<>', $pallet->id)
            ->delete();

        VehicleTransportPalletAllocation::updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'transport_pallet_id' => $pallet->id,
                'load_sheet_id' => $loadSheetId,
            ],
            [
                'row' => $row,
                'column' => $column,
                'committed_by_user_id' => null,
                'committed_by_name' => null,
                'committed_at' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'affectedRows' => 1,
            'loadSheetId' => $loadSheetId,
            'createdNewLoadSheet' => $createdNewLoadSheet,
        ]);
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

        $pallet = TransportPallet::find($outgoingPalletId);
        if (!$pallet) {
            return response()->json(['error' => 'Pallet not found'], 404);
        }

        $type = TransportPalletType::query()
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
        $loadSheetId = (int) $request->input('loadSheetId', 0);

        if ($dueDate === '' || $depotSiteId <= 0 || $reg === '') {
                return response()->json(['orders' => []]);
        }

        $pallets = TransportPallet::with('pickWeightOuts.pickWeightOut','customer','transportPalletType')->where('estimated_delivery_date', $dueDate)->orWhereNull('estimated_delivery_date')->get();

        $allocationsQuery = VehicleTransportPalletAllocation::with('vehicle');

        $vehicle = Vehicle::whereRaw('TRIM(reg) = ?', [$reg])->first();
        if ($vehicle) {
            $allocationsQuery->where('vehicle_id', (int) $vehicle->id);
        }

        if ($loadSheetId > 0) {
            $allocationsQuery->where('load_sheet_id', $loadSheetId);
        } else {
            $allocationsQuery->whereRaw('1 = 0');
        }

        $allocations = $allocationsQuery
            ->get()
            ->keyBy('transport_pallet_id');

        $anyAllocationByPalletId = VehicleTransportPalletAllocation::query()
            ->with(['vehicle', 'loadSheet'])
            ->whereIn('transport_pallet_id', $pallets->pluck('id')->map(fn ($id) => (int) $id)->all())
            ->orderByDesc('id')
            ->get()
            ->groupBy('transport_pallet_id')
            ->map(function ($group) {
                return $group->first();
            });

        $orders = [];
        $canLoadByCustomerId = [];
        foreach ($pallets as $pallet)
        {
            $customerId = (int) ($pallet->customer_id ?? 0);
            if (!array_key_exists($customerId, $canLoadByCustomerId)) {
                $canLoadByCustomerId[$customerId] = $this->canTransportPalletBeLoaded($customerId);
            }

            $allocation = $allocations->get($pallet->id);
            if ($allocation) {
                $regAllocatedTo = $allocation->vehicle ? trim((string)$allocation->vehicle->reg) : '';
            } else {
                $regAllocatedTo = '';
            }

            $anyAllocation = $anyAllocationByPalletId->get($pallet->id);
            $anyAllocatedVehicleReg = $anyAllocation && $anyAllocation->vehicle
                ? trim((string) ($anyAllocation->vehicle->reg ?? ''))
                : '';
            $anyAllocatedLoadSheetId = $anyAllocation ? (int) ($anyAllocation->load_sheet_id ?? 0) : 0;

            $anyAllocatedLoadSheetLabel = '';
            if ($anyAllocation && $anyAllocation->loadSheet) {
                $sheetId = (int) ($anyAllocation->loadSheet->id ?? 0);
                $sheetCreated = $anyAllocation->loadSheet->created_at
                    ? $anyAllocation->loadSheet->created_at->format('H:i')
                    : '';
                if ($sheetId > 0) {
                    $anyAllocatedLoadSheetLabel = 'Sheet #' . $sheetId;
                    if ($sheetCreated !== '') {
                        $anyAllocatedLoadSheetLabel .= ' • ' . $sheetCreated;
                    }
                }
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
                    'palletType' => $pallet->transportPalletType->name,
                    'weightKg' => (int)($pallet->getTotalWeight() ?? 0),
                    'contentsPreview' => $contentsPreview,
                    'freshFrozen' => $pallet->getTemperatureCategory() ?? '',
                    'regAllocatedTo' => $regAllocatedTo ?? '',
                    'row' => $allocation ? (int) $allocation->row : null,
                    'column' => $allocation ? (int) $allocation->column : null,
                    'isAllocatedAnywhere' => $anyAllocation ? true : false,
                    'allocatedVehicleReg' => $anyAllocatedVehicleReg,
                    'allocatedLoadSheetId' => $anyAllocatedLoadSheetId > 0 ? $anyAllocatedLoadSheetId : null,
                    'allocatedLoadSheetLabel' => $anyAllocatedLoadSheetLabel,
                    'canBeLoaded' => (bool) ($canLoadByCustomerId[$customerId] ?? false),
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

        $query = TransportPallet::query();
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

        $pallet = TransportPallet::with(['customer', 'transportPalletType', 'pickWeightOuts.pickWeightOut'])->find($outgoingPalletId);
        if (!$pallet) {
            return response()->json(['error' => 'Pallet not found'], 404);
        }

        $address = ClientAddress::where('client_id', $pallet->customer_id)
            ->where('address_id', $pallet->address_id)
            ->where('client_type', ClientType::CUSTOMER->value)
            ->first();

        $pickLinks = TransportPalletPickWeight::where('transport_pallet_id', $pallet->id)->get();
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
                'palletType' => $pallet->transportPalletType->name ?? '',
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

        // try {
        //         $client = new \GuzzleHttp\Client([
        //                 'verify' => 'C:\\inetpub\\intakemaster\\certs\\cacert.pem',
        //         ]);
        //         $response = $client->post('https://api.openai.com/v1/chat/completions', [
        //                 'headers' => [
        //                         'Content-Type' => 'application/json',
        //                         'Authorization' => 'Bearer ' . $apiKey,
        //                 ],
        //                 'json' => $payload,
        //         ]);
        //         $data = json_decode($response->getBody(), true);
        //         $itinerary = $data['choices'][0]['message']['content'] ?? '';
        //         return response()->json(['itinerary' => $itinerary]);
        // } catch (\Exception $e) {
        //         return response()->json([
        //                 'error' => 'OpenAI request failed',
        //                 'detail' => $e->getMessage(),
        //         ], 500);
        // }
        return response()->json([
                'error' => 'OpenAI integration is currently disabled in this environment.',
        ], 503);
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
        $allocations = VehicleTransportPalletAllocation::with('outgoingPallet')
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('transport_pallet_id', $outgoingPalletIds)
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
            $loadSheetId = null;
            if ($allocations->isNotEmpty()) {
                $firstAllocation = $allocations->first();
                $loadSheet = LoadSheet::create([
                    'user_id' => $committedByUserId,
                    'vehicle_id' => (int) ($firstAllocation->vehicle_id ?? 0),
                    'date' => $dueDate,
                ]);
                $loadSheetId = (int) $loadSheet->id;
            }

            foreach ($allocations as $allocation) {
                $allocation->load_sheet_id = $loadSheetId;
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
        $loadSheetId = (int) $request->input('loadSheetId', 0);

        if ($reg === '' || $depotSiteId <= 0) {
            return response('Missing reg or depot parameter', 400);
        }

        $vehicle = Vehicle::with('site')->whereRaw('TRIM(reg) = ?', [$reg])->first();
        if (!$vehicle) {
            return response('Vehicle not found', 404);
        }

        $maxRows = $this->normalizeMaxPalletRows($vehicle->max_pallet_rows ?? null);

        $depot = Site::find($depotSiteId);

        $query = VehicleTransportPalletAllocation::with([
            'outgoingPallet.pickWeightOuts.pickWeightOut',
            'outgoingPallet.customer',
            'outgoingPallet.transportPalletType',
        ])->where('vehicle_id', $vehicle->id);

        if ($loadSheetId > 0) {
            $query->where('load_sheet_id', $loadSheetId);
        } elseif ($dueDate !== '') {
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
                'palletType' => $pallet->transportPalletType->name ?? 'Euro',
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
            'loadSheetId' => $loadSheetId > 0 ? $loadSheetId : null,
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
