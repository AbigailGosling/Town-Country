<?php

namespace App\Helpers;

use App\Models\ClientAddress;
use App\Models\Vehicle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
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
            return null;
        }

        $hits = data_get($response['data'], 'hits', []);
        if (!is_array($hits) || empty($hits)) {
            return null;
        }

        $first = $hits[0] ?? null;
        if (!is_array($first) || !isset($first['point']['lat'], $first['point']['lng'])) {
            return null;
        }

        return [
            'lat' => (float) $first['point']['lat'],
            'lon' => (float) $first['point']['lng'],
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

    public static function formatAddressForGeocoding(ClientAddress $address): string
    {
        return trim(implode(', ', array_filter([
            (string) ($address->address_1 ?? ''),
            (string) ($address->address_2 ?? ''),
            (string) ($address->address_3 ?? ''),
            (string) ($address->address_4 ?? ''),
            (string) ($address->postcode ?? ''),
            'United Kingdom',
        ])));
    }
    /** @param Collection<Vehicle> $vehicles */
    public static function generifyVehicleTypes(Collection $vehicles,int $planningPalletColumns): array
    {
        $output = [];
        foreach ($vehicles as $vehicle) {
            $tc_vehicle_type = $vehicle->vehicle_type_id;
            $payload = static::planningPayloadForVehicle($vehicle);
            if ($payload === null || $payload != 44000) continue;
            $capacity = static::planningCapacityForVehicle($vehicle, $planningPalletColumns);
            $ghtype_id = $tc_vehicle_type . '-'. $payload . '-' . $capacity;
            foreach ($output as &$existing) {
                if ($existing['type_id'] === $ghtype_id) {
                    //$existing['count']++;
                    continue 2;
                }
            }
            $output[] = [
                'type_id' => $ghtype_id,
                'profile' => config('services.graphhopper.profile', 'truck'),
                'capacity' => [$capacity, $payload],
                'count' => 1,
            ];
        }
        return $output;
    }
    public static function planningPayloadForVehicle(Vehicle $vehicle): ?int
    {
        $payload = str_replace("*", "", str_replace('t', '', strtolower($vehicle->payload)));
        return is_numeric($payload) ? (int) FuncHelper::floorDec(((float)$payload)*1000,0) : null;
    }
    public static function planningCapacityForVehicle(Vehicle $vehicle,int $planningPalletColumns): int
    {
        $maxRows = $vehicle->max_pallet_rows ?? 5;
        return max($planningPalletColumns, $maxRows * $planningPalletColumns);
    }

}
