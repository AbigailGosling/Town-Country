<?php
	require(__DIR__.'/../functions.php');
	global $mysqli;
	$unit = request()->input('unit');
	$intake_id = request()->input('intake_id');
	$pallet_id = request()->input('pallet_id');


	$status_id = request()->input('statuses_id');
	$ubbb = request()->input('ubbb');
	$cut_id = request()->input('cut_id');

	$best_by = request()->input('best_by');
	$product_temp = request()->input('product_temp');
	$range_from = request()->input('best_by_range_from');
	$range_to = request()->input('best_by_range_to');
	$range_extension = request()->input('best_by_range_extension');
	$weight = request()->input('weight');

	$species_id = request()->input('species_id');
	$temperature_id = request()->input('temperature_id');


	$nationality_id = request()->input('nationality_id');
	$brand_id = request()->input('brand_id');

	$pallet_tare = request()->input('pallet_tare');
	$tare_per_carton = request()->input('tare_per_carton');
	$number_of_cartons = request()->input('number_of_cartons');
	$comments = request()->input('comments');


	$akg = request()->input('akg');

    $kill_date = request()->input('kill_date');

	$quantity=request()->input('quantity'); # Fix the loop from starting at 0
	$quantity++;

	$x = "INSERT INTO `product` (akg,pallet_id,cut_id,brand_id,nationality_id,cooling_id,range_from,range_to,range_extension,ubbb,unit,best_by,product_temp,comments,kill_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'sssssssssssssss',[$akg,$pallet_id,$cut_id,$brand_id,$nationality_id,$temperature_id,$range_from,$range_to,$range_extension,$ubbb,$unit,$best_by,$product_temp,$comments,$kill_date]);

	$product_id = $mysqli->insert_id;


	// echo '<br/><br/>';

	if($akg != ''){
		$x = "INSERT INTO `weights` (`product_id`,`status_id`,`weight_gross`,`weight_tear`) VALUES (?,?,?,?)";
		$y = prepareExecuteQuery($x,'ssss',[$product_id,$status_id,$akg,$akg],true);
        loggedDataChange("weight_gross", $y, $akg);
        loggedDataChange("weight_tear", $y, $akg);
	}else{
		for($a = 1; $a < $quantity; $a++){
 			$individualweights = request()->input('individualweights');

			if($individualweights == 'C'){
				# Catch Weights
				$weight = request()->input('weights' . $a);

				$x = "INSERT INTO `weights` (`product_id`,`status_id`,`weight_gross`,`weight_tear`) VALUES (?,?,?,?)";
				$y = prepareExecuteQuery($x,'ssss',[$product_id,$status_id,$weight,$weight],true);
                loggedDataChange("weight_gross", $y, $weight);
                loggedDataChange("weight_tear", $y, $weight);

			}else if($individualweights == 'D'){
				# Dolav Weights

				$weight = $gross_weight_val - $tear_weight_val;

				$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear,pallet_tare,tare_per_carton,number_of_cartons)
				VALUES (?,?,?,?,?,?,?)";
				$y = prepareExecuteQuery($x,'sssssss',[$product_id,$status_id,$gross_weight_val,$tear_weight_val,$pallet_tare,$tare_per_carton,$number_of_cartons],true);
                loggedDataChange("weight_gross", $y, $gross_weight_val);
                loggedDataChange("weight_tear", $y, $tear_weight_val);
                loggedDataChange("weight_pallet_tare", $y, $pallet_tare);
                loggedDataChange("weight_tare_per_carton", $y, $tare_per_carton);
                loggedDataChange("weight_number_of_cartons", $y, $number_of_cartons);

			}else{
				# Single Weight Value
				$weight = request()->input('single_weight_val');


				$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES (?,?,?,?)";
				$y = prepareExecuteQuery($x,'ssss',[$product_id,$status_id,$weight,$weight]);
                loggedDataChange("weight_gross", $y, $weight);
                loggedDataChange("weight_tear", $y, $weight);
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
	range_extension: <?php echo $range_extension; ?><br/>
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
