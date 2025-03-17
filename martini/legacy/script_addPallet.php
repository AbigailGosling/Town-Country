<?php
	include('functions.php');
	$unit = request()->input('unit');
	$intake_id = request()->input('intake_id');
	$status_id = request()->input('statuses_id');
	$ubbb = request()->input('ubbb');
	$cut_id = request()->input('cut_id');
	$best_by = request()->input('best_by');
	$range_from = request()->input('best_by_range_from');
	$range_to = request()->input('best_by_range_to');
	$range_extension = request()->input('best_by_range_extension');
	$species_id = request()->input('species_id');
	$temperature_id = request()->input('temperature_id');
	$comments = request()->input('comments');
	$product_temp = request()->input('product_temp');


	$gross_weight_val = request()->input('gross_weight_val');

	$original_intake_id = request()->input('original_intake_id');
	$original_pallet_id = request()->input('original_pallet_id');


	$pallet_tare = request()->input('pallet_tare');
	$tare_per_carton = request()->input('tare_per_carton');
	$number_of_cartons = request()->input('number_of_cartons');


	$unit = request()->input('unit');
	$bin = request()->input('bin');


	$note_units = request()->input('note_units');
	$note_weight = request()->input('note_weight');

	$akg = request()->input('akg');


	$single_weight_val = request()->input('single_weight_val');

	$individualweights = request()->input('individualweights');

    $kill_date = request()->input('kill_date');

	$note_weight = $single_weight_val;


	if($bin == 1){
		$status = 1;
	}else{
		$status = 0;
	}

	$nationality_id = request()->input('nationality_id');
	$brand_id = request()->input('brand_id');


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
		$vars = [$akg,$quantity,$pallet_id,$status,$note_units,$note_weight,$original_intake_id,$original_pallet_id,$cut_id,$product_temp,$brand_id,$nationality_id,$temperature_id,$range_from,$range_to,$range_extension,$ubbb,$unit,$best_by,$kill_date];
		$x = "INSERT INTO `product` (akg,quantity,pallet_id,status,note_units,note_weight,original_intake_id,original_pallet_id,cut_id,product_temp,brand_id,nationality_id,cooling_id,range_from,range_to,range_extension,ubbb,unit,best_by,kill_date) VALUES
		(".implode(",",array_fill(0,count($vars),"?")).")";
		$product_id = prepareExecuteQuery($x,str_repeat("s",count($vars)),$vars,true);
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
		$x = "INSERT INTO `product` (akg,quantity,pallet_id,status,note_units,note_weight,original_intake_id,original_pallet_id,cut_id,product_temp,brand_id,nationality_id,cooling_id,range_from,range_to,range_extension,ubbb,unit,best_by,comments) VALUES
		(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$product_id = prepareExecuteQuery($x,'ssssssssssssssssssss',[$akg,$quantity,$pallet_id,$status,$note_units,$note_weight,$original_intake_id,$original_pallet_id,$cut_id,$product_temp,$brand_id,$nationality_id,$temperature_id,$range_from,$range_to,$range_extension,$ubbb,$unit,$best_by,$comments],true);
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
	$storage_location = request()->input('storage_location');
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
