<?php

namespace App\Console\Commands;

use App\Helpers\FuncHelper;
use App\Helpers\InternalCache;
use App\Models\Intake;
use App\Models\Location;
use App\Models\OutgoingPallet;
use App\Models\OutgoingPalletPickWeight;
use App\Models\PickerSheet;
use App\Models\PickWeightOut;
use App\Models\Vehicle;
use App\Models\VehicleOutgoingPalletAllocation;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

        $rejected_weight_ids = [];
        $rejected_reason = [];
        //Process complete failure - all items rejected
        if ($payload["PARENT_TASK"]["UserData"]["STATUS"] == "DELIVERY_REJECTED") {
            foreach ($payload["SUB_TASKS"] as $line) {
                $thisWeights = explode(',', $line["UserData"]["PRODUCT_WEIGHT_INFO"]);
                foreach ($thisWeights as $weightInfo) {
                    $weightId = (int) explode('|', $weightInfo)[0];
                    $rejected_weight_ids[] = $weightId;
                    $rejected_reason[$weightId] = $payload["PARENT_TASK"]["UserData"]["ALL_FAIL_REASON"] . ' - ' . $payload["PARENT_TASK"]["UserData"]["ALL_FAIL_NOTES"];
                }
            }
        }
        //Partial failure - some items rejected, some accepted
        foreach ($payload["SUB_TASKS"] as $line) {
            if (isset($line["UserData"]["STATUS"]) && $line["UserData"]["STATUS"] == "REJECTED_ITEMS") {
                $thisRejctions = explode('|', $line["UserData"]["REJECTED_PRODUCTS"]);
                foreach ($thisRejctions as $rej) {
                    $rejected_weight_ids[] = (int) $rej;
                    $rejected_reason[$rej] = $line["UserData"]["ITEM_FAIL_REASON"] . ' - ' . $line["UserData"]["ITEM_FAIL_NOTES"];
                }
            }
        }
        $rejected_weights = Weight::whereIn('id', $rejected_weight_ids)->get();
        if (count($rejected_weights) === 0) {
            //No rejected weights, nothing to process
            return Command::SUCCESS;
        }

        //Store rejected for return intake creation, but also organise by nationality/brand/cut for easier processing when creating new pallets/products/weights for the return intake
        $organisedByNatBrandCut = [];
        foreach ($rejected_weights as $weight) {
            $natBrandCut = $weight->product->nationality_id . '-' . $weight->product->brand_id . '-' . $weight->product->cut_id;
            if (!isset($organisedByNatBrandCut[$natBrandCut])) {
                $organisedByNatBrandCut[$natBrandCut] = [];
            }
            $organisedByNatBrandCut[$natBrandCut][] = $weight;
        }
        $oldPickID = $payload["PARENT_TASK"]["UserData"]["TC_DNOTE"];
        $oldPick = PickerSheet::where('id', $oldPickID)->first();

        $returnIntake = new Intake();
        $returnIntake->returned = 1;
        $returnIntake->delivery_note_number = $oldPickID;
        $returnIntake->supplier_id = $oldPick->customer_id;
        $returnIntake->security_id = 3; // TODO: Determine appropriate security_id

        //find the original vehicle by looking at the original pick weights and their associated outgoing pallets and vehicle allocations
        if (array_key_exists('TC_VEHICLE_ID', $payload["PARENT_TASK"]["UserData"])) {
            $vehicle = Vehicle::find($payload["PARENT_TASK"]["UserData"]["TC_VEHICLE_ID"]);
        } else {
            $pwos = PickWeightOut::where('pickersheet_id', $oldPickID)->get();
            foreach ($pwos as $pwo) {
                $pwoWeights = explode(',', $pwo->weight_ids);
                if (count(FuncHelper::custom_intersect($rejected_weight_ids, $pwoWeights)) > 0) {
                    $oppw = OutgoingPalletPickWeight::where('pickWeightOut_id', $pwo->id)->first();
                    $vopa = VehicleOutgoingPalletAllocation::where("outgoing_pallet_id", $oppw->outgoing_pallet_id)->first();
                    $vehicle = Vehicle::find($vopa->vehicle_id);
                    break;
                }
            }
        }
        $returnIntake->vehicle_reg = $vehicle->reg ?? 'UNKNOWN';
        $returnIntake->user_id = $vehicle->driver ?? 'UNKNOWN';
        $returnIntake->date_received = Carbon::now()->format('d/m/Y');
        $returnIntake->notes = 'Auto-created return intake for rejected items. Rejection Reason(s):' . PHP_EOL . implode(PHP_EOL, array_unique($rejected_reason));
        $returnIntake->save();
        $this->info("Created return intake with ID: " . $returnIntake->id);
        Log::info("Created return intake with ID: " . $returnIntake->id);

        $site_id = null;
        foreach ($organisedByNatBrandCut as $natBrandCut => $weights) {
            $oldPallet = $weights[0]->product->pallet;
            if (!$site_id) {

                $site_id = Location::find($oldPallet->storage_location)->site_id;
            }
            $newPallet = $oldPallet->replicate();
            $newPallet->intake_id = $returnIntake->id;
            $newPallet->comments = $rejected_reason[$weights[0]->id] . PHP_EOL . $oldPallet->comments;
            $newPallet->save();

            $oldProduct = $weights[0]->product;
            $newProduct = $oldProduct->replicate();
            $newProduct->pallet_id = $newPallet->id;
            $oldWeightNote = $oldProduct->weightnote ?? '';
            $newProduct->weightnote = $rejected_reason[$weights[0]->id] . PHP_EOL . $oldWeightNote;
            $newProduct->original_product_id = $oldProduct->id;
            $newProduct->original_pallet_id = $oldProduct->pallet_id;
            $newProduct->original_intake_id = $oldPallet->intake_id;
            $newProduct->save();

            foreach ($weights as $oldWeight) {
                $newWeight = $oldWeight->replicate();
                $newWeight->product_id = $newProduct->id;
                $newWeight->status_id = 0;
                $newWeight->save();
            }
        }
        $returnIntake->site_id = $site_id ?? 1;
        $returnIntake->save();
        InternalCache::forget($cacheKey);
        return Command::SUCCESS;
    }
}
