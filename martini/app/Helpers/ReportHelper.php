<?php
namespace App\Helpers;

use App\Models\ReportColumn;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PDO;
use stdClass;

class ReportHelper 
{
    private static array $customers;
    private static array $users;
    private static array $suppliers;
    private static array $brands;
    private static array $nationalities;
    private static array $temperatures;
    private static array $cuts;
    private static array $cutgroups;
    private static array $species;
    /** @var Connection $conn */
    private static $conn;
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
    public static function getDataRange(Carbon $start, Carbon $end):array
    {
        ini_set('memory_limit', '4G');
        //Alter DB Settings
        static::$conn = DB::connection("tandc_live");
        $pdo = static::$conn->getPdo();
        $pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, true);

        $resultQB = static::$conn->table("pickerSheets")
            ->join("pickerItems","pickerSheets.id"              ,'=',"pickerItems.pickersheet_id")
            ->join("palletsOut","pickerSheets.id"              ,'=',"palletsOut.pickersheet_id")
            ->selectRaw("count(pickerItems.product_id)")
            ->select([
                "pickerSheets.*",
                "pickerItems.*" ,
                "palletsOut.*" ,])
            ->groupBy("pickerItems.product_id")
            ->whereDate("pickerSheets.date_completed",">=",$start)
            ->whereDate("pickerSheets.date_completed","<" ,$end)
            ;
        /** @var Collection $debits */
        $debits = $resultQB->get();

        $resultQB = static::$conn->table("pickerSheets")
            ->join("invoice_payments","pickerSheets.id"        ,'=',"invoice_payments.invoice_id")
            ->join("credit_note_items","invoice_payments.id"        ,'=',"credit_note_items.payment_id")
            ->select([
                "pickerSheets.*",
                "invoice_payments.*" ,
                "credit_note_items.*" ,])
            ->whereDate("invoice_payments.created_at",">=",$start)
            ->whereDate("invoice_payments.created_at","<" ,$end)
            ;
        /** @var Collection $credits */
        $credits = $resultQB->get();
        //throw new \Exception(json_encode($credits));
        static::$customers = static::$conn->table("customers")->select("customers.*")->get()->toArray();
        static::$users = static::$conn->table("users")->select(["users.id","users.name"])->get()->toArray();
        static::$suppliers = static::$conn->table("supplier")->select("supplier.*")->get()->toArray();
        static::$brands = static::$conn->table("brands")->select("brands.*")->get()->toArray();
        static::$nationalities = static::$conn->table("nationality")->select("nationality.*")->get()->toArray();
        static::$temperatures = static::$conn->table("temperature")->select("temperature.*")->get()->toArray();
        static::$cuts = static::$conn->table("cuts")->select("cuts.*")->get()->toArray();
        static::$cutgroups = static::$conn->table("cutgroups")->select("cutgroups.*")->get()->toArray();
        static::$species = static::$conn->table("species")->select("species.*")->get()->toArray();

        //Restore DB Settings
        $pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
        $finalDebits = new Collection();
        foreach($debits as $result)
        {
            $col = "palletsOut.weight_ids";
            $col2= "pickerItems.product_id";
            $weightQB = static::$conn->table("weights")
                ->selectRaw("weights.id, count(weights.product_id) as `rows`, sum(weight_gross) as `weight_gross`, sum(weight_tear) as `weight_tear`, sum(number_of_cartons) as `number_of_cartons`")
                ->whereIn("weights.id",explode(",",$result->$col))
                ->where("weights.product_id",explode(",",$result->$col2))
                ->groupBy("weights.product_id");
            $item = $weightQB->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"weights.");

            $col = "pickerItems.product_id";
            $item = static::$conn->table("product")->select("product.*")->where("product.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"product.");

            if (static::bulkMergeIn($result))$finalDebits->add($result);
        }
        $finalCredits = new Collection();
        foreach($credits as $result)
        {
            $col = "credit_note_items.product_id";
            $item = static::$conn->table("product")->select("product.*")->where("product.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"product.");

            if (static::bulkMergeIn($result))$finalCredits->add($result);
        }
        //throw new \Exception(json_encode($finalCredits));
        return array($finalDebits,$finalCredits);
    }
    private static function bulkMergeIn(&$result):bool {
        $col = "product.pallet_id";
        $item = static::$conn->table("pallet")->select("pallet.*")->where("pallet.id",$result->$col)->first();
        if ($item===null) return false;
        static::row_merge($result,$item,"pallet.");

        $col = "pallet.intake_id";
        $item = static::$conn->table("intake")->select("intake.*")->where("intake.id",$result->$col)->first();
        if ($item===null) return false;
        static::row_merge($result,$item,"intake.");

        $col = "pickerSheets.customer_id";
        $item = static::array_search_multidim(static::$customers,"customers.id",$result->$col);
        static::row_merge($result,$item);

        $col = "pickerSheets.user_from_id";
        $item = static::array_search_multidim(static::$users,"users.id",$result->$col);
        if ($item===null) return false;
        static::row_merge($result,$item);
        
        $col = "intake.supplier_id";
        $item = static::array_search_multidim(static::$suppliers,"supplier.id",$result->$col);
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

        return true;
    }
    public static function resolveHeader(ReportColumn $reportColumn):string
    {
        return sprintf($reportColumn->html_header,$reportColumn->label);
    }
    public static function resolveFooter(ReportColumn $reportColumn,array $data):string
    {
        $result = "";
        if ($reportColumn->metadata != null && isset($reportColumn->metadata['footer']))
        {
            $columnData = array_column($data,$reportColumn->label);
            $result = static::finaliseItem($reportColumn,$reportColumn->metadata['footer']($columnData));
        }
        return $result;
    }
    public static function resolveBody(Collection $reportColumns, Collection $rows):array
    {
        $results = [];
        $rows2 = $rows->toArray();
        usort($rows2,function($a, $b) {
            $c = "pickerSheets.id";
            return ($a->$c > $b->$c);
        });
        foreach ($rows2 as $dbRow) 
        {   
            $workingResult = [];
            foreach ($reportColumns as $reportColumn)
            {
                $workingResult[$reportColumn->label] = "";
                if (!static::filterCheck($reportColumn,$workingResult,$dbRow)) 
                {
                    $workingResult[$reportColumn->label] = static::resolveCell($reportColumn,"","");
                    continue;
                }
                if ($reportColumn->processing_type == "calculate" && $reportColumn->metadata != NULL && array_key_exists("calculate",$reportColumn->metadata))
                {
                    $workingResult[$reportColumn->label] = static::resolveCell($reportColumn,$workingResult[$reportColumn->label],static::calculate(
                        $reportColumn->metadata['calculate']['operator'],
                        $reportColumn->metadata['calculate']['args'],
                        $workingResult,
                        $dbRow));
                }
                else if ($reportColumn->pointers != null)
                {
                    foreach($reportColumn->pointers as $colString)
                    {
                        $workingResult[$reportColumn->label] = static::resolveCell($reportColumn,$workingResult[$reportColumn->label],static::getValue($colString,$workingResult,$dbRow));
                    }
                }
                if ($reportColumn->metadata != NULL && array_key_exists("fallback",$reportColumn->metadata))
                {
                    if ($workingResult[$reportColumn->label] == null || $workingResult[$reportColumn->label] === "" || $workingResult[$reportColumn->label] == "0")
                    {
                        $workingResult[$reportColumn->label] = static::resolveCell($reportColumn,$workingResult[$reportColumn->label],static::getValue($reportColumn->metadata["fallback"],$workingResult,$dbRow));
                    }
                }
            }
            $results[] = $workingResult; 
        } 
        return $results;
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
        return $left.DateTime::createFromFormat($reportColumn->metadata['format_from'],$right)->getTimestamp();
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
        return $result;
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
    public static function finaliseCell(ReportColumn $reportColumn,array $workingResult):string
    {
        $workingVal = $workingResult[$reportColumn->label];
        if ($workingVal === "0" && $reportColumn->data_type != "currency") return "";
        return static::finaliseItem($reportColumn,$workingVal);
    }
    private static function finaliseItem(ReportColumn $reportColumn,string $workingVal)
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
                return $negMarker . "£" . number_format((double)$workingVal,2);
            }
            case "double":
            {
                return number_format((double)$workingVal,3);
            }
            case "int":
            {
                return number_format((int)$workingVal);
            }
            case "id":
            {
                return (int)$workingVal;
            }
            case "date":
            {
                $v = new DateTime();
                $v->setTimestamp((int)$workingVal);
                return $v->format($reportColumn->metadata['format_to']);
            }
            default:
            {
                return ($workingVal)?$workingVal:"";
            }
        }
    }
}
?>