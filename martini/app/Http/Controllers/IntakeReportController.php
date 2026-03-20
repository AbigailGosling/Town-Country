<?php

namespace App\Http\Controllers;

use App\Helpers\ReportHelper;
use App\Models\Brand;
use App\Models\CreditNoteItem;
use App\Models\Customer;
use App\Models\Cut;
use App\Models\Intake;
use App\Models\InvoicePayment;
use App\Models\Nationality;
use App\Models\Pallet;
use App\Models\PickWeightOut;
use App\Models\PickerItem;
use App\Models\PickerSheet;
use App\Models\Product;
use App\Models\Species;
use App\Models\Supplier;
use App\Models\Temperature;
use App\Models\User;
use App\Models\Weight;
use Illuminate\Database\Eloquent\Collection;
use stdClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class IntakeReportController extends Controller
{
    public function show(){
        if (request()->has("intake_id") == false)
        {
            return view("reports.intake");
        }
        else
        {
            $search_intake_id = $intake_id = request()->input("intake_id");
            do
            {
                $intake = Intake::find($intake_id);
                if ($intake == null)
                {
                    return Redirect::back()->withErrors("Intake $intake_id does not Exist");
                }
                $pallets = Pallet::where("intake_id",$intake->id)->get();
                if ($pallets->count() < 1)
                {
                    return Redirect::back()->withErrors("Intake $intake_id has no pallets");
                }
                $products = Product::whereIn("pallet_id",$pallets->pluck("id")->toArray())->get();
                if ($pallets->count() < 1)
                {
                    return Redirect::back()->withErrors("Intake $intake_id has no products");
                }
                if (!($products->first()->original_intake_id == null || $products->first()->original_intake_id == ""))
                    $intake_id = $products->first()->original_intake_id;
            }
            while (!($products->first()->original_intake_id == null || $products->first()->original_intake_id == ""));

            $weights = Weight::whereIn("product_id",$products->pluck("id")->toArray())->get();
            $brands = Brand::whereIn("id",$products->pluck("brand_id")->toArray())->get();
            $nationalities = Nationality::whereIn("id",$products->pluck("nationality_id")->toArray())->get();
            $supplier = Supplier::find($intake->supplier_id);
            $supCust = Customer::find($intake->supplier_id);
            $pickItems = PickerItem::whereIn("product_id",$products->pluck("id")->toArray())->groupBy(["pickersheet_id","product_id"])->get();
            $sales = PickerSheet::whereIn("id",$pickItems->pluck("pickersheet_id")->toArray())->get();
            $credits = InvoicePayment::where("payment_method","=","CREDIT_NOTE")->whereIn("invoice_id",$pickItems->pluck("pickersheet_id")->toArray())->get();
            $creditNotes = CreditNoteItem::whereIn("payment_id",$credits->pluck("id")->toArray())->get();
            $returnProducts = Product::where("original_intake_id",$intake_id)->orWhereIn("original_pallet_id",$pallets->pluck("id")->toArray())->orWhereIn("id",$creditNotes->pluck("product_id")->toArray())->get();
            $returnWeights = Weight::whereIn("product_id",$returnProducts->pluck("id")->toArray())->get();
            $resalePickItems = PickerItem::whereIn("product_id",$returnProducts->pluck("id")->toArray())->groupBy(["pickersheet_id","product_id"])->get();
            $resales = PickerSheet::whereIn("id",$resalePickItems->pluck("pickersheet_id")->toArray())->get();
            $cuts = Cut::whereIn("id",$products->pluck("cut_id")->toArray())->get();
            $returnCuts = Cut::whereIn("id",$returnProducts->pluck("cut_id")->toArray())->get();

            $summary = new Collection();
            foreach ($cuts as $cut)
            {
                $out = new stdClass();
                $out->name = Species::find($cut->species_id)->name ." ".$cut->name;
                $productsForCut = $products->where("cut_id",$cut->id);
                $weightsForCut = $weights->whereIn("product_id",$productsForCut->pluck("id")->toArray());
                $out->qty = count($weightsForCut);
                $out->kg =$weightsForCut->sum("weight_tear");
                $out->cost = $productsForCut->first()->cost;
                $out->actCost = ($productsForCut->first()->price)?$productsForCut->first()->price:$productsForCut->first()->cost;
                $out->subTotal = ($productsForCut->first()->unit!="PPC")? $this->quickFloatMulti($out->cost , $out->kg) : $this->quickFloatMulti($out->cost , $out->qty);
                $out->actSubTotal = ($productsForCut->first()->unit!="PPC")? $this->quickFloatMulti($out->actCost , $out->kg) : $this->quickFloatMulti($out->actCost , $out->qty);
                $summary->add($out);
            }
            $saleInfo =new Collection();
            $creditInfo =new Collection();
            foreach ($sales as $sale)
            {
                $outPallets = PickWeightOut::where("pickersheet_id",$sale->id)->get();
                $weightIDsForSale = array();
                $internalCount = 0;
                foreach ($outPallets as $outPallet){
                    foreach (explode(",",$outPallet->weight_ids) as $weightID)
                    {
                        $weight = $weights->find($weightID);
                        if (!$weight) continue;
                        $product = $products->find($weight->product_id);
                        if (!isset($weightIDsForSale[$product->id]))$weightIDsForSale[$product->id] = array();
                        $weightIDsForSale[$product->id][] = $weightID;
                        $internalCount++;
                    }
                }
                if ($internalCount==0)continue;
                $customer = ($sale->is_return_to_supplier == false)?Customer::find($sale->customer_id):Supplier::find($sale->customer_id);
                $user = User::find(($sale->is_return_to_supplier == false)?$customer->default_salesman_id:$sale->user_from_id);
                $salePickItems = $pickItems->whereIn("pickersheet_id",$sale->id);
                foreach ($salePickItems as $pickItem)
                {
                    if (!array_key_exists($pickItem->product_id,$weightIDsForSale)){
                        continue;
                    }
                    $out = new stdClass();
                    $product = $products->find($pickItem->product_id);
                    $cut = $cuts->find($product->cut_id);
                    $out->salesperson = $user->name;
                    $out->date = $sale->date_completed;
                    $out->invoice_id = $sale->id;
                    $out->customer = ($sale->is_return_to_supplier == false)?$customer->businessname:$customer->name;
                    $out->pallet_id = $product->pallet_id;
                    $out->product_name = Species::find($cut->species_id)->name ." ".$cut->name;
                    $out->nationality_name = $nationalities->find($product->nationality_id)->name;
                    $out->cooling_name = Temperature::find($product->cooling_id)->name;
                    $out->brand_name = $brands->find($product->brand_id)->name;
                    $out->supplier_name = ($supplier)?$supplier->name:$supCust->businessname;
                    $out->qty = count($weightIDsForSale[$product->id]);
                    switch ($product->unit)
                    {
                        case "PPC":
                            $out->unit = "PPC";
                            break;
                        case "P":
                            $out->unit = "G/T";
                            break;
                        case "C":
                            $out->unit = "Cases";
                            break;
                    }
                    $out->kg = $weights->whereIn("id",$weightIDsForSale[$product->id])->sum("weight_tear");
                    if ($product->unit != "PPC") {
                        $out->cost = $this->quickFloatMulti($product->cost, $out->kg);
                        $out->actCost = $this->quickFloatMulti($product->price, $out->kg);
                        $out->sell = $this->quickFloatMulti($pickItem->price, $out->kg);
                    } else {
                        $out->cost = $this->quickFloatMulti($product->cost, $out->qty);
                        $out->actCost = $this->quickFloatMulti($product->price, $out->qty);
                        $out->sell = $this->quickFloatMulti($pickItem->price, $out->qty);
                    }
                    if ($out->actCost == 0) $out->actCost =$out->cost;
                    $out->profit = $out->sell-$out->cost;
                    $out->actProfit = $out->sell-$out->actCost;
                    $saleInfo->add($out);
                }
                $paymentsForSale = $credits->where("invoice_id",$sale->id)->pluck("id")->toArray();
                foreach ($creditNotes->whereIn("payment_id",$paymentsForSale) as $creditNote)
                {
                    if ($creditNote->product_id == null || $creditNote->product_id == 0) continue;
                    $payment = $credits->find($creditNote->payment_id);
                    $newproduct = $returnProducts->find($creditNote->product_id);
                    $orgproduct = $this->guessTheOriginal($newproduct);
                    $out = new stdClass();
                    $cut = $returnCuts->find($newproduct->cut_id);
                    $out->salesperson = $user->name;
                    $out->date = $payment->created_at;
                    $out->invoice_id = $sale->id;
                    $out->customer = $customer->businessname;
                    $out->new_intake_id = Pallet::find($newproduct->pallet_id)->intake_id;
                    $out->pallet_id = $newproduct->pallet_id;
                    $out->product_name = Species::find($cut->species_id)->name ." ".$cut->name;
                    $out->nationality_name = $nationalities->find($newproduct->nationality_id)?->name;
                    $out->cooling_name = Temperature::find($newproduct->cooling_id)?->temperature;
                    $out->brand_name = $brands->find($newproduct->brand_id)?->name;
                    $out->supplier_name = ($supplier)?$supplier->name:$supCust->businessname;
                    $out->qty = $creditNote->quantity;
                    switch ($orgproduct->unit)
                    {
                        case "PPC":
                            $out->unit = "PPC";
                            break;
                        case "P":
                            $out->unit = "G/T";
                            break;
                        case "C":
                            $out->unit = "Cases";
                            break;
                    }
                    $out->kg = Weight::where("product_id",$newproduct->id)->get()->sum("weight_tear");
                    $pickItem = $pickItems->where("product_id",$orgproduct->id)->first();
                    $pickItemPrice = ($pickItem)? $pickItem->price: $newproduct->cost;
                    if ($orgproduct->unit != "PPC") {
                        $out->cost = $out->actCost = $this->quickFloatMulti($out->kg, $creditNote->price);
                        $out->sell = $this->quickFloatMulti($pickItemPrice, $out->kg);
                    } else {
                        $out->cost = $out->actCost = $this->quickFloatMulti($out->quantity, $creditNote->price);
                        $out->sell = $this->quickFloatMulti($pickItemPrice, $out->qty);
                    }
                    $out->profit = $out->sell-$out->cost;
                    $out->actProfit = $out->sell-$out->actCost;
                    $creditInfo->add($out);
                }

            }
            $resaleInfo = new Collection();
            foreach($resales as $resale)
            {
                $outPallets = PickWeightOut::where("pickersheet_id",$resale->id)->get();
                $weightIDsForSale = array();
                $internalCount = 0;
                foreach ($outPallets as $outPallet){
                    foreach (explode(",",$outPallet->weight_ids) as $weightID)
                    {
                        $weight = $returnWeights->find($weightID);
                        if (!$weight) continue;
                        $product = $returnProducts->find($weight->product_id);
                        if (!isset($weightIDsForSale[$product->id]))$weightIDsForSale[$product->id] = array();
                        $weightIDsForSale[$product->id][] = $weightID;
                        $internalCount++;
                    }
                }
                if ($internalCount==0)continue;
                $customer = Customer::find($resale->customer_id);
                $user = User::find($customer->default_salesman_id);
                $salePickItems = $resalePickItems->whereIn("pickersheet_id",$resale->id);
                foreach ($salePickItems as $pickItem)
                {
                    $out = new stdClass();
                    $product = $returnProducts->find($pickItem->product_id);
                    $cut = $returnCuts->find($product->cut_id);
                    $out->salesperson = $user->name;
                    $out->date = $resale->date_completed;
                    $out->invoice_id = $resale->id;
                    $out->customer = $customer->businessname;
                    $out->pallet_id = $product->pallet_id;
                    $out->product_name = Species::find($cut->species_id)->name ." ".$cut->name;
                    $out->nationality_name = $nationalities->find($product->nationality_id)?->name;
                    $out->cooling_name = Temperature::find($product->cooling_id)->name;
                    $out->brand_name = $brands->find($product->brand_id)?->name;
                    $out->supplier_name = ($supplier)?$supplier->name:$supCust->businessname;
                    $out->qty = count($weightIDsForSale[$product->id]);
                    switch ($product->unit)
                    {
                        case "PPC":
                            $out->unit = "PPC";
                            break;
                        case "P":
                            $out->unit = "G/T";
                            break;
                        case "C":
                            $out->unit = "Cases";
                            break;
                    }
                    $out->kg = $returnWeights->whereIn("id",$weightIDsForSale[$product->id])->sum("weight_tear");
                    if ($product->unit != "PPC") {
                        $out->cost = $this->quickFloatMulti($product->cost, $out->kg);
                        $out->actCost = $this->quickFloatMulti($product->price, $out->kg);
                        $out->sell = $this->quickFloatMulti($pickItem->price, $out->kg);
                    } else {
                        $out->cost = $this->quickFloatMulti($product->cost, $out->qty);
                        $out->actCost = $this->quickFloatMulti($product->price, $out->qty);
                        $out->sell = $this->quickFloatMulti($pickItem->price, $out->qty);
                    }
                    if ($out->actCost == 0) $out->actCost =$out->cost;
                    $out->profit = $out->sell-$out->cost;
                    $out->actProfit = $out->sell-$out->actCost;
                    $resaleInfo->add($out);
                }
            }
            $stockInfo = new Collection();
            $combiCuts = $cuts->merge($returnCuts);
            $combiProducts = $products->merge($returnProducts);
            $combiWeights = $weights->merge($returnWeights)->where("status_id",0);
            foreach ($combiCuts as $cut)
            {
                $out = new stdClass();
                $out->name = Species::find($cut->species_id)->name ." ".$cut->name;
                $productsForCut = $combiProducts->where("cut_id",$cut->id);
                $weightsForCut = $combiWeights->whereIn("product_id",$productsForCut->pluck("id")->toArray());
                $out->qty = count($weightsForCut);
                $out->kg =$weightsForCut->sum("weight_tear");
                $out->cost = $productsForCut->first()->cost;
                $out->actCost = ($productsForCut->first()->price)?$productsForCut->first()->price:$productsForCut->first()->cost;
                $out->subTotal = ($productsForCut->first()->unit!="PPC")? $this->quickFloatMulti($out->cost , $out->kg) : $this->quickFloatMulti($out->cost , $out->qty);
                $out->actSubTotal = ($productsForCut->first()->unit!="PPC")? $this->quickFloatMulti($out->actCost , $out->kg) : $this->quickFloatMulti($out->actCost , $out->qty);
                $stockInfo->add($out);
            }
            return view("reports.intake", ['intake_id'=>$intake_id,'search_intake_id'=>$search_intake_id,'summary'=>$summary->toArray(),"saleInfo"=>$saleInfo,"creditInfo"=>$creditInfo,"resaleInfo"=>$resaleInfo,"stockInfo"=>$stockInfo]);
        }
    }
    private function guessTheOriginal(Product $newproduct):Product
    {
        $potentialProds = Product::where("pallet_id",$newproduct->original_pallet_id)->get();
        if (count($potentialProds) == 1) return $potentialProds->first();

        if (count(array_unique($potentialProds->pluck("cut_id")->toArray()))==1) return $potentialProds->first();

        return $newproduct;

    }
    private function quickFloatMulti($val1, $val2,$percision = 2)
    {
        $i = 1000 * 1000;
        $val1 *= 1000;
        $val2 *= 1000;
        return ReportHelper::floorDec(($val1*$val2)/$i,$percision);
    }
}
