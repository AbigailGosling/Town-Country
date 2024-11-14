<?php

use App\Models\CutGroupNationalityDate;
use App\Models\Location;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

    ini_set('memory_limit','32M');
	require(__DIR__.'/../functions.php');

	$intake_id = request()->input('intake_id');
    $pallet_id = request()->input('pallet_id');
	$cut_id = request()->input('cut_id');
	$class = request()->input('class');
	$nationality_id = request()->input('nationality_id');
    $ubbb = request()->input('ubbb');
    $timeSensitivityStatus = (int)request()->input('time',0);
    if ($timeSensitivityStatus == null) $timeSensitivityStatus = 0;
    if (request()->input('locked') == "y"){
        $locked = true;
    }
    else {
        $locked = false;
    }
    $showEditIntake = (User::find(Auth::id())->hasPermission("intakeList.php"));
    if(!empty($pallet_id)){
        $palletFilter = 'product.pallet_id = '.$mysqli->real_escape_string($pallet_id);
    }else{
        $palletFilter = 'true';
    }

    $totalW = 0;


        if($ubbb == 0){
            $ubtext = 'UB';
        }else if($ubbb == 1){
            $ubtext = 'BB';
        }else{
            $ubtext = 'N/A';
        }

        ####
        $productsX2 = "SELECT * , product.id productid
        FROM `product`
        INNER JOIN `pallet`
        ON product.pallet_id=pallet.id
        WHERE pallet.intake_id= ?
        && product.cut_id = ?
		&& product.nationality_id= ?
        && ".$palletFilter."
        ORDER BY product.cut_id DESC";

        $productsY2 = prepareExecuteQuery($productsX2,'iii',[$intake_id,$cut_id,$nationality_id]);
        $products2Count = mysqli_num_rows($productsY2);
        ####
        $totalW = 0;

        $relatedWeights = [];

        foreach($weights as $weight){

            if($weight["product_id"] == $productsRow["productid"])
            {
                $relatedWeights[] = $weight;
            }

        }


        foreach($relatedWeights as $weight){


	        $w = 0;
			if($weight['weight_tear'] == $weight['weight_gross']){
				$w = $weight['weight_gross'];
			}else{
				$w = $weight['weight_gross'] - $weight['weight_tear'];
			}

			$totalW = $totalW + $w;


        }
 		if($products2Count > 0){
			while($productsRow2 = mysqli_fetch_array($productsY2)){
                $numOfWeights = numWeightsAvailableFromProductID($productsRow2['productid']);
                if($numOfWeights > 0){
                    if (stripos(Location::find($productsRow2['storage_location'])->name, "coldstore")!=false || $locked){
                        $class = request()->input('class') . " searchAccordTitle locked";
                    }
                    else {
                        $class = request()->input('class');
                    }
                    $temp_id = $productsRow2['cooling_id'];
                    $smallestDate = ($productsRow2['range_extension']!= null && $productsRow2['range_extension']!= '')?$productsRow2['range_extension']:$productsRow2['range_from'];
                    $largestDate = ($productsRow2['range_extension']!= null && $productsRow2['range_extension']!= '')?$productsRow2['range_extension']:$productsRow2['range_to'];
                    $largestDate2 = ($productsRow2['range_extension']!= null && $productsRow2['range_extension']!= '')?"EXTENSION":$productsRow2['range_to'];
                    $pallet_id = $productsRow2['pallet_id'];
                    $product_id = $productsRow2['productid'];
                    $pallet_comments_query_sql = "SELECT `body` FROM `comment_logging` WHERE `type` = 'pallet' AND `entity_id` = $pallet_id ORDER BY `id` DESC LIMIT 1";
                    $pallet_comments_query = mysqli_query($mysqli,$pallet_comments_query_sql);
                    $pallet_comments = "";
                    if (mysqli_num_rows($pallet_comments_query) > 0)
                    {
                        $pallet_comments = mysqli_fetch_assoc($pallet_comments_query);
                        $pallet_comments = $pallet_comments['body'];
                    }
                    if($productsRow2['akg'] != ''){
                        $this_row_weight = totalWeightOfAdvisedKGProduct($intake_id,$nationality_id);
                    }else{
                        $this_row_weight = weightSoldFromProductID($product_id);
                    }

                    if($productsRow2['grosspallet'] == 1){
                        if($this_row_weight == 0){ continue; }
                    }
                    $state = 0;
                    if($ubbb != 2 && $temp_id == 1){
                        $toDate = DateTime::createFromFormat('d/m/Y',$smallestDate)->getTimestamp();
                        $toDate2 = DateTime::createFromFormat('d/m/Y',$largestDate)->getTimestamp();
                        if ($toDate2 < $toDate) $toDate = $toDate2;
                        $cutResult= CutGroupNationalityDate::lookupFromProductID($productsRow2['productid']);
                        $bgCol = "";
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
                    if ($timeSensitivityStatus > 0 &&  $state != $timeSensitivityStatus) continue;
                    ?>
                    <tr <?php if(isset($smallestDate)) echo $bgCol; ?>class="subrow <?php echo $class; ?>">
                    <td colspan="1">
                    <?php if(stripos(Location::find($productsRow2['storage_location'])->name,"coldstore")!=false || $locked){ ?>
                        <i class="fa fa-lock"></i>
                    <?php } ?>
                        <?php echo $numInPicking; ?>
                    <?php if ($showEditIntake) {?><a href="intake.php?id=<?php echo intakeIDfromPalletID($pallet_id); ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;"><b><?php echo intakeIDfromPalletID($pallet_id); ?></b></a></td>
                    <?php } else { echo intakeIDfromPalletID($pallet_id);}?>
                        <td colspan="1">
                        <form method="post">

                        <select style="min-width: 95px;width:100%" name="location"<?php if (!$showEditIntake) { echo "disabled";}?>>
                                <?php
                                echo Site::generateOldHTMLList(Location::find($productsRow2['storage_location'])->name);
                                ?>
                        </select>
                            <input type="text" name="pallet_id" class="pallet" value="<?php echo $productsRow2['pallet_id']; ?>" style="display:none;">
                        </form>
                    </td>
                    <td colspan="1"><?php echo $productsRow2['pallet_id']; ?></td>
                    <td></td>
                    <td colspan="1">
                        <select class="quantitybox" id="quantity-<?php echo $productsRow2['productid']; ?>-<?php echo $productsRow2['pallet_id']; ?>">
                            <?php for($i=1;$i<$numOfWeights+1;$i++){?>
                                <option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td <?php if($temp_id == 1){ echo 'style="background:#a02f24;color:#fff;"'; }else { echo 'style="background:#2980b9;color:#fff;"'; } ?>><?php echo getTemp($temp_id); ?></td>
                    <td class="bold" colspan="1"><?php echo getCut($productsRow2['cut_id']); ?></td>
                    <td colspan="1"><?php echo getNationality($productsRow2['nationality_id']);?></td>
                    <td colspan="1">
                        <form method="post">
                            <textarea name="pallet-comment" palletid="<?php echo $productsRow2['pallet_id']."-".$productsRow2['productid']; ?>" class="overviewcomment"><?php echo $pallet_comments; ?></textarea>
                            <input type="text" name="pallet_id" class="pallet" value="<?php echo $productsRow2['pallet_id']; ?>" style="display:none;">
							<i class="fa fa-save" onclick="saveDeepComment(<?php echo $productsRow2['pallet_id']; ?>,<?php echo $productsRow2['productid']; ?>)"></i>
                        </form>
                    </td>
                    <td><?php echo getBrand($productsRow2['brand_id']); ?></td>
                    <?php
                    if($ubbb != 2 && $temp_id == 1){
                        $toDate = DateTime::createFromFormat('d/m/Y',$smallestDate)->getTimestamp();
                        $toDate2 = DateTime::createFromFormat('d/m/Y',$largestDate)->getTimestamp();
                        if ($toDate2 < $toDate) $toDate = $toDate2;
                        $cutQuery = mysqli_query($mysqli,"SELECT * FROM `cuts` WHERE id = ".$cut_id);
                        $cutResult= mysqli_fetch_assoc($cutQuery);
                        $bgCol = "";
                        if ((isset($cutResult['warning']) && $cutResult['warning'] != "")||(isset($cutResult['danger']) && $cutResult['danger'] != ""))
                        {
                            $bgCol = '';
                            $now = time();
                            $alreadyFlagged = false;
                            if (isset($cutResult['warning']) && $cutResult['warning'] != "")
                            {
                                $pastWarning1 = $toDate - ($cutResult['warning'] * 86400);
                                if ($pastWarning1 <= $now)
                                {
                                    $bgCol = 'style="background-color:LemonChiffon"';
                                }
                            }
                            if (isset($cutResult['danger']) && $cutResult['danger'] != "")
                            {
                                $pastWarning2 = $toDate - ($cutResult['danger'] * 86400);
                                if ($pastWarning2 <= $now)
                                {
                                    $bgCol = 'style="background-color:LightPink"';
                                    $alreadyFlagged = true;
                                }
                            }
                        }


                        if(isset($smallestDate)) echo '<td '.$bgCol.'>'.$ubtext . ' ' . $smallestDate . '-' . $largestDate2.'</td>';
                        else echo '<td>--</td>';
                    }else{
                        echo '<td>'.$ubtext . ' ' . $smallestDate . ' - ' . $largestDate2.'</td>';
                    }
                    ?>

                    <td class="bold"><?php

                    if($productsRow2['grosspallet'] == 1){
                        echo '[GT] ';
                    }
                    if($productsRow2['unit'] == 'PPC'){
                        ?><b>PPC</b><?php
                    }else{
                        echo $this_row_weight . 'kg';
                    }
                    ?></td>
                    <td></td>
                    <?php if (User::find(Auth::id())->hasPermission("viewcosts")) { ?><td></td><?php } ?>
                    <td>
                    <?php if(stripos(Location::find($productsRow2['storage_location'])->name, "coldstore")==false && $locked != true){ ?>
                        <a href="javascript:;" class="plusButton" onclick="checkStockAvailabile('<?php echo $productsRow2['productid']; ?>','<?php echo $productsRow2['pallet_id']; ?>','<?php echo $productsRow2['cut_id']; ?>','<?php echo $class; ?>','<?php echo $largestDate; ?>');"><i class="fa fa-plus" style="font-size:24px;color:#000;"></i></a>
                    <?php } ?>
                    </td>
                </tr>
                <?php
              }
            }
		}
 ?>
 <script>
    $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
	function saveDeepComment(pallet_id,product_id){
		var currentComment = $('[palletid="'+pallet_id+'-'+product_id+'"]').val();
		console.log(currentComment);
		var pallet = pallet_id;
		$.ajax({
			method: "POST",
			url: "ajax/loggedDataChange.php",
			data: {body: currentComment, entity_id: pallet_id, type:'pallet'},
		});
        alert("Done!");
	}
    $(document).ready(function()
    {
        $('select[name="location"]') .each(function()
        {
            $(this).change(function() {

                var location = $(this).parent().find('select[name="location"]').val();
                var pallet = $(this).parent().find('[name="pallet_id"]').val();
                console.log(location);
                $.get("ajax/saveLocation.php?location="+location+'&pallet='+pallet, function(data, status){
                    console.log(data);
                });
            });
        });
    });

 </script>
