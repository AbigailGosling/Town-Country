<?php

use App\Models\PalletMovementTracking;

	include('functions.php');

	$intake_id = request()->input('intake_id');
	$pallet_id = request()->input('pallet_id');
	$product_id = request()->input('product_id');
	$cut_id = request()->input('cut_id');
	$weight_id = request()->input('weight_id');


	$statuses_id = request()->input('statuses_id');
	$best_by = request()->input('best_by');
	$ubbb = request()->input('ubbb');
	$best_by_range_from = request()->input('best_by_range_from');
	$best_by_range_to = request()->input('best_by_range_to');
	$best_by_range_extension = request()->input('best_by_range_extension');
	$temperature_id = request()->input('temperature_id');
	$comments = request()->input('comments');

	$nationality_id = request()->input('nationality_id');
	$brand_id = request()->input('brand_id');
	$species_id = request()->input('species_id');

	$unit = request()->input('unit');
	$individualweights = request()->input('individualweights');
	$akg = request()->input('akg');
	$gross_weight_val = request()->input('gross_weight_val');
	$pallet_tare = request()->input('pallet_tare');
	$tare_per_carton = request()->input('tare_per_carton');
	$number_of_cartons = request()->input('number_of_cartons');

	$cost = request()->input('cost');
	$price = request()->input('price');

	$storage_location = request()->input('storage_location');
	PalletMovementTracking::moveStock($pallet_id, $storage_location);

	$cost = request()->input('cost');
	$price = request()->input('price');

    $kill_date = request()->input('kill_date');

	$single_weight_val = request()->input('single_weight_val');

	$original_intake_id = request()->input('original_intake_id');
	$original_pallet_id = request()->input('original_pallet_id');
	$product_temp = request()->input('product_temp');
	$mode = $individualweights;
	if($unit == 'P'){
		$mode = 'D';
	}
	if($mode == 'AKG'){
		$single_weight_val = null;
	}

	$productAkg = '';
	if($mode == 'AKG'){
		$productAkg = $akg;
	}

	$x = "UPDATE `product` SET original_intake_id = ?, original_pallet_id = ?, pallet_id=?, best_by=?, cut_id=?, brand_id=?,nationality_id=?,cooling_id=?,status=?,range_from=?,range_to=?, range_extension=?, ubbb=?,unit=?,comments=?,product_temp = ?,kill_date = ?, akg = ?";
	$varsArr =[$original_intake_id,$original_pallet_id,$pallet_id,$best_by,$cut_id,$brand_id,$nationality_id,$temperature_id,0,$best_by_range_from,$best_by_range_to,$best_by_range_extension,$ubbb,$unit,$comments,$product_temp,$kill_date,$productAkg];
	$varStr  ='iiisiiiiisssssssss';
	if($cost != NULL){
		$x .= ", cost=?, price=?";
		$varsArr[]=$cost;
		$varsArr[]=$price;
		$varStr = $varStr . 'ss';
	}

	$x .= " WHERE id=?";
	$varsArr[]=$product_id;
	$varStr = $varStr . 'i';
	$y = prepareExecuteQuery($x,$varStr,$varsArr);

	if($mode == 'D'){
		$original_gross = number_format((float)$gross_weight_val, 2, '.', '');
		$num_cartons = number_format((float)$number_of_cartons, 2, '.', '');
		$pallet_tare_val = number_format((float)$pallet_tare, 2, '.', '');
		$tare_per_carton_val = number_format((float)$tare_per_carton, 2, '.', '');

		$carton_tare = (float)$num_cartons * (float)$tare_per_carton_val;
		$total_tare = $carton_tare + (float)$pallet_tare_val;
		$net_weight = (float)$original_gross - $total_tare;

		$xp = "UPDATE `pallet` SET `grosspallet` = 1, `gross_weight` = ?, `pallet_tare` = ?, `tare_per_carton` = ?, `number_of_cartons` = ?, `net_weight` = ? WHERE id = ?";
		prepareExecuteQuery($xp,'sssssi',[$original_gross,$pallet_tare_val,$tare_per_carton_val,$num_cartons,$net_weight,$pallet_id]);
	}



	$xtest = "SELECT * FROM `weights` WHERE product_id=?";
	$ytest = prepareExecuteQuery($xtest,'i',[$product_id]);
	$weightCount = mysqli_num_rows($ytest);

	$hasSoldWeights = false;
	if($mode == 'S'){
		$xsold = "SELECT COUNT(*) AS sold_count FROM `weights` WHERE product_id=? AND status_id=1";
		$ysold = prepareExecuteQuery($xsold,'i',[$product_id]);
		if($soldRow = mysqli_fetch_array($ysold)){
			$hasSoldWeights = ((int)$soldRow['sold_count'] > 0);
		}
	}

	$allowStandardBulkUpdate = ($mode == 'S' && !empty($single_weight_val) && !$hasSoldWeights);

	while($row = mysqli_fetch_array($ytest)){
		$weightid = $row['id'];

		$weightVal = request()->input('weight'.$weightid);

		if($mode == 'AKG'){
			$weightVal = $akg;
		}else if($mode == 'D'){
			$weightVal = isset($net_weight) ? $net_weight : $weightVal;
		}else if($mode == 'S' && !empty($single_weight_val)){
			if($allowStandardBulkUpdate){
				$weightVal = $single_weight_val;
			}else if((int)$weightid === (int)$weight_id){
				$weightVal = $single_weight_val;
			}else if($weightVal === null || $weightVal === ''){
				$weightVal = $row['weight_gross'];
			}
		}

		$xxx = "UPDATE `weights` SET product_id=?,weight_gross=?,weight_tear=? WHERE id=?";
		$y = prepareExecuteQuery($xxx,'issi',[$product_id,$weightVal,$weightVal,$weightid]);
        loggedDataChange("weight_gross", $weightid, $weightVal);
        loggedDataChange("weight_tear", $weightid, $weightVal);

		if($mode == 'D'){
			$xxx2 = "UPDATE `weights` SET pallet_tare=?, tare_per_carton=?, number_of_cartons=? WHERE id=?";
			prepareExecuteQuery($xxx2,'sssi',[$pallet_tare_val,$tare_per_carton_val,$num_cartons,$weightid]);
            loggedDataChange("weight_pallet_tare", $weightid, $pallet_tare_val);
            loggedDataChange("weight_tare_per_carton", $weightid, $tare_per_carton_val);
            loggedDataChange("weight_number_of_cartons", $weightid, $num_cartons);
		}
	}
?>
<br/>
<script>
	// window.location = 'intake.php?id=<?php  echo $intake_id; ?>';
	window.location = 'intake.php?id=<?php  echo $intake_id; ?>&palletupdated=<?php echo $pallet_id; ?>';
</script>
