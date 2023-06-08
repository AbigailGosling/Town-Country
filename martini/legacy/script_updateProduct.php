<?php
	include('functions.php');
	
	$intake_id = $mysqli->real_escape_string( request()->input('intake_id'));	
	$pallet_id = $mysqli->real_escape_string( request()->input('pallet_id'));	
	$product_id = $mysqli->real_escape_string( request()->input('product_id'));	
	$cut_id = $mysqli->real_escape_string( request()->input('cut_id'));	
	$weight_id = $mysqli->real_escape_string( request()->input('weight_id'));	
	
	
	$statuses_id = $mysqli->real_escape_string( request()->input('statuses_id'));	
	$best_by = $mysqli->real_escape_string( request()->input('best_by'));	
	$ubbb = $mysqli->real_escape_string( request()->input('ubbb'));	
	$best_by_range_from = $mysqli->real_escape_string( request()->input('best_by_range_from'));	
	$best_by_range_to = $mysqli->real_escape_string( request()->input('best_by_range_to'));	
	$temperature_id = $mysqli->real_escape_string( request()->input('temperature_id'));	
	$comments = $mysqli->real_escape_string( request()->input('comments'));	
	
	$nationality_id = $mysqli->real_escape_string( request()->input('nationality_id'));	
	$brand_id = $mysqli->real_escape_string( request()->input('brand_id'));	
	$species_id = $mysqli->real_escape_string( request()->input('species_id'));

	$unit = $mysqli->real_escape_string( request()->input('unit'));
	
	$cost = $mysqli->real_escape_string( request()->input('cost'));
	$price = $mysqli->real_escape_string( request()->input('price'));
	
	$storage_location = $mysqli->real_escape_string( request()->input('storage_location'));
	$palletx = "UPDATE `pallet` SET `storage_location`=? WHERE `id`=?";
	$pallety = prepareExecuteQuery($palletx,'si',[$storage_location,$pallet_id]);
	
	$cost = $mysqli->real_escape_string( request()->input('cost'));
	$price = $mysqli->real_escape_string( request()->input('price'));
	
	$single_weight_val = $mysqli->real_escape_string( request()->input('single_weight_val'));
	
	$original_intake_id = $mysqli->real_escape_string( request()->input('original_intake_id'));
	$original_pallet_id = $mysqli->real_escape_string( request()->input('original_pallet_id'));
	$product_temp = $mysqli->real_escape_string( request()->input('product_temp'));
	$x = "UPDATE `product` SET original_intake_id = ?, original_pallet_id = ?, pallet_id=?, best_by=?, cut_id=?, brand_id=?,nationality_id=?,cooling_id=?,status=?,range_from=?,range_to=?, ubbb=?,unit=?,comments=?,product_temp = ?";
	$varsArr =[$original_intake_id,$original_pallet_id,$pallet_id,$best_by,$cut_id,$brand_id,$nationality_id,$temperature_id,0,$best_by_range_from,$best_by_range_to,$ubbb,$unit,$comments,$product_temp];
	$varStr  ='iiisiiiiissssss';
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
		
		$weightVal = $mysqli->real_escape_string( request()->input('weight'.$weightid));

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
