<?php
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

	$cost = request()->input('cost');
	$price = request()->input('price');

	$storage_location = request()->input('storage_location');
	$palletx = "UPDATE `pallet` SET `storage_location`=? WHERE `id`=?";
	$pallety = prepareExecuteQuery($palletx,'si',[$storage_location,$pallet_id]);

    $health_id = request()->input('health_id');

	$cost = request()->input('cost');
	$price = request()->input('price');

	$single_weight_val = request()->input('single_weight_val');

	$original_intake_id = request()->input('original_intake_id');
	$original_pallet_id = request()->input('original_pallet_id');
	$product_temp = request()->input('product_temp');
	$x = "UPDATE `product` SET original_intake_id = ?, original_pallet_id = ?, pallet_id=?, best_by=?, cut_id=?, brand_id=?,nationality_id=?,cooling_id=?,status=?,range_from=?,range_to=?, range_extension=?, ubbb=?,unit=?,comments=?,product_temp = ?,health_id = ?";
	$varsArr =[$original_intake_id,$original_pallet_id,$pallet_id,$best_by,$cut_id,$brand_id,$nationality_id,$temperature_id,0,$best_by_range_from,$best_by_range_to,$best_by_range_extension,$ubbb,$unit,$comments,$product_temp,$health_id];
	$varStr  ='iiisiiiiisssssssi';
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



	$xtest = "SELECT * FROM `weights` WHERE product_id=?";
	$ytest = prepareExecuteQuery($xtest,'i',[$product_id]);
	$weightCount = mysqli_num_rows($ytest);

	while($row = mysqli_fetch_array($ytest)){
		$weightid = $row['id'];

		$weightVal = request()->input('weight'.$weightid);

		if(!empty($single_weight_val)){
			$weightVal = $single_weight_val;
		}

		$xxx = "UPDATE `weights` SET product_id=?,weight_gross=?,weight_tear=? WHERE id=?";
		$y = prepareExecuteQuery($xxx,'issi',[$product_id,$weightVal,$weightVal,$weightid]);
	}
?>
<br/>
<script>
	// window.location = 'intake.php?id=<?php  echo $intake_id; ?>';
	window.location = 'intake.php?id=<?php  echo $intake_id; ?>&palletupdated=<?php echo $pallet_id; ?>';
</script>
