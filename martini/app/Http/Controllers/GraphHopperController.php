<?php

namespace App\Http\Controllers;

use App\Helpers\GraphHopperHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GraphHopperController extends Controller
{
    private function normalizeRouteProfile(?string $rawProfile): string
    {
        $value = strtolower(trim((string) $rawProfile));

        if ($value === '') {
            return 'car';
        }

        if (str_contains($value, 'truck') || str_contains($value, 'hgv') || str_contains($value, 'lgv')) {
            return 'truck';
        }

        if (str_contains($value, 'bike') || str_contains($value, 'bicycle') || str_contains($value, 'cycle')) {
            return 'bike';
        }

        if (str_contains($value, 'foot') || str_contains($value, 'walk') || str_contains($value, 'pedestrian') || str_contains($value, 'hike')) {
            return 'foot';
        }

        if (str_contains($value, 'scooter')) {
            return 'scooter';
        }

        return in_array($value, ['car', 'truck', 'bike', 'foot', 'scooter', 'motorcycle'], true)
            ? $value
            : 'car';
    }

    public function route(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'points' => ['required', 'array', 'min:2'],
            'points.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'points.*.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'points.*.lon' => ['nullable', 'numeric', 'between:-180,180'],
            'profile' => ['nullable', 'string'],
            'locale' => ['nullable', 'string', 'max:10'],
            'instructions' => ['nullable', 'boolean'],
            'calc_points' => ['nullable', 'boolean'],
            'elevation' => ['nullable', 'boolean'],
            'points_encoded' => ['nullable', 'boolean'],
            'debug' => ['nullable', 'boolean'],
            'ch.disable' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['points'] as $index => $point) {
            $hasLng = array_key_exists('lng', $point) && $point['lng'] !== null;
            $hasLon = array_key_exists('lon', $point) && $point['lon'] !== null;

            if (!$hasLng && !$hasLon) {
                return response()->json([
                    'message' => "The points.$index field must include either lng or lon.",
                ], 422);
            }
        }

        $points = array_map(static function (array $point): string {
            $lon = $point['lng'] ?? $point['lon'];

            return $point['lat'] . ',' . $lon;
        }, $validated['points']);

        $query = [
            'point' => $points,
            'profile' => $this->normalizeRouteProfile($validated['profile'] ?? config('services.graphhopper.profile', 'car')),
            'locale' => $validated['locale'] ?? config('services.graphhopper.locale', 'en'),
            'instructions' => $validated['instructions'] ?? true,
            'calc_points' => $validated['calc_points'] ?? true,
            'elevation' => $validated['elevation'] ?? false,
            'points_encoded' => $validated['points_encoded'] ?? false,
            'debug' => $validated['debug'] ?? false,
            'ch.disable' => data_get($validated, 'ch.disable', false),
        ];

        $response = GraphHopperHelper::get('/route', $query);

        if (($response['status'] ?? 500) === 429) {
            return response()->json([
                'ok' => false,
                'error' => 'GraphHopper rate limit exceeded',
                'message' => data_get($response, 'data.message', 'GraphHopper returned HTTP 429'),
            ], 429);
        }

        return response()->json($response['data'], $response['status']);
    }

    public function geocode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'locale' => ['nullable', 'string', 'max:10'],
            'provider' => ['nullable', 'string', 'max:30'],
            'point' => ['nullable', 'array', 'size:2'],
            'point.0' => ['required_with:point', 'numeric', 'between:-90,90'],
            'point.1' => ['required_with:point', 'numeric', 'between:-180,180'],
            'country' => ['nullable', 'string', 'size:2'],
        ]);

        $query = [
            'q' => $validated['q'],
            'limit' => $validated['limit'] ?? 10,
            'locale' => $validated['locale'] ?? config('services.graphhopper.locale', 'en'),
        ];

        if (isset($validated['provider'])) {
            $query['provider'] = $validated['provider'];
        }

        if (isset($validated['point'])) {
            $query['point'] = $validated['point'][0] . ',' . $validated['point'][1];
        }

        if (isset($validated['country'])) {
            $query['country'] = strtoupper($validated['country']);
        }

        $response = GraphHopperHelper::get('/geocode', $query);

        return response()->json($response['data'], $response['status']);
    }

    public function health(): JsonResponse
    {
        $apiKey = GraphHopperHelper::apiKey();

        if ($apiKey === '') {
            return response()->json([
                'ok' => false,
                'error' => 'GraphHopper API key not configured',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'base_url' => GraphHopperHelper::baseUrl(),
            'profile' => config('services.graphhopper.profile', 'car'),
            'locale' => config('services.graphhopper.locale', 'en'),
        ]);
    }
}
