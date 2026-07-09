<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\PalletMovementTracking;
use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InternalScripts\SLabsEmailer;

class SendPalletMovementEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:pallet_movement_email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pallet movement email';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = now();
        $start = $now->copy()->subDays(1);
        $sites = Site::where([['pallet_movement_tracking_enabled', true], ['disabled', false]])->get();
        $output = "";
        foreach($sites as $site) {
            $locations = $site->locations()->where('disabled', false)->get()->pluck('id')->toArray();
            $movements = PalletMovementTracking::whereBetween('created_at', [$start, $now])->where(function($query) use ($locations)
            {
                $query->whereIn('from_location', $locations)->orWhereIn('to_location', $locations);
            })->get();
            if ($movements->count() === 0) {
                continue;
            }
            $output .= "<h2>{$site->abbreviation} : {$site->name} Movements</h2>";
            $output .= $this->processMovements($movements,$locations);
        }
        $u = [
            "gemma@townandcountrymeats.co.uk",
            "office@townandcountrymeats.co.uk",
            "ross.whetton@townandcountrymeats.co.uk",
            "abigail.gosling@tang.solutions"
            ];
        SLabsEmailer::send_email(-1,"ColdStoreMovements",$u,"Pallet Movement Report",$output);
        return Command::SUCCESS;
    }
    public function processMovements(Collection $movements, array $locations) {
        $output = "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;'><thead><tr><th>Pallet ID</th><th>By</th><th>From Location</th><th>To Location</th><th>At</th></tr></thead><tbody>";
        foreach ($movements as $movement) {
            if ($movement->from_location == $movement->to_location) continue;
            $name = $movement->createdBy->name ?? 'System';
            $output .= "<tr><td>{$movement->pallet->id}</td>
            <td>{$name}</td>
            <td>{$this->parseLocationName($movement, $locations, true)}</td>
            <td>{$this->parseLocationName($movement, $locations, false)}</td>
            <td>{$movement->created_at}</td></tr>";
        }
        $output .= "</tbody></table>";
        return $output;
    }
    public function parseLocationName(PalletMovementTracking $pmt, array $locations, $from = true) {
        $interestedID = $from ? $pmt->from_location : $pmt->to_location;
        if ($interestedID === -2) {
            return "Before Tracking";
        }
        if ($interestedID === -1) {
            return "New Pallet";
        }
        $internalLocation = $from ? $pmt->fromLocation : $pmt->toLocation;
        if (!in_array($interestedID, $locations)) {
            return "{$internalLocation->site->abbreviation} : {$internalLocation->name}";
        }
        return "{$internalLocation->name}";
    }
}
