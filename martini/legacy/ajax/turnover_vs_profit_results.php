<?php

use App\Helpers\ReportHelper;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
	ini_set('max_execution_time', '0');
	if (User::find(Auth::id())->hasPermission("viewcosts"))
    {
        $garyCols = array
        (
            'Salesman' => "User",
            'DATE' => "Date Assembled",
            'Inv ID' => "Invoice",
            'Cust.' => "Customer",
            'Int ID' => "Intake ID",
            'Plt ID' => "Pallet ID",
            //'Species' => "Species",
            'Nation.' => "Nationality",
            'Temp.' => "Temp",
            //'Cat.' => "Group",
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
        );
    }
    else
    {
        $garyCols = array
        (
            'Salesman' => "User",
            'DATE' => "Date Assembled",
            'Inv ID' => "Invoice",
            'Cust.' => "Customer",
            'Int ID' => "Intake ID",
            'Plt ID' => "Pallet ID",
            //'Species' => "Species",
            'Nation.' => "Nationality",
            'Temp.' => "Temp",
            //'Cat.' => "Group",
            'Prod.' => "Cut",
            'Brand' => "Brand",
            'Supp.' => "Supplier",
            'Qty' => "qty",
            'Unit' => "unit",
            'kg' => "kg",
            'Cost' => "Cost Value",
            'Sell' => "Sell Value",
            'Profit' => "Profit",
        );
    }

	$percision = 3;
	$magShift = pow(10,$percision);

   	require(__DIR__.'/../functions.php');
    ini_set('max_execution_time',0);
    ini_set('memory_limit', '1G');
    $filters = array();
	$calenderPeriod = false;
	if(request()->input('date_start') != ''){
        $date_start = request()->input('date_start');
        $date_start = str_replace('/', '-', $date_start);
        $date_start = Carbon::createFromTimestamp(strtotime($date_start))->startOfDay();

        if(request()->input('date_end') == ''){
            $date_end = date('d/m/Y');
			$calenderPeriod = true;
        }else{
            $date_end = request()->input('date_end');
        }

        $date_end = str_replace('/', '-', $date_end);
        $date_end = Carbon::createFromTimestamp(strtotime($date_end))->endOfDay();
    }
	$INTERESTED_PICKS = [];

    $INVOICE_ID = request()->input('invoice_id',null);
    if ($INVOICE_ID!=null && $INVOICE_ID!="")$INTERESTED_PICKS[] =$INVOICE_ID;
    $INTAKE_ID = request()->input('intake_id');
    $PALLET_ID = request()->input('pallet_id');
    $USER_ID = request()->input('user_id');
    $CUSTOMER_ID = request()->input('customer_id');
    $SPECIES_ID = request()->input('species_id');
    $CUTGROUP_ID = request()->input('cutgroup_id');
    $COOLING_ID = request()->input('cooling_id');
    $BRAND_ID = request()->input('brand_id');
    $NATIONALITY_ID = request()->input('nationality_id');
    $SUPPLIER_ID = request()->input('supplier_id');

    $CASES = "Cases";
    $GT = "G/T";
    $PPC = "PPC";

    $report = Report::find(1);
    $filters = ReportHelper::filterBuilder($INTERESTED_PICKS,$INVOICE_ID,$INTAKE_ID,$PALLET_ID,$USER_ID,$CUSTOMER_ID,$SPECIES_ID,$CUTGROUP_ID,$COOLING_ID,$BRAND_ID,$NATIONALITY_ID,$SUPPLIER_ID);
    if (count(array_keys($filters))==0)$filters = null;
	if ($date_start=== null && $date_end=== null && ($INTERESTED_PICKS === null||count($INTERESTED_PICKS)==0)) $dataRanges= [];
    else $dataRanges = ReportHelper::getCollectionsForReportRange($report,ReportHelper::DATE_TYPE_ASSEMBLED,$date_start,$date_end,$INTERESTED_PICKS,$CUSTOMER_ID,$USER_ID,$filters);

    $dataRanges2= [];
	$processed = [];
	$tableSums = [];
    foreach ($dataRanges as $key=>$range)
    {
        $dataRanges2[]=ReportHelper::resolveTableBody($report->getTables()[$key],$range);
    }
    while (count($dataRanges2)<4){
        $dataRanges2[] = [];
    }
	$generalFormat = "word-wrap: break-all; overflow:hidden;";
	$divFormat =  "width:100% max-width: ".floorDec((request()->input('width')/(count($garyCols)-1)))."px; ".$generalFormat;
	$cellFormat = "width:100% max-width: ".floorDec((request()->input('width')/(count($garyCols)-1)))."px; ".$generalFormat;
    foreach ($report->getTables() as $index=>$table){
		$processed[$table->name] = [];
        $reportColumns =$table->getColumns();
        ?>
		<table style="table-layout:fixed;" id="resultsTable">
		<thead style="position: sticky; top: 0; background-color: white;"><tr><th align="left"colspan="<?php echo count($garyCols); ?>"><?php echo $table->name; ?></th></tr>
        <tr><td style="border-color: black; border-size: 2px;" colspan="<?php echo count($garyCols); ?>">======================================================</td></tr>
        <tr>
        <?php
        foreach($garyCols as $garyCol=>$discard)
        {
        ?>
            <th style="<?php echo $cellFormat; ?> font-size:15px;" align="left"><?php echo $garyCol; ?></th>
        <?php
        }
        ?>
        </tr></thead>
        <?php
		$rollingQty = 0;
        foreach($dataRanges2[$index] as $i2=>$row)
        {
			$d = new stdClass();
            echo '<tr class="result">';
            foreach($garyCols as $garyCol)
            {
                if ($index % 2 == 0) echo "<td style='".$cellFormat." font-size:15px;'><div style='".$divFormat." font-size:15px;'>";
                else echo "<td style='color:red;".$cellFormat." font-size:15px;'><div style='".$divFormat." font-size:15px;'>";
                if ($garyCol == "qty")
                {
                    $qty = "";
                    $unitlable = "";
                    if ($row[$CASES] != "0")
                    {
                        $qty = $row[$CASES];
						$rollingQty += $qty;
                        $unitlable = "Cases";
                    }
                    else if ($row[$PPC] != "0")
                    {
                        $qty = $row[$PPC];
						$rollingQty += $qty;
                        $unitlable = "PPC";
                    }
                    else
                    {
                        $qty = $row[$GT];
                        $unitlable = "G/T";
                    }
                    echo $qty;
                }
                else if ($garyCol == "unit")
                {
                    echo $unitlable;
                }
                else foreach($reportColumns as $reportCol)
                {
                    if ($reportCol->getLabel($report->getTables()[0]) == $garyCol)
                    {
                        $t = ReportHelper::finaliseCell($reportCol,$row,$table->mode);
						$col = $reportCol->getLabel($table->mode);
						$d->$col = preg_replace("/[£,]/", '', $t);
                        if ($garyCol == "Invoice")
                        {
                            $t = '<a style="'.$divFormat.' font-size:15px;" href="invoice.php?id='.$t.'" target="_blank">'.$t.'</a>';
                        }
						$kgS=($garyCol=="kg")?" kg":"";
                        echo $t.$kgS;
                        break;
                    }
                }
                echo "</div></td>";
                if ($index % 2 == 1 && (strpos($garyCol,"Profit") == false && floatval($d->$col) > 0)) $d->$col = -$d->$col;
            }
            echo "</tr>".PHP_EOL;
			$processed[$table->name][] = $d;
        }
        ?>
<tfoot style="position: sticky; bottom: 0;"><tr class="totals" style="background:#d6d6d6;padding:10px;font-weight:bold;">
<?php
	$summary = new stdClass();
	foreach($garyCols as $garyColLab=>$garyCol)
	{
		$re = "";
		if ($garyCol == "qty")
		{
			echo '<td style="'.$cellFormat.' font-size:12px;"><div class="" style="'.$divFormat.' font-size:12px;">'.$rollingQty;//$qty
		}
		else if ($garyCol == "unit")
		{
			echo '<td style="'.$cellFormat.' font-size:12px;"><div class="" style="'.$divFormat.' font-size:12px;">';
		}
		else foreach($reportColumns as $reportCol)
		{
			if ($reportCol->getLabel($report->getTables()[0]) == $garyCol)
			{
				$col = $reportCol->getLabel($table->mode);
				$re = ReportHelper::resolveFooter($reportCol,[],$processed[$table->name],$table->mode);
				$kgS=($garyCol=="kg")?" kg":"";
				echo '<td style="'.$cellFormat.' font-size:12px;"><div class="" style="'.$divFormat.' font-size:12px;">'.$re.$kgS;
				break;
			}
		}
		$summary->$garyCol = preg_replace("/[£,]/", '', $re);
		if ($garyCol == "Profit" || $garyCol == "Actual Profit")
		{
			$costpointer = (strpos($garyCol,"Actual")===false)?"Cost Value":"Actual Cost Value";
			$profitpointer = (strpos($garyCol,"Actual")===false)?"Profit":"Actual Profit";
			$rollingCost = filter_var($summary->$costpointer, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
			$rollingCost = floorDec(floatval($rollingCost)*$magShift,0)/$magShift;
			if ($rollingCost == 0)
			{
				echo "<br/>0.000%";
			}
			else
			{
				$rollingProfit = filter_var($summary->$profitpointer, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
				$rollingProfit = floorDec(floatval($rollingProfit)*$magShift,0)/$magShift;
				$profitRatio = $rollingProfit/$rollingCost;
				$percentage = floorDec($profitRatio*100,3);
				echo "<br/>".$percentage."%";
			}

		}
		echo '</div></td>';
	}
	$tableSums[] = $summary;
?>
</tr></tfoot>
</table>
<br/>
<?php } ?>
<table style="table-layout:fixed;" id="resultsTable<?php echo count($dataRanges2); ?>">
<thead style="position: sticky; top: 0; background-color: white;"><tr><th align="left"colspan="<?php echo count($garyCols); ?>">Summary</th></tr>
<tr><td style="border-color: black; border-size: 2px;" colspan="<?php echo count($garyCols); ?>">======================================================</td></tr>
<tr>
<?php
foreach($garyCols as $garyCol=>$discard)
{
?>
	<th style="<?php echo $cellFormat; ?>" align="left"><?php echo $garyCol; ?></th>
<?php
}
?>
</tr></thead>
<tfoot style="position: sticky; bottom: 0;"><tr class="totals" style="background:#d6d6d6;padding:10px;font-weight:bold;">
<?php
	$summary = new stdClass();
	foreach($garyCols as $garyColLab=>$garyCol)
	{
		echo '<td style="'.$cellFormat.'"><div class="" style="'.$divFormat.'">';
		$t = "";
		if ($tableSums[0]->$garyCol != "")
		{
			$columnData = array_column($tableSums,$garyCol);
			$result = 0;
			for($i=0;$i<count($columnData);$i++)
			{
				$rolling = filter_var(str_replace("£","",$columnData[$i]), FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
				$rolling = floorDec(floatval($rolling)*$magShift,0);
				$result += $rolling;
			}
			foreach($reportColumns as $reportCol)
			{
				if ($reportCol->getLabel($report->getTables()[0]) == $garyCol)
				{
					$t = ReportHelper::finaliseItem($reportCol,floorDec($result/$magShift,$percision));
					$col = $reportCol->getLabel($table->mode);
					$kgS=($garyCol=="kg")?" kg":"";
					echo $t.$kgS;
					break;
				}
			}
		}
		$summary->$garyCol = preg_replace("/[£,]/", '', $t);
		if ($garyCol == "Profit" || $garyCol == "Actual Profit")
		{
			$costpointer = (strpos($garyCol,"Actual")===false)?"Cost Value":"Actual Cost Value";
			$profitpointer = (strpos($garyCol,"Actual")===false)?"Profit":"Actual Profit";
			$rollingCost = filter_var($summary->$costpointer, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
			$rollingCost = floorDec(floatval($rollingCost)*$magShift,0)/$magShift;
			if ($rollingCost == 0)
			{
				echo "<br/>0.000%";
			}
			else
			{
				$rollingProfit = filter_var($summary->$profitpointer, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
				$rollingProfit = floorDec(floatval($rollingProfit)*$magShift,0)/$magShift;
				$profitRatio = $rollingProfit/$rollingCost;
				$percentage = floorDec($profitRatio*100,3);
				echo "<br/>".$percentage."%";
			}
		}
		echo '</div></td>';
	}
?></tr></tfoot>
</table>
