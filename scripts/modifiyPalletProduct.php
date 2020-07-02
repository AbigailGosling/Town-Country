<?php
	include('functions.php');
	
	$unit = mysqli_real_escape_string($conn, $_POST['unit']);	
	$intake_id = mysqli_real_escape_string($conn, $_POST['intake_id']);
	$status_id = mysqli_real_escape_string($conn, $_POST['statuses_id']);
	$cut_id = mysqli_real_escape_string($conn, $_POST['cut_id']);
	$best_by = mysqli_real_escape_string($conn, $_POST['best_by']);
	$best_by_range_from = mysqli_real_escape_string($conn, $_POST['best_by_range_from']);
	$best_by_range_to = mysqli_real_escape_string($conn, $_POST['best_by_range_to']);
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']);
	$temperature_id = mysqli_real_escape_string($conn, $_POST['temperature_id']);
	$comments = mysqli_real_escape_string($conn, $_POST['comments']);
	
	
	$tear_weight_val = mysqli_real_escape_string($conn, $_POST['tear_weight_val']);
	$gross_weight_val = mysqli_real_escape_string($conn, $_POST['gross_weight_val']);
	
	$unit = mysqli_real_escape_string($conn, $_POST['unit']);
	
	$nationality_id = mysqli_real_escape_string($conn, $_POST['nationality_id']);
	$brand_id = mysqli_real_escape_string($conn, $_POST['brand_id']);
	
	$_POST['quantity']++; # Fix the loop from starting at 0
	
	// $x = "INSERT into `pallets` (`intake_id`, `comments`) VALUES ('$intake_id','$comments')";
	$x = "UPDATE `pallets` SET comments = '$comments' WHERE id = '$comments'";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
	$pallet_id = mysqli_insert_id($conn);

	for($a = 1; $a < $_POST['quantity']; $a++){
		
		$individualweights = $_POST['individualweights'];
		
		if($individualweights == 'C'){
			# Catch Weights
			$weight = $_POST['weights' . $a];
			$x = "INSERT into `products` (`pallet_id`, `species_id`,`cut_id`,`nationality_id`,`brand_id`,`temp`,`best_by`,`bb_from`,`bb_to`) VALUES ('$pallet_id','$species_id', '$cut_id','$nationality_id','$brand_id','$temperature_id','$best_by','$best_by_range_from','$best_by_range_to')";
			$y = mysqli_query($conn, $x);
			$product_id = mysqli_insert_id($conn);
			$x = "INSERT into `boxes` (`product_id`, `status_id`,`weight`,`unit`) VALUES ('$product_id','$status_id', '$weight', '$unit')";
			$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
			
		}else if($individualweights == 'D'){
			# Dolav Weights
			$weight = $_POST['single_weight_val'];
			$x = "INSERT into `products` (`pallet_id`, `species_id`,`cut_id`,`nationality_id`,`brand_id`,`temp`,`best_by`,`bb_from`,`bb_to`) VALUES ('$pallet_id','$species_id', '$cut_id','$nationality_id','$brand_id','$temperature_id','$best_by','$best_by_range_from','$best_by_range_to')";
			$y = mysqli_query($conn, $x);
			$product_id = mysqli_insert_id($conn);
			$x = "INSERT into `boxes` (`product_id`, `status_id`,`weight_tear`,`weight_gross`,`unit`) VALUES ('$product_id','$status_id', '$tear_weight_val','$gross_weight_val', '$unit')";
			$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
			
		}else{
			# Single Weight Value
			$weight = $_POST['single_weight_val'];
			$x = "INSERT into `products` (`pallet_id`, `species_id`,`cut_id`,`nationality_id`,`brand_id`,`temp`,`best_by`,`bb_from`,`bb_to`) VALUES ('$pallet_id','$species_id', '$cut_id','$nationality_id','$brand_id','$temperature_id','$best_by','$best_by_range_from','$best_by_range_to')";
			$y = mysqli_query($conn, $x);
			$product_id = mysqli_insert_id($conn);
			$x = "INSERT into `boxes` (`product_id`, `status_id`,`weight`,`unit`) VALUES ('$product_id','$status_id', '$weight', '$unit')";
			$y = mysqli_query($conn, $x) or die(mysqli_error($conn));	
		}
		

		
		// $x = "INSERT into `products` (`pallet_id`, `species_id`,`cut_id`,`nationality_id`,`brand_id`,`temp`,`best_by`,`bb_from`,`bb_to`) VALUES ('$pallet_id','$species_id', '$cut_id','$nationality_id','$brand_id','$temperature_id','$best_by','$best_by_range_from','$best_by_range_to')";
		// $y = mysqli_query($conn, $x);
		
		// $product_id = mysqli_insert_id($conn);
		
		// $x = "INSERT into `boxes` (`product_id`, `status_id`,`weight`,`unit`) VALUES ('$product_id','$status_id', '$weight', '$unit')";
		// $y = mysqli_query($conn, $x);	
	}	
?>
<br/>
<script>
	window.location = 'intake.php?id=<?php  echo $intake_id; ?>';
</script>
