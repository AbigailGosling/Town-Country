<?php

namespace App\Exports;

use App\Helpers\FuncHelper;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Cut;
use App\Models\Intake;
use App\Models\Nationality;
use App\Models\Pallet;
use App\Models\Product;
use App\Models\Species;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;

class ReturnedStockExport implements FromCollection
{
    private Collection $cuts;
    private Collection $species;
    private Collection $nationalities;
    private Collection $brands;
    private Collection $customers;
    private int $_dayRange;
    function __construct(int $dayRange = -70)
    {
        $this->_dayRange = min($dayRange,-1);
        $this->cuts = Cut::all()->keyBy('id');
        $this->species = Species::all()->keyBy('id');
        $this->nationalities = Nationality::all()->keyBy('id');
        $this->brands = Brand::all()->keyBy('id');
        $this->customers = Customer::all()->keyBy('id');
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
            $target_date = Carbon::now()->addDays($this->_dayRange)->startOfDay();
            $intakes = Intake::where("returned",1)->get();
            $r = null;
            foreach ($intakes as $p)
            {
                if ($p)$r=$this->process($p,$target_date);
                if ($r !== null)$m=array_merge($m,$r);
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
    private function process(Intake $intake,Carbon $target_date):array|null
    {

        if ($intake->date_received->timestamp < $target_date->timestamp) return null;

        $pallets = Pallet::where("intake_id",$intake->id)->get();
        if ($pallets->count() < 1) return null;

        /** @var Collection $products */
        $products = new Collection();

        /** @var Pallet $pallet */
        foreach ($pallets as $pallet){
            $products2 = Product::where("pallet_id",$pallet->id)->get();
            if ($products2->count() < 1) continue;
            $products = $products->merge($products2);
        }
        if ($products->count() < 1) return null;
        $output = [];
        /** @var Product $product */
        foreach ($products as $product)
        {
            $r = [];
            $cut = $this->cuts[$product->cut_id]??null;
            if ($cut == null) continue;

            $s = $this->species[$cut->species_id]??null;
            if ($s == null || $s->id == 14 || $s->id == 11 || $s->id == 12) continue;

            $customer = $this->customers[$intake->supplier_id]??null;
            if($customer == null) continue;

            $r['Customer']=$customer->businessname;
            if ($r['Customer'] == null) continue;

            $r['ID']=$customer->id;
            $r['Invoice']=$intake->delivery_note_number;
            $r['Return Date']=$intake->date_received->format("d/m/Y");
            $r['Return Intake']=$intake->id;
            $r['Pallet']=$product->pallet_id;
            $r['Nationality']=$this->nationalities[$product->nationality_id]->name;
            $r['Species']=$s->name;
            $r['Brand']=$this->brands[$product->brand_id]->name;
            $r['Product Name']=$cut->name;
            $r['Date From']=($product->range_extension != null || $product->range_extension != "")?"EXTENDED":$product->range_from;
            $r['Date To']=$product->range_to;
            $kg = (double)0;
            $count = 0;
            $thisweights = Weight::where("product_id",$product->id)->get();
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
            $r['Cases']=$count;
            if ($r['Cases'] < 1 && $product->unit != 'PPC') continue;
            if ($product->unit == 'PPC') $r['kg']=$thisweights->count();
            if ($r['kg'] < 1 || $r['Cases'] < 1) continue;
            $r['d']=$intake->date_received->timestamp;
            $output[]=$r;
        }
        if (empty($output)) return null;
        return $output;
    }
}
