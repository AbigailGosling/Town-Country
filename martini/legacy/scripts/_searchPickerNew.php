<?php

use App\Models\Location;
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
    if (request()->input('locked') == "y"){
        $locked = true;
    }
    else {
        $locked = false;
    }
    
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
                    if (Location::find($productsRow2['storage_location'])->name == "Coldstore" || $locked){
                        $class = request()->input('class') . " searchAccordTitle locked";
                    }
                    else {
                        $class = request()->input('class');
                    }
                    $temp_id = $productsRow2['cooling_id'];
                    $smallestDate = $productsRow2['range_from'];
                    $largestDate = ($productsRow2['range_extension']!= null && $productsRow2['range_extension']!= '')?$productsRow2['range_extension']:$productsRow2['range_to'];
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
                    if($ubbb != 2 && $temp_id == 1){
                        $toDate = DateTime::createFromFormat('d/m/Y',$smallestDate)->getTimestamp();
                        $toDate2 = DateTime::createFromFormat('d/m/Y',$largestDate)->getTimestamp();
                        if ($toDate2 < $toDate) $toDate = $toDate2;
                        $cutQuery = mysqli_query($mysqli,"SELECT * FROM `cuts` WHERE id = ".$cut_id);
                        $cutResult= mysqli_fetch_assoc($cutQuery);
                        $bgCol = "";
                        if ((isset($cutResult['warning']) && $cutResult['warning'] != "")||(isset($cutResult['danger']) && $cutResult['danger'] != "")) 
                        {
                            $now = time();
                            $alreadyFlagged = false;
                            if (isset($cutResult['warning']) && $cutResult['warning'] != "")
                            {
                                $pastWarning1 = $toDate - ($cutResult['warning'] * 86400);
                                if ($pastWarning1 <= $now)
                                {
                                    $bgCol = 'background-color:#FFBF00"';
                                }
                            }
                            if (isset($cutResult['danger']) && $cutResult['danger'] != "")
                            {
                                $pastWarning2 = $toDate - ($cutResult['danger'] * 86400);
                                if ($pastWarning2 <= $now)
                                {
                                    $bgCol = 'style="background-color:red"';
                                    $alreadyFlagged = true;
                                }
                            }
                        }
                    }
                    ?>
                    <tr <?php if(isset($smallestDate)) echo $bgCol; ?>class="subrow <?php echo $class; ?>">
                    <td colspan="1">
                    <?php if(Location::find($productsRow2['storage_location'])->name == "Coldstore" || $locked){ ?>
                        <i class="fa fa-lock"></i>
                    <?php } ?>
                        <?php echo $numInPicking; ?>
                    <a href="intake.php?id=<?php echo intakeIDfromPalletID($pallet_id); ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;"><b><?php echo intakeIDfromPalletID($pallet_id); ?></b></a></td>
                    <td colspan="1">
                        <form method="post">
                        <?php
                            $unit11 = "";
                            $unit13 = "";
                            $unit23 = "";
                            $unitGatwick = "";
                            $unitDry = "";
                            $DirectDelivery = "";
                            $otherLoc = "";
                            $coldstore = "";
                            
                            switch (Location::find($productsRow2['storage_location'])->name)
                            {
                                case "Unit 11":
                                    $unit11 = " selected";
                                    break;
                                case "Unit 13 - 14":
                                    $unit13 = " selected";
                                    break;
                                case "Unit 23":
                                    $unit23 = " selected";
                                    break;
                                case "Gatwick":
                                    $unitGatwick = " selected";
                                    break;
                                case "Dry Store":
                                    $unitDry = " selected";
                                    break;
                                case "Unit 15 - 17":
                                    $unit15 = " selected";
                                    break;
                                case "Direct Drop":
                                    $DirectDelivery = " selected";
                                    break;
                                case "Other":
                                    $otherLoc = " selected";
                                    break;
                                case "Coldstore":
                                    $coldstore = " selected";
                                    break;
                            }
                        ?>
                        <select style="width:100%" name="location">
                                <option></option>
                                <option <?php echo $unit11; ?>>Unit 11</option>
                                <option <?php echo $unit13; ?>>Unit 13 - 14</option>
                                <option <?php echo $unit23; ?>>Unit 23</option>
                                <option <?php echo $unitGatwick; ?>>Gatwick</option>
                                <option <?php echo $unitDry; ?>>Dry Store</option>
                                <option <?php echo $unit15; ?>>Unit 15 - 17</option>			
                                <option <?php echo $DirectDelivery; ?>>Direct Drop</option>
                                <option <?php echo $coldstore; ?>>Coldstore</option>
			                    <option <?php echo $otherLoc; ?>>Other</option>
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
                            <textarea name="pallet-comment" class="overviewcomment"><?php echo $pallet_comments; ?></textarea>
                            <input type="text" name="pallet_id" class="pallet" value="<?php echo $productsRow2['pallet_id']; ?>" style="display:none;">
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
                        
                        
                        if(isset($smallestDate)) echo '<td '.$bgCol.'>'.$ubtext . ' ' . $smallestDate . '-' . $largestDate.'</td>';
                        else echo '<td>--</td>';
                    }else{
                        echo '<td>'.$ubtext . ' ' . $smallestDate . ' - ' . $largestDate.'</td>';
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
                    <?php if(Location::find($productsRow2['storage_location'])->name != "Coldstore" && $locked != true){ ?>
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
        
        $('textarea[name="pallet-comment"]') .each(function()
        {
            $(this).on('keypress',function(e) 
            {
                if(e.keyCode == 13) 
                {
                    var location = $(this).parent().find('textarea[name="pallet-comment"]').val();
                    var pallet = $(this).parent().find('[name="pallet_id"]').val();
                    console.log(location);
                    console.log(pallet);
                    $.post("ajax/loggedDataChange.php", {'body': location, 'entity_id': pallet, 'type':'pallet'}, function(data, status){
                        console.log(data);
                    });
                }
            });
        });
    });

 </script>