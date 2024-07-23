<?php

use App\Helpers\ReportHelper;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
	
	$percision = 3;
	$magShift = pow(10,$percision);
	
   	require(__DIR__.'/../functions.php');
    ini_set('max_execution_time','256');
    ini_set('memory_limit', '512M');
    $filters = array();

    $INVOICE_ID = request()->input('invoice_id');
    if ($INVOICE_ID != null && $INVOICE_ID != '' && $INVOICE_ID != '...' && $INVOICE_ID != '0') $filters['pickerSheets.id'] = $INVOICE_ID;

    $INTAKE_ID = request()->input('intake_id');
    if ($INTAKE_ID != null && $INTAKE_ID != '' && $INTAKE_ID != '...' && $INTAKE_ID != '0') $filters['intake.id'] = $INTAKE_ID;

    $PALLET_ID = request()->input('pallet_id');
    if ($PALLET_ID != null && $PALLET_ID != '' && $PALLET_ID != '...' && $PALLET_ID != '0') $filters['pallet.id'] = $PALLET_ID;

    $USER_ID = request()->input('user_id');
    if ($USER_ID != null && $USER_ID != '' && $USER_ID != '...' && $USER_ID != '0') $filters['customers.salesman'] = $PALLET_ID;

    $CUSTOMER_ID = request()->input('customer_id');
    if ($CUSTOMER_ID != null && $CUSTOMER_ID != '' && $CUSTOMER_ID != '...' && $CUSTOMER_ID != '0') $filters['customers.id'] = $CUSTOMER_ID;

    $SPECIES_ID = request()->input('species_id');
    if ($SPECIES_ID != null && $SPECIES_ID != '' && $SPECIES_ID != '...' && $SPECIES_ID != '0') $filters['species.id'] = $SPECIES_ID;

    $CUTGROUP_ID = request()->input('cutgroup_id');
    if ($CUTGROUP_ID != null && $CUTGROUP_ID != '' && $CUTGROUP_ID != '...' && $CUTGROUP_ID != '0') $filters['cut_group.id'] = $CUTGROUP_ID;

    $COOLING_ID = request()->input('cooling_id');
    if ($COOLING_ID != null && $COOLING_ID != '' && $COOLING_ID != '...' && $COOLING_ID != '0') $filters['tempurature.id'] = $COOLING_ID;

    $BRAND_ID = request()->input('brand_id');
    if ($BRAND_ID != null && $BRAND_ID != '' && $BRAND_ID != '...' && $BRAND_ID != '0') $filters['brand.id'] = $BRAND_ID;

    $NATIONALITY_ID = request()->input('nationality_id');
    if ($NATIONALITY_ID != null && $NATIONALITY_ID != '' && $NATIONALITY_ID != '...' && $NATIONALITY_ID != '0') $filters['nationality.id'] = $NATIONALITY_ID;

    $SUPPLIER_ID = request()->input('supplier_id');
    if ($SUPPLIER_ID != null && $SUPPLIER_ID != '' && $SUPPLIER_ID != '...' && $SUPPLIER_ID != '0') $filters['supplier.id'] = $NATIONALITY_ID;

    if(request()->input('date_start') != ''){
        $date_start = request()->input('date_start');
        $date_start = str_replace('/', '-', $date_start);
        $date_start = Carbon::createFromTimestamp(strtotime($date_start));
        
        if(request()->input('date_end') == ''){
            $date_end = date('d/m/Y');
        }else{
            $date_end = request()->input('date_end');
        }

        $date_end = str_replace('/', '-', $date_end);
        $date_end = Carbon::createFromTimestamp(strtotime($date_end));
    }
    $CASES = "Cases";
    $GT = "G/T";
    $PPC = "PPC";
    if (User::find(Auth::id())->hasPermission("viewcosts")) 
    {
        $garyCols = array
        (
            'SALESMAN' => "User",
            'DATE' => "Date Assembled",
            'INVOICE ID' => "Invoice",
            'Customer' => "Customer",   
            'Intake ID' => "Intake ID",
            'Plt ID' => "Pallet ID",
            'Species' => "Species",
            'Nationality' => "Nationality",
            'Temp.' => "Temp",
            'Category' => "Group",
            'Product' => "Cut",
            'Brand' => "Brand",
            'Supplier' => "Supplier",
            'Qty' => "qty",
            'Unit' => "unit",
            'kg' => "kg",
            'Cost' => "Cost Value",
            'Actual Cost' => "Actual Cost Value",
            'Sell' => "Sell Value",
            'Profit' => "Profit",
            'Actual Profit' => "Actual Profit",
        );
    }
    else
    {
        $garyCols = array
        (
            'SALESMAN' => "User",
            'DATE' => "Date Assembled",
            'INVOICE ID' => "Invoice",
            'Customer' => "Customer",   
            'Intake ID' => "Intake ID",
            'Plt ID' => "Pallet ID",
            'Species' => "Species",
            'Nationality' => "Nationality",
            'Temp.' => "Temp",
            'Category' => "Group",
            'Product' => "Cut",
            'Brand' => "Brand",
            'Supplier' => "Supplier",
            'Qty' => "qty",
            'Unit' => "unit",
            'kg' => "kg",
            'Cost' => "Cost Value",
            'Sell' => "Sell Value",
            'Profit' => "Profit",
        );
    }
    if (count(array_keys($filters))==0)$filters = null;
    $report = Report::find(1);
    $dataRanges = ReportHelper::getCollectionsForReportRange($report,ReportHelper::DATE_TYPE_ASSEMBLED,$date_start->startOfDay(),$date_end->endOfDay(),$filters);
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
    foreach ($report->getTables() as $index=>$table){
		$processed[$table->name] = [];
        $reportColumns =$table->getColumns();
?> <table style="width:100%;" id="resultsTable<?php echo $index; ?>">
        <thead style="position: sticky; top: 0; background-color: white;"><tr><th align="left"colspan="<?php echo count($garyCols); ?>"><?php echo $table->name; ?></th></tr>
        <tr><td style="border-color: black; border-size: 2px;" colspan="<?php echo count($garyCols); ?>">======================================================</td></tr>
        <tr>
        <?php
        foreach($garyCols as $garyCol=>$discard)
        {
        ?>
            <th align="left"><?php echo $garyCol; ?></th>
        <?php
        }
        ?>
        </tr></thead>
        <?php
        foreach($dataRanges2[$index] as $row)
        {
			$d = new stdClass();
            echo '<tr class="result">';
            foreach($garyCols as $garyCol)
            {
                if ($index % 2 == 0) echo "<td>";
                else echo "<td style='color:red;'>";
                if ($garyCol == "qty")
                {
                    $qty = "";
                    $unitlable = "";
                    if ($row[$CASES] != "0")
                    {
                        $qty = $row[$CASES];
                        $unitlable = "Cases";
                    }
                    else if ($row[$PPC] != "0")
                    {
                        $qty = $row[$PPC];
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
                            $t = '<a href="invoice.php?id='.$t.'" target="_blank">'.$t.'</a>';
                        }
						$kgS=($garyCol=="kg")?"kg":"";
                        echo $t.$kgS;
                        break;
                    }
                }
                echo "</td>";
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
			echo '<td><div class="" style="font-size:13px;">'.'';//$qty
		}
		else if ($garyCol == "unit")
		{    
			echo '<td><div class="" style="font-size:13px;">';           
		}
		else foreach($reportColumns as $reportCol)
		{
			if ($reportCol->getLabel($report->getTables()[0]) == $garyCol)
			{
				$col = $reportCol->getLabel($table->mode);
				$re = ReportHelper::resolveFooter($reportCol,$processed[$table->name],$table->mode);
				$kgS=($garyCol=="kg")?"kg":"";
				echo '<td><div class="" style="font-size:13px;">'.$re.$kgS;
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
<script>
	console.log('<?php echo json_encode($tableSums); ?>');
</script>
<table style="width:100%;" id="resultsTable<?php echo count($dataRanges2); ?>">
<thead style="position: sticky; top: 0; background-color: white;"><tr><th align="left"colspan="<?php echo count($garyCols); ?>">Summary</th></tr>
<tr><td style="border-color: black; border-size: 2px;" colspan="<?php echo count($garyCols); ?>">======================================================</td></tr>
<tr>
<?php
foreach($garyCols as $garyCol=>$discard)
{
?>
	<th align="left"><?php echo $garyCol; ?></th>
<?php
}
?>
</tr></thead>
<tfoot style="position: sticky; bottom: 0;"><tr class="totals" style="background:#d6d6d6;padding:10px;font-weight:bold;">
<?php
	$summary = new stdClass();
	foreach($garyCols as $garyColLab=>$garyCol)
	{  
		echo '<td><div class="" style="font-size:13px;">';   
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
					$kgS=($garyCol=="kg")?"kg":"";
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