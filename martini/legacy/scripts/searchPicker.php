<?php

use App\Models\CutGroupNationalityDate;
use App\Models\Location;
use App\Models\Site;
use App\Models\Species;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$showEditIntake = (User::find(Auth::id())->hasPermission("intakeList.php"));
ini_set('memory_limit', '1G');
$timeStamp = microtime(true);
$cutgroup_id = request()->input('cutgroup_id');
$species_id = request()->input('species');
$temperatureID = request()->input('temperatureID',null);
$initial_pallet_id = $pallet_id = request()->input('palletID');
$intake_id = request()->input('intakeID');
$brand =  request()->input('brandID');
$nationality =  request()->input('nationalityID');
$customer_id =  request()->input('customerID') ??request()->input('supplierID');
$site_id =request()->input('siteID',null);
$loc_id =request()->input('locID',null);
$timeSensitivityStatus = (int)request()->input('time',0);
if ($timeSensitivityStatus == null) $timeSensitivityStatus = 0;

?>
<script type="text/javascript">
    $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
    function autoToggleRow(classs,thisclass, productid){

        var ele = $('.' + thisclass);

        toggleRow(classs,ele, productid);
    }

    function toggleRow(classs, ele,intake_id,cut_id,nationality_id,pallet_id,ubbb,locked,site_id){
        $.get( "scripts/_searchPickerNew.php?intake_id="+intake_id+"&cut_id=" + cut_id+"&class=" + classs + "&nationality_id=" + nationality_id + "&pallet_id=" + pallet_id + "&ubbb=" + ubbb + "&locked=" + locked + "&site_id=" + site_id + "&time=<?php echo $timeSensitivityStatus;?>" , function( data ) {
            $(ele).parent().after(data);
            $(ele).next().fadeIn();
            $(ele).remove();
        });
    }

    function toggleVisibleRow(classs){
        $('.' + classs).toggle();
    }
</script>
<table width="100%" class="slim searchRContent" style="display:table;">
    <thead>
        <tr class="searchRContent__head">
	        <th class="searchRContent__id">Intake ID</th>
	        <th class="searchRContent__location" style="width: 86px;">Location</th>
	        <th class="searchRContent__id">Plt ID</th>
            <th class="searchRContent__dropdown"></th>
	        <th class="searchRContent__unit">Unit</th>
	        <th class="searchRContent__chill">Chill/Frz</th>
	        <th class="searchRContent__product">Product</th>
	        <th>Nationality</th>
	        <th>Comments</th>
	        <th>Brand</th>
	        <th class="searchRContent__date-range">Date Range</th>
	        <th>Volume</th>
	        <th>Cost</th>
            <th>1-10 C/S</th>
            <th>10-35 C/S</th>
            <th>35+ C/S</th>
            <?php if (User::find(Auth::id())->hasPermission("viewcosts")) { ?><th style="color: #cacaca;font-weight:normal;font-size:12px;">Actual Cost</th><?php } ?>
	        <th class="searchRContent__plus"></th>
        </tr>
    </thead>
<?php
	require(__DIR__.'/../functions.php');

    $ARRAY_CUTS = array();

    // ??: Gets the same cuts twice here #1
    $ARRAY_CUTS = cutsFromCutGroup($species_id, $cutgroup_id);

    $whereArray = [];
    $varArray = [];
    $varTypes = '';

    if($species_id != '' && $cutgroup_id != ''){ # if these two are posted then they've used the species and cutgroup dropdown
        // ??: and here #2
        // $ARRAY_CUTS = cutsFromCutGroup($species_id, $cutgroup_id); # get array of all the cut_id's from the cutgroup
        $ids = implode(',', $ARRAY_CUTS);

        if(count($ARRAY_CUTS) > 0){ # seems to still get here if i dont do this if??
            $whereArray[] = 'product.cut_id IN ('.$ids.')';
        }

    }else if(($species_id != 'null' && !empty($species_id)) && empty($cutgroup_id)){
        $whereArray[]= "cuts.species_id = ?";
        $varArray[] = $species_id;
        $varTypes .= 'i';
    }
    else{
        $whereArray[] = "cuts.species_id NOT IN (11,12,14)";
    }

    if($pallet_id != ''){ # if this is posted then theyve entered a pallet id

        $whereArray[] = "pallet.id = ?";
        $varArray[] = $pallet_id;
        $varTypes .= 'i';
    }
    $whereArray[] = "`pallet`.`is_hidden` = 0";
    if($intake_id != ''){ # if this is posted then theyve entered a intake id
        $ARRAY_PALLET_IDS = palletIDsFromIntakeID($intake_id); # get array of all the cut_id's from the cutgroup
        $ids = implode(',', $ARRAY_PALLET_IDS);

        // if(!empty($ARRAY_PALLET_IDS)){
        //     $sold_pallet_count = checkForSoldPallets($ARRAY_PALLET_IDS);

        //     if($sold_pallet_count != 0){
        //         array_push($whereArray, "product.status='10'");
        //     }
        // }

        if ($ids != "")$whereArray[] = 'pallet.id IN ('.$ids.')';
    }
    if ($brand != '' && $brand != null && $brand != 'null'){
        $whereArray[] = "product.brand_id = ?";
        $varArray[] = $brand;
        $varTypes .= 'i';
    }
    if ($nationality != '' && $nationality != null && $nationality != 'null'){
        $whereArray[] = "product.nationality_id = ?";
        $varArray[] = $nationality;
        $varTypes .= 'i';
    }
    if ($temperatureID != '' && $temperatureID != null && $temperatureID != 'null' && $temperatureID != 'undefined'){
        $whereArray[] = "product.cooling_id = ?";
        $varArray[] = $temperatureID;
        $varTypes .= 'i';
    }
    if ($loc_id != '' && $loc_id != null && $loc_id != 'null')
    {
        $s = Site::find(Location::find($loc_id)->site_id);
        if ($s->sale_blocked == true)
        {
            echo "<tr><td colspan='15' style='color:red;text-align:center;'>The site associated with this location is blocked for sales. Please contact an administrator.</td></tr>";
            exit;
        }
        $locs = $loc_id;
    }
    elseif ($site_id != '' && $site_id != null && $site_id != 'null'){
        $s = Site::find($site_id);
        if ($s->sale_blocked == true)
        {
            echo "<tr><td colspan='15' style='color:red;text-align:center;'>The site associated with this location is blocked for sales. Please contact an administrator.</td></tr>";
            exit;
        }
        $locs = implode(",",array_column(prepareExecuteQuery("SELECT `id` FROM `location` WHERE `site_id` = ? AND `id` IS NOT NULL",'i',[$site_id])->fetch_all(MYSQLI_ASSOC),"id"));
    }
    else
    {
        $s = implode(",",Site::where([['disabled',false],['sale_blocked',false]])->get()->pluck('id')->toArray());

        $locs = implode(",",array_column(prepareExecuteQuery("SELECT `id` FROM `location` WHERE `id` IS NOT NULL AND `site_id` IN (".$s.")")->fetch_all(MYSQLI_ASSOC),"id"));
    }
    $whereArray[] = "weights.status_id != 1";

    $whereString = implode(' AND ',$whereArray);

    $productsX = "SELECT SQL_NO_CACHE *, `product`.`comments` as productcomments, `product`.`id` as productid, `cuts`.`name` as cutname,`cuts`.`species_id` as `species_id`, `nationality`.`name` as `local` FROM `product` INNER JOIN `pallet` ON `product`.`pallet_id`=`pallet`.`id`
    INNER JOIN `weights` ON `product`.`id` = `weights`.`product_id`
    JOIN `cuts` ON `product`.`cut_id` = `cuts`.`id`
    LEFT JOIN `nationality` ON `product`.`nationality_id` = `nationality`.`id`
    WHERE $whereString";

    $productsY = prepareExecuteQuery($productsX,$varTypes,$varArray);

    $totalW = 0;

    $products2 = mysqli_fetch_all($productsY, MYSQLI_ASSOC);
    $products = [];
    $knownCombo = [];
    $intakeIDsToCheck = array_unique(array_column($products2,'intake_id'));
    if (count($products2) > 0 && count($intakeIDsToCheck) > 0)
        $intakeIDsToCheck = array_column(prepareExecuteQuery("SELECT `id` from `intake` WHERE `deleted` = 0 AND `id` BETWEEN ".min($intakeIDsToCheck)." AND ".max($intakeIDsToCheck)." AND (`approved` = 1 OR `container_id` IS NULL)")->fetch_all(MYSQLI_ASSOC),"id");
        foreach ($products2 as $productRow)
        {
            if (!in_array($productRow['intake_id'],$intakeIDsToCheck)) continue;
            $alasCombo = $productRow['intake_id'] . "-" . $productRow['cut_id'] . "-" . $productRow['nationality_id'];
            if (!array_key_exists($alasCombo,$knownCombo))
            {
                $knownCombo[$alasCombo] = $alasCombo;
                $products[] = $productRow;
            }
        }
    usort($products, function ($item1,$item2){
        return $item2['cut_id'] <=> $item1['cut_id'];
    });
    $products2 = null;
    $productsCount = count($products);
    $overallQuantity =0;
    $overallWeight =0;
    foreach($products as $productsRow){
        $thisclass = 'thisclass'.rand(1,999999);
        $class = 'KIS'.rand(1,999999);
        $pallet_id = $productsRow['pallet_id'];
        $cut_id = $productsRow['cut_id'];
        $temp_id = $productsRow['cooling_id'];
        $ubbb = $productsRow['ubbb'];
        $smallestDate = ($productsRow['range_extension']!= null && $productsRow['range_extension']!= '')?$productsRow['range_extension']:$productsRow['range_from'];
        $largestDate = ($productsRow['range_extension']!= null && $productsRow['range_extension']!= '')?$productsRow['range_extension']:$productsRow['range_to'];

        $intake_id = $productsRow['intake_id'];
        $nationality_id = $productsRow['nationality_id'];
        $local = $productsRow['local'];
        //$cut = getCut($productsRow['cut_id']);
        $cut = $productsRow['cutname'];
        $showComments = Species::find($productsRow['species_id'])->show_comments;
        if($ubbb == 0){
            $ubtext = 'UB';
        }else if($ubbb == 1){
            $ubtext = 'BB';
        }else{
            $ubtext = 'N/A';
        }


        if ($initial_pallet_id != null && $initial_pallet_id != "")
        {
            $productsX2 = "SELECT * , product.id productid FROM `product`
            INNER JOIN `pallet`
            ON product.pallet_id=pallet.id
            WHERE pallet.id = ?
            && product.cut_id = ?
            && product.nationality_id = ?
            AND pallet.storage_location IN ($locs)
            AND pallet.is_hidden = 0
            ORDER BY product.cut_id DESC";
            $pX2d = [$initial_pallet_id,$cut_id,$nationality_id];
        }
        else
        {
            $productsX2 = "SELECT * , product.id productid FROM `product`
            INNER JOIN `pallet`
            ON product.pallet_id=pallet.id
            WHERE pallet.intake_id = ?
            && product.cut_id = ?
            && product.nationality_id = ?
            AND pallet.storage_location IN ($locs)
            AND pallet.is_hidden = 0
            ORDER BY product.cut_id DESC";
            $pX2d = [$intake_id,$cut_id,$nationality_id];
        }
        $productsY2 = prepareExecuteQuery($productsX2,'iss',$pX2d);
        $products2Count = mysqli_num_rows($productsY2);


        ###
        $products2 =  mysqli_fetch_all($productsY2, MYSQLI_ASSOC);

        $product2_palletids = array();
        $product2_cutids = array();
        $product2_productids = array();
        $product2_brands = array();
        $product2_nationalities = array();
        $product2_temperatures = array();
        $product2_dateranges = array();
        $product2_quantity = 0;
        $startdates = array();
        $enddates = array();
        foreach ($products2 as $product2)
        {
            $product2_palletids[]= $product2['pallet_id'];
            $product2_cutids[]= $product2['cut_id'];
            $product2_productids[]= $product2['productid'];
            $numOfWeights = numWeightsAvailableFromProductID($product2['productid']);
            if($product2['akg'] != ''){
                    $this_row_weight = totalWeightOfAdvisedKGProduct($intake_id,$product2['nationality_id']);
            }else{
                $this_row_weight = weightSoldFromProductID($product2['productid']);
            }
            if($product2['grosspallet'] == 1){
                if($this_row_weight != 0){
                    $product2_quantity = $product2_quantity + $numOfWeights;
                    }
            }
            if ($numOfWeights == 0) continue;
            if($numOfWeights > 0){
                $product2_brands[]= $product2['brand_id'];
                $product2_nationalities[]= $product2['nationality_id'];
                array_push($product2_temperatures, $product2['cooling_id']);
                if ($product2['range_extension'] == null || $product2['range_extension'] == ''){
                    array_push($product2_dateranges, $product2['range_from'] .'-'. $product2['range_to']);
                    if ($product2['range_to'] != "")$enddates[]=$product2['range_to'];
                    if ($product2['range_from'] != "")$startdates[]=$product2['range_from'];
                }
                else {
                    array_push($product2_dateranges, $product2['range_extension'] .'-'. $product2['range_extension']);
                    if ($product2['range_extension'] != ""){
                        $startdates[]=$product2['range_extension'];
                        $enddates[]=$product2['range_extension'];
                    }
                }

            }

        }
        //if ($product2_quantity == 0) continue;
        $uniqueBrands = count(array_unique($product2_brands));
        $uniqueNationalities = count(array_unique($product2_nationalities));
        $uniqueTemperatures = count(array_unique($product2_temperatures));
        $uniqueDateranges = count(array_unique($product2_dateranges));
        $bgCol = '';
        $earliestStartDate = '';
        $latestEndDate = '';
        if ($uniqueTemperatures == 0)continue;
        if($uniqueDateranges == 1){
            $earliestStartDate = explode("-",$product2_dateranges[0])[0];
            $latestEndDate = explode("-",$product2_dateranges[0])[1];
        }
        else if ($uniqueDateranges>0) {
            $earliestStartDateTS=DateTime::createFromFormat('d/m/Y',$startdates[0])->getTimestamp();
            foreach($startdates as $startdate){
                $internalTS = DateTime::createFromFormat('d/m/Y',$startdate)->getTimestamp();
                if ($internalTS < $earliestStartDateTS) $earliestStartDateTS = $internalTS;
            }
            $internalDate = new DateTime();
            $internalDate->setTimestamp($earliestStartDateTS);
            $earliestStartDate = $internalDate->format('d/m/Y');

            $latestEndDateTS=DateTime::createFromFormat('d/m/Y',$enddates[0])->getTimestamp();
            foreach($enddates as $enddate){
                $internalTS = DateTime::createFromFormat('d/m/Y',$enddate)->getTimestamp();
                if ($internalTS > $latestEndDateTS) $latestEndDateTS = $internalTS;
            }
            $internalDate = new DateTime();
            $internalDate->setTimestamp($latestEndDateTS);
            $latestEndDate = $internalDate->format('d/m/Y');
        }
        if ($product2['range_extension'] == null || $product2['range_extension'] == '')
        {
            $latestEndDate2 = $latestEndDate;
        }
        else
        {
            $latestEndDate2 = "EXTENSION";
        }
        $state = 0;
        if($ubbb != 2 && $earliestStartDate != "" && $latestEndDate != ""){
            $toDate = DateTime::createFromFormat('d/m/Y',$earliestStartDate)->getTimestamp();
            $toDate2 = DateTime::createFromFormat('d/m/Y',$latestEndDate)->getTimestamp();
            if ($toDate2 < $toDate) $toDate = $toDate2;
            $cutResult= CutGroupNationalityDate::lookupFromProductID($product2_productids[0]);

            if ($temp_id == 1)
            {
                $bgCol = '';
                $now = time();
                if (isset($cutResult['warning']) && $cutResult['warning'] != "")
                {
                    $pastWarning1 = $toDate - ($cutResult['warning'] * 86400);
                    if ($pastWarning1 <= $now)
                    {
                        $bgCol = 'style="background-color:#FFBF00"';
                        $state = 1;
                    }
                }
                if (isset($cutResult['danger']) && $cutResult['danger'] != "")
                {
                    $pastWarning2 = $toDate - ($cutResult['danger'] * 86400);
                    if ($pastWarning2 <= $now)
                    {
                        $bgCol = 'style="background-color:red"';
                        $state = 2;
                    }
                }
                $pastWarning3 = $toDate;
                if ($pastWarning3 <= $now)
                {
                    $bgCol = 'style="background-color:darkred"';
                    $state = 2;
                }
            }
        }
        if ($timeSensitivityStatus > 0 &&  $state != $timeSensitivityStatus) continue;
        if($product2_quantity != 0) $quantityTotal = $product2_quantity;
        else  $quantityTotal = countNumProductsForCutOnPalletArrays(array_unique($product2_palletids), [$product2_cutids[0]], $nationality_id);

        if($quantityTotal < 1){continue;}
        ###

        $totalW += $this_row_weight;
        $totalProducts = weightsAvailableOnProduct($productsRow['productid']);
        //$numOfWeights = countNumProductsForCutOnPalletThatIsntPicked($pallet_id, $cut_id);

        $totalWeightOfProduct = totalWeightOfProduct($product2_productids);

        if($productsRow['cost'] == '0.00' || $productsRow['cost'] == ''){
            $locked = true;
            $lockedT = "y";
        }
        else {
            $locked = false;
            $lockedT = "n";
        }
        if($totalWeightOfProduct < 1 && $productsRow['unit'] != 'PPC'){ continue; }
        if($productsRow['unit'] == 'PPC')$totalWeightOfProduct = $quantityTotal;
        $overallQuantity = $overallQuantity + $quantityTotal;
        $overallWeight = $overallWeight + $totalWeightOfProduct;
        $currentCost = (float)$productsRow['cost'];
        $overallCost = $overallCost + ($totalWeightOfProduct * $currentCost);
        ?>
        <tr <?php if(isset($product2_dateranges[0])) echo $bgCol; ?> class="searchAccordTitle <?php if($locked){ echo 'locked'; } ?>">
            <?php echo "<!-- ".$product2['productid']." -->"; ?>
            <td colspan="1">
            <?php if ($showEditIntake) {?>
            <a class="intakeLink" id="<?php echo $intake_id ?>" href="intake.php?id=<?php echo $intake_id; ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;">
            <?php } else {?><div class="intakeLink"><?php }?>
                    <?php if($locked){ ?>
                        <i class="fa fa-lock"></i>
                    <?php } ?>

                    <b><?php echo $intake_id; ?></b>
                    <?php if ($showEditIntake) {?></a>
                    <?php } else {?></div><?php }?>
			</td>
            <td colspan="1">
             &nbsp;
            </td>
            <td colspan="1"  onclick=""></td>
           <td width="40" align="center" class="<?php echo $thisclass; ?>" onclick="toggleRow('<?php echo $class; ?>', this,'<?php echo $intake_id; ?>','<?php echo $productsRow['cut_id']; ?>','<?php echo $nationality_id;?>','<?php echo (!empty($initial_pallet_id)) ? $pallet_id : $initial_pallet_id; ?>','<?php echo $ubbb;?>','<?php echo $lockedT; ?>','<?php echo $site_id; ?>');"><?php if($products2Count > 0){ ?><i class="searchRContent__icon fa fa-chevron-down"></i><?php } ?></td>
            <td width="40" align="center" onclick="toggleVisibleRow('<?php echo $class; ?>')" style="display:none"><?php if($products2Count > 0){ ?><i class="searchRContent__icon fa fa-chevron-down"></i><?php } ?></td>
            <td class="bold" colspan="1"><?php echo $quantityTotal; ?></td>
            <?php
            // ??: No need to call the database on every loop.
            // ??: The temperatures are just a few entries.
            // ??: Better to get all the entries in the beginning

                if($uniqueTemperatures > 1){
                    ?><td style="background:grey;color:#fff;padding:5px;">Mixed</td><?php
                }else{
                    ?><td <?php if($temp_id == 1){ echo 'style="background:#c0392b;color:#fff;padding:5px;"'; }else { echo 'style="background:#2980b9;color:#fff;padding:5px;"'; } ?>><?php if(isset($product2_temperatures[0])) echo getTemp($product2_temperatures[0]);//echo getTemp($temp_id);?></td><?php
                }
            ?>
            <td class="bold" colspan="1"><?php echo $cut; ?></td>
			<td colspan="1">
                <?php
                 // ??: Same as with temperatures - get all entries in the beginning
                    if($uniqueNationalities > 1){
                        //echo '--';
                        echo 'Various';
                    }else{
                        //echo getNationality($productsRow['nationality_id']);
                        echo $local;
                    }
                ?>
            </td>
			<td colspan="1">
				<form method="post">
					<textarea name="comments" class="overviewcomment" productid="<?php echo $productsRow['productid']; ?>" <?php if($showComments == false) echo "disabled" ?>><?php echo $productsRow['weightnote']; ?></textarea>
					<?php if ($showComments == true) { ?><i class="fa fa-save" onclick="saveOverViewComment(<?php echo $productsRow['productid']; ?>)"></i> <?php } ?>
					<input type="text" name="pallet_id" class="pallet" value="<?php echo $pallet_id; ?>" style="display:none;">
				</form>
			</td>
			<td>
                <?php
                    if($uniqueBrands > 1){
                        //echo '--';
                        echo 'Various';
                    }else{
                        //echo getBrand($productsRow['brand_id']);
                        if(isset($product2_brands[0])) echo getBrand($product2_brands[0]);
                    }
                ?>
            </td>

            <?php
                if($ubbb != 2){

                    if($earliestStartDate != "" && $latestEndDate != "") echo '<td>'.$ubtext . ' ' . $earliestStartDate.' - '.$latestEndDate2.'</td>';
                    else echo '<td>--</td>';
                }else{
                    echo '<td>'.$ubtext.'</td>';
                }
            ?>
            <td class="bold"><?php

                if($productsRow['grosspallet'] == 1){
                    echo '[GT] ';
                }
                if($productsRow['unit'] == 'PPC'){
                    ?><b>PPC</b><?php
                }else{
                    if($productsRow['akg'] != ''){
                        echo totalWeightOfAdvisedKGProduct($intake_id, $productsRow['nationality_id']);
                    }else{
                        echo $totalWeightOfProduct;
                    }

                    echo 'kg';
                }

 				?></td>
            <td class="bold">
                <?php if ($productsRow['cost']) {
                    echo '£' . number_format((float)$productsRow['cost'], 2, '.', '');
                } ?>
            </td>
            <td class="bold">
                <?php if ($productsRow['rrp1'] != null && $productsRow['rrp1'] != '') {
                    echo '£' . number_format((float)$productsRow['rrp1'], 2, '.', '');
                } ?>
            </td>
            <td class="bold">
                <?php if ($productsRow['rrp2'] != null && $productsRow['rrp2'] != '') {
                    echo '£' . number_format((float)$productsRow['rrp2'], 2, '.', '');
                } ?>
            </td>
            <td class="bold">
                <?php if ($productsRow['rrp3'] != null && $productsRow['rrp3'] != '') {
                    echo '£' . number_format((float)$productsRow['rrp3'], 2, '.', '');
                } ?>
            </td>
            <?php if (User::find(Auth::id())->hasPermission("viewcosts")) { ?><td class="bold" style="font-weight:normal;font-size:10px;"><?php if($productsRow['price']){ echo '£' . number_format((float)$productsRow['price'], 2, '.', ''); } ?></td><?php } ?>
            <td></td>
        </tr>
    <?php  ?>

    <?php
    }
    ?>
    <tr class="searchAccordTitle">
    <td colspan="1">
        <div class="intakeLink" style="color:#000;text-decoration:underline;">
            <b>Totals</b>
        </div>
    </td>
    <td colspan="1">
     &nbsp;
    </td>
    <td colspan="1"  onclick=""></td>
   <td width="40" align="center"></td>
    <td class="bold" colspan="1"><?php echo $overallQuantity; ?></td>
    <td></td>
    <td class="bold" colspan="1"></td>
    <td colspan="1">
    </td>
    <td colspan="1">
    </td>
    <td>
    </td>
    <td></td>
    <td class="bold"><?php echo number_format(floorDec($overallWeight,3),3,".",",") . "kg"; ?></td>
    <td class="bold"><?php echo "£".number_format(floorDec($overallCost,2),2,".",","); ?></td>
    <td class="bold"></td>
    <td></td>
</tr>


<script type="text/javascript">


function getCookie(name) {
var value = "; " + document.cookie;
var parts = value.split("; " + name + "=");
if (parts.length == 2) return parts.pop().split(";").shift();
}

$('.searchRHeading').click(function(){
$(this).next('.searchRContent').toggle();
});

var firstExecution = 0
var interval = 1000

function checkStockAvailabile(product_id, pallet_id, cut_id, theClass, date, event){
    $.get("ajax/checkProductStockQuantity.php?product_id=" + product_id, function(num, status){

        var quantitySelected = parseInt($('#quantity-' + product_id + '-' + pallet_id).val());
        var howManyLeft = parseInt(num);
        var COOKIE_NAME = "quantity-"+product_id+"-"+pallet_id;
        if(getCookie(COOKIE_NAME)){
            Swal.fire({
                title: "This has already been added to the sale.",
                text: "Please remove this from sale to re-add this item.",
                icon: "warning",
                showCancelButton: false,
                showConfirmButton: false,
                dangerMode: true,
                showCloseButton: true
            });
        }else{
            if(howManyLeft >= quantitySelected){
                addToSheet(product_id, pallet_id, cut_id, theClass, event);
            }else{
                Swal.fire({
                    title: "This has already been sold",
                    text: "Please search stock again to view available items",
                    icon: "warning",
                    showCancelButton: false,
                    showConfirmButton: false,
                    dangerMode: true,
                    showCloseButton: true
                });
            }
        }
    });
}


function addToSheet(product_id, pallet_id, cut_id, theClass, date, event){

    var milliseconds = new Date().getTime()

    if ((milliseconds - firstExecution) > interval) {
        var q = $('#quantity-' + product_id + '-' + pallet_id).val();
        var comment = $('#comment-' + product_id + '-' + pallet_id).val();


        // console.log(comment);

        var COOKIE_NAME = "quantity-"+product_id+"-"+pallet_id;
        // console.log('Looking for cookie......:' + COOKIE_NAME);


        if(getCookie(COOKIE_NAME)){
            // console.log('we got cookie');

            var howMany = getCookie(COOKIE_NAME);

            var x = Number(howMany)+Number(q);
            document.cookie = COOKIE_NAME + "=" + x;
            // console.log(howMany);

        }else{
            // console.log('setting cookie!');
            document.cookie = COOKIE_NAME + "=" + q;
        }

        var howManyBefore = $('#quantity-' + product_id + '-' + pallet_id).children('option').length;

        if(howManyBefore > q){
            for(i=0; i < q; i++){
                $("#quantity-" + product_id + "-" + pallet_id + " option:last").remove();
            }
        }else{
            for(i=0; i < q; i++){
                $("#quantity-" + product_id + "-" + pallet_id + " option:last").remove();
                $("#quantity-" + product_id + "-" + pallet_id).parent().parent().css('opacity','0.6');
                $("#quantity-" + product_id + "-" + pallet_id).parent().parent().css('pointer-events','none');
            }
        }

        var howManyAfter = $('#quantity-' + product_id + '-' + pallet_id).children('option').length;

        $('#quantity-' + product_id + '-' + pallet_id).val($('#quantity-' + product_id + '-' + pallet_id + ' option:last').val());
        if (<?php echo request()->has('customerID') ? '1' : '0'; ?> == 1)
        {
            var postData1 = {product_id:product_id, pallet_id:pallet_id,cut_id:cut_id,q:q,comment:comment,date:date,customer_id:<?php echo $customer_id;?>};
        }
        else
        {
            var postData1 = {product_id:product_id, pallet_id:pallet_id,cut_id:cut_id,q:q,comment:comment,date:date};
        }
        $.get( "scripts/getBasketItem.php", postData1, function( data ) {
            $('.basketTable').append(data);
            updatePrices();
            setCustomerCreditFeedback();
        });
        firstExecution = milliseconds
    }

}

function toggleWeight(weightdiv){
if($(weightdiv).hasClass('activeWeight')){
    var weight = $(weightdiv).attr('weight');
    var product_id = $(weightdiv).attr('product_id');
    calculateWeight(-weight);
    removeFromList(product_id);

}else{
    var weight = $(weightdiv).attr('weight');
    var product_id = $(weightdiv).attr('product_id');
    calculateWeight(weight);
    addToList(product_id);
}

$(weightdiv).toggleClass('activeWeight');

}

function calculateWeight(value){
var currentWeight = $('.weightVal').text();

var newWeight = parseFloat(currentWeight) + parseFloat(value);

$('.weightVal').text(newWeight);

}
function saveOverViewComment(productid){
	var currentComment = $('[productid="'+productid+'"]').val();
	 $.ajax({
		method: "POST",
		url: "ajax/saveCommentPicker.php",
		data: {
			comment:currentComment,
			productid:productid
		},
	});
    alert("Done!");
}
$(document).ready(function(){

    $('.overviewcomment').focus(function() {
        $(this).height($(this)[0].scrollHeight)
    })

    $('.overviewcomment').blur(function() {
        $(this).height(47)
    })

$.each(document.cookie.split(/; */), function()  {
  var splitCookie = this.split('=');


    if(splitCookie[0].includes('quantity-')){
        console.log(splitCookie[0]);
        var q = splitCookie[1];

        var howManyBefore = $('#' + splitCookie[0]).children('option').length;

        if(howManyBefore > q){
            for(i=0; i < q; i++){
                $('#' + splitCookie[0] + " option:last").remove();
            }
        }else{
            for(i=0; i < q; i++){
                $('#' + splitCookie[0] + " option:last").remove();
                $('#' + splitCookie[0]).parent().parent().css('opacity','0.3');
                $('#' + splitCookie[0]).parent().parent().css('pointer-events','none');
            }
        }
    }
});

$('.quantitybox').change(function(){

     $('.subrow').removeClass('activeRedRow');
    $(this).parent().parent().addClass('activeRedRow');
 });
});
</script>
<style type="text/css">
.weightbox{
padding:10px;
border:2px solid #cacaca;
display:inline-block;
cursor:pointer;
margin-bottom:5px;
}
.activeWeight { background:#3faddd !important; color:#fff !important}
.weightbox:hover{
background:#cacaca;
}
</style>

<?php
function perfcheck()
{
    global $timeStamp;
    $timeStamp2 = microtime(true);
    echo '<br>script execution time: ' . ($timeStamp2 - $timeStamp);
}
// perfcheck();

?>
