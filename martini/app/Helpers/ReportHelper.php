<?php
namespace App\Helpers;

use App\Models\Cut;
use App\Models\Intake;
use App\Models\Pallet;
use App\Models\Product;
use App\Models\Report;
use App\Models\ReportColumn;
use App\Models\ReportTable;
use App\Models\Species;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use stdClass;

class ReportHelper
{
    public const DATE_TYPE_ASSEMBLED = "assembled";
    public const DATE_TYPE_CREATED = "created";
    public const DATE_TYPE_DELIVERED = "delivered";
    private static array $customers;
    private static array $users;
    private static array $suppliers;
    private static array $brands;
    private static array $nationalities;
    private static array $temperatures;
    private static array $cuts;
    private static array $cutgroups;
    private static array $species;
    private static array $health_marks;

    /** @var Connection $conn */
    private static $conn;
    /** @var PDO $pdo */
    private static $pdo;
    public static function filterBuilder(array &$INTERESTED_PICKS,
    string $INVOICE_ID = null,
    string $INTAKE_ID = null,
    string $PALLET_ID = null,
    string $USER_ID = null,
    string $CUSTOMER_ID = null,
    string $SPECIES_ID = null,
    string $CUTGROUP_ID = null,
    string $COOLING_ID = null,
    string $BRAND_ID = null,
    string $NATIONALITY_ID = null,
    string $SUPPLIER_ID = null,
    string $HEALTH_ID = null,
    string $INTERNAL_NUM = null,
    string $IMPORT_NUM = null
    ):array{
        ini_set('max_execution_time', 0);
        $INTERESTED_PRODUCTIDS=[];
        $filters=[];
        if ($INVOICE_ID != null && $INVOICE_ID != '' && $INVOICE_ID != '...' && $INVOICE_ID != '0')
        {
            $filters['pickerSheets.id'] = $INVOICE_ID;
            $INTERESTED_PICKS[] = $INVOICE_ID;
        }

        if ($INTAKE_ID != null && $INTAKE_ID != '' && $INTAKE_ID != '...' && $INTAKE_ID != '0')
        {

            $q = Intake::find($INTAKE_ID);
            if ($q)
            {
                $filters['intake.id'] = $INTAKE_ID;
                $ids = Pallet::where("intake_id",$INTAKE_ID)->pluck('id')->toArray();
                $ids = Product::whereIn("pallet_id",$ids)->pluck('id')->toArray();
                if (count($ids)>0)
                {
                    if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                    else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
                }
            }
        }

        if ($HEALTH_ID != null && $HEALTH_ID != '' && $HEALTH_ID != '...' && $HEALTH_ID != '0')
        {

            $q = Intake::where("health_id",$HEALTH_ID);
            if ($q)
            {
                $filters['health_mark.id'] = $HEALTH_ID;
                $ids = array_column($q->get()->toArray(),'id');
                $ids = Pallet::whereIn("intake_id",$ids)->pluck('id')->toArray();
                $ids = Product::whereIn("pallet_id",$ids)->pluck('id')->toArray();
                if (count($ids)>0)
                {
                    if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                    else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
                }
            }
        }
        if ($INTERNAL_NUM != null && $INTERNAL_NUM != '' && $INTERNAL_NUM != '...' && $INTERNAL_NUM != '0')
        {

            $q = Intake::where('internal_num',$INTERNAL_NUM);
            if ($q)
            {
                $filters['intake.internal_num'] = $INTERNAL_NUM;
                $ids = array_column($q->get()->toArray(),'id');
                $ids = Pallet::whereIn("intake_id",$ids)->pluck('id')->toArray();
                $ids = Product::whereIn("pallet_id",$ids)->pluck('id')->toArray();
                if (count($ids)>0)
                {
                    if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                    else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
                }
            }
        }
        if ($IMPORT_NUM != null && $IMPORT_NUM != '' && $IMPORT_NUM != '...' && $IMPORT_NUM != '0')
        {

            $q = Intake::where("import_num",$IMPORT_NUM);
            if ($q)
            {
                $filters['intake.import_num'] = $IMPORT_NUM;
                $ids = array_column($q->get()->toArray(),'id');
                $ids = Pallet::whereIn("intake_id",$ids)->pluck('id')->toArray();
                $ids = Product::whereIn("pallet_id",$ids)->pluck('id')->toArray();
                if (count($ids)>0)
                {
                    if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                    else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
                }
            }
        }
        if ($PALLET_ID != null && $PALLET_ID != '' && $PALLET_ID != '...' && $PALLET_ID != '0')
        {
            $filters['pallet.id'] = $PALLET_ID;
            $ids = Product::where("pallet_id",$PALLET_ID)->pluck('id')->toArray();
            if (count($ids)>0)
            {
                if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
            }
        }

        if ($USER_ID != null && $USER_ID != '' && $USER_ID != '...' && $USER_ID != '0') $filters['pickerSheets.user_from_id'] = $USER_ID;

        if ($CUSTOMER_ID != null && $CUSTOMER_ID != '' && $CUSTOMER_ID != '...' && $CUSTOMER_ID != '0') $filters['customers.id'] = $CUSTOMER_ID;

        if ($SPECIES_ID != null && $SPECIES_ID != '' && $SPECIES_ID != '...' && $SPECIES_ID != '0')
        {
            $ids = Cut::where("species_id",$SPECIES_ID)->pluck('id')->toArray();
            if (count($ids)>0)
            {
                $filters['species.id'] = $SPECIES_ID;
                $ids = Product::whereIn("cut_id",$ids)->pluck('id')->toArray();
                if (count($ids)>0)
                {
                    if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                    else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
                }
            }
        }

        if ($CUTGROUP_ID != null && $CUTGROUP_ID != '' && $CUTGROUP_ID != '...' && $CUTGROUP_ID != '0')
        {
            $ids = Cut::where("cutgroup_id",$CUTGROUP_ID)->pluck('id')->toArray();
            if (count($ids)>0)
            {
                $filters['cuts.cutgroup_id'] = $CUTGROUP_ID;
                $ids = Product::whereIn("cut_id",$ids)->pluck('id')->toArray();
                if (count($ids)>0)
                {
                    if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                    else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
                }
            }
        }

        if ($COOLING_ID != null && $COOLING_ID != '' && $COOLING_ID != '...' && $COOLING_ID != '0')
        {

            $ids = Product::where("cooling_id",$COOLING_ID)->pluck('id')->toArray();
            if (count($ids)>0)
            {
                $filters['product.cooling_id'] = $COOLING_ID;
                if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
            }
        }

        if ($BRAND_ID != null && $BRAND_ID != '' && $BRAND_ID != '...' && $BRAND_ID != '0')
        {
            $ids = Product::where("brand_id",$BRAND_ID)->pluck('id')->toArray();
            if (count($ids)>0)
            {
                $filters['brands.id'] = $BRAND_ID;
                if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
            }
        }

        if ($NATIONALITY_ID != null && $NATIONALITY_ID != '' && $NATIONALITY_ID != '...' && $NATIONALITY_ID != '0')
        {
            $ids = Product::where("nationality_id",$NATIONALITY_ID)->pluck('id')->toArray();
            if (count($ids)>0)
            {
                $filters['nationality.id'] = $NATIONALITY_ID;
                if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
            }
        }

        if ($SUPPLIER_ID != null && $SUPPLIER_ID != '' && $SUPPLIER_ID != '...' && $SUPPLIER_ID != '0')
        {
            $ids = Intake::where("supplier_id",$SUPPLIER_ID)->pluck('id')->toArray();
            if (count($ids)>0)
            {
                $filters['supplier.id'] = $SUPPLIER_ID;
                $ids = Pallet::whereIn("intake_id",$ids)->pluck('id')->toArray();
                if (count($ids)>0)
                {
                    $ids = Product::whereIn("pallet_id",$ids)->pluck('id')->toArray();
                    if (count($ids)>0)
                    {
                        if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
                        else $INTERESTED_PRODUCTIDS = static::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
                    }
                }
            }
        }

        if (count($INTERESTED_PRODUCTIDS)>0)
        {
            $INTERESTED_PRODUCTIDS = static::custom_unique($INTERESTED_PRODUCTIDS);
            $q = DB::connection("tandc_live")->select("SELECT DISTINCT `pickersheet_id` FROM `pickeritems`  USE INDEX (`product_pickersheet`) WHERE `product_id` IN (".implode(",",$INTERESTED_PRODUCTIDS).") ORDER BY `pickersheet_id`");
            $picks = array();
            foreach ($q as $r)
            {
                $picks[] = $r->pickersheet_id;
            }
            if (count($picks)> 0)
            {
                $INTERESTED_PICKS = array_merge($INTERESTED_PICKS,$picks);
            }

        }
        if (count($INTERESTED_PICKS)>0)
        {
            $INTERESTED_PICKS = static::custom_unique($INTERESTED_PICKS);
            sort($INTERESTED_PICKS,SORT_NUMERIC);
        }
        return $filters;
    }
    public static function getCollectionsForReportRange(Report $report,string $dateType,Carbon $start = NULL,Carbon $end = NULL, array $pickIDs = NULL, int $customerID = NULL, int $userID = NULL, array $filters = NULL):array
    {
        ini_set('memory_limit', '4G');
		if ($start == null)
		{
			$start = Carbon::createFromTimestamp(0);
		}
		if ($end == null) $end = Carbon::now();
		$start = $start->startOfDay();
		$end = $end->endOfDay();
        //Alter DB Settings
        static::$conn = DB::connection("tandc_live");
        static::$pdo = static::$conn->getPdo();
        static::$conn->statement("SET SESSION group_concat_max_len = 1000000;");
        $result = [];
        switch ($report->mode)
        {
            case "product":
                $result = static::getProductRange($dateType,$start,$end,$pickIDs,$customerID,$userID);
                break;
            case "invoice":
                $result = static::getInvoiceRange($dateType,$start,$end,$pickIDs,$customerID,$userID);
                break;
        }
		if ($filters === null || count($filters)==0) return $result;
		 $result2 = array();
        foreach ($result as $collection)
        {
            $rolling = $collection;
            foreach ($filters as $field=>$values)
            {
                $rolling = new Collection();
                foreach ($collection as $item)
                {
					$original = 'original_'.$field;
                    foreach (explode(",",$values) as $value)
                    {
                        if ((property_exists($item,$field)&&$item->$field == $value) || (property_exists($item,$original)&&$item->$original == $value))
                        {
                            $rolling->add($item);
                            break;
                        }
                    }
                }
                $collection = $rolling;
            }
            $result2[]=$collection;
        }
        return $result2;
    }
    private static function getProductRange(string $dateType, Carbon $start = NULL, Carbon $end = NULL, array $pickIDs = NULL, int $customerID = NULL, int $userID = NULL):array
    {
        //Alter DB Settings
        static::$pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, true);
        $resultQB = static::$conn->table("pickerSheets")
            ->join("pickerItems","pickerSheets.id"              ,'=',"pickerItems.pickersheet_id")
            ->join("palletsOut","pickerSheets.id"              ,'=',"palletsOut.pickersheet_id")
            ->selectRaw("pickerSheets.*,pickerItems.*,palletsOut.*,group_concat(palletsOut.weight_ids) as weight_ids,count(pickerItems.product_id),STR_TO_DATE(`pickerSheets`.`estimated_delivery_date`, '%d/%m/%Y') as parsedDate")
            ->groupBy(["pickerSheets.id","pickerItems.product_id"]);
        if ($start != NULL && $end != NULL) static::applyDateRange($resultQB,$dateType,$start,$end);
		if ($pickIDs != NULL && count($pickIDs)>0) $resultQB->whereIn("pickerSheets.id",$pickIDs);
		if ($customerID != NULL) $resultQB->where("pickerSheets.customer_id",$customerID);
		if ($userID != NULL) $resultQB->where("pickerSheets.user_from_id",$userID);

        /** @var Collection $debits */
        $debits = $resultQB->get();

        $resultQB = static::$conn->table("pickerSheets")
            ->join("invoice_payments","pickerSheets.id"         ,'=',"invoice_payments.invoice_id")
            ->join("credit_note_items","invoice_payments.id"    ,'=',"credit_note_items.payment_id")
            ->selectRaw("pickerSheets.*,invoice_payments.*,credit_note_items.*,STR_TO_DATE(`pickerSheets`.`estimated_delivery_date`, '%d/%m/%Y') as parsedDate")
            ->orderBy("invoice_payments.created_at")
            ->groupBy(["pickerSheets.id","credit_note_items.product_id"]);
		if ($start != NULL && $end != NULL) $resultQB->whereBetween("invoice_payments.created_at",[$start,$end]);
		if ($pickIDs != NULL && count($pickIDs)>0) $resultQB->whereIn("pickerSheets.id",$pickIDs);
		if ($customerID != NULL) $resultQB->where("pickerSheets.customer_id",$customerID);
		if ($userID != NULL) $resultQB->where("pickerSheets.user_from_id",$userID);

        /** @var Collection $credits */
        $credits = $resultQB->get();
        static::initialiseLookupArrays();
        //Restore DB Settings
        static::$pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
        $finalSupp = new Collection();
        $finalDebits = new Collection();

        foreach($debits as $result)
        {
            $col = ".weight_ids";
            $weightids = array();
            foreach (explode(",",$result->$col) as $id){
				$id = (int)trim($id);
                if ($id !== null && !in_array($id,$weightids)) $weightids[] = $id;
            }
            $col2= "pickerItems.product_id";
            $weightQB = static::$conn->table("weights")
                ->selectRaw("weights.id, count(weights.product_id) as `rows`, sum(weight_gross) as `weight_gross`, sum(weight_tear) as `weight_tear`, sum(number_of_cartons) as `number_of_cartons`")
                ->where("weights.product_id",$result->$col2)
                ->groupBy("weights.product_id");
            static::applyWeightRange($weightQB,$weightids,$result->$col2);
            $item = $weightQB->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"weights.");

            $col = "pickerItems.product_id";
            $item = static::$conn->table("product")->select("product.*")->where("product.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"product.");

            if (static::bulkMergeIn($result))
            {
                $col = "pickerSheets.isSupplemental";
                if ($result->$col === true || $result->$col === 1)
                {
                    $col = "pickerSheets.isSupplementalCredit";
                    if ($result->$col === false || $result->$col === 0) $finalSupp->add($result);
                }
                else $finalDebits->add($result);
            }
        }
		$finalCredits = new Collection();
        $finalSuppCred = new Collection();
        foreach($credits as $result)
        {
            $col = "credit_note_items.product_id";
            $weightQB = static::$conn->table("weights")
                ->selectRaw("weights.id, count(weights.product_id) as `rows`, sum(weight_gross) as `weight_gross`, sum(weight_tear) as `weight_tear`, sum(number_of_cartons) as `number_of_cartons`")
                ->where("weights.product_id",$result->$col)
                ->groupBy("weights.product_id");
            $item = $weightQB->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"weights.");

            $col = "credit_note_items.product_id";
            $item = static::$conn->table("product")->select("product.*")->where("product.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"product.");
            $col = "pickerSheets.isSupplemental";
            if ($result->$col !== true && $result->$col !== 1)
            {
            $col1 = "product.original_pallet_id";
            $col2 = "product.cut_id";
            $col3 = "product.brand_id";
            $col4 = "product.nationality_id";
            $item = static::$conn->table("product")->select("product.*")->where([["product.pallet_id",$result->$col1],[$col2,$result->$col2],[$col3,$result->$col3],[$col4,$result->$col4]])->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"original_product.");

            $col = "product.original_pallet_id";
            $item = static::$conn->table("pallet")->select("pallet.*")->where("pallet.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"original_pallet.");

            $col = "original_pallet.intake_id";
            $item = static::$conn->table("intake")->select("intake.*")->where("intake.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"original_intake.");

            $col = "original_intake.supplier_id";
            $item = static::$conn->table("supplier")->select("supplier.*")->where("supplier.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"original_supplier.");

            $co  = "original_product.id";
            $col = "product.id";
            $col2 = "pickerSheets.id";
            $qb = static::$conn->table("pickerItems")->select("pickerItems.*")->whereIn("pickerItems.product_id",[$result->$co,$result->$col])->where([["pickerItems.pickersheet_id",$result->$col2]]);
            $item =$qb->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"pickerItems.");
            }

            if (static::bulkMergeIn($result))
            {
                $col = "pickerSheets.isSupplemental";
                if ($result->$col === true || $result->$col === 1) $finalSuppCred->add($result);
                else $finalCredits->add($result);
            }
        }
		return array($finalDebits,$finalCredits,$finalSupp,$finalSuppCred);
    }
    private static function getInvoiceRange(string $dateType, Carbon $start = NULL, Carbon $end = NULL, array $pickIDs = NULL, int $customerID = NULL, int $userID = NULL):array
    {
        //Alter DB Settings
        static::$pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, true);

        $resultQB = static::$conn->table("pickerSheets")
            ->join("palletsOut","pickerSheets.id"               ,'=',"palletsOut.pickersheet_id")
            ->join("pickerItems","pickerSheets.id"              ,'=',"pickerItems.pickersheet_id")
            ->selectRaw("pickerSheets.*, count(pickerItems.product_id), GROUP_CONCAT(pickerItems.product_id) as product_ids, GROUP_CONCAT(pickerItems.price) as prices, GROUP_CONCAT(DISTINCT palletsOut.weight_ids) as weight_ids,STR_TO_DATE(`pickerSheets`.`estimated_delivery_date`, '%d/%m/%Y') as parsedDate")
            ->groupBy(["pickerSheets.id"]);
        if ($start != NULL && $end != NULL) static::applyDateRange($resultQB,$dateType,$start,$end);
		if ($pickIDs != NULL && count($pickIDs)>0) $resultQB->whereIn("pickerSheets.id",$pickIDs);
		if ($customerID != NULL) $resultQB->where("pickerSheets.customer_id",$customerID);
		if ($userID != NULL) $resultQB->where("pickerSheets.user_from_id",$userID);

        /** @var Collection $debits */
        $debits = $resultQB->get();

        $resultQB = static::$conn->table("pickerSheets")
            ->join("invoice_payments","pickerSheets.id"         ,'=',"invoice_payments.invoice_id")
            ->join("credit_note_items","invoice_payments.id"    ,'=',"credit_note_items.payment_id")
            ->selectRaw("pickerSheets.*, GROUP_CONCAT(credit_note_items.product_id) as product_ids, GROUP_CONCAT(credit_note_items.quantity) as quantities, GROUP_CONCAT(credit_note_items.price) as prices,STR_TO_DATE(`pickerSheets`.`estimated_delivery_date`, '%d/%m/%Y') as parsedDate")
            ->groupBy(["pickerSheets.id"])
            ->orderBy("invoice_payments.created_at");
		if ($start != NULL && $end != NULL) $resultQB->whereBetween("invoice_payments.created_at",[$start,$end]);
		if ($pickIDs != NULL && count($pickIDs)>0) $resultQB->whereIn("pickerSheets.id",$pickIDs);
		if ($customerID != NULL) $resultQB->where("pickerSheets.customer_id",$customerID);
		if ($userID != NULL) $resultQB->where("pickerSheets.user_from_id",$userID);

        /** @var Collection $credits */
        $credits = $resultQB->get();

        static::initialiseLookupArrays();

        //Restore DB Settings
        static::$pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);

        $finalDebits = new Collection();
        $finalSupp = new Collection();
        foreach($debits as $result)
        {
            $col = ".weight_ids";
            $col2= ".product_ids";
            $col3= ".prices";

            $product_ids = explode(",",$result->$col2);
            $prices = explode(",",$result->$col3);
            $rollingItem = null;
            $rollingActCost = $rollingCost = $rollingTotal = 0;
            $processedProds = [];
            foreach ($product_ids as $index => $product_id)
            {
                if (array_key_exists($product_id,$processedProds))continue;
                else $processedProds[$product_id] = true;
                $price = $prices[$index];
                $col = ".weight_ids";
                $weightids = array();
                foreach (explode(",",$result->$col) as $id){
                    if ($id !== null && trim($id) !== '') $weightids[] = $id;
                }
                $col2= "pickerItems.product_id";
                $weightQB = static::$conn->table("weights")
                    ->selectRaw("weights.id, count(weights.product_id) as `rows`, sum(weight_gross) as `weight_gross`, sum(weight_tear) as `weight_tear`, sum(number_of_cartons) as `number_of_cartons`")
                    ->where("weights.product_id",$product_id)
                    ->groupBy("weights.product_id");
                static::applyWeightRange($weightQB,$weightids,$product_id);
                $weight = $weightQB->first();
                if ($weight == null) continue;
                $product = static::$conn->table("product")->select("product.*")->where("product.id",$product_id)->first();

                if ($rollingItem == null) $rollingItem = $weight;
                else foreach ($weight as $colName => $val)
                {
                    if (!is_numeric($rollingItem->$colName)) continue;
                    (double)$rollingItem->$colName += (double)$val;
                }

                if ($product->unit == "PPC")
                {
                    $rowCountPointer = "rows";
                }
                else
                {
                    $rowCountPointer = "weight_tear";
                }
                $rollingTotal += static::floorDec(($weight->$rowCountPointer*$price),2);
                $rollingCost += static::floorDec(($weight->$rowCountPointer*$product->cost),2);
                if ($product->price && $product->price > 0)$rollingActCost += static::floorDec(($weight->$rowCountPointer*$product->price),2);
                else $rollingActCost += static::floorDec(($weight->$rowCountPointer*$product->cost),2);
            }
            if ($rollingItem == null) continue;
            static::row_merge($result,$rollingItem,"weights.");

            $prodPalletCheck = array_keys($processedProds);
            $prodPallet = static::$conn->table("product")
                ->selectRaw("product.*")
                ->whereIn("product.id",$prodPalletCheck)
                ->groupBy("product.pallet_id")->get();
            $bulkMergeFull = false;
            $subTotal = "pickerSheets.subTotal";
            $result->$subTotal = $rollingTotal;
            if (count($prodPallet)==1)
            {
                $bulkMergeFull = true;
                static::row_merge($result,$prodPallet[0],"product.");
            }
            else
            {
                $subTotal = "pickerSheets.cost";
                $result->$subTotal = $rollingCost;

                $subTotal = "pickerSheets.actCost";
                $result->$subTotal = $rollingActCost;
            }
            if (static::bulkMergeIn($result,$bulkMergeFull))
            {
                $col = "pickerSheets.isSupplemental";
                if ($result->$col === true || $result->$col === 1)
                {
                    $col = "pickerSheets.isSupplementalCredit";
                    if ($result->$col === false || $result->$col === 0) $finalSupp->add($result);
                }
                else $finalDebits->add($result);
            }
        }
        $finalSuppCred = new Collection();
        $finalCredits = new Collection();
        foreach($credits as $result)
        {
            $col2= ".product_ids";
            $col3= ".prices";

            $product_ids = explode(",",$result->$col2);
            $prices = explode(",",$result->$col3);
            $rollingItem = null;
            $rollingActCost = $rollingCost = $rollingTotal = 0;
            $processedProds = [];
            foreach ($product_ids as $index => $product_id)
            {
                if (array_key_exists($product_id,$processedProds))continue;
                else $processedProds[$product_id] = true;
                $price = $prices[$index];
                $weightQB = static::$conn->table("weights")
                    ->selectRaw("weights.id, count(weights.product_id) as `rows`, sum(weight_gross) as `weight_gross`, sum(weight_tear) as `weight_tear`, sum(number_of_cartons) as `number_of_cartons`")
                    ->where("weights.product_id",$product_id)
                    ->groupBy("weights.product_id");
                $weight = $weightQB->first();
                if ($weight == null) continue;
                $product = static::$conn->table("product")->select("product.*")->where("product.id",$product_id)->first();

                $col = "product.original_pallet_id";
                $item = static::$conn->table("pallet")->select("pallet.*")->where("pallet.id",$product->original_pallet_id)->first();
                if ($item===null) continue;
                static::row_merge($result,$item,"original_pallet.");

                $col = "original_pallet.intake_id";
                $item = static::$conn->table("intake")->select("intake.*")->where("intake.id",$result->$col)->first();
                if ($item===null) continue;
                static::row_merge($result,$item,"original_intake.");

                $col = "original_intake.supplier_id";
                $item = static::$conn->table("supplier")->select("supplier.*")->where("supplier.id",$result->$col)->first();
                if ($item===null) continue;
                static::row_merge($result,$item,"original_supplier.");

                if ($rollingItem == null) $rollingItem = $weight;
                else foreach ($weight as $colName => $val)
                {
                    if (!is_numeric($rollingItem->$colName)) continue;
                    (double)$rollingItem->$colName += (double)$val;
                }

                if ($product->unit == "PPC")
                {
                    $rowCountPointer = "rows";
                }
                else
                {
                    $rowCountPointer = "weight_tear";
                }
                $rollingTotal += static::floorDec(($weight->$rowCountPointer*$price),2);
                $rollingCost += static::floorDec(($weight->$rowCountPointer*$product->cost),2);
                if ($product->price && $product->price > 0)$rollingActCost += static::floorDec(($weight->$rowCountPointer*$product->price),2);
                else $rollingActCost += static::floorDec(($weight->$rowCountPointer*$product->cost),2);
            }
            if ($rollingItem == null) continue;
            static::row_merge($result,$rollingItem,"weights.");

            $dateCol = "pickerSheets.date";
            $dateTo = "invoice_payments.created_at";
            $result->$dateTo =  $result->$dateCol;
            $prodPalletCheck = array_keys($processedProds);
            $prodPallet = static::$conn->table("product")
                ->selectRaw("product.*")
                ->whereIn("product.id",$prodPalletCheck)
                ->groupBy("product.pallet_id")->get();
            $bulkMergeFull = false;
            $subTotal = "pickerSheets.subTotal";
            $result->$subTotal = $rollingTotal;
            if (count($prodPallet)==1)
            {
                $bulkMergeFull = true;
                static::row_merge($result,$prodPallet[0],"product.");
            }
            else
            {
                $subTotal = "pickerSheets.cost";
                $result->$subTotal = $rollingCost;

                $subTotal = "pickerSheets.actCost";
                $result->$subTotal = $rollingActCost;
            }
            if (static::bulkMergeIn($result,$bulkMergeFull))
            {
                $col = "pickerSheets.isSupplementalCredit";
                if ($result->$col === true || $result->$col === 1) $finalSuppCred->add($result);
                else $finalCredits->add($result);
            }
        }
        return array($finalDebits,$finalCredits,$finalSupp,$finalSuppCred);
    }
    public static function resolveHeader(ReportColumn $reportColumn,string $mode):string
    {
        return sprintf($reportColumn->header,$reportColumn->getLabel($mode));
    }
    public static function resolveFooter(ReportColumn $reportColumn,array $data,string $mode):string
    {
       if ($reportColumn->metadata!=null && array_key_exists('footer',$reportColumn->metadata) != null)return static::recursiveResolveFooter($reportColumn,$reportColumn->metadata['footer'], $data, $mode);
       else return "";
    }
    private static function recursiveResolveFooter(ReportColumn $reportColumn,array|string $function, array $data, string $mode)
    {
        $result = null;
        if (is_array($function)) {
            foreach ($function as $fun)
            {
                $result .= static::recursiveResolveFooter($reportColumn, $fun, $data, $mode);
            }
        }
        else
        {
            $percision = 3;
            $magShift = pow(10,$percision);
            $columnData = array_column($data,$reportColumn->getLabel($mode));
            for($i=0;$i<count($columnData);$i++)
            {
                $rolling = filter_var(str_replace("£","",$columnData[$i]), FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
                $rolling = static::floorDec(floatval($rolling)*$magShift,0);

                switch($function)
                {
                    case "array_sum":
                    {
                        $result += $rolling;
                        break;
                    }
                    case "":
                    {

                        break;
                    }
                }
            }
            $result = static::finaliseItem($reportColumn,static::floorDec($result/$magShift,$percision));
        }
        if ($result == null || $reportColumn->footer == "") $result = "";
        return $result;
    }
    public static function resolveTableBody(ReportTable $reportTable, Collection $rows):array
    {
        $results = [];
        $mode = $reportTable->mode;
        $rows2 = $rows->toArray();
        foreach ($rows2 as $dbRow)
        {
            $workingResult = [];
            $col = "pickerSheets.id";
            $workingResult['internal_id'] = $dbRow->$col;
            foreach ($reportTable->getColumns() as $reportColumn)
            {
                $workingResult[$reportColumn->getLabel($mode)] = "";
                if (!static::filterCheck($reportColumn,$workingResult,$dbRow))
                {
                    $workingResult[$reportColumn->getLabel($mode)] = static::resolveCell($reportColumn,"","");
                    continue;
                }
                if ($reportColumn->processing_type == "calculate" && $reportColumn->metadata != NULL && array_key_exists("calculate",$reportColumn->metadata))
                {
                    $workingResult[$reportColumn->getLabel($mode)] = static::resolveCell($reportColumn,$workingResult[$reportColumn->getLabel($mode)],static::calculate(
                        $reportColumn->metadata['calculate']['operator'],
                        $reportColumn->metadata['calculate']['args'],
                        $workingResult,
                        $dbRow));
                }
                else if ($reportColumn->pointers != null)
                {
                    if (!array_key_exists($mode,$reportColumn->pointers) && array_is_list($reportColumn->pointers))
                    {
                        $temp = $reportColumn->pointers;
                        $reportColumn->pointers=[
                            "debits"    => $temp,
                            "credits"   => $temp,
                            $mode       => $temp,
                        ];
                    }
                    foreach($reportColumn->pointers[$mode] as $colString)
                    {
                        $workingResult[$reportColumn->getLabel($mode)] = static::resolveCell($reportColumn,$workingResult[$reportColumn->getLabel($mode)],static::getValue($colString,$workingResult,$dbRow));
                    }
                }
                if ($reportColumn->metadata != NULL && array_key_exists("fallback",$reportColumn->metadata))
                {
                    if ($workingResult[$reportColumn->getLabel($mode)] == null || $workingResult[$reportColumn->getLabel($mode)] === "" || $workingResult[$reportColumn->getLabel($mode)] == "0")
                    {
                        $workingResult[$reportColumn->getLabel($mode)] = static::resolveFallback($reportColumn,$workingResult,$dbRow,$mode);
                    }
                }
            }
            $results[] = $workingResult;
        }
        return $results;
    }
    public static function finaliseCell(ReportColumn $reportColumn,array $workingResult,string $mode):string
    {
        if (!isset($workingResult[$reportColumn->getLabel($mode)])) throw new \Exception(json_encode([$reportColumn->getLabel($mode),$reportColumn,$workingResult,$mode]));
        $workingVal = $workingResult[$reportColumn->getLabel($mode)];
        if (($workingVal === "0" && $reportColumn->data_type != "currency") || $reportColumn->cell == "") return "";
        return static::finaliseItem($reportColumn,$workingVal);
    }
    public static function resolveCell(ReportColumn $reportColumn,$left,$right):string
    {
        $data_type = $reportColumn->data_type;
        if ($data_type == "string") return $left.$right;
        if ($data_type == "date") return static::resolveDateCell($reportColumn,$left,$right);
        if ($data_type == "currency") $data_type = "double";
        if ($data_type == "id") $data_type = "int";

        settype($left,$data_type);
        settype($right,$data_type);
        return $left + $right;
    }
    public static function resolveDateCell(ReportColumn $reportColumn,$left,$right):string
    {
        $t = DateTime::createFromFormat($reportColumn->metadata['format_from'],$right);
        if ($t) $t = $t->getTimestamp();
        else $t = "";
        return $left.$t;
    }
    private static function array_search_multidim($array, $column, $key)
    {
        return $array[array_search($key, array_column($array, $column))];
    }
    private static function row_merge(&$to, $from, $prefix="")
    {
        if ($from === null) throw new \Exception(json_encode($to));
        foreach ($from as $key => $value)
        {
            $col2 = $prefix.$key;
            $to->$col2 = $value;
        }
    }
    private static function initialiseLookupArrays() {
        static::$customers = static::$conn->table("customers")->select("customers.*")->get()->toArray();
        static::$users = static::$conn->table("users")->select(["users.id","users.name"])->get()->toArray();
        static::$suppliers = static::$conn->table("supplier")->select("supplier.*")->get()->toArray();
        static::$brands = static::$conn->table("brands")->select("brands.*")->get()->toArray();
        static::$nationalities = static::$conn->table("nationality")->select("nationality.*")->get()->toArray();
        static::$temperatures = static::$conn->table("temperature")->select("temperature.*")->get()->toArray();
        static::$cuts = static::$conn->table("cuts")->select("cuts.*")->get()->toArray();
        static::$cutgroups = static::$conn->table("cutgroups")->select("cutgroups.*")->get()->toArray();
        static::$species = static::$conn->table("species")->select("species.*")->get()->toArray();
        static::$health_marks = static::$conn->table("health_mark")->select("health_mark.*")->get()->toArray();

    }
    private static function bulkMergeIn(&$result,$full = true):bool {
        $col = "pickerSheets.customer_id";
        $item = static::array_search_multidim(static::$customers,"customers.id",$result->$col);
        static::row_merge($result,$item);

        $col = "pickerSheets.user_from_id";
        $item = static::array_search_multidim(static::$users,"users.id",$result->$col);
        if ($item!==null) static::row_merge($result,$item);

        if ($full)
        {
            $col = "product.pallet_id";
            $item = static::$conn->table("pallet")->select("pallet.*")->where("pallet.id",$result->$col)->first();
            if ($item!==null)
            {
            static::row_merge($result,$item,"pallet.");
            $col = "pallet.intake_id";
            $item = static::$conn->table("intake")->select("intake.*")->where("intake.id",$result->$col)->first();
                    if ($item!==null)
                    {
            static::row_merge($result,$item,"intake.");
            $col = "intake.supplier_id";
            $col2 = "intake.returned";
            if ($result->$col2!=1)
            {
                $item = static::array_search_multidim(static::$suppliers,"supplier.id",$result->$col);
            }
            else
            {
                $item = static::array_search_multidim(static::$customers,"customers.id",$result->$col);
                $item2 = new stdClass();
                $k1 = "customers.id";
                $k2 = "supplier.id";
                $item2->$k2 = $item->$k1;

                $k1 = "customers.businessname";
                $k2 = "supplier.name";
                $item2->$k2 = $item->$k1;
                $item = $item2;
            }
            static::row_merge($result,$item);

            $col = "product.brand_id";
            $item = static::array_search_multidim(static::$brands,"brands.id",$result->$col);
            static::row_merge($result,$item);

            $col = "product.nationality_id";
            $item = static::array_search_multidim(static::$nationalities,"nationality.id",$result->$col);
            static::row_merge($result,$item);

            $col = "product.cooling_id";
            $item = static::array_search_multidim(static::$temperatures,"temperature.id",$result->$col);
            static::row_merge($result,$item);

            $col = "product.cut_id";
            $item = static::array_search_multidim(static::$cuts,"cuts.id",$result->$col);
            static::row_merge($result,$item);

            $col = "cuts.cutgroup_id";
            $item = static::array_search_multidim(static::$cutgroups,"cutgroups.id",$result->$col);
            static::row_merge($result,$item);

            $col = "cuts.species_id";
            $item = static::array_search_multidim(static::$species,"species.id",$result->$col);
            static::row_merge($result,$item);

            $col = "intake.health_id";
            if ($result->$col!=-1)
            {
                $item = static::array_search_multidim(static::$health_marks,"intake.health_id",$result->$col);
                static::row_merge($result,$item);
            }
                }

            }

        }
        return true;
    }
    private static function resolveFallback(ReportColumn $reportColumn,$workingResult,$dbRow,string $mode):string
    {
        $result = "";
        if ($reportColumn->metadata != NULL && array_key_exists("fallback",$reportColumn->metadata))
        {
            if ($workingResult[$reportColumn->getLabel($mode)] == null || $workingResult[$reportColumn->getLabel($mode)] === "" || $workingResult[$reportColumn->getLabel($mode)] == "0")
            {
                $fallbacks = [];
                if (is_string($reportColumn->metadata["fallback"]))
                {
                    $fallbacks[]=$reportColumn->metadata["fallback"];
                }
                else
                {
                    $fallbacks=$reportColumn->metadata["fallback"];
                }
                foreach ($fallbacks as $fallback)
                {
                    $result = static::resolveCell($reportColumn,$workingResult[$reportColumn->getLabel($mode)],static::getValue($fallback,$workingResult,$dbRow));
                    if (!($result == null || $result === "" ||$result == "0"))
                        break;
                }

            }
        }
        return $result;
    }
    private static function calculate(string $operator,$args,$workingResult,$dbRow)
    {
        (double) $result = null;
        foreach ($args as $arg)
        {
            $item = (is_array($arg))?(double)static::calculate($arg['operator'],$arg['args'],$workingResult,$dbRow):(double)static::getValue($arg,$workingResult,$dbRow);

            if ($result === null) $result = (double)$item;
            else
            {
                switch ($operator)
                {
                    case "/":
                    {
                        $result /= $item;
                        break;
                    }
                    case "*":
                    {
                        $result *= $item;
                        break;
                    }
                    case "-":
                    {
                        $result -= $item;
                        break;
                    }
                    default:
                    {
                        $result += $item;
                        break;
                    }
                }
            }
        }
        return static::floorDec($result,3);
    }
    private static function getValue(string $colString,$workingResult,$dbRow):string|null
    {
        if (strpos($colString,"this")===0)
        {
            $col = explode(".",$colString)[1];
            if (array_key_exists($col,$workingResult)) return $workingResult[$col];
            else return "";
        }
        else
        {
            if (property_exists($dbRow,$colString)) return $dbRow->$colString;
            else return "";
        }
    }
    private static function filterCheck(ReportColumn $reportColumn,$workingResult,$dbRow):bool
    {
        if ($reportColumn->metadata != null && array_key_exists("filters",$reportColumn->metadata))
        {
            foreach ($reportColumn->metadata['filters'] as $colString => $filterValue)
            {
                if (static::getValue($colString,$workingResult,$dbRow) != $filterValue)
                {
                    return false;
                }
            }
        }
        return true;
    }
    public static function finaliseItem(ReportColumn $reportColumn,string $workingVal)
    {
        switch ($reportColumn->data_type)
        {
            case "currency":
            {
                $negMarker = "";
                settype($workingVal,"double");
                if ($workingVal < 0)
                {
                    $negMarker = "-";
                    $workingVal *= -1;
                }
                return $negMarker . "£" . number_format(static::floorDec($workingVal,2),2);
            }
            case "double":
            {
                return number_format((double)static::floorDec($workingVal,3),3);
            }
            case "int":
            {
                return number_format((int)static::floorDec($workingVal,0));
            }
            case "id":
            {
                return (int)$workingVal;
            }
            case "date":
            {
                if ((int)$workingVal != 0 && (int)$workingVal != null){
                    $v = new DateTime();
                    $v->setTimestamp((int)$workingVal);
                    return $v->format($reportColumn->metadata['format_to']);
                }
                else return "";
            }
            default:
            {
                return ($workingVal)?$workingVal:"";
            }
        }
    }
    private static function applyWeightRange(Builder &$weightQB, array $weightids, int $productid)
    {
        if (count($weightids)>2000)
        {
            //PDO has limits on the number of vars we can submit in a prepare and a string length limit so we need to shrink the set
            $pw = static::$conn->table("weights")->selectRaw("GROUP_CONCAT(id) as ids")->where("product_id",$productid)->first()->ids;
            $pw = explode(",",$pw);
            $weightids = static::custom_intersect($pw,$weightids);
        }
        $weightQB->whereIn("weights.id",$weightids);
    }
    public static function custom_intersect(array $arrayOne, array $arrayTwo):array
    {
        //Fastest array intersect https://stackoverflow.com/a/53203232/1856411
        $first = array_flip($arrayOne);
        $second = array_flip($arrayTwo);

        $x = array_intersect_key($first, $second);

        return array_flip($x);
    }
	public static function custom_unique(array $array):array
    {
		$x = array();
		foreach ($array as $item)
		{
			if ($item !== null && $item !== "")$x[$item] = true;
		}
		return array_keys($x);
	}
    private static function applyDateRange(Builder &$resultQB, string $dateType, Carbon $start, Carbon $end)
    {
        switch ($dateType)
        {
            case self::DATE_TYPE_ASSEMBLED:
            {
                $resultQB->whereBetween("pickerSheets.date_completed",[$start,$end])
                        ->orderBy("pickerSheets.date_completed");
                break;
            }
            case self::DATE_TYPE_CREATED:
            {
                $resultQB->whereBetween("pickerSheets.date",[$start,$end])
                        ->orderBy("pickerSheets.date");
                break;
            }
            case self::DATE_TYPE_DELIVERED:
            {
                $resultQB->whereRaw("(STR_TO_DATE(`pickerSheets`.`estimated_delivery_date`, '%d/%m/%Y') BETWEEN '".$start."' AND '".$end."')")
                        ->orderBy("parsedDate");
                break;
            }
        }
    }
    public static function floorDec($val, $precision = 2) {
		if ($precision < 0) { $precision = 0; }
		$numPointPosition = intval(strpos($val, '.'));
		if ($numPointPosition === 0) { //$val is an integer
			return $val;
		}
		return floatval(substr($val, 0, $numPointPosition + $precision + 1));
	}
}
?>
