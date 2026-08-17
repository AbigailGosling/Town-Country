<?php

namespace App\Http\Controllers;

use App\Helpers\FuncHelper;
use App\Models\CreditNoteItem;
use App\Models\Customer;
use App\Models\CustomerOutstandingCache;
use App\Models\User;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InsuranceExposureReportController extends Controller
{
    public function index()
    {
        ini_set("memory_limit","1G");
        $detailedView = request()->input("detailed-view",0) == 1;
        $allowedCustomers = User::find(Auth::id())->listViewableCustomers();
        $customers = Customer::query()
            ->whereIn("customers.id",$allowedCustomers)
            ->where('customers.credit_enabled', true)
            ->where('customers.disabled', false)
            ->whereNotIn('customers.businessname', ['', '.. search'])
            ->get();
        $data = collect();
        foreach ($customers as $customer) {
            $ret = $this->get_customer_soa_results($customer);
            if (count($ret) == 0) continue;
            $at = [];
            $ot = [];
            $gt = [];
            $atOutstanding = 0;
            $otOutstanding = 0;
            $gtOutstanding = 0;
            foreach ($ret as $invoice) {
                if ($invoice->terms_passed == false && $invoice->grace_passed == false && $detailedView) {
                    $at[] = $invoice;
                    $atOutstanding += $invoice->outstanding;
                } elseif ($invoice->terms_passed == true && $invoice->grace_passed == false) {
                    $ot[] = $invoice;
                    $otOutstanding += $invoice->outstanding;
                } elseif ($invoice->grace_passed == true) {
                    $gt[] = $invoice;
                    $gtOutstanding += $invoice->outstanding;
                }
            }
            if (!$detailedView && count($ot) == 0 && count($gt) == 0) continue;
            $data->push((object)[
                'customer' => $customer,
                'at' => $at,
                'ot' => $ot,
                'gt' => $gt,
                'total_outstanding' => $atOutstanding + $otOutstanding + $gtOutstanding,
                'at_total_outstanding' => $atOutstanding,
                'ot_total_outstanding' => $otOutstanding,
                'gt_total_outstanding' => $gtOutstanding,
            ]);
        }
        $data = $data->sortBy([['gt_total_outstanding', 'desc'], ['ot_total_outstanding', 'desc'], ['at_total_outstanding', 'desc']]);
        return view('reports.insuranceexposure', [
            'data' => $data,
            'detailedView' => $detailedView,
        ]);
    }

    private array $customers = [];

    private function get_customer_soa_results(Customer $customer)
    {
        $now = time();
        $conn = DB::connection("tandc_live");
        $oldest = CustomerOutstandingCache::find($customer->id)?->oldest_unpaid_id ?? 0;
        $customerPicksheets = $conn->table('pickerSheets')
            ->leftJoin('invoice_payments', function ($join) {
                $join->on('pickerSheets.id', '=', 'invoice_payments.invoice_id')
                    ->where('invoice_payments.payment_method', '!=', 'CREDIT_NOTE');
            })
            ->where('pickerSheets.is_return_to_supplier', 0)
            ->where('pickerSheets.completed', 1)
            ->where('pickerSheets.customer_id', $customer->id)
            ->where('pickerSheets.id', '>=', $oldest)
            ->select('pickerSheets.id', 'pickerSheets.customer_id', 'pickerSheets.date', 'pickerSheets.date as creation_date', 'pickerSheets.estimated_delivery_date', DB::raw('SUM(invoice_payments.amount) as paid'))
            ->groupBy('pickerSheets.id')
            ->orderByDesc('pickerSheets.id')
            ->get()
            ->toArray();

        $pickSheets1 = $customerPicksheets;
        $knownPickIDs = [];
        $pickSheets = [];
        foreach($pickSheets1 as $picksheet){
            $picksheet->hasReturns = false;
            $pickSheets[$picksheet->id] = $picksheet;
            $knownPickIDs[] = $picksheet->id;
        }
        if (count($knownPickIDs) == 0) return [];

        $aKnownPickIDs = array_map("strval",$knownPickIDs);
        $customerReturns = $conn->table('intake')
            ->where('supplier_id', $customer->id)
            ->where('returned', 1)
            ->whereIn('delivery_note_number', $aKnownPickIDs)
            ->select('delivery_note_number', DB::raw('count(id) AS count'))
            ->groupBy('delivery_note_number')
            ->get()
            ->toArray();
        foreach ($customerReturns as $return){
            $pickSheets[$return->delivery_note_number]->hasReturns = ($return->count > 0);
        }
        $pickerItems = [];
        $pickWeightOuts = [];
        $creditPayments = [];
        foreach(DB::connection("tandc_live")->table('pickeritems')->whereIn('pickersheet_id', $knownPickIDs)->get() as $item) {
            if (!array_key_exists($item->pickersheet_id, $pickerItems)) {
                $pickerItems[$item->pickersheet_id] = [];
            }
            $pickerItems[$item->pickersheet_id][] = $item;
        }
        foreach(DB::connection("tandc_live")->table('pickweightout')->whereIn('pickersheet_id', $knownPickIDs)->get() as $item) {
            if (!array_key_exists($item->pickersheet_id, $pickWeightOuts)) {
                $pickWeightOuts[$item->pickersheet_id] = [];
            }
            $pickWeightOuts[$item->pickersheet_id][] = $item;
        }
        foreach(DB::connection("tandc_live")->table('invoice_payments')->whereIn('invoice_id', $knownPickIDs)->where('payment_method', 'CREDIT_NOTE')->get() as $item) {
            if (!array_key_exists($item->invoice_id, $creditPayments)) {
                $creditPayments[$item->invoice_id] = [];
            }
            $creditPayments[$item->invoice_id][] = $item;
        }
        $ret = [];
        foreach($pickSheets as $picksheet){
            if (!property_exists($picksheet, 'id')) {
                continue;
            }
            $picksheet->date = str_replace('/', '-', $picksheet->estimated_delivery_date);
            $picksheet->datetime = strtotime($picksheet->date);
            $picksheet->date = date('d/m/Y', $picksheet->datetime);

            $due_threshold = Carbon::createFromFormat('d/m/Y', $picksheet->date);
            if ($customer->due_warning != "")$due_threshold = $due_threshold->addDays(min(abs($customer->due_warning),21));
            $due_threshold = $due_threshold->getTimestamp();
            if ($now <= $due_threshold) continue;
            $picksheet->due_threshold_passed = true;

            $picksheet->credited = $picksheet->credit = (double) FuncHelper::floorDec($this->totalValueCreditedOnInvoiceID($creditPayments[$picksheet->id]??[]),2);
            $picksheet->price = (double) FuncHelper::floorDec($this->invoiceTotal($picksheet->id,$pickerItems[$picksheet->id]??[],$pickWeightOuts[$picksheet->id]??[]),2);

            $picksheet->paid = (double) FuncHelper::floorDec($picksheet->paid,2) + $picksheet->credited;
            $picksheet->invoicePaid = false;

            $picksheet->outstanding = FuncHelper::floorDec((double) $picksheet->price - $picksheet->paid,2);
            if ($picksheet->outstanding < 0.02) continue;

            $picksheet->creditNotes = $this->getInvoiceCreditNotes($picksheet->id,$creditPayments[$picksheet->id]??[]);
            $picksheet->hasCreditNote = (count($picksheet->creditNotes)>0);

            $credit_terms = Carbon::createFromFormat('d/m/Y', $picksheet->date);
            if ($customer->credit_terms != "")$credit_terms = $credit_terms->addDays(min(abs($customer->credit_terms),28));
            $credit_terms = $credit_terms->getTimestamp();
            $picksheet->terms_passed = ($now > $credit_terms);

            $credit_grace = Carbon::createFromFormat('d/m/Y', $picksheet->date);
            if ($customer->credit_grace != "")$credit_grace = $credit_grace->addDays(min(abs($customer->credit_grace),35));
            $credit_grace = $credit_grace->getTimestamp();
            $picksheet->grace_passed = ($now > $credit_grace);

            $estimated_delivery_date = strtotime(str_replace('/', '-', $picksheet->estimated_delivery_date));
            $picksheet->sortableDueDateFormat = date('d-m-Y',$estimated_delivery_date);
            $ret[] = $picksheet;
        }
        usort($ret,[$this,'date_compare']);
        return $ret;
    }
    private function date_compare($element1, $element2) {
        $datetime1 = $element1->datetime;
        $datetime2 = $element2->datetime;
        return $datetime1 - $datetime2;
    }
    private function totalValueCreditedOnInvoiceID(array $creditPayments){
		$price = 0;
		$paymentsResult = $creditPayments ?? [];
		foreach ($paymentsResult as $paymentData)
		{
			$price = $price + (double)$this->creditNoteTotal($paymentData->id);
		}
		return FuncHelper::ceilDec($price,2);
 	}
    private function creditNoteTotal(int $invoice_payment_id){
    	$price = 0;
		$creditNoteResult = CreditNoteItem::where('payment_id', $invoice_payment_id)->get();

		foreach ($creditNoteResult as $creditNoteItem) {
            $prod = ($creditNoteItem->product_id != 0) ? $this->getProducts([$creditNoteItem->product_id])[0] ?? null:null;
            if ($creditNoteItem->product_id != 0 && $prod == null) continue;
			if($creditNoteItem->product_id == 0 || $prod->unit == 'PPC'){ # bespoke credit note, not attached product
				$price += (double)$creditNoteItem->price * (double)$creditNoteItem->quantity;
			}else{
				$weight = $this->weightFromProductIDArray([$creditNoteItem->product_id]);
				$price += ((double)$creditNoteItem->price * (double)$weight);
			}
		}
		return FuncHelper::ceilDec($price,2);
	}
    private function weightFromProductIDArray(array $PRODUCT_IDS){
		$y = Weight::whereIn('product_id', $PRODUCT_IDS)->get();
		$weight = 0;
		foreach ($y as $row) {
			$weight = $row->getNetWeight() + $weight;
		}
		return $weight;
	}
    private function invoiceTotal(int $pickersheet_id,array $pickerItems,array $pickWeightOuts){

		$outpalletQuery = $pickWeightOuts ?? [];
        $totalPrice = 0;
		foreach($outpalletQuery as $outpallet){

			$weightids = array_values(array_map('intval', array_unique(array_filter(explode(',', $outpallet->weight_ids)))));
			$productIDArray = array();

			if (count($weightids)>0 && FuncHelper::array_consecutive($weightids)) {
				$y = Weight::whereBetween("id", [min($weightids), max($weightids)])->get();
			} else {
				$y = Weight::whereIn("id",$weightids)->get();
			}
            $ya = [];
            foreach($y as $weight){
                if (in_array($weight->id, $weightids)) {
                    $ya[] = $weight;
                }
            }
			$weightsByProductID = array();
			foreach($ya as $weight)
			{
				if(!in_array($weight->product_id, $productIDArray)){
					array_push($productIDArray, $weight->product_id);
					$weightsByProductID[$weight->product_id] = array();
				}
				$weightsByProductID[$weight->product_id][] = $weight;
			}
			$pickerItemByProductID = array();
			if (count($productIDArray) == 0) continue;
			$howManyX = $pickerItems ?? [];
			foreach ($howManyX as $pickItemByProd){
				$sheetproduct = $pickersheet_id . "_" . $pickItemByProd->product_id;
				if(!array_key_exists($sheetproduct, $weightsByProductID)){
					$pickerItemByProductID[$sheetproduct] = $pickItemByProd;
				}
			}
			$y1 = $this->getProducts($productIDArray);
			foreach($y1 as $product){
				$productID = $product->id;
				$count = count($weightsByProductID[$productID]);
				$sheetproduct = $pickersheet_id . "_" . $productID;
                if (!array_key_exists($sheetproduct, $pickerItemByProductID)) continue;
				$pickerItem = $pickerItemByProductID[$sheetproduct];

				$kg = 0;
				foreach($weightsByProductID[$productID] as $weightRow){
                    $tw = $weightRow->getNetWeight();
					$kg = $kg + $tw;
					$kg = number_format($kg, 3, '.', '');
				}
				if($product->unit == 'PPC'){
					$totalPrice += number_format((double)$count * (double)$pickerItem->price, 2, '.', '');
				}else{
					$totalPrice += number_format((double)$kg * (double)$pickerItem->price, 2, '.', '');
				}
			}
		}

		return $totalPrice;
	}
    private function getInvoiceCreditNotes(int $invoice_id,array $creditPayments):array{

		$result = $creditPayments ?? [];
		$array = array();
		foreach ($result as $row)
		{
            if ($row->id != $invoice_id) continue;
			$row->noteItems = array();
			$cnq = DB::connection("tandc_live")->select(DB::raw(
				"SELECT
					`credit_note_items`.`id` AS `credit_note_items_id`,
					`credit_note_items`.`payment_id` AS `credit_note_items_payment_id`,
					`credit_note_items`.`product_id` AS `credit_note_items_product_id`,
					`credit_note_items`.`quantity` AS `credit_note_items_quantity`,
					`credit_note_items`.`price` AS `credit_note_items_price`,
					`credit_note_items`.`description` AS `credit_note_items_description`,
					`product`.`id` AS `product_id`,
					`product`.`pallet_id` AS `product_pallet_id`,
					`product`.`cut_id` AS `product_cut_id`,
					`product`.`brand_id` AS `product_brand_id`,
					`product`.`nationality_id` AS `product_nationality_id`,
					`product`.`cooling_id` AS `product_cooling_id`,
					`product`.`status` AS `product_status`,
					`product`.`range_from` AS `product_range_from`,
					`product`.`range_to` AS `product_range_to`,
					`product`.`range_extension` AS `product_range_extension`,
					`product`.`ubbb` AS `product_ubbb`,
					`product`.`unit` AS `product_unit`,
					`product`.`comments` AS `product_comments`,
					`product`.`best_by` AS `product_best_by`,
					`product`.`pricetype` AS `product_pricetype`,
					`product`.`cost` AS `product_cost`,
					`product`.`price` AS `product_price`,
					`product`.`box_id` AS `product_box_id`,
					`product`.`weightnote` AS `product_weightnote`,
					`product`.`product_temp` AS `product_product_temp`
				FROM `credit_note_items`
				LEFT JOIN `product` ON `credit_note_items`.`product_id` = `product`.`id` WHERE `credit_note_items`.`payment_id` = ".$row->id));

				foreach ($cnq as $cnr)
				{
					if($cnr->product_id == 0 || $this->getProducts([$cnr->product_id])[0]->unit == 'PPC'){ # bespoke credit note, not attached product
						$cnr->finalValue = (double)FuncHelper::floorDec((double) $cnr->credit_note_items_price * (double) $cnr->credit_note_items_quantity);
					}else{
						$weight = (double)$this->weightFromProductIDArray([$cnr->product_id]);
						$cnr->finalValue = (double)FuncHelper::floorDec(((double)$cnr->credit_note_items_price * (double)$weight));
					}
					$row->noteItems[] = $cnr;
				}
			$array[] = $row;
		}
   		return $array;
	}

    private function getCustomer(int $customer_id): Customer
    {
        if (array_key_exists($customer_id, $this->customers)) {
            return $this->customers[$customer_id];
        }
        $customer = Customer::find($customer_id);
        $this->customers[$customer_id] = $customer;
        return $customer;
    }
    private $_products = [];
    private function getProducts(array $productIds):array
    {
        $ret = [];
        $toFind = [];
        foreach ($productIds as $productId) {
            if (array_key_exists($productId, $this->_products)) {
                $ret[] = $this->_products[$productId];
            }
            else
            {
                $toFind[] = $productId;
            }
        }
        if(count($toFind) > 0){
            foreach(DB::connection("tandc_live")->table('product')->whereBetween('id', [min($toFind), max($toFind)])->get() as $product) {
                $this->_products[$product->id] = $product;
                if (in_array($product->id, $toFind)) {
                    $ret[] = $product;
                }
            }
        }
        return $ret;
    }
}
