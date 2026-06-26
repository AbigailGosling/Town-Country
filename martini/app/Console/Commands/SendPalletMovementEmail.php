<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\PalletMovementTracking;
use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
        $start = $now->copy()->subDay();
        $sites = Site::where([['pallet_movement_tracking_enabled', true], ['disabled', false]])->get();
        $output = "";
        foreach($sites as $site) {
            $locations = $site->locations()->where('disabled', false)->get()->pluck('id')->toArray();
            $movements = PalletMovementTracking::whereIn('from_location', $locations)->orWhereIn('to_location', $locations)->whereBetween('created_at', [$start, $now])->get();
            if ($movements->count() === 0) {
                continue;
            }
            $output .= "<h2>{$site->abbreviation} : {$site->name} Movements</h2>";
            $output .= $this->processMovements($movements,$locations);
        }
        SLabsEmailer::send_email(-1,"TEST",["abigail.gosling@tang.solutions"],"Pallet Movement Report",$output);
        return Command::SUCCESS;
    }
    public function processMovements(Collection $movements, array $locations) {
        $output = "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;'><thead><tr><th>Pallet ID</th><th>By</th><th>From Location</th><th>To Location</th><th>At</th></tr></thead><tbody>";
        foreach ($movements as $movement) {
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
        return "{$internalLocation->site->abbreviation}";
    }
}
