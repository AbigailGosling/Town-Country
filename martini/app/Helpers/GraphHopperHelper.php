<?php

namespace App\Helpers;

use App\Models\ClientAddress;
use App\Models\Customer;
use App\Models\TransportPallet;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GraphHopperHelper
{
    public static function apiKey(): string
    {
        return trim((string) config('services.graphhopper.key', ''));
    }

    public static function baseUrl(): string
    {
        return rtrim((string) config('services.graphhopper.base_url', 'https://graphhopper.com/api/1'), '/');
    }

    public static function get(string $endpoint, array $query = []): array
    {
        $apiKey = self::apiKey();
        if ($apiKey === '') {
            return [
                'ok' => false,
                'status' => 500,
                'data' => [
                    'ok' => false,
                    'error' => 'GraphHopper API key not configured',
                ],
            ];
        }

        $query['key'] = $query['key'] ?? $apiKey;

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.graphhopper.timeout', 20))
                ->retry(2, 250)
                ->get(self::baseUrl() . $endpoint, $query);
            sleep(1);

            if (!$response->successful()) {
                Log::warning('GraphHopper request failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            $payload = $response->json();
            if (!is_array($payload)) {
                $payload = ['raw' => $response->body()];
            }

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'data' => $payload,
            ];
        } catch (ConnectionException | RequestException $exception) {
            return [
                'ok' => false,
                'status' => 502,
                'data' => [
                    'ok' => false,
                    'error' => 'Unable to communicate with GraphHopper API',
                    'message' => $exception->getMessage(),
                ],
            ];
        } catch (\Throwable $exception) {
            Log::error('Unexpected GraphHopper exception', [
                'endpoint' => $endpoint,
                'message' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => 500,
                'data' => [
                    'ok' => false,
                    'error' => 'Unexpected error while calling GraphHopper API',
                ],
            ];
        }
    }

    public static function geocodeAddress(string $queryAddress): ?array
    {
        $response = self::get('/geocode', [
            'q' => $queryAddress,
            'limit' => 1,
            'locale' => config('services.graphhopper.locale', 'en'),
        ]);
        if (!$response['ok']) {
            Log::error('GraphHopper geocoding failed', [
                'address' => $queryAddress,
                'error' => $response,
            ]);
            return null;
        }

        $hits = data_get($response['data'], 'hits', []);
        if (!is_array($hits) || empty($hits)) {
            Log::error('GraphHopper geocoding returned no hits', [
                'address' => $queryAddress,
                'response' => $response['data'],
            ]);
            return null;
        }

        $first = $hits[0] ?? null;
        if (!is_array($first) || !isset($first['point']['lat'], $first['point']['lng'])) {
            Log::error('GraphHopper geocoding returned invalid hit', [
                'address' => $queryAddress,
                'hit' => $first,
            ]);
            return null;
        }

        return [
            'lat' => (float) $first['point']['lat'],
            'lon' => (float) $first['point']['lng'],
            'hits'=> count($hits),
        ];
    }

    public static function vrp(array $payload): array
    {
        $apiKey = self::apiKey();
        if ($apiKey === '') {
            return [
                'ok' => false,
                'error' => 'GraphHopper API key not configured',
            ];
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.graphhopper.timeout', 20))
                ->retry(2, 250)
                ->post(self::baseUrl() . '/vrp?key=' . urlencode($apiKey), $payload);

            if (!$response->successful()) {
                Log::warning('GraphHopper VRP request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'ok' => false,
                    'error' => $response->body(),
                ];
            }

            $data = $response->json();
            if (!is_array($data)) {
                $data = ['raw' => $response->body()];
            }
            return [
                'ok' => true,
                'data' => $data,
            ];
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            Log::error("payload", [$payload]);
            return [
                'ok' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public static function clusters(array $payload): array
    {
        $apiKey = self::apiKey();
        if ($apiKey === '') {
            return [
                'ok' => false,
                'error' => 'GraphHopper API key not configured',
            ];
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.graphhopper.timeout', 20))
                ->retry(2, 250)
                ->post(self::baseUrl() . '/cluster?key=' . urlencode($apiKey), $payload);

            if (!$response->successful()) {
                Log::warning('GraphHopper clustering request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'ok' => false,
                    'error' => $response->body(),
                ];
            }

            $data = $response->json();
            if (!is_array($data)) {
                $data = ['raw' => $response->body()];
            }
            return [
                'ok' => true,
                'data' => $data,
            ];
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            Log::error("payload", [$payload]);
            return [
                'ok' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public static function formatAddressForGeocoding(ClientAddress $address): string
    {
        return trim(implode(', ', array_filter([
            (string) ($address->address_1 ?? ''),
            // (string) ($address->address_2 ?? ''),
            // (string) ($address->address_3 ?? ''),
            // (string) ($address->address_4 ?? ''),
            (string) ($address->postcode ?? ''),
            'United Kingdom',
        ])));
    }
    /**
     * @param Collection<Vehicle> $vehicles
     * @param int $planningPalletColumns
     * @return array
     */
    public static function generifyVehicleTypes(Collection $vehicles,int $planningPalletColumns): array
    {
        $output = [];
        foreach ($vehicles as $vehicle) {
            $tc_vehicle_type = $vehicle->vehicle_type_id;
            $payload = $vehicle->planningPayloadForVehicle();
            $capacity = $vehicle->planningCapacityForVehicle($planningPalletColumns);
            if ($payload === null || $payload === '' || $payload <= 0 || $capacity === null || $capacity === '' || $capacity <= 0) {
                continue;
            }
            $ghtype_id = $tc_vehicle_type . '-'. $capacity . '-' . $payload;
            foreach ($output as &$existing) {
                if ($existing['type_id'] == $ghtype_id) {
                    $existing['vehicle'][] = $vehicle;
                    continue 2;
                }
            }
            $output[] = [
                'type_id' => $ghtype_id,
                'profile' => config('services.graphhopper.profile', 'truck'),
                'capacity' => [$capacity, $payload],
                'vehicle' => [$vehicle],
            ];
        }
        usort($output,"self::vicSorter");
        return $output;
    }
    private static function vicSorter($a,$b)
    {
        if ($a['capacity'][0] != $b['capacity'][0])
        {
            return $b['capacity'][0] - $a['capacity'][0];
        }
        else
        {
            return $b['capacity'][1] - $a['capacity'][1];
        }
    }
    /**
     * @param array $generifiedVehicleTypes
     * @param array $vrcVehicleTypes
     * @param array $depotLocation
     * @param Carbon $dueDate
     * @param int $overnight_limit
     * @param bool $genericMode If true, uses max_driving_time instead of specific time windows
     * @param int $maxOperatingSeconds Maximum driving time for generic routes (default 14 hours)
     * @return array
     */
    public static function vehiclesFromGenerifiedTypes(array $generifiedVehicleTypes, array &$vrcVehicleTypes, array $depotLocation, Carbon $dueDate, int $overnight_limit = 2, bool $genericMode = false, int $maxOperatingSeconds = 50400): array
    {
        $daytwo = $dueDate->copy()->addDay()->format('Y-m-d');
        $vrpVehicles = [];
        $overnighters = 0;
        foreach ($generifiedVehicleTypes as $type) {
            $type_overview = explode('-', $type['type_id']);
            $vrcVehicleTypes[] = [
                'type_id' => $type['type_id'],
                'profile' => $type['profile'],
                'capacity' => $type['capacity'],
            ];
            foreach ($type['vehicle'] as $i => $vehicle) {
                if (count($vrpVehicles)>=20)break 2;
                //$startLocation = ($vehicle->lat && $vehicle->lon) ? ['location_id' => $vehicle->reg, 'lat' => (float)$vehicle->lat, 'lon' => (float)$vehicle->lon] : $depotLocation;
                $startLocation = $depotLocation;
                
                if ($genericMode) {
                    // Generic mode: no specific time windows, just max driving time
                    $vrpVehicle = [
                        'vehicle_id' => $type['type_id'] . '-' . $i,
                        'type_id' => $type['type_id'],
                        'start_address' => $depotLocation,
                        'end_address' => $depotLocation,
                        'max_driving_time' => $maxOperatingSeconds,
                        'return_to_depot' => true,
                        'min_jobs' => 1,
                    ];
                } else {
                    // Time-specific mode: original behavior with time windows
                    if (($type_overview[0] == 3 && $overnighters <= $overnight_limit || $startLocation['location_id'] !== 'depot')) {
                        $overnighters++;
                        $vrpVehicle = [
                            'vehicle_id' => $type['type_id'] . '-' . $i,
                            'type_id' => $type['type_id'],
                            'start_address' => $depotLocation,
                            'earliest_start' => strtotime($dueDate->format('Y-m-d') . ' 04:00:00'),
                            'latest_end' => strtotime($dueDate->format('Y-m-d') . ' 20:00:00'),
                            'break' => [
                                'earliest' => strtotime($dueDate->format('Y-m-d') . ' 12:00:00'),
                                'latest' => strtotime($dueDate->format('Y-m-d') . ' 14:00:00'),
                                'duration' => 3600,
                            ],
                            'return_to_depot' => false,
                            'min_jobs' => 2,
                        ];
                        if ($startLocation['location_id'] !== 'depot') {
                            $vrpVehicle['return_to_depot'] = true;
                            $vrpVehicle['end_address'] = $depotLocation;
                        }
                    }
                    else
                    {
                       $vrpVehicle = [
                            'vehicle_id' => $type['type_id'] . '-' . $i,
                            'type_id' => $type['type_id'],
                            'start_address' => $depotLocation,
                            'end_address' => $depotLocation,
                            'earliest_start' => strtotime($dueDate->format('Y-m-d') . ' 04:00:00'),
                            'latest_end' => strtotime($dueDate->format('Y-m-d') . ' 14:00:00'),
                            'min_jobs' => 2,
                        ];
                    }
                }
                
                if ($vehicle->has_tail_lift) {
                    $vrpVehicle['skills'] = ['tail_lift'];
                }
                $vrpVehicles[] = $vrpVehicle;

            }
        }
        return $vrpVehicles;
    }

    /**
     * @param int $site_id
     * @param Collection<TransportPallet> $pallets
     * @param Collection<Customer> $customers
     * @param Collection<ClientAddress> $customerAddresses
     * @param int $serviceDurationSeconds
     * @param Carbon $workingDate
     * @param array $vrpVehicles
     * @param array<int, array{outgoingPalletId: int, reason: string}> $skipped
     * @param array<int, string> $skippedAddresses
     * @param array<string, array{fresh: bool, frozen: bool}> $addressDelTypes
     * @param bool $genericMode If true, omits time_windows from services
    */
    public static function servicesFromPallets(int $site_id, Collection $pallets, Collection $customers, Collection $customerAddresses, int $serviceDurationSeconds, Carbon $workingDate, array $vrpVehicles, array &$skipped = [], array &$skippedAddresses = [], array &$addressDelTypes = [], bool $genericMode = false): array
    {
        $services = [];
        foreach ($pallets as $pallet) {
            // if (count($services) >= 5) {
            //     $skipped[] = ['outgoingPalletId' => (int) $pallet->id, 'reason' => 'Service limit reached, skipping remaining pallets',];
            //     continue;
            // }
            $address = $customerAddresses[$pallet->customer_id . '-' . $pallet->address_id] ?? null;
            if (!$address) {
                $skipped[] = ['outgoingPalletId' => (int) $pallet->id, 'reason' => 'Client address missing',];
                continue;
            }
            if ($address->collection) continue;
            if ($address->site_id !== $site_id) {
                continue;
            }
            if (in_array($pallet->customer_id.'-'.$pallet->address_id, $skippedAddresses, true) || ($address->geocoding_tried && (!$address->lat || !$address->lon))) {
                $skippedAddresses[] = $pallet->customer_id.'-'.$pallet->address_id;
                $skipped[] = ['outgoingPalletId' => (int) $pallet->id, 'reason' => 'Previously skipped due to geocoding failure for this address',];
                continue;
            }
            if (strpos(trim(strtolower((string) $address->address_1)), 'collect') === 0) {
                $skipped[] = ['outgoingPalletId' => (int) $pallet->id, 'reason' => 'Ignore Collections',];
                continue;
            }
            if ($pallet->pickWeightOuts->isEmpty()) {
                $skipped[] = ['outgoingPalletId' => (int) $pallet->id, 'reason' => 'No pick weight outs linked to pallet',];
                continue;
            }
            $location = null;
            $storedLat = $address->lat;
            $storedLon = $address->lon;
            if (is_numeric($storedLat) && is_numeric($storedLon)) {
                $storedLat = (float) $storedLat;
                $storedLon = (float) $storedLon;
                $location = ['lat' => $storedLat,'lon' => $storedLon,];
            }
            if ($location == null) {
                if ($address->geocoding_tried) {
                    $skippedAddresses[] = $pallet->customer_id.'-'.$pallet->address_id;
                    $skipped[] = ['outgoingPalletId' => (int) $pallet->id,'reason' => 'Previously tried geocoding and failed',];
                    continue;
                }
                $queryAddress = GraphHopperHelper::formatAddressForGeocoding($address);
                if ($queryAddress === '') {
                    $skipped[] = ['outgoingPalletId' => (int) $pallet->id,'reason' => 'Address is empty',];
                    continue;
                }
                $address->geocoding_tried = true;
                $location = GraphHopperHelper::geocodeAddress($queryAddress);
                if ($location === null) $location = GraphHopperHelper::geocodeAddress($address->postcode);
                if ($location === null) {
                    $skippedAddresses[] = $pallet->customer_id.'-'.$pallet->address_id;
                    $skipped[] = [
                        'outgoingPalletId' => (int) $pallet->id,
                        'reason' => 'Failed to geocode address',
                        'address' => $queryAddress,
                    ];
                    $address->save();
                    continue;
                }
                $address->lat = (float) $location['lat'];
                $address->lon = (float) $location['lon'];
                $address->save();
            }

            $customer = $customers[$pallet->customer_id] ?? null;
            $tempCategory = $pallet->normalizePlanningTemperatureCategory();
            $allowedVehicles = [];
            $allowedVehicleTypes = $address->allowed_vehicle_types ? explode(',', $address->allowed_vehicle_types) : [];
            foreach ($vrpVehicles as $vehicle) {
                $type_overview = explode('-', $vehicle['type_id']);
                if (in_array($type_overview[0], $allowedVehicleTypes)) {
                    $allowedVehicles[] = $vehicle['vehicle_id'];
                }
            }
            
            $thisService =[
                'id' => (string)$pallet->id,
                'name' => $customer->businessname . ' - ' . ($address->address_1 ?? '') . ' - ' . ($address->postcode ?? ''),
                'address' => [
                    'location_id' => (string)$address->client_id . '-' . $address->address_id,
                    'lat' => $location['lat'],
                    'lon' => $location['lon'],
                ],
                'setup_time' => $serviceDurationSeconds,
                'size' => [($pallet->type_id == 1 ? 1.5 : 1),(int)FuncHelper::ceilDec($pallet->getTotalWeight(), 0) ?? 0],
                //'group' => $tempCategory,
                'allowed_vehicles' => $allowedVehicles,
            ];
            
            // Add time_windows only if not in generic mode
            if (!$genericMode) {
                $addressOpeningTime = $address->opening_time;
                if ($addressOpeningTime == null) {
                    $addressOpeningTime = Carbon::now()->setTime(4, 0, 0);
                }
                $addressClosingTime = $address->closing_time;
                if ($addressClosingTime == null) {
                    $addressClosingTime = Carbon::now()->setTime(23, 0, 0);
                }
                $thisService['time_windows'] = [
                    [
                        'earliest' => $addressOpeningTime->copy()->setDate($workingDate->year, $workingDate->month, $workingDate->day)->timestamp,
                        'latest' => $addressClosingTime->copy()->setDate($workingDate->year, $workingDate->month, $workingDate->day)->timestamp,
                    ],
                ];
            }
            
            if ($address->require_tail_lift) {
                $thisService['required_skills'] = ['tail_lift'];
            }
            $services[] = $thisService;
            if (!array_key_exists($address->client_id . '-' . $address->address_id, $addressDelTypes)) {
                $addressDelTypes[$address->client_id . '-' . $address->address_id] = ['fresh' => false, 'frozen' => false];
            }
            if ($tempCategory === 'fresh') {
                $addressDelTypes[$address->client_id . '-' . $address->address_id]['fresh'] = true;
            } elseif ($tempCategory === 'frozen') {
                $addressDelTypes[$address->client_id . '-' . $address->address_id]['frozen'] = true;
            }
        }
        return $services;
    }
    public static function payloadMaker(array $vrpVehicles, array $vrcVehicleTypes, array $services, array $relations): array
    {
        return [
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
    }
}
