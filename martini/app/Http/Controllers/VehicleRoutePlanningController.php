<?php
namespace App\Http\Controllers;

use App\Helpers\GraphHopperHelper;
use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\Customer;
use App\Models\DebugLogging;
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

    public function view()
    {
        return view('outgoing-pallets.route-planning');
    }

    public function vehicle(Request $request):JsonResponse
    {
        $depotSiteId = (int) $request->input('depot', 0);
        $dueDateInput = trim((string) $request->input('dueDate', now()->format('Y-m-d')));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDateInput)) {
            $dueDateInput = now()->format('Y-m-d');
        }

        // Return all vehicle registrations as JSON using Eloquent
        $vehiclesQuery = Vehicle::orderBy('reg', 'asc')
            ->whereNotNull('reg')
            ->where('reg', '<>', '')
            ->whereNotIn('vehicle_type_id', [1, 5])
            ->where(function ($query) use ($dueDateInput) {
                $query->whereNull('last_used')
                    ->orWhereDate('last_used', '!=', $dueDateInput);
            });

        if ($depotSiteId > 0) {
            $vehiclesQuery->where('site_id', $depotSiteId);
        }

        $vehicles = $vehiclesQuery->get();
        $rigidCount = $vehicles->where('vehicle_type_id', 2)->count();
        $articCount = $vehicles->where('vehicle_type_id', 3)->count();
        $vanCount = $vehicles->where('vehicle_type_id', 4)->count();

        $vehicles->transform(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'reg' => $vehicle->reg,
                'vehicleTypeId' => (int) $vehicle->vehicle_type_id,
                'payloadKg' => $vehicle->planningPayloadForVehicle(),
                'palletCapacity' => $vehicle->planningCapacityForVehicle(self::PLANNING_PALLET_COLUMNS),
            ];
        });

        return response()->json([
            'vehicleOptions' => $vehicles,
            'vehicleTypeCounts' => [
                'rigids' => $rigidCount,
                'artics' => $articCount,
                'vans' => $vanCount,
            ],
        ]);
    }

    public function multiVehiclePlan(Request $request): JsonResponse
    {
        $routeMode = strtolower((string) $request->input('routeMode', 'generic'));
        $routeStartDateInput = trim((string) $request->input('routeStartDate', ''));
        $routeEndDateInput = trim((string) $request->input('routeEndDate', ''));
        $routeStartTimeInput = trim((string) $request->input('routeStartTime', '04:00'));
        $routeEndTimeInput = trim((string) $request->input('routeEndTime', '18:00'));
        $selectedVehicleId = trim((string) $request->input('routeVehicleId', ''));
        $routePalletIds = array_values(array_filter(array_map(function ($id) {
            $parsed = (int) $id;
            return $parsed > 0 ? $parsed : null;
        }, (array) $request->input('routePalletIds', []))));
        $storedRouteData = $request->input('storedRouteData');

        if ($selectedVehicleId !== '') {
            $selectedVehicle = Vehicle::where('id', (int) $selectedVehicleId)->first();
            if (!$selectedVehicle) {
                $selectedVehicle = Vehicle::whereRaw('TRIM(reg) = ?', [$selectedVehicleId])->first();
            }
            if ($selectedVehicle) {
                $selectedVehicleId = (string) $selectedVehicle->id;
            }
        }

        if ($routeMode === 'stored' && $routeStartDateInput !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $routeStartDateInput)) {
            $dueDate = Carbon::createFromFormat('Y-m-d', $routeStartDateInput);
        } else {
            $dueDate = Carbon::createFromFormat('Y-m-d', trim((string) $request->input('dueDate', now()->format('Y-m-d'))));
        }

        $depotSite = Site::find((int) $request->input('depot', 0));
        $maxRigidsInput = $request->input('maxRigids');
        $maxArticsInput = $request->input('maxArtics');
        $maxVansInput = $request->input('maxVans');
        $maxRigids = is_numeric($maxRigidsInput) ? max(0, (int) $maxRigidsInput) : null;
        $maxArtics = is_numeric($maxArticsInput) ? max(0, (int) $maxArticsInput) : null;
        $maxVans = is_numeric($maxVansInput) ? max(0, (int) $maxVansInput) : null;
        $genericMode = filter_var($request->input('genericMode', ($routeMode === 'generic')), FILTER_VALIDATE_BOOLEAN);
        if ($routeMode === 'stored') {
            $genericMode = false;
        }
        $maxOperatingSeconds = max(3600, (int) $request->input('maxOperatingSeconds', 50400)); // Default 14 hours

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
            ->where('site_id', $depotSite->id)
            ->where(function ($query) use ($dueDate) {
                $query->whereNull('last_used')
                    ->orWhereDate('last_used', '!=', $dueDate->format('Y-m-d'));
            });
        $vehicles = $vehicleQuery->orderBy("has_tail_lift")->orderBy("reg")->get();

        if ($maxRigids !== null || $maxArtics !== null || $maxVans !== null) {
            $rigidCount = 0;
            $articCount = 0;
            $vanCount = 0;

            $vehicles = $vehicles->filter(function ($vehicle) use ($maxRigids, $maxArtics, $maxVans, &$rigidCount, &$articCount, &$vanCount) {
                $vehicleTypeId = (int) ($vehicle->vehicle_type_id ?? 0);

                if ($vehicleTypeId === 2) {
                    if ($maxRigids !== null && $rigidCount >= $maxRigids) {
                        return false;
                    }

                    $rigidCount++;
                    return true;
                }

                if ($vehicleTypeId === 3) {
                    if ($maxArtics !== null && $articCount >= $maxArtics) {
                        return false;
                    }

                    $articCount++;
                    return true;
                }

                if ($vehicleTypeId === 4) {
                    if ($maxVans !== null && $vanCount >= $maxVans) {
                        return false;
                    }

                    $vanCount++;
                    return true;
                }

                return true;
            })->values();
        }

        $selectedVehicle = null;
        if ($selectedVehicleId !== '') {
            $selectedVehicle = Vehicle::where('id', (int) $selectedVehicleId)->first();
            if (!$selectedVehicle) {
                $selectedVehicle = Vehicle::whereRaw('TRIM(reg) = ?', [$selectedVehicleId])->first();
            }
        }

        if ($routeMode === 'stored' && $selectedVehicleId !== '') {
            $vehicles = $vehicles->filter(function ($vehicle) use ($selectedVehicleId) {
                return (string) ($vehicle->id ?? '') === (string) $selectedVehicleId;
            })->values();
        }

        if ($routeMode === 'stored' && is_array($storedRouteData) && !empty($storedRouteData)) {
            $storedRequest = $storedRouteData['request'] ?? [];
            $storedServices = is_array($storedRequest['services'] ?? null) ? $storedRequest['services'] : [];
            $storedVehicles = is_array($storedRequest['vehicles'] ?? null) ? $storedRequest['vehicles'] : [];
            $storedVehicleTypes = is_array($storedRequest['vehicle_types'] ?? null) ? $storedRequest['vehicle_types'] : [];
            $storedRelations = is_array($storedRequest['relations'] ?? null) ? $storedRequest['relations'] : [];
            $selectedVehicleTypeId = $selectedVehicle ? (string) ($selectedVehicle->vehicle_type_id ?? '') : null;

            if (!empty($routePalletIds)) {
                $routePalletIdSet = array_fill_keys(array_map('strval', $routePalletIds), true);
                $storedServices = array_values(array_filter($storedServices, function ($service) use ($routePalletIdSet) {
                    $serviceId = (string) ($service['id'] ?? '');
                    return $serviceId !== '' && isset($routePalletIdSet[$serviceId]);
                }));

                $storedRelations = array_values(array_filter($storedRelations, function ($relation) use ($routePalletIdSet) {
                    if (!is_array($relation['ids'] ?? null)) {
                        return false;
                    }

                    $filteredIds = array_values(array_filter(array_map('strval', $relation['ids']), function ($id) use ($routePalletIdSet) {
                        return isset($routePalletIdSet[$id]);
                    }));

                    return !empty($filteredIds);
                }));
            }

            if ($selectedVehicleId !== '') {
                $storedVehicles = array_values(array_filter($storedVehicles, function ($vehicle) use ($selectedVehicleId, $selectedVehicleTypeId) {
                    $vehicleId = (string) ($vehicle['vehicle_id'] ?? '');
                    $typeId = (string) ($vehicle['type_id'] ?? '');

                    if ($selectedVehicleTypeId !== null && $typeId !== '' && str_starts_with($typeId, $selectedVehicleTypeId . '-')) {
                        return true;
                    }

                    return $vehicleId !== '' && $vehicleId === $selectedVehicleId;
                }));
            }

            foreach ($storedVehicles as &$storedVehicle) {
                $storedVehicleId = (string) ($storedVehicle['vehicle_id'] ?? '');
                $storedVehicleTypeId = (string) ($storedVehicle['type_id'] ?? '');
                $matchesSelectedVehicle = $selectedVehicleId !== '' && $storedVehicleId === $selectedVehicleId;
                $matchesSelectedVehicleType = $selectedVehicle !== null
                    && $selectedVehicleTypeId !== null
                    && $storedVehicleTypeId !== ''
                    && str_starts_with($storedVehicleTypeId, $selectedVehicleTypeId . '-');

                if ($matchesSelectedVehicle || $matchesSelectedVehicleType) {
                    $storedVehicle['earliest_start'] = strtotime($dueDate->format('Y-m-d') . ' ' . ($routeStartTimeInput !== '' ? $routeStartTimeInput : '04:00'));
                    $storedVehicle['latest_end'] = strtotime($dueDate->format('Y-m-d') . ' ' . ($routeEndTimeInput !== '' ? $routeEndTimeInput : '18:00'));
                }
            }
            unset($storedVehicle);

            foreach ($storedServices as &$service) {
                $address = null;
                $locationId = (string) (($service['address']['location_id'] ?? '') ?? '');
                if ($locationId !== '') {
                    [$clientId, $addressId] = array_pad(explode('-', $locationId, 2), 2, null);
                    if ($clientId !== null && $addressId !== null) {
                        $address = ClientAddress::query()
                            ->where('client_type', ClientType::CUSTOMER->value)
                            ->where('client_id', (int) $clientId)
                            ->where('address_id', (int) $addressId)
                            ->first();
                    }
                }

                if ($address) {
                    $openingTime = $address->opening_time ?: Carbon::createFromTime(4, 0, 0);
                    $closingTime = $address->closing_time ?: Carbon::createFromTime(23, 0, 0);
                    $service['time_windows'] = [[
                        'earliest' => $openingTime->copy()->setDate($dueDate->year, $dueDate->month, $dueDate->day)->timestamp,
                        'latest' => $closingTime->copy()->setDate($dueDate->year, $dueDate->month, $dueDate->day)->timestamp,
                    ]];
                }
            }
            unset($service);

            $refinedPayload = [
                'configuration' => [
                    'routing' => [
                        'calc_points' => true,
                        'consider_traffic' => true,
                        'network_data_provider' => 'tomtom',
                    ],
                ],
                'vehicle_types' => $storedVehicleTypes,
                'vehicles' => $storedVehicles,
                'services' => $storedServices,
                'relations' => $storedRelations,
                'objectives' => [],
            ];

            $graphResponse = GraphHopperHelper::vrp($refinedPayload);

            if (!$graphResponse['ok']) {
                return response()->json([
                    'error' => 'GraphHopper VRP request failed for recovered route',
                    'detail' => $graphResponse['error'],
                    'skipped' => [],
                ], 502);
            }

            return response()->json([
                'success' => true,
                'dryRun' => true,
                'routeMode' => $routeMode,
                'genericMode' => false,
                'dueDate' => $dueDate,
                'routeStartDate' => $routeStartDateInput !== '' ? $routeStartDateInput : $dueDate->format('Y-m-d'),
                'routeEndDate' => $routeEndDateInput !== '' ? $routeEndDateInput : $dueDate->format('Y-m-d'),
                'selectedVehicleId' => $selectedVehicleId,
                'maxRigids' => $maxRigids,
                'maxArtics' => $maxArtics,
                'maxVans' => $maxVans,
                'maxOperatingSeconds' => $maxOperatingSeconds,
                'vehicleCount' => count($storedVehicles),
                'serviceCount' => count($storedServices),
                'skipped' => [],
                'persistence' => false,
                'request' => $refinedPayload,
                'response' => $graphResponse['data'],
            ]);
        }

        if ($vehicles->isEmpty()) {
            return response()->json(['error' => 'No vehicles available for planning'], 422);
        }

        $pallets = TransportPallet::with(['transportPalletType'])
            ->whereDate('estimated_delivery_date', $dueDate->format('Y-m-d'))
            ->where(function ($query) {
                $query->whereNull('dispatched')->orWhere('dispatched', 0);
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

        $generifiedVehicleTypes = GraphHopperHelper::generifyVehicleTypes($vehicles, self::PLANNING_PALLET_COLUMNS);
        $vrcVehicleTypes = [];
        $depotLocation = [
            'location_id' => 'depot',
            'lat' => $depotSite->lat ?? 0,
            'lon' => $depotSite->lon ?? 0,
        ];
        $vrpVehicles = GraphHopperHelper::vehiclesFromGenerifiedTypes($generifiedVehicleTypes, $vrcVehicleTypes, $depotLocation, $dueDate, 20, $genericMode, $maxOperatingSeconds);

        $skipped = [];
        $skippedAddresses = [];
        $addressDelTypes = [];
        $services = GraphHopperHelper::servicesFromPallets($depotSite->id, $pallets, $customers, $customerAddresses, $serviceDurationSeconds, $dueDate, $vrpVehicles, $skipped, $skippedAddresses, $addressDelTypes, $genericMode);
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
            // if ($addrDetails['fresh'] && $addrDetails['frozen']) {
            //     $service['group'] = 'freshfrozen';
            // }
            // if (!in_array($service['group'], $allGroups, true)) {
            //     $allGroups[] = $service['group'];
            // }
        }
        $relations = [
            // [
            //     'type'=> 'not_in_same_route',
            //     'groups'=> $ffgroups,
            // ],

        ];
        $locClusteringPayload = [
            'customers' => array_values($locClusterFinding),
            "configuration"=>  [
                "routing"=>  [
                    "profile"=>  config('services.graphhopper.profile', 'truck'),
                    "cost_per_meter"=> 1,
                    "cost_per_second"=> 0
                ],
                "clustering"=>  [
                    "num_clusters"=> 40
                ]
            ]
        ];
        $clusterResponse = GraphHopperHelper::clusters($locClusteringPayload)['data']["clusters"] ?? [];
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
        // if (in_array('fresh', $allGroups, true)) {
        //     $allGroups2[] = 'fresh';
        // }
        // if (in_array('freshfrozen', $allGroups, true)) {
        //     $allGroups2[] = 'freshfrozen';
        // }
        // if (in_array('frozen', $allGroups, true)) {
        //     $allGroups2[] = 'frozen';
        // }

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
                    //'return_snapped_waypoints' => true,
                    'consider_traffic' => !$genericMode, // Don't consider traffic for generic routes
                    //'snap_preventions' => ["motorway", "bridge", "ford", "tunnel", "ferry"],
                    'network_data_provider' =>"tomtom"
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

        // Only attempt overnight optimization for time-specific planning at depot 1
        $overnights = 2;
        $lastUnassigned = PHP_INT_MAX;
        while (!$genericMode && $graphResponse && $graphResponse['data']['solution']['no_unassigned'] > 0 && $graphResponse['data']['solution']['no_unassigned'] < $lastUnassigned && $depotSite->id == 1 && $overnights <= 1) {
            sleep(15);
            $lastUnassigned = $graphResponse['data']['solution']['no_unassigned'];
            $vrcVehicleTypes = [];
            $overnights++;
            $vrpVehicles = GraphHopperHelper::vehiclesFromGenerifiedTypes($generifiedVehicleTypes, $vrcVehicleTypes, $depotLocation, $dueDate, $overnights, $genericMode, $maxOperatingSeconds);
            $vrpPayload = [
                'configuration' => [
                    'routing' => [
                        'calc_points' => true,
                        //'return_snapped_waypoints' => true,
                        'consider_traffic' => true,
                        //'snap_preventions' => ["motorway", "bridge", "ford", "tunnel", "ferry"],
                        'network_data_provider' =>"tomtom"
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
        $d = new DebugLogging();
            $d->page = "send_pod";
            $d->request = json_encode($vrpPayload);
            $d->user_id = -1;
            $d->session_id = -1;
            $d->body = json_encode($graphResponse);
            $d->save();
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
            'routeMode' => $routeMode,
            'genericMode' => $genericMode,
            'dueDate' => $dueDate,
            'routeStartDate' => $routeStartDateInput !== '' ? $routeStartDateInput : $dueDate->format('Y-m-d'),
            'routeEndDate' => $routeEndDateInput !== '' ? $routeEndDateInput : $dueDate->format('Y-m-d'),
            'selectedVehicleId' => $selectedVehicleId,
            'maxRigids' => $maxRigids,
            'maxArtics' => $maxArtics,
            'maxVans' => $maxVans,
            'maxOperatingSeconds' => $maxOperatingSeconds,
            'vehicleCount' => count($vrpVehicles),
            'serviceCount' => count($services),
            'skipped' => $skipped,
            'persistence' => false,
            'storedRouteData' => $storedRouteData,
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

            $vehicle->last_used = $committedAt;

            if ($terminalLat !== null && $terminalLon !== null) {
                $vehicle->lat = $terminalLat;
                $vehicle->lon = $terminalLon;
            }

            $vehicle->save();
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
