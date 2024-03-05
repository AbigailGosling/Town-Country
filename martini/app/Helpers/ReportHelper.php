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
    public static function getDataRange(Carbon $start, Carbon $end):Collection
    {
        ini_set('memory_limit', '4G');
        //Alter DB Settings
        /** @var Connection $conn */
        $conn = DB::connection("tandc_live");
        $pdo = $conn->getPdo();
        $pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, true);

        $resultQB = $conn->table("pickerSheets")
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
        /** @var Collection $result */
        $results = $resultQB->get();
        
        $customers = $conn->table("customers")->select("customers.*")->get()->toArray();
        $users = $conn->table("users")->select("users.*")->get()->toArray();
        $suppliers = $conn->table("supplier")->select("supplier.*")->get()->toArray();
        $brands = $conn->table("brands")->select("brands.*")->get()->toArray();
        $nationalities = $conn->table("nationality")->select("nationality.*")->get()->toArray();
        $temperatures = $conn->table("temperature")->select("temperature.*")->get()->toArray();
        $cuts = $conn->table("cuts")->select("cuts.*")->get()->toArray();
        $cutgroups = $conn->table("cutgroups")->select("cutgroups.*")->get()->toArray();
        $species = $conn->table("species")->select("species.*")->get()->toArray();

        //Restore DB Settings
        $pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
        $finalResult = new Collection();
        foreach($results as $result)
        {
            $col = "palletsOut.weight_ids";
            $weightQB = $conn->table("weights")
                ->selectRaw("weights.id, count(weights.product_id) as `rows`, sum(weight_gross) as `weight_gross`, sum(weight_tear) as `weight_tear`, sum(number_of_cartons) as `number_of_cartons`")
                ->whereIn("weights.id",explode(",",$result->$col))
                ->groupBy("weights.product_id");
            $item = $weightQB->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"weights.");

            $col = "pickerItems.product_id";
            $item = $conn->table("product")->select("product.*")->where("product.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"product.");

            $col = "product.pallet_id";
            $item = $conn->table("pallet")->select("pallet.*")->where("pallet.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"pallet.");

            $col = "pallet.intake_id";
            $item = $conn->table("intake")->select("intake.*")->where("intake.id",$result->$col)->first();
            if ($item===null) continue;
            static::row_merge($result,$item,"intake.");

            $col = "pickerSheets.customer_id";
            $item = static::array_search_multidim($customers,"customers.id",$result->$col);
            static::row_merge($result,$item);

            $col = "pickerSheets.user_from_id";
            $item = static::array_search_multidim($users,"users.id",$result->$col);
            if ($item===null) throw new \Exception(json_encode($result));
            static::row_merge($result,$item);
            
            $col = "intake.supplier_id";
            $item = static::array_search_multidim($suppliers,"supplier.id",$result->$col);
            static::row_merge($result,$item);
            
            $col = "product.brand_id";
            $item = static::array_search_multidim($brands,"brands.id",$result->$col);
            static::row_merge($result,$item);

            $col = "product.nationality_id";
            $item = static::array_search_multidim($nationalities,"nationality.id",$result->$col);
            static::row_merge($result,$item);

            $col = "product.cooling_id";
            $item = static::array_search_multidim($temperatures,"temperature.id",$result->$col);
            static::row_merge($result,$item);

            $col = "product.cut_id";
            $item = static::array_search_multidim($cuts,"cuts.id",$result->$col);
            static::row_merge($result,$item);

            $col = "cuts.cutgroup_id";
            $item = static::array_search_multidim($cutgroups,"cutgroups.id",$result->$col);
            static::row_merge($result,$item);

            $col = "cuts.species_id";
            $item = static::array_search_multidim($species,"species.id",$result->$col);
            static::row_merge($result,$item);

            $finalResult->add($result);
        }
        return $finalResult;
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
            $result = $reportColumn->metadata['footer']($columnData);
        }
        return sprintf($reportColumn->html_footer,$result);
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
                if (!static::filterCheck($reportColumn,$workingResult,$dbRow)) $workingResult[$reportColumn->label] = static::resolveCell($reportColumn,"","");
                if ($reportColumn->processing_type == "calculate" && $reportColumn->metadata != NULL && array_key_exists("calculate",$reportColumn->metadata))
                {
                    $workingResult[$reportColumn->label] = static::calculate(
                        $reportColumn->metadata['calculate']['operator'],
                        $reportColumn->metadata['calculate']['args'],
                        $workingResult,
                        $dbRow);
                }
                else if ($reportColumn->pointers != null)
                {
                    foreach($reportColumn->pointers as $colString)
                    {
                        $workingResult[$reportColumn->label] = static::getValue($colString,$workingResult,$dbRow);
                    }
                }
                if ($reportColumn->metadata != NULL && array_key_exists("fallback",$reportColumn->metadata))
                {
                    if ($workingResult[$reportColumn->label] == null || $workingResult[$reportColumn->label] === "" || $workingResult[$reportColumn->label] === 0)
                    {
                        $workingResult[$reportColumn->label] = static::getValue($reportColumn->metadata["fallback"],$workingResult,$dbRow);
                    }
                }
            }
            $results[] = $workingResult; 
        } 
        return $results;
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
            return $workingResult[$col];
        }
        else
        {
            return $dbRow->$colString;
        }
    }
    private static function filterCheck(ReportColumn $reportColumn,$workingResult,$dbRow):bool
    {
        if ($reportColumn->metadata != null && array_key_exists("filters",$reportColumn->metadata)) 
        {
            foreach ($reportColumn->metadata['filters'] as $colString => $filterValue)
            {
                throw new \Exception(json_encode([$colString,$filterValue,static::getValue($colString,$workingResult,$dbRow)]));
                if (static::getValue($colString,$workingResult,$dbRow) != $filterValue) 
                {
                    return false;
                }
            }
        }
        return true;
    }
    public static function resolveCell(ReportColumn $reportColumn,$left,$right):string
    {
        $data_type = $reportColumn->data_type;
        if ($data_type == "string") return $left.$right;
        if ($data_type == "date") return static::resolveDateCell($reportColumn,$left,$right);
        if ($data_type == "currency") $data_type = "double";

        settype($left,$data_type);
        settype($right,$data_type);
        
        return $left + $right;
    }
    public static function resolveDateCell(ReportColumn $reportColumn,$left,$right):string
    {
        return $left.DateTime::createFromFormat($reportColumn->metadata['format_from'],$right)->getTimestamp();
    }
    public static function finaliseCell(ReportColumn $reportColumn,array $workingResult):string
    {
        $workingVal = $workingResult[$reportColumn->label];
        if ($workingVal === "0") return "";
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
                return $negMarker . " £ " . number_format((double)$workingVal,2);
            }
            case "double":
            {
                return number_format((double)$workingVal,3);
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