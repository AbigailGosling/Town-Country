<?php

namespace App\Http\Controllers;

use App\Helpers\InternalCache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PodDispatchController extends Controller
{
    /**
     * Accept outgoing pallet IDs + vehicle ID, then run pods:send in a background process.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'outgoing_pallets'   => ['required', 'array', 'min:1'],
            'outgoing_pallets.*' => ['required', 'integer', 'exists:tandc_live.outgoing_pallet,id'],
            'vehicle'            => ['required', 'integer', 'exists:tandc_live.vehicle,id'],
        ]);

        $palletIds = $validated['outgoing_pallets'];
        $vehicleId = (int) $validated['vehicle'];

        $cacheKey = 'pods_send_' . Str::uuid();
        InternalCache::put($cacheKey, [
            'outgoing_pallet_ids' => $palletIds,
            'vehicle_id'          => $vehicleId,
        ], 300);

        pclose(popen('php '.base_path('artisan').' pods:send '.$cacheKey.' >NUL 2>NUL', 'r'));

        return response()->json([
            'queued'              => true,
            'command'             => 'pods:send',
            'process_id'          => "N/A",
            'cache_key'           => $cacheKey,
            'outgoing_pallet_ids' => $palletIds,
            'vehicle_id'          => $vehicleId,
        ], 202);
    }
    public function receive(Request $request)
    {
        if (!$request->has('Key')) {
            return response()->json(['error' => 'Missing API Key'], 400);
        }
        if ($request->input('Key') !== env('POD_RECEIVE_KEY')) {
            return response()->json(['error' => 'Invalid API Key'], 401);
        }
        if (!$request->has('Data')) {
            return response()->json(['error' => 'Missing Data parameter'], 400);
        }
        $cacheKey = 'pods_receive_' . Str::uuid();
        InternalCache::put($cacheKey, [
            'request' => $request->all(),
        ], Carbon::now()->addDays(100));
        Log::info('Received POD data', ['cmd' => 'php '.base_path('artisan').' pods:receive '.$cacheKey]);
        //pclose(popen('php '.base_path('artisan').' pods:receive '.$cacheKey.' >NUL 2>NUL', 'r'));

        return response()->json([
            //'queued'              => true,
            //'command'             => 'pods:receive',
            //'process_id'          => $process->getPid(),
            //'cache_key'           => $cacheKey,
            //'request'             => $request->all(),
        ], 202);
    }
}
