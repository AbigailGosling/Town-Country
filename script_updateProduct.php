<?php
	include('functions.php');
	
	$intake_id = mysqli_real_escape_string($conn, $_POST['intake_id']);	
	$pallet_id = mysqli_real_escape_string($conn, $_POST['pallet_id']);	
	$product_id = mysqli_real_escape_string($conn, $_POST['product_id']);	
	$cut_id = mysqli_real_escape_string($conn, $_POST['cut_id']);	
	$weight_id = mysqli_real_escape_string($conn, $_POST['weight_id']);	
	
	
	$statuses_id = mysqli_real_escape_string($conn, $_POST['statuses_id']);	
	$best_by = mysqli_real_escape_string($conn, $_POST['best_by']);	
	$ubbb = mysqli_real_escape_string($conn, $_POST['ubbb']);	
	$best_by_range_from = mysqli_real_escape_string($conn, $_POST['best_by_range_from']);	
	$best_by_range_to = mysqli_real_escape_string($conn, $_POST['best_by_range_to']);	
	$temperature_id = mysqli_real_escape_string($conn, $_POST['temperature_id']);	
	$comments = mysqli_real_escape_string($conn, $_POST['comments']);	
	
	$nationality_id = mysqli_real_escape_string($conn, $_POST['nationality_id']);	
	$brand_id = mysqli_real_escape_string($conn, $_POST['brand_id']);	
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']);

	$unit = mysqli_real_escape_string($conn, $_POST['unit']);
	
	$cost = mysqli_real_escape_string($conn, $_POST['cost']);
	$price = mysqli_real_escape_string($conn, $_POST['price']);
	
	$storage_location = mysqli_real_escape_string($conn, $_POST['storage_location']);
	$palletx = "UPDATE `pallet` SET `storage_location`='$storage_location' WHERE `id`='$pallet_id'";
	$pallety = mysqli_query($conn, $palletx);
	
	$cost = mysqli_real_escape_string($conn, $_POST['cost']);
	$price = mysqli_real_escape_string($conn, $_POST['price']);
	
	$single_weight_val = mysqli_real_escape_string($conn, $_POST['single_weight_val']);
	
	$original_intake_id = mysqli_real_escape_string($conn, $_POST['original_intake_id']);
	$original_pallet_id = mysqli_real_escape_string($conn, $_POST['original_pallet_id']);
	$product_temp = mysqli_real_escape_string($conn, $_POST['product_temp']);

	$x = "UPDATE `product` SET original_intake_id = '$original_intake_id', original_pallet_id = '$original_pallet_id', pallet_id='$pallet_id', best_by='$best_by', cut_id='$cut_id', brand_id='$brand_id',nationality_id='$nationality_id',cooling_id='$temperature_id',status='0',range_from='$best_by_range_from',range_to='$best_by_range_to', ubbb='$ubbb',unit='$unit',comments='$comments',product_temp = '$product_temp'";
	
	if($cost != NULL){
		$x .= ", cost='$cost', price='$price'";
	}
	
	$x .= " WHERE id='$product_id'";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
	
	
	$xtest = "SELECT * FROM `weights` WHERE product_id='$product_id'";
	$ytest = mysqli_query($conn, $xtest);
	$weightCount = mysqli_num_rows($ytest);
	
	while($row = mysqli_fetch_array($ytest)){
		$weightid = $row['id'];
		
		$weightVal = mysqli_real_escape_string($conn, $_POST['weight'.$weightid]);

		if(!empty($single_weight_val)){
			$weightVal = $single_weight_val;
		}
		
		$xxx = "UPDATE `weights` SET product_id='$product_id',weight_gross='$weightVal',weight_tear='$weightVal' WHERE id='$weightid'";
		
		$y = mysqli_query($conn, $xxx);
	}
?>
<br/>
<script>
	// window.location = 'intake.php?id=<?php  echo $intake_id; ?>';
	window.location = 'intake.php?id=<?php  echo $intake_id; ?>&palletupdated=<?php echo $pallet_id; ?>';
</script>
