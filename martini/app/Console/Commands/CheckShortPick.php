<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Cut;
use App\Models\Nationality;
use App\Models\PickerSheet;
use App\Models\Product;
use App\Models\User;
use App\Models\Weight;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use InternalScripts\SLabsEmailer;
use InternalScripts\SLabsEmailerType;

class CheckShortPick extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:checkshortpick {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if pick was short picked and email someone about it.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pick = PickerSheet::with(["pickWeightOut","pickerItems"])->find($this->argument('id'));
        $weightIDs = [];
        foreach ($pick->pickWeightOut as $palletOut){
            $weightIDs[] = $palletOut->weight_ids;
        }
        $weightIDs = implode(",",$weightIDs);
        $weightIDs = explode(",",$weightIDs);
        $pickItems = $pick->pickerItems->where("deleted",0);
        if ($pickItems->count() > count($weightIDs))
        {
            $productIDs = [];

            foreach ($pickItems as $pickItem)
            {
                if (!array_key_exists($pickItem->product_id,$productIDs))$productIDs[$pickItem->product_id]=0;
                $productIDs[$pickItem->product_id]++;
            }
            $targetByAlias = [];
            $actualByAlias = [];
            $prodByAlias = [];
            foreach ($productIDs as $productID=>$count)
            {
                $weights = Weight::where("product_id",$productID)->whereIn("id",$weightIDs)->get();
                if ($count > $weights->count())
                {
                    $prod = Product::find($productID);
                    $alias = $prod->cut_id . $prod->brand_id . $prod->nationality_id;
                    if (!array_key_exists($alias,$targetByAlias))
                    {
                        $targetByAlias[$alias]=0;
                        $actualByAlias[$alias]=0;
                        $prodByAlias[$alias] =$prod;
                    }
                    $targetByAlias[$alias]+=$count;
                    $actualByAlias[$alias]+=$weights->count();
                }
            }
            $missing = "<table><thead><th>Description</th><th>Target Count</th><th>Actual Count</th></thead><tbody>";
            foreach ($prodByAlias as $alias=>$prod)
            {
                $missing .= "<tr>";
                $missing .= "<td>".Nationality::find($prod->nationality_id)->name." : ".Brand::find($prod->brand_id)->name." : ".Cut::find($prod->cut_id)->name."</td>";
                $missing .= "<td>".$targetByAlias[$alias]."</td>";
                $missing .= "<td>".$actualByAlias[$alias]."</td>";
                $missing .= "</tr>";
            }
            $missing .= "</tbody></table>";
            $cust = Customer::find($pick->customer_id);
            $u = [User::find($cust->default_salesman_id)->actual_email];
            SLabsEmailer::send_email($pick->customer_id,SLabsEmailerType::ShortPick,$u,"Sale ".$pick->id." Short Picked","<html>Customer: ".$cust->businessname."<br/>Sale ".$pick->id." has completed pick, however ".$pick->pickerItems->where("deleted",0)->count() - count($weightIDs)." items were not picked.<br/><br/>".$missing."</html>",'','',$pick->id,true);
        }
        return Command::SUCCESS;
    }
}
