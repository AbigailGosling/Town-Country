<?php
	include('functions.php');
	
    
	$unit = mysqli_real_escape_string($conn, $_POST['unit']);	
	$intake_id = mysqli_real_escape_string($conn, $_POST['intake_id']);
	$status_id = mysqli_real_escape_string($conn, $_POST['statuses_id']);
	$ubbb = mysqli_real_escape_string($conn, $_POST['ubbb']);
	$cut_id = mysqli_real_escape_string($conn, $_POST['cut_id']);
	$best_by = mysqli_real_escape_string($conn, $_POST['best_by']);
	$range_from = mysqli_real_escape_string($conn, $_POST['best_by_range_from']);
	$range_to = mysqli_real_escape_string($conn, $_POST['best_by_range_to']);
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']);
	$temperature_id = mysqli_real_escape_string($conn, $_POST['temperature_id']);
	$comments = mysqli_real_escape_string($conn, $_POST['comments']);
	$product_temp = mysqli_real_escape_string($conn, $_POST['product_temp']);
	
	
	$gross_weight_val = mysqli_real_escape_string($conn, $_POST['gross_weight_val']);
	
	$original_intake_id = mysqli_real_escape_string($conn, $_POST['original_intake_id']);
	$original_pallet_id = mysqli_real_escape_string($conn, $_POST['original_pallet_id']);
	
	
	$pallet_tare = mysqli_real_escape_string($conn, $_POST['pallet_tare']);
	$tare_per_carton = mysqli_real_escape_string($conn, $_POST['tare_per_carton']);
	$number_of_cartons = mysqli_real_escape_string($conn, $_POST['number_of_cartons']);

	
	$unit = mysqli_real_escape_string($conn, $_POST['unit']);
	$bin = mysqli_real_escape_string($conn, $_POST['bin']);
	
	
	$note_units = mysqli_real_escape_string($conn, $_POST['note_units']);
	$note_weight = mysqli_real_escape_string($conn, $_POST['note_weight']);
	
	$akg = mysqli_real_escape_string($conn, $_POST['akg']);
	
	
	$single_weight_val = mysqli_real_escape_string($conn, $_POST['single_weight_val']);
	
	$individualweights = $_POST['individualweights'];
	
	$note_weight = $single_weight_val;
	
	
	if($bin == 1){
		$status = 1;
	}else{
		$status = 0;
	}
	
	$nationality_id = mysqli_real_escape_string($conn, $_POST['nationality_id']);
	$brand_id = mysqli_real_escape_string($conn, $_POST['brand_id']);
	
	
	if($individualweights == 'D'){ # Dolav Pallet
        
        $quantity = $_POST['quantity'];

        
		$original_gross = number_format($gross_weight_val, 2, '.', '');
		$num_cartons = number_format($number_of_cartons, 2, '.', '');
		$pallet_tare = number_format($pallet_tare, 2, '.', '');
		$tare_per_carton = number_format($tare_per_carton, 2, '.', '');
		
		$carton_tare = $num_cartons * $tare_per_carton;
		$total_tare = $carton_tare + $pallet_tare;
		$net_weight = $original_gross - $total_tare;
		
		# # # Create Pallet 
		$y = mysqli_query($conn, "INSERT into `pallet` (`user_id`,`intake_id`,`comments`,`grosspallet`,`gross_weight`,`pallet_tare`,`tare_per_carton`,`number_of_cartons`,`net_weight`) 
												VALUES ('$userid','$intake_id','$comments','1','$original_gross','$pallet_tare','$tare_per_carton','$number_of_cartons', '$net_weight')");
		$pallet_id = mysqli_insert_id($conn);
		# # #
		
		# # # Create Product
		$x = "INSERT INTO `product` (akg,quantity,pallet_id,status,note_units,note_weight,original_intake_id,original_pallet_id,cut_id,product_temp,brand_id,nationality_id,cooling_id,range_from,range_to,ubbb,unit,best_by) VALUES
		('$akg','$quantity','$pallet_id','$status','$note_units','$note_weight','$original_intake_id','$original_pallet_id','$cut_id','$product_temp','$brand_id','$nationality_id','$temperature_id','$range_from','$range_to','$ubbb','$unit','$best_by')";
		$y = mysqli_query($conn, $x);
		$product_id = mysqli_insert_id($conn); 
		# # #
		
		
		# # # Create Weight
		$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES ('$product_id','$status_id','$net_weight','$net_weight')";
		$y = mysqli_query($conn, $x);
		# # #
		
		
	}else{ # Something Else
        $quantity = $_POST['quantity'];
		$_POST['quantity']++; # Fix the loop starting at 0
		
		# # # Create Pallet 
		$y = mysqli_query($conn, "INSERT into `pallet` (`user_id`,`intake_id`,`grosspallet`) VALUES ('$userid','$intake_id',0)");
		$pallet_id = mysqli_insert_id($conn);
		# # #
		
		# # # Create Product
		$x = "INSERT INTO `product` (akg,quantity,pallet_id,status,note_units,note_weight,original_intake_id,original_pallet_id,cut_id,product_temp,brand_id,nationality_id,cooling_id,range_from,range_to,ubbb,unit,best_by,comments) VALUES
		('$akg','$quantity','$pallet_id','$status','$note_units','$note_weight','$original_intake_id','$original_pallet_id','$cut_id','$product_temp','$brand_id','$nationality_id','$temperature_id','$range_from','$range_to','$ubbb','$unit','$best_by','$comments')";
		$y = mysqli_query($conn, $x);
		$product_id = mysqli_insert_id($conn); 
		# # #
		
		
		if($akg != ''){
			$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES ('$product_id','$status_id','$akg','$akg')";
			$y = mysqli_query($conn, $x);	
		}else{
			
			for($a = 1; $a < $_POST['quantity']; $a++){

				if($individualweights == 'C'){
					# Catch Weights
					$weight = $_POST['weights' . $a];

					$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES ('$product_id','$status_id','$weight','$weight')";
					$y = mysqli_query($conn, $x);

				}else{
					# Single Weight Value
					$weight = $single_weight_val;

					$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES ('$product_id','$status_id','$weight','$weight')";
					$y = mysqli_query($conn, $x);
				}
			}
			
		}
	}
	$storage_location = mysqli_real_escape_string($conn, $_POST['storage_location']);
	$palletx = "UPDATE `pallet` SET `storage_location`='$storage_location' WHERE `id`='$pallet_id'";
	$pallety = mysqli_query($conn, $palletx);
	if($_GET['dupe'] == 'true'){
		echo $pallet_id;	
	}else{
	?>
		<script>
			window.location = 'intake.php?id=<?php echo $intake_id; ?>&pallet_id=<?php echo $pallet_id; ?>';
		</script>
	<?php
	}
?>