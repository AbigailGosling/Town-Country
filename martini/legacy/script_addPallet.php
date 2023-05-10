<?php
	include('functions.php');
	$unit = $mysqli->real_escape_string( request()->input('unit'));	
	$intake_id = $mysqli->real_escape_string( request()->input('intake_id'));
	$status_id = $mysqli->real_escape_string( request()->input('statuses_id'));
	$ubbb = $mysqli->real_escape_string( request()->input('ubbb'));
	$cut_id = $mysqli->real_escape_string( request()->input('cut_id'));
	$best_by = $mysqli->real_escape_string( request()->input('best_by'));
	$range_from = $mysqli->real_escape_string( request()->input('best_by_range_from'));
	$range_to = $mysqli->real_escape_string( request()->input('best_by_range_to'));
	$species_id = $mysqli->real_escape_string( request()->input('species_id'));
	$temperature_id = $mysqli->real_escape_string( request()->input('temperature_id'));
	$comments = $mysqli->real_escape_string( request()->input('comments'));
	$product_temp = $mysqli->real_escape_string( request()->input('product_temp'));
	
	
	$gross_weight_val = $mysqli->real_escape_string( request()->input('gross_weight_val'));
	
	$original_intake_id = $mysqli->real_escape_string( request()->input('original_intake_id'));
	$original_pallet_id = $mysqli->real_escape_string( request()->input('original_pallet_id'));
	
	
	$pallet_tare = $mysqli->real_escape_string( request()->input('pallet_tare'));
	$tare_per_carton = $mysqli->real_escape_string( request()->input('tare_per_carton'));
	$number_of_cartons = $mysqli->real_escape_string( request()->input('number_of_cartons'));

	
	$unit = $mysqli->real_escape_string( request()->input('unit'));
	$bin = $mysqli->real_escape_string( request()->input('bin'));
	
	
	$note_units = $mysqli->real_escape_string( request()->input('note_units'));
	$note_weight = $mysqli->real_escape_string( request()->input('note_weight'));
	
	$akg = $mysqli->real_escape_string( request()->input('akg'));
	
	
	$single_weight_val = $mysqli->real_escape_string( request()->input('single_weight_val'));
	
	$individualweights = request()->input('individualweights');
	
	$note_weight = $single_weight_val;
	
	
	if($bin == 1){
		$status = 1;
	}else{
		$status = 0;
	}
	
	$nationality_id = $mysqli->real_escape_string( request()->input('nationality_id'));
	$brand_id = $mysqli->real_escape_string( request()->input('brand_id'));
	
	
	if($individualweights == 'D'){ # Dolav Pallet
        
        $quantity = request()->input('quantity');

        
		$original_gross = number_format($gross_weight_val, 2, '.', '');
		$num_cartons = number_format($number_of_cartons, 2, '.', '');
		$pallet_tare = number_format($pallet_tare, 2, '.', '');
		$tare_per_carton = number_format($tare_per_carton, 2, '.', '');
		
		$carton_tare = $num_cartons * $tare_per_carton;
		$total_tare = $carton_tare + $pallet_tare;
		$net_weight = $original_gross - $total_tare;
		
		# # # Create Pallet 
		$pallet_id = prepareExecuteQuery("INSERT into `pallet` (`user_id`,`intake_id`,`comments`,`grosspallet`,`gross_weight`,`pallet_tare`,`tare_per_carton`,`number_of_cartons`,`net_weight`) 
												VALUES (?,?,?,?,?,?,?,?,?)"
												,'iisisssss',[$userid,$intake_id,$comments,1,$original_gross,$pallet_tare,$tare_per_carton,$number_of_cartons, $net_weight],true);
		# # #
		
		# # # Create Product
		$x = "INSERT INTO `product` (akg,quantity,pallet_id,status,note_units,note_weight,original_intake_id,original_pallet_id,cut_id,product_temp,brand_id,nationality_id,cooling_id,range_from,range_to,ubbb,unit,best_by) VALUES
		(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$product_id = prepareExecuteQuery($x,'ssssssssssssssssss',[$akg,$quantity,$pallet_id,$status,$note_units,$note_weight,$original_intake_id,$original_pallet_id,$cut_id,$product_temp,$brand_id,$nationality_id,$temperature_id,$range_from,$range_to,$ubbb,$unit,$best_by],true);
		# # #
		
		
		# # # Create Weight
		$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES (?,?,?,?)";
		$y = prepareExecuteQuery($x,'iiss',[$product_id,$status_id,$net_weight,$net_weight]);
		# # #
		
		
	}else{ # Something Else
        $quantity = request()->input('quantity');
		$quantity++; # Fix the loop starting at 0
		
		# # # Create Pallet 
		$pallet_id = prepareExecuteQuery("INSERT into `pallet` (`user_id`,`intake_id`,`grosspallet`) VALUES (?,?,?)",'iii',[$userid,$intake_id,0],true);
		# # #
		
		# # # Create Product
		$x = "INSERT INTO `product` (akg,quantity,pallet_id,status,note_units,note_weight,original_intake_id,original_pallet_id,cut_id,product_temp,brand_id,nationality_id,cooling_id,range_from,range_to,ubbb,unit,best_by,comments) VALUES
		(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$product_id = prepareExecuteQuery($x,'sssssssssssssssssss',[$akg,$quantity,$pallet_id,$status,$note_units,$note_weight,$original_intake_id,$original_pallet_id,$cut_id,$product_temp,$brand_id,$nationality_id,$temperature_id,$range_from,$range_to,$ubbb,$unit,$best_by,$comments],true);
		# # #
		
		
		if($akg != ''){
			$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES (?,?,?,?)";
			$y = prepareExecuteQuery($x,'iiss',[$product_id,$status_id,$akg,$akg]);	
		}else{
			
			for($a = 1; $a <= request()->input('quantity'); $a++){

				if($individualweights == 'C'){
					# Catch Weights
					$weight = request()->input('weights' . $a);

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
	$storage_location = $mysqli->real_escape_string( request()->input('storage_location'));
	$palletx = "UPDATE `pallet` SET `storage_location`=? WHERE `id`=?";
	$pallety = prepareExecuteQuery($palletx,'si',[$storage_location,$pallet_id]);
	if(request()->input('dupe') == 'true'){
		echo $pallet_id;	
	}else{
	?>
		<script>
			window.location = 'intake.php?id=<?php echo $intake_id; ?>&pallet_id=<?php echo $pallet_id; ?>';
		</script>
	<?php
	}
?>