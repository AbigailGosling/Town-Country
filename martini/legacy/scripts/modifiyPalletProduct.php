<?php
	include('functions.php');
	
	$unit = request()->input('unit');	
	$intake_id = request()->input('intake_id');
	$status_id = request()->input('statuses_id');
	$cut_id = request()->input('cut_id');
	$best_by = request()->input('best_by');
	$best_by_range_from = request()->input('best_by_range_from');
	$best_by_range_to = request()->input('best_by_range_to');
	$best_by_range_extension = request()->input('best_by_range_extension');
	$species_id = request()->input('species_id');
	$temperature_id = request()->input('temperature_id');
	$comments = request()->input('comments');
	
	
	$tear_weight_val = request()->input('tear_weight_val');
	$gross_weight_val = request()->input('gross_weight_val');
	
	$unit = request()->input('unit');
	
	$nationality_id = request()->input('nationality_id');
	$brand_id = request()->input('brand_id');
	
	$quantity = request()->input('quantity'); # Fix the loop from starting at 0
	$quantity++;
	// $x = "INSERT into `pallets` (`intake_id`, `comments`) VALUES ('$intake_id','$comments')";
	$x = "UPDATE `pallets` SET comments = '$comments' WHERE id = '$comments'";
	$y = prepareExecuteQuery($x) or die(mysqli_error($mysqli));
	
	$pallet_id = mysqli_insert_id($mysqli);

	for($a = 1; $a < $quantity; $a++){
		
		$individualweights = request()->input('individualweights');
		
		if($individualweights == 'C'){
			# Catch Weights
			$weight = request('weights' . $a);
			$x = "INSERT into `products` (`pallet_id`, `species_id`,`cut_id`,`nationality_id`,`brand_id`,`temp`,`best_by`,`bb_from`,`bb_to`) VALUES ('$pallet_id','$species_id', '$cut_id','$nationality_id','$brand_id','$temperature_id','$best_by','$best_by_range_from','$best_by_range_to')";
			$y = prepareExecuteQuery($x);
			$product_id = mysqli_insert_id($mysqli);
			$x = "INSERT into `boxes` (`product_id`, `status_id`,`weight`,`unit`) VALUES ('$product_id','$status_id', '$weight', '$unit')";
			$y = prepareExecuteQuery($x) or die(mysqli_error($mysqli));
			
		}else if($individualweights == 'D'){
			# Dolav Weights
			$weight = request()->input('single_weight_val');
			$x = "INSERT into `products` (`pallet_id`, `species_id`,`cut_id`,`nationality_id`,`brand_id`,`temp`,`best_by`,`bb_from`,`bb_to`) VALUES ('$pallet_id','$species_id', '$cut_id','$nationality_id','$brand_id','$temperature_id','$best_by','$best_by_range_from','$best_by_range_to')";
			$y = prepareExecuteQuery($x);
			$product_id = mysqli_insert_id($mysqli);
			$x = "INSERT into `boxes` (`product_id`, `status_id`,`weight_tear`,`weight_gross`,`unit`) VALUES ('$product_id','$status_id', '$tear_weight_val','$gross_weight_val', '$unit')";
			$y = prepareExecuteQuery($x) or die(mysqli_error($mysqli));
			
		}else{
			# Single Weight Value
			$weight = request()->input('single_weight_val');
			$x = "INSERT into `products` (`pallet_id`, `species_id`,`cut_id`,`nationality_id`,`brand_id`,`temp`,`best_by`,`bb_from`,`bb_to`) VALUES ('$pallet_id','$species_id', '$cut_id','$nationality_id','$brand_id','$temperature_id','$best_by','$best_by_range_from','$best_by_range_to')";
			$y = prepareExecuteQuery($x);
			$product_id = mysqli_insert_id($mysqli);
			$x = "INSERT into `boxes` (`product_id`, `status_id`,`weight`,`unit`) VALUES ('$product_id','$status_id', '$weight', '$unit')";
			$y = prepareExecuteQuery($x) or die(mysqli_error($mysqli));	
		}
		

		
		// $x = "INSERT into `products` (`pallet_id`, `species_id`,`cut_id`,`nationality_id`,`brand_id`,`temp`,`best_by`,`bb_from`,`bb_to`) VALUES ('$pallet_id','$species_id', '$cut_id','$nationality_id','$brand_id','$temperature_id','$best_by','$best_by_range_from','$best_by_range_to')";
		// $y = prepareExecuteQuery($x);
		
		// $product_id = mysqli_insert_id($mysqli);
		
		// $x = "INSERT into `boxes` (`product_id`, `status_id`,`weight`,`unit`) VALUES ('$product_id','$status_id', '$weight', '$unit')";
		// $y = prepareExecuteQuery($x);	
	}	
?>
<br/>
<script>
	window.location = 'intake.php?id=<?php  echo $intake_id; ?>';
</script>
