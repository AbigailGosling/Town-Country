<?php

namespace App\Console\Commands;

use App\Helpers\InternalCache;
use App\Helpers\PodHelper;

use Illuminate\Console\Command;

class ReceivePod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pods:receive {cache_key : InternalCache key for the payload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process received POD data, triggered by PodDispatchController, create return intakes if needed, and log the data for now';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cacheKey = $this->argument('cache_key');
        $payload = InternalCache::get($cacheKey)['request'];

        if (!$payload) {
            $this->error("No payload found for cache key: $cacheKey");
            return Command::FAILURE;
        }
        $status = PodHelper::receivePod($payload);
        InternalCache::forget($cacheKey);
        return $status ? Command::SUCCESS : Command::FAILURE;
    }
}
