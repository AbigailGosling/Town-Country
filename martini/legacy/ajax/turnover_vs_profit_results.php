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

	$INTERESTED_PRODUCTIDS = [];
	$INTERESTED_PICKS = [];


    $INVOICE_ID = request()->input('invoice_id');
    if ($INVOICE_ID != null && $INVOICE_ID != '' && $INVOICE_ID != '...' && $INVOICE_ID != '0')
	{
		$filters['pickerSheets.id'] = $INVOICE_ID;
		$INTERESTED_PICKS[] = $INVOICE_ID;
	}

    $INTAKE_ID = request()->input('intake_id');
    if ($INTAKE_ID != null && $INTAKE_ID != '' && $INTAKE_ID != '...' && $INTAKE_ID != '0')
	{
		$filters['intake.id'] = $INTAKE_ID;
		$q = prepareExecuteQuery("SELECT `id` FROM pallet WHERE intake_id = ?",'i',[$INTAKE_ID]);
		if ($q->num_rows>0)
		{
			$ids = implode(",",array_column($q->fetch_all(MYSQLI_ASSOC),'id'));
			$q = prepareExecuteQuery("SELECT `id` FROM product WHERE pallet_id IN (".$ids.")");
			if ($q->num_rows>0)
			{
				$ids = array_column($q->fetch_all(MYSQLI_ASSOC),'id');
				if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
				else $INTERESTED_PRODUCTIDS = ReportHelper::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
			}
		}
	}

    $PALLET_ID = request()->input('pallet_id');
    if ($PALLET_ID != null && $PALLET_ID != '' && $PALLET_ID != '...' && $PALLET_ID != '0')
	{
		$filters['pallet.id'] = $PALLET_ID;
		$q = prepareExecuteQuery("SELECT `id` FROM product WHERE pallet_id = ?",'i',[$PALLET_ID]);
		if ($q->num_rows>0)
		{
			$ids = array_column($q->fetch_all(MYSQLI_ASSOC),'id');
			if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
			else $INTERESTED_PRODUCTIDS = ReportHelper::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
		}
	}

    $USER_ID = request()->input('user_id');
    if ($USER_ID != null && $USER_ID != '' && $USER_ID != '...' && $USER_ID != '0') $filters['pickerSheets.user_from_id'] = $USER_ID;

    $CUSTOMER_ID = request()->input('customer_id');
    if ($CUSTOMER_ID != null && $CUSTOMER_ID != '' && $CUSTOMER_ID != '...' && $CUSTOMER_ID != '0') $filters['customers.id'] = $CUSTOMER_ID;

    $SPECIES_ID = request()->input('species_id');
    if ($SPECIES_ID != null && $SPECIES_ID != '' && $SPECIES_ID != '...' && $SPECIES_ID != '0')
	{
		$filters['species.id'] = $SPECIES_ID;
		$q = prepareExecuteQuery("SELECT `id` FROM `cuts` WHERE species_id = ?",'i',[$SPECIES_ID]);
		if ($q->num_rows>0)
		{
			$ids = implode(",",array_column($q->fetch_all(MYSQLI_ASSOC),'id'));
			$q = prepareExecuteQuery("SELECT `id` FROM `product` WHERE cut_id IN (".$ids.")");
			if ($q->num_rows>0)
			{
				$ids = array_column($q->fetch_all(MYSQLI_ASSOC),'id');
				if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
				else $INTERESTED_PRODUCTIDS = ReportHelper::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
			}
		}
	}

    $CUTGROUP_ID = request()->input('cutgroup_id');
    if ($CUTGROUP_ID != null && $CUTGROUP_ID != '' && $CUTGROUP_ID != '...' && $CUTGROUP_ID != '0')
	{
		$filters['cuts.cutgroup_id'] = $CUTGROUP_ID;
		$q = prepareExecuteQuery("SELECT `id` FROM `cuts` WHERE `cutgroup_id` = ?",'i',[$CUTGROUP_ID]);
		if ($q->num_rows>0)
		{
			$ids = implode(",",array_column($q->fetch_all(MYSQLI_ASSOC),'id'));
			$q = prepareExecuteQuery("SELECT `id` FROM `product` WHERE `cut_id` IN (".$ids.")");
			if ($q->num_rows>0)
			{
				$ids = array_column($q->fetch_all(MYSQLI_ASSOC),'id');
				if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
				else $INTERESTED_PRODUCTIDS = ReportHelper::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
			}
		}
	}

    $COOLING_ID = request()->input('cooling_id');
    if ($COOLING_ID != null && $COOLING_ID != '' && $COOLING_ID != '...' && $COOLING_ID != '0')
	{
		$filters['product.cooling_id'] = $COOLING_ID;
		$q = prepareExecuteQuery("SELECT `id` FROM `product` WHERE `cooling_id` = ?",'i',[$COOLING_ID]);
		if ($q->num_rows>0)
		{
			$ids = array_column($q->fetch_all(MYSQLI_ASSOC),'id');
			if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
			else $INTERESTED_PRODUCTIDS = ReportHelper::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
		}
	}

    $BRAND_ID = request()->input('brand_id');
    if ($BRAND_ID != null && $BRAND_ID != '' && $BRAND_ID != '...' && $BRAND_ID != '0')
	{
		$filters['brands.id'] = $BRAND_ID;
		$q = prepareExecuteQuery("SELECT `id` FROM `product` WHERE `brand_id` = ?",'i',[$BRAND_ID]);
		if ($q->num_rows>0)
		{
			$ids = array_column($q->fetch_all(MYSQLI_ASSOC),'id');
			if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
			else $INTERESTED_PRODUCTIDS = ReportHelper::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
		}
	}

    $NATIONALITY_ID = request()->input('nationality_id');
    if ($NATIONALITY_ID != null && $NATIONALITY_ID != '' && $NATIONALITY_ID != '...' && $NATIONALITY_ID != '0')
	{
		$filters['nationality.id'] = $NATIONALITY_ID;
		$q = prepareExecuteQuery("SELECT `id` FROM `product` WHERE `nationality_id` = ?",'i',[$NATIONALITY_ID]);
		if ($q->num_rows>0)
		{
			$ids = array_column($q->fetch_all(MYSQLI_ASSOC),'id');
			if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
			else $INTERESTED_PRODUCTIDS = ReportHelper::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
		}
	}

    $SUPPLIER_ID = request()->input('supplier_id');
    if ($SUPPLIER_ID != null && $SUPPLIER_ID != '' && $SUPPLIER_ID != '...' && $SUPPLIER_ID != '0')
	{
		$filters['supplier.id'] = $SUPPLIER_ID;

		$q = prepareExecuteQuery("SELECT `id` FROM intake WHERE supplier_id = ?",'i',[$SUPPLIER_ID]);
		if ($q->num_rows>0)
		{
			$ids = implode(",",array_column($q->fetch_all(MYSQLI_ASSOC),'id'));
			$q = prepareExecuteQuery("SELECT `id` FROM pallet WHERE intake_id IN (".$ids.")");
			if ($q->num_rows>0)
			{
				$ids = implode(",",array_column($q->fetch_all(MYSQLI_ASSOC),'id'));
				$q = prepareExecuteQuery("SELECT `id` FROM product WHERE pallet_id IN (".$ids.")");
				if ($q->num_rows>0)
				{
					$ids = array_column($q->fetch_all(MYSQLI_ASSOC),'id');
					if (count($INTERESTED_PRODUCTIDS)==0) $INTERESTED_PRODUCTIDS = $ids;
					else $INTERESTED_PRODUCTIDS = ReportHelper::custom_intersect($INTERESTED_PRODUCTIDS,$ids);
				}
			}
		}
	}

	if (count($INTERESTED_PRODUCTIDS)>0)
	{
		$INTERESTED_PRODUCTIDS = ReportHelper::custom_unique($INTERESTED_PRODUCTIDS);
		$q = prepareExecuteQuery("SELECT DISTINCT `pickersheet_id` FROM pickeritems  USE INDEX (product_pickersheet) WHERE product_id IN (".implode(",",$INTERESTED_PRODUCTIDS).") ORDER BY pickersheet_id");
		$picks = array();
		while ($r = $q->fetch_assoc())
		{
			$picks[] = $r['pickersheet_id'];
		}
		if (count($picks)> 0)
		{
			$INTERESTED_PICKS = array_merge($INTERESTED_PICKS,$picks);
		}

	}
	if (count($INTERESTED_PICKS)>0)
	{
		$INTERESTED_PICKS = ReportHelper::custom_unique($INTERESTED_PICKS);
		sort($INTERESTED_PICKS,SORT_NUMERIC);
	}
    $CASES = "Cases";
    $GT = "G/T";
    $PPC = "PPC";

    if (count(array_keys($filters))==0)$filters = null;

    $report = Report::find(1);

    $dataRanges = ReportHelper::getCollectionsForReportRange($report,ReportHelper::DATE_TYPE_ASSEMBLED,$date_start,$date_end,$INTERESTED_PICKS,$CUSTOMER_ID,$USER_ID,$filters);

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
				$re = ReportHelper::resolveFooter($reportCol,$processed[$table->name],$table->mode);
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
