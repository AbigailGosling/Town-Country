<?php

namespace App\Http\Controllers;

use App\Helpers\InternalCache;
use App\Helpers\ProcessHelper;
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
            'transport_pallets'   => ['required', 'array', 'min:1'],
            'transport_pallets.*' => ['required', 'integer', 'exists:tandc_live.transport_pallets,id'],
            'vehicle'            => ['required', 'integer', 'exists:tandc_live.vehicle,id'],
        ]);

        $palletIds = $validated['transport_pallets'];
        $vehicleId = (int) $validated['vehicle'];

        $cacheKey = 'pods_send_' . Str::uuid();
        InternalCache::put($cacheKey, [
            'transport_pallet_ids' => $palletIds,
            'vehicle_id'          => $vehicleId,
        ], 300);
        ProcessHelper::runInBackground('pods:send '.$cacheKey);

        return response()->json([
            'queued'              => true,
            'command'             => 'pods:send',
            'process_id'          => "N/A",
            'cache_key'           => $cacheKey,
            'transport_pallet_ids' => $palletIds,
            'vehicle_id'          => $vehicleId,
        ], 202);
    }
    public function receive(Request $request)
    {
        $input = json_decode($request->all()[0], true);
        if (!array_key_exists('Key', $input)) {
            return response()->json(['error' => 'Missing API Key'], 400);
        }
        if ($input['Key'] !== env('POD_RECEIVE_KEY')) {
            return response()->json(['error' => 'Invalid API Key'], 401);
        }
        if (!array_key_exists('Data', $input)) {
            return response()->json(['error' => 'Missing Data parameter'], 400);
        }
        $cacheKey = 'pods_receive_' . Str::uuid();
        InternalCache::put($cacheKey, [
            'request' => $input['Data'],
        ], Carbon::now()->addDays(100));
        ProcessHelper::runInBackground('pods:receive '.$cacheKey);

        return response()->json([
            //'queued'              => true,
            //'command'             => 'pods:receive',
            //'process_id'          => $process->getPid(),
            //'cache_key'           => $cacheKey,
            //'request'             => $input['Data'],
        ], 202);
    }
}
