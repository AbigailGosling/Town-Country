<?php

use App\Helpers\FuncHelper;
use App\Helpers\ReportHelper;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
require(__DIR__.'/../functions.php');
ini_set('max_execution_time',0);
ini_set('memory_limit', '1G');

function calculateReport($request) {
    $date_start = $request->input('date_start') ? Carbon::parse(str_replace('/', '-', $request->input('date_start')))->startOfDay() : null;
    $date_end = $request->input('date_end') ? Carbon::parse(str_replace('/', '-', $request->input('date_end')))->endOfDay() : Carbon::now()->endOfDay();
    $INTERESTED_PICKS = [];
    $filters = ReportHelper::filterBuilder(
        $INTERESTED_PICKS,
        $request->input('invoice_id'),
        $request->input('intake_id'),
        $request->input('pallet_id'),
        $request->input('user_id'),
        $request->input('customer_id'),
        $request->input('species_id'),
        $request->input('cutgroup_id'),
        $request->input('cooling_id'),
        $request->input('brand_id'),
        $request->input('nationality_id'),
        $request->input('supplier_id')
    );

    $filters = count($filters) ? $filters : null;

    $report = Report::find(1);
    $dataRanges = ReportHelper::getCollectionsForReportRange(
        $report,
        ReportHelper::DATE_TYPE_ASSEMBLED,
        $date_start,
        $date_end,
        [],
        $request->input('customer_id'),
        $request->input('user_id'),
        $filters
    );

    $dataRanges2 = [];
    foreach ($dataRanges as $key => $range) {
        $data = ReportHelper::resolveTableBody($report->getTables()[$key], $range);
        foreach($data as &$item)
        {
            $qty = "";
            $unitlable = "";
            if ($item["Cases"] != "0")
            {
                $qty = $item["Cases"];
                $unitlable = "Cases";
            }
            else if ($item["PPC"] != "0")
            {
                $qty = $item["PPC"];
                $unitlable = "PPC";
            }
            else
            {
                $qty = $item["G/T"];
                $unitlable = "G/T";
            }
            $item["qty"] = $qty;
            $item["unit"] = $unitlable;
        }
        $dataRanges2[] = $data;
    }

    return [
        'dataRanges' => $dataRanges2,
        'reportTables' => $report->getTables(),
    ];
}
function renderHTMLReport($reportData, $width) {
    $user = User::find(Auth::id());
    $hasCostsPermission = $user && $user->hasPermission("viewcosts");
    $percision = 3;
	$magShift = pow(10,$percision);

    $output = "";
    $dataRanges2 = $reportData['dataRanges'];
    $reportTables = $reportData['reportTables'];

    foreach ($reportTables as $index => $table) {
        $colLookup = getColLookup($hasCostsPermission,$table->mode == "credit");
        $generalFormat = "word-wrap: break-all; overflow:hidden;";
        $cellFormat = "width:100%; max-width: " . floor($width / (count($colLookup) - 1)) . "px; $generalFormat";
        $divFormat = $cellFormat;

        $output .= "<table style='table-layout:fixed;' id='resultsTable'>";
        $output .= "<thead style='position: sticky; top: 0; background-color: white;'>";
        $output .= "<tr><th align=\"left\" colspan=" . count($colLookup) . ">{$table->name}</th></tr>";
        $output .= "<tr><td colspan=" . count($colLookup) . ">======================================================</td></tr>";
        $output .= "<tr>";
        foreach ($colLookup as $displayedCol => $discard) {
            $output .= "<th style='{$cellFormat}'>{$displayedCol}</th>";
        }
        $output .= "</tr></thead>";

        foreach ($dataRanges2[$index] as $row) {
            $output .= "<tr class='result'>";
            $extraFormating = ($index % 2 == 0)?"":"color:red;";
            foreach ($colLookup as $displayedCol => $internalCol) {
                if (array_key_exists($internalCol,$row)==false) $internalCol = "Org ".$internalCol;
                $output .= "<td style='{$extraFormating}{$cellFormat}'><div style='{$divFormat}'>";
                $internalValue = $row[$internalCol] ?? '';
                switch ($displayedCol){
                    case "DATE":
                        $internalValue = Carbon::createFromTimestamp($internalValue)->format("d/m/Y");
                        if ($internalValue =="01/01/1970") throw new \Exception(json_encode([$displayedCol,$internalCol,$row]));
                        break;
                    case "Inv ID":
                        $internalValue = '<a style="'.$divFormat.' font-size:15px;" href="invoice.php?id='.$internalValue.'" target="_blank">'.$internalValue.'</a>';
                        break;
                    case "kg":
                        $internalValue = number_format(FuncHelper::floorDec(floatval($internalValue),3),3)." kg";
                        break;
                    case "Cost":
                    case "Act Cost":
                    case "Sell":
                        $noPercentShow = true;
                    case "Profit":
                    case "Act Profit":
                        $salePointer = ($displayedCol=="Profit")?"Cost Value":"Actual Cost Value";
                        $rollingCost = FuncHelper::floorDec(floatval($row[$salePointer])*$magShift,0)/$magShift;
                        $internalValue = currencyCleanUp($internalValue,$rollingCost,$noPercentShow);
                        $noPercentShow = false;
                        break;
                }
                $output .= $internalValue."</div></td>";
            }
            $output .= "</tr>";
        }
        $output .= '<tfoot style="position: sticky; bottom: 0;"><tr class="totals" style="background:#d6d6d6;padding:10px;font-weight:bold;">';
        foreach ($colLookup as $displayedCol => $internalCol) {
            $column = array_column($dataRanges2[$index],$internalCol);
            $output .= "<td style='{$cellFormat}font-size:12px;'><div style='{$divFormat}font-size:12px;'>";
            switch ($displayedCol)
            {
                case "Qty":
                    $output .= number_format(array_sum($column));
                    break;
                case "kg":
                    $output .= number_format(array_sum($column),3)." kg";
                    break;
                case "Cost":
                case "Act Cost":
                case "Sell":
                    $noPercentShow = true;
                case "Profit":
                case "Act Profit":
                    $internalValue = array_sum($column);
                    $salePointer = ($displayedCol=="Profit")?"Cost Value":"Actual Cost Value";
                    $rollingCost = array_sum(array_column($dataRanges2[$index],$salePointer));
                    $output .= currencyCleanUp($internalValue,$rollingCost,$noPercentShow);
                    $noPercentShow = false;
                    break;

            }
            $output .= '</div></td>';

        }
        $output .= "</tr></tfoot></table><br/>";
    }
    return $output;
}
function getColLookup($hasCostsPermission,$isCredit)
{
    if ($isCredit == false)
    return $hasCostsPermission
        ? [
            'Salesman' => "User",
            'DATE' => "Date Assembled",
            'Inv ID' => "Invoice",
            'Cust.' => "Customer",
            'Int ID' => "Intake ID",
            'Plt ID' => "Pallet ID",
            'Nation.' => "Nationality",
            'Temp.' => "Temp",
            'Prod.' => "Cut",
            'Brand' => "Brand",
            'Supp.' => "Supplier",
            'Qty' => "qty",
            'Unit' => "unit",
            'kg' => "kg",
            'Cost' => "Cost Value",
            'Act Cost' => "Actual Cost Value",
            'Sell' => "Sell Value",
            'Profit' => "Profit",
            'Act Profit' => "Actual Profit",
        ]
        : [
            'Salesman' => "User",
            'DATE' => "Date Assembled",
            'Inv ID' => "Invoice",
            'Cust.' => "Customer",
            'Int ID' => "Intake ID",
            'Plt ID' => "Pallet ID",
            'Nation.' => "Nationality",
            'Temp.' => "Temp",
            'Prod.' => "Cut",
            'Brand' => "Brand",
            'Supp.' => "Supplier",
            'Qty' => "qty",
            'Unit' => "unit",
            'kg' => "kg",
            'Cost' => "Cost Value",
            'Sell' => "Sell Value",
            'Profit' => "Profit",
        ];
    return $hasCostsPermission
    ? [
        'Salesman' => "User",
        'DATE' => "Org Date Assembled",
        'Inv ID' => "Invoice",
        'Cust.' => "Customer",
        'Int ID' => "Intake ID",
        'Plt ID' => "Pallet ID",
        'Nation.' => "Nationality",
        'Temp.' => "Temp",
        'Prod.' => "Cut",
        'Brand' => "Brand",
        'Supp.' => "Supplier",
        'Qty' => "qty",
        'Unit' => "unit",
        'kg' => "kg",
        'Cost' => "Original Cost Value",
        'Act Cost' => "Act Original Cost",
        'Sell' => "Credit",
        'Profit' => "Loss",
        'Act Profit' => "Act Loss",
    ]
    : [
        'Salesman' => "User",
        'DATE' => "Date Assembled",
        'Inv ID' => "Invoice",
        'Cust.' => "Customer",
        'Int ID' => "Intake ID",
        'Plt ID' => "Pallet ID",
        'Nation.' => "Nationality",
        'Temp.' => "Temp",
        'Prod.' => "Cut",
        'Brand' => "Brand",
        'Supp.' => "Supplier",
        'Qty' => "qty",
        'Unit' => "unit",
        'kg' => "kg",
        'Cost' => "Cost Value",
        'Sell' => "Sell Value",
        'Profit' => "Profit",
    ];
}
function currencyCleanUp($startingProfit,$startingSale,$noPercentShow)
{
    $percision = 3;
	$magShift = pow(10,$percision);
    $currency = "£";
    $negPercent="";
    $percent = "";
    if ($startingProfit<0)
    {
        $startingProfit = floatval($startingProfit) * -1;
        $currency = "-£";
        $negPercent="-";
    }
    if ($noPercentShow == false)
    {

        $rollingCost = FuncHelper::floorDec(floatval($startingSale)*$magShift,0)/$magShift;
        if ($rollingCost == 0)
        {
            $percent = "<br/>0.000%";
        }
        else
        {
            $rollingProfit = FuncHelper::floorDec(floatval($startingProfit)*$magShift,0)/$magShift;
            $profitRatio = $rollingProfit/$rollingCost;
            $percentage = FuncHelper::floorDec($profitRatio*100,3);
            $percent = "<br/>{$negPercent}{$percentage}%";
        }
    }
    return $currency . number_format(FuncHelper::floorDec(floatval($startingProfit)),2).$percent;
}
$request = request();
$reportData = calculateReport($request);

if ($request->input('forExcel')) echo json_encode($reportData);
else echo renderHTMLReport($reportData, $request->input('width'));
