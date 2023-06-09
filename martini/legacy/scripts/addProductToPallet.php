<?php
	require(__DIR__.'/../functions.php');
	global $mysqli;
	$unit = $mysqli->real_escape_string( request()->input('unit'));
	$intake_id = $mysqli->real_escape_string( request()->input('intake_id'));
	$pallet_id = $mysqli->real_escape_string( request()->input('pallet_id'));
	
	
	$status_id = $mysqli->real_escape_string( request()->input('statuses_id'));
	$ubbb = $mysqli->real_escape_string( request()->input('ubbb'));
	$cut_id = $mysqli->real_escape_string( request()->input('cut_id'));
	
	$best_by = $mysqli->real_escape_string( request()->input('best_by'));
	$range_from = $mysqli->real_escape_string( request()->input('best_by_range_from'));
	$range_to = $mysqli->real_escape_string( request()->input('best_by_range_to'));
	$weight = $mysqli->real_escape_string( request()->input('weight'));
	
	$species_id = $mysqli->real_escape_string( request()->input('species_id'));
	$temperature_id = $mysqli->real_escape_string( request()->input('temperature_id'));
	
	
	$nationality_id = $mysqli->real_escape_string( request()->input('nationality_id'));
	$brand_id = $mysqli->real_escape_string( request()->input('brand_id'));
	
	$pallet_tare = $mysqli->real_escape_string( request()->input('pallet_tare'));
	$tare_per_carton = $mysqli->real_escape_string( request()->input('tare_per_carton'));
	$number_of_cartons = $mysqli->real_escape_string( request()->input('number_of_cartons'));
	
	
	$akg = $mysqli->real_escape_string( request()->input('akg'));

	
	$quantity=request()->input('quantity'); # Fix the loop from starting at 0
	$quantity++;
	
	$x = "INSERT INTO `product` (akg,pallet_id,cut_id,brand_id,nationality_id,cooling_id,range_from,range_to,ubbb,unit) VALUES (?,?,?,?,?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'ssssssssss',[$akg,$pallet_id,$cut_id,$brand_id,$nationality_id,$temperature_id,$range_from,$range_to,$ubbb,$unit]);
			
	$product_id = $mysqli->insert_id; 
	
	
	// echo '<br/><br/>';
	
	if($akg != ''){
		$x = "INSERT INTO `weights` (`product_id`,`status_id`,`weight_gross`,`weight_tear`) VALUES (?,?,?,?)";
		$y = prepareExecuteQuery($x,'ssss',[$product_id,$status_id,$akg,$akg]);	
	}else{
		for($a = 1; $a < $quantity; $a++){
 			$individualweights = request()->input('individualweights');
			
			if($individualweights == 'C'){
				# Catch Weights
				$weight = request('weights' . $a);
				
				$x = "INSERT INTO `weights` (`product_id`,`status_id`,`weight_gross`,`weight_tear`) VALUES (?,?,?,?)";
				$y = prepareExecuteQuery($x,'ssss',[$product_id,$status_id,$weight,$weight]);
				
			}else if($individualweights == 'D'){
				# Dolav Weights
				
				$weight = $gross_weight_val - $tear_weight_val;
			
				$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear,pallet_tare,tare_per_carton,number_of_cartons)
				VALUES (?,?,?,?,?,?,?)";
				$y = prepareExecuteQuery($x,'sssssss',[$product_id,$status_id,$gross_weight_val,$tear_weight_val,$pallet_tare,$tare_per_carton,$number_of_cartons]);
				
			}else{
				# Single Weight Value
				$weight = request()->input('single_weight_val');
				
				
				$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES (?,?,?,?)";
				$y = prepareExecuteQuery($x,'ssss',[$product_id,$status_id,$weight,$weight]);
			}
			
		}	
	}
?>

<div style="display:none;">
	akg: <?php echo $akg; ?><br/>
	product_id: <?php echo $product_id; ?><br/>
	unit: <?php echo $unit; ?><br/>
	intake_id: <?php echo $intake_id; ?><br/>
	pallet_id: <?php echo $pallet_id; ?><br/>
	status_id: <?php echo $status_id; ?><br/>
	ubbb: <?php echo $ubbb; ?><br/>
	cut_id: <?php echo $cut_id; ?><br/>
	best_by: <?php echo $best_by; ?><br/>
	range_from: <?php echo $range_from; ?><br/>
	range_to: <?php echo $range_to; ?><br/>
	weight: <?php echo $weight; ?><br/>
	species_id: <?php echo $species_id; ?><br/>
	temperature_id: <?php echo $temperature_id; ?><br/>
	nationality_id: <?php echo $nationality_id; ?><br/>
	brand_id: <?php echo $brand_id; ?><br/>
	quantity: <?php echo $quantity; ?><br/>
	individualweights: <?php echo request()->input('individualweights'); ?><br/>
	single_weight_val: <?php echo request()->input('single_weight_val'); ?><br/>
</div>
<br/>
<script>
	window.location = 'intake.php?id=<?php  echo $intake_id; ?>';
</script>
