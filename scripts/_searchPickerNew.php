<?php
    ini_set('memory_limit','16M');
	require('../functions.php');
	
	$intake_id = $_GET['intake_id'];
    $pallet_id = $_GET['pallet_id'];
	$cut_id = $_GET['cut_id'];
	$class = $_GET['class'];
	$nationality_id = $_GET['nationality_id'];
    $ubbb = $_GET['ubbb'];
    
    if(!empty($pallet_id)){
        $palletFilter = 'product.pallet_id = '.$pallet_id;
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
        WHERE pallet.intake_id='$intake_id' 
        && product.cut_id = '$cut_id'
		&& product.nationality_id='$nationality_id'
        && ".$palletFilter."
        ORDER BY product.cut_id DESC";
        
        $productsY2 = mysqli_query($conn, $productsX2) or die(mysqli_error($conn));
        $products2Count = mysqli_num_rows($productsY2);
        ####
        $totalW = 0;
        
        $relatedWeights = [];
        
        array_map(function($weight) use ($productsRow, &$relatedWeights){
            
            if($weight["product_id"] == $productsRow["productid"])
            {
                $relatedWeights[] = $weight; 
            }

        },$weights);
        
        
        array_map(function($weight) use (&$totalW, $productsRow){
           
           
	        $w = 0;
			if($weight['weight_tear'] == $weight['weight_gross']){
				$w = $weight['weight_gross'];
			}else{
				$w = $weight['weight_gross'] - $weight['weight_tear'];
			}
			
			$totalW = $totalW + $w;
 			
		
        },$relatedWeights);
 		if($products2Count > 0){
			while($productsRow2 = mysqli_fetch_array($productsY2)){

                $numOfWeights = numWeightsAvailableFromProductID($productsRow2['productid']);
                if($numOfWeights > 0){
                    $temp_id = $productsRow2['cooling_id'];
                    $smallestDate = $productsRow2['range_from'];
                    $largestDate = $productsRow2['range_to'];
                    $pallet_id = $productsRow2['pallet_id'];
                    $product_id = $productsRow2['productid'];
                    ?>
                    <tr class="subrow <?php echo $class; ?>">
                    <td colspan="1">
                        <?php echo $numInPicking; ?>
                    <a href="intake.php?id=<?php echo intakeIDfromPalletID($pallet_id); ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;"><b><?php echo intakeIDfromPalletID($pallet_id); ?></b></a></td>
                    <td colspan="1">
                        <form method="post">
                            <input type="text" name="location" class="location-input" value="<?php echo $productsRow2['storage_location']; ?>" >
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
                    <td colspan="1"></td>
                    <td><?php echo getBrand($productsRow2['brand_id']); ?></td>
                    <td><?php echo $ubtext . ' ' . $smallestDate . ' - ' . $largestDate; ?></td>
                    
                    <td class="bold"><?php 
                    
                    if($productsRow2['grosspallet'] == 1){
                        echo '[GT] ';
                    }
                    if($productsRow2['unit'] == 'PPC'){
                        ?><b>PPC</b><?php
                    }else{
                        if($productsRow2['akg'] != ''){
                            echo totalWeightOfAdvisedKGProduct($intake_id);
                        }else{
                            echo weightSoldFromProductID($product_id);
                        }

                        echo 'kg';
                    }
                    ?></td>
                    <td></td>
                    <td></td>
                    <td><a href="javascript:;" class="plusButton" onclick="checkStockAvailabile('<?php echo $productsRow2['productid']; ?>','<?php echo $productsRow2['pallet_id']; ?>','<?php echo $productsRow2['cut_id']; ?>','<?php echo $class; ?>');"><i class="fa fa-plus" style="font-size:24px;color:#000;"></i></a></td>
                </tr>
                <?php
              }
            }
		}
 ?>
 <script>
    
    $(document).ready(function(){
        $('.location-input').each(function(){
            $(this).on('keypress',function(e) {
                if(e.which == 13) {
                    
                    var location = $(this).parent().find('.location-input').val();
                    var pallet = $(this).parent().find('.pallet').val();
                    
                    $.get("<?php echo $domain; ?>ajax/saveLocation.php?location="+location+'&pallet='+pallet, function(data, status){
                        console.log(data);
                    });
                }
            });
        });
    });

 </script>