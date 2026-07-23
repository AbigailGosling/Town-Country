<?php
namespace App\Http\Controllers;

use App\Helpers\GraphHopperHelper;
use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\Customer;
use App\Models\LoadSheet;
use App\Models\TransportPallet;
use App\Models\Site;
use App\Models\Vehicle;
use App\Models\VehicleTransportPalletAllocation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleRoutePlanningController extends Controller
{
    private const DEFAULT_MAX_PALLET_ROWS = 5;
    private const PLANNING_PALLET_COLUMNS = 3;

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
            && $column <= self::PLANNING_PALLET_COLUMNS;
    }

    public function view()
    {
        return view('outgoing-pallets.route-planning');
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

        $vehicles = $vehiclesQuery->get();
        $vehicles->transform(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'reg' => $vehicle->reg,
                'payloadKg' => $vehicle->planningPayloadForVehicle(),
                'palletCapacity' => $vehicle->planningCapacityForVehicle(self::PLANNING_PALLET_COLUMNS),
            ];
        });

        return response()->json(['vehicleOptions' => $vehicles]);
    }

    public function multiVehiclePlan(Request $request): JsonResponse
    {
        $dueDate = Carbon::createFromFormat('Y-m-d', trim((string) $request->input('dueDate', now()->format('Y-m-d'))));
        $depotSite = Site::find((int) $request->input('depot', 0));
        if (!$depotSite || $depotSite->disabled) {
            return response()->json(['error' => 'Invalid depot site'], 400);
        }
        if (!$depotSite->lat || !$depotSite->lon) {
            return response()->json(['error' => 'Depot site must have valid lat and lon'], 400);
        }
        $serviceDurationSeconds = max(60, (int) $request->input('serviceDurationSeconds', 1200));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate->format('Y-m-d'))) {
            return response()->json(['error' => 'dueDate must be in Y-m-d format'], 422);
        }

        $vehicleQuery = Vehicle::whereNotIn("vehicle_type_id", [1,5])
            ->whereNotNull('reg')
            ->where('reg', '<>', '')
            ->where('site_id', $depotSite->id);
        $vehicles = $vehicleQuery->orderBy("reg")->get();
        if ($vehicles->isEmpty()) {
            return response()->json(['error' => 'No vehicles available for planning'], 422);
        }

        $pallets = TransportPallet::with(['transportPalletType'])
            ->whereDate('estimated_delivery_date', $dueDate->format('Y-m-d'))
            ->where(function ($query) {
                //$query->whereNull('dispatched')->orWhere('dispatched', 0);
            })
            ->get();
        if ($pallets->isEmpty()) {
            return response()->json([
                'error' => 'No outgoing pallets found for date',
                'dueDate' => $dueDate->format('Y-m-d'),
            ], 404);
        }

        $customers = Customer::whereIn('id', $pallets->pluck('customer_id')->unique())->get()->keyBy('id');
        $customerAddresses = ClientAddress::whereIn('client_id', $pallets->pluck('customer_id')->unique())
            ->whereIn('address_id', $pallets->pluck('address_id')->unique())
            ->where('client_type', ClientType::CUSTOMER->value)
            ->get()
            ->keyBy(function ($ca) {
                return $ca->client_id . '-' . $ca->address_id;
            });

        $skipped = [];
        $skippedAddresses = [];
        $addressDelTypes = [];
        $services = GraphHopperHelper::servicesFromPallets($depotSite->id, $pallets, $customers, $customerAddresses, $serviceDurationSeconds, $skipped, $skippedAddresses, $addressDelTypes);
        if (empty($services)) {
            Log::error('No services could be created from outgoing pallets', [
                'dueDate' => $dueDate->format('Y-m-d'),
                'palletCount' => count($pallets),
                'skippedPalletIds' => $skipped,
                'skippedAddressIds' => $skippedAddresses,
            ]);
            return response()->json([
                'error' => 'No services could be created from outgoing pallets',
                'skipped' => $skipped,
            ], 422);
        }
        $allGroups = [];
        $locClusterFinding = [];
        $locServices = [];
        foreach ($services as &$service) {
            $addrId = $service['address']['location_id'];
            if (!array_key_exists($addrId, $locClusterFinding)) {
                $locServices[$addrId] = [];
                $locClusterFinding[$addrId] = [
                    'id' => $addrId,
                    'address' => [
                        'lat' => $service['address']['lat'],
                        'lon' => $service['address']['lon'],
                    ],
                    "quantity" => 1,
                ];
            }
            $locServices[$addrId][] = $service['id'];
            $addrDetails = $addressDelTypes[$addrId] ?? ['fresh' => false, 'frozen' => false];
            if ($addrDetails['fresh'] && $addrDetails['frozen']) {
                $service['group'] = 'freshfrozen';
            }
            if (!in_array($service['group'], $allGroups, true)) {
                $allGroups[] = $service['group'];
            }
        }

        $generifiedVehicleTypes = GraphHopperHelper::generifyVehicleTypes($vehicles, self::PLANNING_PALLET_COLUMNS);
        $vrcVehicleTypes = [];
        $depotLocation = [
            'location_id' => 'depot',
            'lat' => $depotSite->lat ?? 0,
            'lon' => $depotSite->lon ?? 0,
        ];
        $vrpVehicles = GraphHopperHelper::vehiclesFromGenerifiedTypes($generifiedVehicleTypes, $vrcVehicleTypes, $depotLocation, $dueDate);

        $locClusteringPayload = [
            'customers' => array_values($locClusterFinding),
            "configuration"=>  [
                "routing"=>  [
                    "profile"=>  config('services.graphhopper.profile', 'truck'),
                    "cost_per_meter"=> 1,
                    "cost_per_second"=> 0
                ],
                "clustering"=>  [
                    "num_clusters"=> count($vrpVehicles)/2
                ]
            ]
        ];
        $clusterResponse = GraphHopperHelper::clusters($locClusteringPayload)['data']["clusters"] ?? [];
        $relations = [
            // [
            //     'type'=> 'not_in_same_route',
            //     'groups'=> $ffgroups,
            // ],

        ];
        foreach ($clusterResponse as $cluster) {
            $serviceIds = [];
            foreach ($cluster['ids'] as $locId) {
                if (!array_key_exists($locId, $locServices)) {
                    continue;
                }
                $serviceIds = array_merge($serviceIds, $locServices[$locId]);
            }
            if (count($serviceIds) < 2) {
                continue;
            }
            $relations[] = [
                'type'=> 'in_same_route',
                'ids' => $serviceIds,
            ];
        }
        $allGroups2 = [];
        if (in_array('fresh', $allGroups, true)) {
            $allGroups2[] = 'fresh';
        }
        if (in_array('freshfrozen', $allGroups, true)) {
            $allGroups2[] = 'freshfrozen';
        }
        if (in_array('frozen', $allGroups, true)) {
            $allGroups2[] = 'frozen';
        }

        if (count($allGroups2) > 1) {
            $relations[] = [
                'type'=> 'in_sequence',
                'groups'=> $allGroups2,
            ];
        }

        $vrpPayload = [
            'configuration' => [
                'routing' => [
                    'calc_points' => true,
                    'return_snapped_waypoints' => true,
                    'consider_traffic' => true,
                    'snap_preventions' => ["motorway", "trunk", "bridge", "ford", "tunnel", "ferry"],
                ],
            ],
            'vehicle_types' => $vrcVehicleTypes,
            'vehicles' => $vrpVehicles,
            'services' => $services,
            'relations' => $relations,
            'objectives' => [
                // [
                //     "type"=> "min",
                //     "value"=> "vehicles",
                // ]
            ],
        ];
        $graphResponse = GraphHopperHelper::vrp($vrpPayload);
        Log::debug('',
            $graphResponse
        );
        $overnights = 2;
        while ($graphResponse && $graphResponse['data']['solution']['no_unassigned'] > 0 && $depotSite->id == 1 && $overnights <= 5) {
            sleep(15);
            $vrcVehicleTypes = [];
            $overnights++;
            $vrpVehicles = GraphHopperHelper::vehiclesFromGenerifiedTypes($generifiedVehicleTypes, $vrcVehicleTypes, $depotLocation, $dueDate,$overnights);
            $vrpPayload = [
                'configuration' => [
                    'routing' => [
                        'calc_points' => true,
                        'return_snapped_waypoints' => true,
                        'consider_traffic' => true,
                        'snap_preventions' => ["motorway", "trunk", "bridge", "ford", "tunnel", "ferry"],
                    ],
                ],
                'vehicle_types' => $vrcVehicleTypes,
                'vehicles' => $vrpVehicles,
                'services' => $services,
                'relations' => $relations,
                'objectives' => [
                    // [
                    //     "type"=> "min",
                    //     "value"=> "vehicles",
                    // ]
                ],
            ];
            $graphResponse = GraphHopperHelper::vrp($vrpPayload);
        }
        if (!$graphResponse['ok']) {
            return response()->json([
                'error' => 'GraphHopper VRP request failed',
                'detail' => $graphResponse['error'],
                'skipped' => $skipped,
            ], 502);
        }

        return response()->json([
            'success' => true,
            'dryRun' => true,
            'dueDate' => $dueDate,
            'vehicleCount' => count($vrpVehicles),
            'serviceCount' => count($services),
            'skipped' => $skipped,
            'persistence' => false,
            'request' => $vrpPayload,
            'response' => $graphResponse['data'],
        ]);
    }

    public function commitAllocations(Request $request): JsonResponse
    {
        $reg = trim((string) $request->input('reg', ''));
        $dueDate = trim((string) $request->input('dueDate', ''));
        $outgoingPalletIds = array_reverse($request->input('outgoingPalletIds', []));
        $returnToOrigin = filter_var($request->input('returnToOrigin', false), FILTER_VALIDATE_BOOLEAN);

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

        $orderedPalletIds = array_values(array_filter(array_map(function ($id) {
            return (int) $id;
        }, $outgoingPalletIds), function ($id) {
            return $id > 0;
        }));

        if (empty($orderedPalletIds)) {
            return response()->json(['error' => 'outgoingPalletIds must contain valid numeric IDs'], 400);
        }

        $terminalLat = null;
        $terminalLon = null;

        if ($returnToOrigin) {
            $site = Site::find((int) ($vehicle->site_id ?? 0));
            if ($site && is_numeric($site->lat) && is_numeric($site->lon)) {
                $terminalLat = (float) $site->lat;
                $terminalLon = (float) $site->lon;
            }
        } else {
            $lastDeliveryPalletId = end($orderedPalletIds);
            if ($lastDeliveryPalletId !== false) {
                $lastDeliveryPallet = TransportPallet::query()
                    ->where('id', (int) $lastDeliveryPalletId)
                    ->first(['customer_id', 'address_id']);

                if ($lastDeliveryPallet) {
                    $lastDeliveryAddress = ClientAddress::query()
                        ->where('client_type', ClientType::CUSTOMER->value)
                        ->where('client_id', (int) $lastDeliveryPallet->customer_id)
                        ->where('address_id', (int) $lastDeliveryPallet->address_id)
                        ->first(['lat', 'lon']);

                    if ($lastDeliveryAddress && is_numeric($lastDeliveryAddress->lat) && is_numeric($lastDeliveryAddress->lon)) {
                        $terminalLat = (float) $lastDeliveryAddress->lat;
                        $terminalLon = (float) $lastDeliveryAddress->lon;
                    }
                }
            }

            if ($terminalLat === null || $terminalLon === null) {
                $site = Site::find((int) ($vehicle->site_id ?? 0));
                if ($site && is_numeric($site->lat) && is_numeric($site->lon)) {
                    $terminalLat = (float) $site->lat;
                    $terminalLon = (float) $site->lon;
                }
            }
        }

        $palletsById = TransportPallet::query()
            ->whereIn('id', $orderedPalletIds)
            ->get(['id', 'transport_pallet_type_id'])
            ->keyBy('id');

        $placements = [];
        $currentRow = 1;
        $occupiedColumnsByRow = [];

        foreach ($orderedPalletIds as $palletId) {
            $pallet = $palletsById->get($palletId);
            if (!$pallet) {
                continue;
            }

            // Type 1 pallets cannot be placed in column 3.
            $isTypeOne = (int) ($pallet->transport_pallet_type_id ?? 0) === 1;
            $placed = false;

            while (!$placed) {
                if ($currentRow > $maxRows) {
                    break 2;
                }

                $occupied = $occupiedColumnsByRow[$currentRow] ?? [];
                $candidateColumns = $isTypeOne ? [1, 2] : [1, 2, 3];

                $columnToUse = null;
                foreach ($candidateColumns as $candidateColumn) {
                    if (!in_array($candidateColumn, $occupied, true)) {
                        $columnToUse = $candidateColumn;
                        break;
                    }
                }

                if ($columnToUse === null) {
                    $currentRow++;
                    continue;
                }

                $placements[] = [
                    'transport_pallet_id' => (int) $palletId,
                    'row' => $currentRow,
                    'column' => $columnToUse,
                ];

                $occupied[] = $columnToUse;
                $occupiedColumnsByRow[$currentRow] = $occupied;
                $placed = true;
            }
        }

        $committedAt = now();
        $authUser = $request->user();
        $committedByUserId = $authUser ? (int) $authUser->id : null;
        $committedByName = $authUser ? (string) $authUser->name : null;

        $committedPalletIds = array_map(function ($placement) {
            return (int) $placement['transport_pallet_id'];
        }, $placements);

        DB::connection('tandc_live')->transaction(function () use ($vehicle, $placements, $committedAt, $committedByUserId, $committedByName, $dueDate, $committedPalletIds, $terminalLat, $terminalLon) {
            $loadSheetId = null;
            if (!empty($placements)) {
                $loadSheet = LoadSheet::create([
                    'user_id' => $committedByUserId,
                    'vehicle_id' => (int) $vehicle->id,
                    'date' => $dueDate,
                ]);
                $loadSheetId = (int) $loadSheet->id;
            }

            if (!empty($committedPalletIds)) {
                VehicleTransportPalletAllocation::whereIn('transport_pallet_id', $committedPalletIds)->delete();
            }

            foreach ($placements as $placement) {
                $allocation = VehicleTransportPalletAllocation::create([
                    'vehicle_id' => (int) $vehicle->id,
                    'load_sheet_id' => $loadSheetId,
                    'transport_pallet_id' => (int) $placement['transport_pallet_id'],
                    'row' => (int) $placement['row'],
                    'column' => (int) $placement['column'],
                    'committed_by_user_id' => $committedByUserId,
                    'committed_by_name' => $committedByName,
                    'committed_at' => $committedAt,
                ]);

                $pallet = TransportPallet::find((int) $allocation->transport_pallet_id);
                if ($pallet) {
                    $pallet->dispatched = true;
                    $pallet->estimated_delivery_date = $dueDate;
                    $pallet->save();
                }
            }

            if ($terminalLat !== null && $terminalLon !== null) {
                $vehicle->lat = $terminalLat;
                $vehicle->lon = $terminalLon;
                $vehicle->save();
            }
        });

        $committedCount = count($placements);
        $requestedCount = count($orderedPalletIds);
        $skippedCount = max(0, $requestedCount - $committedCount);

        return response()->json([
            'success' => true,
            'committedCount' => $committedCount,
            'skippedCount' => $skippedCount,
            'committedAt' => $committedAt,
            'committedByUserId' => $committedByUserId,
            'committedByName' => $committedByName,
            'returnToOrigin' => $returnToOrigin,
        ]);
    }
}
