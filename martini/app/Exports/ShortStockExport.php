<?php

namespace App\Exports;

use App\Models\Brand;
use App\Models\Cut;
use App\Models\CutGroup;
use App\Models\Nationality;
use App\Models\Pallet;
use App\Models\PickerItem;
use App\Models\Product;
use App\Models\Species;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ShortStockExport implements FromCollection
{
    private Collection $cuts;
    private Collection $cutgroups;
    private Collection $species;
    private Collection $nationalities;
    private Collection $brands;

    function __construct()
    {
        $this->cuts = Cut::all();
        $this->cutgroups = CutGroup::all();
        $this->species = Species::all();
        $this->nationalities = Nationality::all();
        $this->brands = Brand::all();
    }
    private Collection $t;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if (!isset($this->t))
        {
            $m=[];
            $target_date = Carbon::now()->setDay(+10)->startOfDay()->format("d/m/Y");
            $lazysearch = DB::connection("tandc_live")->table("product")->selectRaw("DISTINCT `product`.`id`")->join("weights","product.id","=","weights.product_id")->join("pallet","pallet.id","=","product.pallet_id")->join("intake","intake.id","=","pallet.intake_id")->where([["intake.approved",true],["intake.deleted",0],["weights.status_id",0],["product.range_from","<>",""],["product.range_to","<>",""],["product.cooling_id",1]])->whereNotNull(["product.range_from","product.range_to"])->cursor();
            $target_date = Carbon::now()->addDays(+10)->startOfDay();
            foreach ($lazysearch as $lw)
            {
                $p=Product::find($lw->id);
                if ($p)$r=$this->process($p,$target_date);
                if ($r !== null)$m[]=$r;
            }
            $dateTS = array_column($m,"d");
            array_multisort($dateTS,SORT_ASC,$m);
            $this->t = new Collection($m);
        }
        return $this->t;
    }
    public function download() {
        return Excel::download($this,Carbon::now().".xlsx");
    }
    public function file() {
        $this->addHeader();
        $r = Carbon::now()->format('d-M-Y').".xlsx";//Storage::path("app/".Carbon::now().".xlsx");
        Log::debug(Excel::store($this,$r,"public"));
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
        $this->t = $c;
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
        if ($range_from->isAfter($target_date) && $range_to->isAfter($target_date)) return null;
        $shortestDate = ($range_from->isBefore($range_to))?$range_from:$range_to;

        $cut =$this->cuts->firstWhere("id",$product->cut_id);
        if ($cut == null) return null;
        $s = $this->species->firstWhere("id",$cut->species_id);
        if ($s == null || $s->id == 14 || $s->id == 11 || $s->id == 12)return null;
        $pallet = Pallet::find($product->pallet_id);
        $r['Intake']=$pallet->intake_id;
        $r['Pallet']=$product->pallet_id;
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
                $count++;
            }
        }
        $r['kg']=$this->floorDec($kg,3);
        $r['Cases']=$count - PickerItem::where([["product_id",$product->id],["deleted",false],['status',0]])->count();
        if ($r['Cases'] < 1 && $product->unit != 'PPC') return null;
        if ($product->unit == 'PPC') $r['kg']=$r['Cases'];
        if ($r['kg'] < 1 || $r['Cases'] < 1) return null;
        $r['Species']=$s->name;
        $r['Product Name']=$this->cutgroups->firstWhere("id",$cut->cutgroup_id)->name." ".$cut->name;
        $r['Nationality']=$this->nationalities->firstWhere("id",$product->nationality_id)->name;
        $r['Brand']=$this->brands->firstWhere("id",$product->brand_id)->name;
        $r['Date From']=($product->range_extension != null || $product->range_extension != "")?"EXTENDED":$product->range_from;
        $r['Date To']=$product->range_to;
        $r['Cost']="£".$product->cost;
        $r['QC HOLD']=($shortestDate->timestamp<$now)?"QC HOLD":"";
        $r['d']=$shortestDate->timestamp;
        return $r;
    }
    private function floorDec($val, $precision = 2) {
		if ($precision < 0) { $precision = 0; }
		$numPointPosition = intval(strpos($val, '.'));
		if ($numPointPosition === 0) { //$val is an integer
			return $val;
		}
		return floatval(substr($val, 0, $numPointPosition + $precision + 1));
	}
}
