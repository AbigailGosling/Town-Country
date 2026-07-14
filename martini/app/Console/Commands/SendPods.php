<?php

namespace App\Console\Commands;

use App\Helpers\InternalCache;
use App\Helpers\PodHelper;
use App\Models\TransportPallet;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pods:send {cache_key : InternalCache key for the payload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send PODs for outgoing pallets and a vehicle';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cacheKey = (string) $this->argument('cache_key');
        $data = InternalCache::get($cacheKey);
        InternalCache::forget($cacheKey);
        if (!is_array($data)) {
            $this->error('Payload not found in cache or has expired.');
            Log::error('pods:send failed - invalid payload', ['cache_key' => $cacheKey]);
            return self::FAILURE;
        }

        $outgoingPalletIds = $data['transport_pallet_ids'] ?? [];
        $vehicleId = $data['vehicle_id'] ?? null;

        if (!is_array($outgoingPalletIds) || $outgoingPalletIds === [] || !is_numeric($vehicleId)) {
            $this->error('Payload must contain transport_pallet_ids and vehicle_id.');
            Log::error('pods:send failed - invalid payload structure', ['cache_key' => $cacheKey, 'payload' => $data]);
            return self::FAILURE;
        }

        $outgoingPallets = TransportPallet::whereIn('id', $outgoingPalletIds)->get();
        $vehicle = Vehicle::find((int) $vehicleId);

        if ($outgoingPallets->isEmpty()) {
            $this->error('No outgoing pallets found for provided ids.');
            Log::error('pods:send failed - no outgoing pallets found', ['cache_key' => $cacheKey, 'transport_pallet_ids' => $outgoingPalletIds]);
            return self::FAILURE;
        }

        if ($vehicle === null) {
            $this->error('Vehicle not found.');
            Log::error('pods:send failed - vehicle not found', ['cache_key' => $cacheKey, 'vehicle_id' => $vehicleId]);
            return self::FAILURE;
        }

        try {
            PodHelper::sendPods($outgoingPallets, $vehicle);
            $this->info('POD send completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('pods:send failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('POD send failed. Check logs for details.');

            return self::FAILURE;
        }
    }
}
