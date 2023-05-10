<?php
    ini_set('memory_limit','16M');
	require(__DIR__.'/../functions.php');
	
    $intake_id = request()->input('intake_id');
	$cut_id = request()->input('cut_id');
	$class = request()->input('class');
	$nationality_id = request()->input('nationality_id');
  
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
        ORDER BY product.cut_id DESC";
        
        $productsY2 = prepareExecuteQuery($productsX2,'iii',[$intake_id,$cut_id,$nationality_id]);
        $products2Count = mysqli_num_rows($productsY2);
        ####
        $totalW = 0;
        
        $relatedWeights = [];
        
        foreach ($weights as $weight){
            
            if($weight["product_id"] == $productsRow["productid"])
            {
                $relatedWeights[] = $weight; 
            }

        }
        
        
		foreach ($relatedWeights as $weight){
              
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
                $temp_id = $productsRow2['cooling_id'];
				$smallestDate = $productsRow2['range_from'];
				$largestDate = $productsRow2['range_to'];
				$pallet_id = $productsRow2['pallet_id'];
				$product_id = $productsRow2['productid'];
			    ?>
			    <tr class="subrow <?php echo $class; ?>">
				<td colspan="1">
					<a class="intakeLink" href="intake.php?id=<?php echo intakeIDfromPalletID($pallet_id); ?>&ref=salesconfirmationsheet" style="color:#000;text-decoration:underline;">
						<b><?php echo intakeIDfromPalletID($pallet_id); ?></b>
					</a>
				</td>
				<td colspan="1">
					<form method="post">
						<input type="text" name="location" class="location-input" value="<?php echo $productsRow2['storage_location']; ?>" placeholder="location">
						<input type="text" name="pallet_id" class="pallet" value="<?php echo $productsRow2['pallet_id']; ?>" style="display:none;">
					</form>
				</td>
				<td colspan="1"><?php echo $productsRow2['pallet_id']; ?></td>
				<td></td>
				<td colspan="1">
					<?php
						$numOfWeights = numWeightsAvailableFromProductID($productsRow2['productid']);
					?>
					<select class="quantitybox" id="quantity-<?php echo $productsRow2['productid']; ?>-<?php echo $productsRow2['pallet_id']; ?>">
						<?php for($i=1;$i<$numOfWeights+1;$i++){?>
							<option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
						<?php } ?>
					</select>
				</td>
				<td <?php if($temp_id == 1){ echo 'style="background:#a02f24;color:#fff;padding:5px;"'; }else { echo 'style="background:#2980b9;color:#fff;padding:5px;"'; } ?>><?php echo getTemp($temp_id); ?></td>
				<td colspan="1"><?php echo getCut($productsRow2['cut_id']); ?></td>
				<td colspan="1"><?php echo getNationality($productsRow2['nationality_id']);?></td>
				<td colspan="1"></td>
				<td><?php echo getBrand($productsRow2['brand_id']); ?></td>
				<td><?php echo $ubtext . ' ' . $smallestDate . ' - ' . $largestDate; ?></td>
				
				<td><?php 
				
				if($productsRow['akg'] != ''){
					echo $productsRow['akg'];
				}else{
					echo weightSoldFromProductID($product_id);
				}
				?>kg</td>
				<td></td>
				<td></td>
 			</tr>
			<?php
            }
		}
 ?>
  <script>
 $.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': "<?php echo csrf_token();?>" }
	});
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