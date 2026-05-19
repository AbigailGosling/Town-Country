<?php

namespace App\Exports;

use App\Helpers\FuncHelper;
use App\Models\Brand;
use App\Models\Cut;
use App\Models\Intake;
use App\Models\Location;
use App\Models\Nationality;
use App\Models\Pallet;
use App\Models\PickerItem;
use App\Models\Product;
use App\Models\Site;
use App\Models\Species;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;

class ShortStockExport implements FromCollection
{
    private Collection $cuts;
    //private Collection $cutgroups;
    private Collection $species;
    private Collection $nationalities;
    private Collection $brands;
    private Collection $locations;
    private Collection $sites;
    private int $_dayRange;
    function __construct(int $dayRange = +9)
    {
        $this->_dayRange = max($dayRange,+1);
        $this->cuts = Cut::all()->keyBy('id');
        //$this->cutgroups = CutGroup::all()->keyBy('id');
        $this->species = Species::all()->keyBy('id');
        $this->nationalities = Nationality::all()->keyBy('id');
        $this->brands = Brand::all()->keyBy('id');
        $this->locations = Location::all()->keyBy('id');
        $this->sites = Site::all()->keyBy('id');
    }
    private Collection $_collection;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if (!isset($this->_collection))
        {
            $m=[];
            $lazysearch = DB::connection("tandc_live")->table("product")->selectRaw("DISTINCT `product`.`id`")->join("weights","product.id","=","weights.product_id")->join("pallet","pallet.id","=","product.pallet_id")->join("intake","intake.id","=","pallet.intake_id")->where([["intake.approved",true],["intake.deleted",0],["weights.status_id",0],["product.range_from","<>",""],["product.range_to","<>",""],["product.cooling_id",1]])->whereNotNull(["product.range_from","product.range_to"])->cursor();
            $target_date = Carbon::now()->addDays(+$this->_dayRange)->startOfDay();
            $products = Product::whereIn("id",$lazysearch->pluck("id"))->get();
            $r = null;
            foreach ($products as $p)
            {
                if ($p)$r=$this->process($p,$target_date);
                if ($r !== null)$m[]=$r;
            }
            $dateTS = array_column($m,"d");
            array_multisort($dateTS,SORT_ASC,$m);
            $this->_collection = new Collection($m);
        }
        return $this->_collection;
    }
    public function download() {
        return Excel::download($this,Carbon::now()->format('d-M-Y').".xlsx");
    }
    public function file() {
        $this->addHeader();
        $r = Carbon::now()->format('d-M-Y').".xlsx";
        Excel::store($this,$r,"public");
        return $r;
    }
    private function addHeader(){
        $c = $this->collection();
        $c->transform(function(array $item) {
            return Arr::except($item, 'd');
        });
        $temp = [];
        foreach ($c[0] as $key=>$value){
            $temp[$key] = $key;
        }
        $c->prepend($temp);
        $this->_collection = $c;
    }
    private function process(Product $product,Carbon $target_date)
    {
        $r = [];
        $now = Carbon::now()->timestamp;
        //if($product->cost == '0.00' || $product->cost == '') return null;
        $thisweights = Weight::where([["product_id",$product->id],["status_id",0]])->get();
        if ($thisweights->count() == 0)  return null;

        $range_from = ($product->range_from == null || $product->range_from == "")?Carbon::createFromFormat("d/m/Y",$product->range_to):Carbon::createFromFormat("d/m/Y",$product->range_from);
        $range_to = ($product->range_to == null || $product->range_to == "")?Carbon::createFromFormat("d/m/Y",$product->range_from):Carbon::createFromFormat("d/m/Y",$product->range_to);
        if ($product->range_extension != null || $product->range_extension != "") $range_from = $range_to = Carbon::createFromFormat("d/m/Y",$product->range_extension);
        $shortestDate = ($range_from->isBefore($range_to))?$range_from:$range_to;
        if ($shortestDate->isAfter($target_date)) return null;

        $cut = $this->cuts[$product->cut_id]??null;
        if ($cut == null) return null;
        $s = $this->species[$cut->species_id]??null;
        if ($s == null || $s->id == 14 || $s->id == 11 || $s->id == 12) return null;
        $pallet = Pallet::find($product->pallet_id);
        $r['Intake']=$pallet->intake_id;
        $r['Date']=Intake::find($pallet->intake_id)->date_received->format("d/m/Y");
        $r['Pallet']=$product->pallet_id;
        $loc = $this->locations[$pallet->storage_location]??null;
        $r['Location']=$this->sites[$loc?->site_id]->abbreviation??"";
        $kg = (double)0;
        $count = 0;
        foreach ($thisweights as $weight)
        {
            if($weight->weight_gross == $weight->weight_tear){
                $w = (double)$weight->weight_gross;
            }else{
                $w = (double)$weight->weight_gross - (double)$weight->weight_tear;
            }
            if ($w>=1)
            {
                $kg = (double)$kg + (double)$w;
            }
            $count++;
        }
        $r['kg']=FuncHelper::floorDec($kg,3);
        $r['Cases']=$count - PickerItem::where([["product_id",$product->id],["deleted",0],['status','0']])->get()->count();
        if ($r['Cases'] < 1 && $product->unit != 'PPC') return null;
        if ($product->unit == 'PPC') $r['kg']=$thisweights->count();
        if ($r['kg'] < 1 || $r['Cases'] < 1) return null;
        $r['Species']=$s->name;
        $r['Product Name']=$cut->name;//$this->cutgroups[(int)$cut->cutgroup_id]->name." ".
        $r['Nationality']=$this->nationalities[$product->nationality_id]->name;
        $r['Brand']=$this->brands[$product->brand_id]->name;
        $r['Date From']=($product->range_extension != null || $product->range_extension != "")?"EXTENDED":$product->range_from;
        $r['Date To']=$product->range_to;
        $r['Cost']="£".$product->cost;
        $r['QC HOLD']=($shortestDate->timestamp<$now||$pallet->qc_hold)?"QC HOLD":"";
        $r['d']=$shortestDate->timestamp;
        return $r;
    }
}
