<?php
	include('functions.php');
	
	$unit = $mysqli->real_escape_string( request()->input('unit'));	
	$intake_id = $mysqli->real_escape_string( request()->input('intake_id'));
	$status_id = $mysqli->real_escape_string( request()->input('statuses_id'));
	$cut_id = $mysqli->real_escape_string( request()->input('cut_id'));
	$best_by = $mysqli->real_escape_string( request()->input('best_by'));
	$best_by_range_from = $mysqli->real_escape_string( request()->input('best_by_range_from'));
	$best_by_range_to = $mysqli->real_escape_string( request()->input('best_by_range_to'));
	$species_id = $mysqli->real_escape_string( request()->input('species_id'));
	$temperature_id = $mysqli->real_escape_string( request()->input('temperature_id'));
	$comments = $mysqli->real_escape_string( request()->input('comments'));
	
	
	$tear_weight_val = $mysqli->real_escape_string( request()->input('tear_weight_val'));
	$gross_weight_val = $mysqli->real_escape_string( request()->input('gross_weight_val'));
	
	$unit = $mysqli->real_escape_string( request()->input('unit'));
	
	$nationality_id = $mysqli->real_escape_string( request()->input('nationality_id'));
	$brand_id = $mysqli->real_escape_string( request()->input('brand_id'));
	
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
