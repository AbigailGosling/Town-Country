<?php
	include('functions.php');
	
    
	$unit = $mysqli->real_escape_string( request('unit'));	
	$intake_id = $mysqli->real_escape_string( request('intake_id'));
	$status_id = $mysqli->real_escape_string( request('statuses_id'));
	$ubbb = $mysqli->real_escape_string( request('ubbb'));
	$cut_id = $mysqli->real_escape_string( request('cut_id'));
	$best_by = $mysqli->real_escape_string( request('best_by'));
	$range_from = $mysqli->real_escape_string( request('best_by_range_from'));
	$range_to = $mysqli->real_escape_string( request('best_by_range_to'));
	$species_id = $mysqli->real_escape_string( request('species_id'));
	$temperature_id = $mysqli->real_escape_string( request('temperature_id'));
	$comments = $mysqli->real_escape_string( request('comments'));
	$product_temp = $mysqli->real_escape_string( request('product_temp'));
	
	
	$gross_weight_val = $mysqli->real_escape_string( request('gross_weight_val'));
	
	$original_intake_id = $mysqli->real_escape_string( request('original_intake_id'));
	$original_pallet_id = $mysqli->real_escape_string( request('original_pallet_id'));
	
	
	$pallet_tare = $mysqli->real_escape_string( request('pallet_tare'));
	$tare_per_carton = $mysqli->real_escape_string( request('tare_per_carton'));
	$number_of_cartons = $mysqli->real_escape_string( request('number_of_cartons'));

	
	$unit = $mysqli->real_escape_string( request('unit'));
	$bin = $mysqli->real_escape_string( request('bin'));
	
	
	$note_units = $mysqli->real_escape_string( request('note_units'));
	$note_weight = $mysqli->real_escape_string( request('note_weight'));
	
	$akg = $mysqli->real_escape_string( request('akg'));
	
	
	$single_weight_val = $mysqli->real_escape_string( request('single_weight_val'));
	
	$individualweights = request('individualweights');
	
	$note_weight = $single_weight_val;
	
	
	if($bin == 1){
		$status = 1;
	}else{
		$status = 0;
	}
	
	$nationality_id = $mysqli->real_escape_string( request('nationality_id'));
	$brand_id = $mysqli->real_escape_string( request('brand_id'));
	
	
	if($individualweights == 'D'){ # Dolav Pallet
        
        $quantity = request('quantity');

        
		$original_gross = number_format($gross_weight_val, 2, '.', '');
		$num_cartons = number_format($number_of_cartons, 2, '.', '');
		$pallet_tare = number_format($pallet_tare, 2, '.', '');
		$tare_per_carton = number_format($tare_per_carton, 2, '.', '');
		
		$carton_tare = $num_cartons * $tare_per_carton;
		$total_tare = $carton_tare + $pallet_tare;
		$net_weight = $original_gross - $total_tare;
		
		# # # Create Pallet 
		$y = prepareExecuteQuery("INSERT into `pallet` (`user_id`,`intake_id`,`comments`,`grosspallet`,`gross_weight`,`pallet_tare`,`tare_per_carton`,`number_of_cartons`,`net_weight`) 
												VALUES (?,?,?,?,?,?,?,?,?)"
												,'iisisssss',[$userid,$intake_id,$comments,1,$original_gross,$pallet_tare,$tare_per_carton,$number_of_cartons, $net_weight]);
		$pallet_id = $mysqli->insert_id;
		# # #
		
		# # # Create Product
		$x = "INSERT INTO `product` (akg,quantity,pallet_id,status,note_units,note_weight,original_intake_id,original_pallet_id,cut_id,product_temp,brand_id,nationality_id,cooling_id,range_from,range_to,ubbb,unit,best_by) VALUES
		(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$y = prepareExecuteQuery($x,'ssssssssssssssssss',[$akg,$quantity,$pallet_id,$status,$note_units,$note_weight,$original_intake_id,$original_pallet_id,$cut_id,$product_temp,$brand_id,$nationality_id,$temperature_id,$range_from,$range_to,$ubbb,$unit,$best_by]);
		$product_id =$mysqli->insert_id; 
		# # #
		
		
		# # # Create Weight
		$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES ('$product_id','$status_id','$net_weight','$net_weight')";
		$y = prepareExecuteQuery($x,'iiss',[$product_id,$status_id,$net_weight,$net_weight]);
		# # #
		
		
	}else{ # Something Else
        $quantity = request('quantity');
		request('quantity')++; # Fix the loop starting at 0
		
		# # # Create Pallet 
		$y = prepareExecuteQuery("INSERT into `pallet` (`user_id`,`intake_id`,`grosspallet`) VALUES (?,?,?)",'iii',[$userid,$intake_id,0]);
		$pallet_id = $mysqli->insert_id;
		# # #
		
		# # # Create Product
		$x = "INSERT INTO `product` (akg,quantity,pallet_id,status,note_units,note_weight,original_intake_id,original_pallet_id,cut_id,product_temp,brand_id,nationality_id,cooling_id,range_from,range_to,ubbb,unit,best_by,comments) VALUES
		(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$y = prepareExecuteQuery($x,'sssssssssssssssssss',[$akg,$quantity,$pallet_id,$status,$note_units,$note_weight,$original_intake_id,$original_pallet_id,$cut_id,$product_temp,$brand_id,$nationality_id,$temperature_id,$range_from,$range_to,$ubbb,$unit,$best_by,$comments]);
		$product_id = $mysqli->insert_id; 
		# # #
		
		
		if($akg != ''){
			$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES (?,?,?,?)";
			$y = prepareExecuteQuery($x,'iiss',[$product_id,$status_id,$akg,$akg]);	
		}else{
			
			for($a = 1; $a < request('quantity'); $a++){

				if($individualweights == 'C'){
					# Catch Weights
					$weight = request('weights' . $a);

					$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES (?,?,?,?)";
					$y = prepareExecuteQuery($x,'iiss',[$product_id,$status_id,$weight,$weight]);

				}else{
					# Single Weight Value
					$weight = $single_weight_val;

					$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES (?,?,?,?)";
					$y = prepareExecuteQuery($x,'iiss',[$product_id,$status_id,$weight,$weight]);
				}
			}
			
		}
	}
	$storage_location = $mysqli->real_escape_string( request('storage_location'));
	$palletx = "UPDATE `pallet` SET `storage_location`='$storage_location' WHERE `id`=?";
	$pallety = prepareExecuteQuery($palletx,'i',[$pallet_id]);
	if(request('dupe') == 'true'){
		echo $pallet_id;	
	}else{
	?>
		<script>
			window.location = 'intake.php?id=<?php echo $intake_id; ?>&pallet_id=<?php echo $pallet_id; ?>';
		</script>
	<?php
	}
?>