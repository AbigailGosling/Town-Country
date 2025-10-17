<?php

namespace App\Http\Controllers;

use App\Models\InvoicePayment;
use App\Models\PickerItem;
use App\Models\PalletsOut;
use App\Models\PickerSheet;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierReturn;
use App\Models\Weight;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use stdClass;

class SupplierReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $items = new Collection();
        $searchTerm = $request->input("search","");
        $supReturnQ = SupplierReturn::selectRaw("ANY_VALUE(id) AS `id`,ANY_VALUE(supplier_id) AS `supplier_id`, MAX(`updated_at`) AS `updated_at`")->groupBy("supplier_id")->orderBy("updated_at")->orderByDesc("id");
        if ($searchTerm!="")
        {
            $supList = Supplier::where("name","LIKE","%$searchTerm%")->pluck('id')->toArray();
            $supReturnQ->whereIn("supplier_id",$supList);
        }
        foreach($supReturnQ->get() as $supplierR)
        {
            $item = $this->sumSupplier(Supplier::find($supplierR->supplier_id));
            if ($item->trans > 0)
            {
                $items->add($item);
            }
        }
        $output = new stdClass();
        $output->outstanding = $items->sum("outstanding");
        $output->value = $items->sum("value");
        $output->paid = $items->sum("paid");
        $output->trans= $items->count();
        return view('supplier-return-statements/index',['items' => $items,'summary'=>$output,'search_term' => $searchTerm]);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Supplier  $supplierReturn
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        return redirect('legacy/supplier_return_statement.php?id='.$supplier->id);
    }
    public function sumSupplier(Supplier $supplier):stdClass
    {
        $supplierL = $this->processSupplier($supplier);
        $output = new stdClass();
        $output->outstanding = $supplierL->sum("outstanding");
        $output->value = $supplierL->sum("value");
        $output->paid = $supplierL->sum("paid");
        $output->trans= $supplierL->count();
        $output->supplier=$supplier;
        $output->items=$supplierL;
        return $output;
    }
    public function processSupplier(Supplier $supplier):Collection
    {
        $returnCol = new Collection;
        foreach (PickerSheet::where([["is_return_to_supplier",1],["customer_id",$supplier->id]])->get() as $pick)
        {
            $line = new stdClass();
            $line->outstanding = 0;
            $line->value = 0;
            $line->paid = 0;
            $returnProducts = PickerItem::selectRaw("ANY_VALUE(`price`) AS `price`, ANY_VALUE(`product_id`) AS `product_id`, count(`product_id`) as `count`")->where("pickersheet_id",$pick->id)->groupBy('product_id')->get();
            $quickWeightLookup = explode(",",implode(",",PalletsOut::where("pickersheet_id",$pick->id)->pluck("weight_ids")->toArray()));
            foreach ($returnProducts as $returnProduct)
            {
                $internalProduct = Product::find($returnProduct->product_id);
                if ($internalProduct->unit=="PPC")
                {
                    $itemCost = $returnProduct->price * $returnProduct->count;

                }
                else
                {
                    $q = Weight::where("product_id",$returnProduct->product_id)->whereIn("id",$quickWeightLookup);
                    $tear = $q->get()->sum("weight_tear");
                    $itemCost = $returnProduct->price * $tear;
                }
                $line->value += $itemCost;
            }
            $line->paid = InvoicePayment::where("invoice_id",$pick->id)->get()->sum("amount");
            if ($line->paid==null)$line->paid=0;
            $line->outstanding = ($line->value - $line->paid);
            $line->items[] = [$internalProduct,$returnProduct,$quickWeightLookup];
            $returnCol->add($line);
        }
        return $returnCol;
    }
}
