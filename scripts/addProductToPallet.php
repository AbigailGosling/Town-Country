<?php
	require('../functions.php');
	
	$unit = mysqli_real_escape_string($conn, $_POST['unit']);	
	$intake_id = mysqli_real_escape_string($conn, $_POST['intake_id']);
	$pallet_id = mysqli_real_escape_string($conn, $_POST['pallet_id']);
	
	
	$status_id = mysqli_real_escape_string($conn, $_POST['statuses_id']);
	$ubbb = mysqli_real_escape_string($conn, $_POST['ubbb']);
	$cut_id = mysqli_real_escape_string($conn, $_POST['cut_id']);
	
	$best_by = mysqli_real_escape_string($conn, $_POST['best_by']);
	$range_from = mysqli_real_escape_string($conn, $_POST['best_by_range_from']);
	$range_to = mysqli_real_escape_string($conn, $_POST['best_by_range_to']);
	$weight = mysqli_real_escape_string($conn, $_POST['weight']);
	
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']);
	$temperature_id = mysqli_real_escape_string($conn, $_POST['temperature_id']);
	
	
	$nationality_id = mysqli_real_escape_string($conn, $_POST['nationality_id']);
	$brand_id = mysqli_real_escape_string($conn, $_POST['brand_id']);
	
	$pallet_tare = mysqli_real_escape_string($conn, $_POST['pallet_tare']);
	$tare_per_carton = mysqli_real_escape_string($conn, $_POST['tare_per_carton']);
	$number_of_cartons = mysqli_real_escape_string($conn, $_POST['number_of_cartons']);
	
	
	$akg = mysqli_real_escape_string($conn, $_POST['akg']);

	
	$_POST['quantity']++; # Fix the loop from starting at 0
	
	
	$x = "INSERT INTO `product` (akg,pallet_id,cut_id,brand_id,nationality_id,cooling_id,range_from,range_to,ubbb,unit) VALUES ('$akg','$pallet_id','$cut_id','$brand_id','$nationality_id','$temperature_id','$range_from','$range_to','$ubbb','$unit')";
	$y = mysqli_query($conn, $x);
			
	$product_id = mysqli_insert_id($conn); 
	
	
	// echo '<br/><br/>';
	
	if($akg != ''){
		$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES ('$product_id','$status_id','$akg','$akg')";
		$y = mysqli_query($conn, $x);	
	}else{
		for($a = 1; $a < $_POST['quantity']; $a++){
 			$individualweights = $_POST['individualweights'];
			
			if($individualweights == 'C'){
				# Catch Weights
				$weight = $_POST['weights' . $a];
				
				$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES ('$product_id','$status_id','$weight','$weight')";
				$y = mysqli_query($conn, $x);
				
			}else if($individualweights == 'D'){
				# Dolav Weights
				
				$weight = $gross_weight_val - $tear_weight_val;
			
				$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear,pallet_tare,tare_per_carton,number_of_cartons)
				VALUES ('$product_id','$status_id','$gross_weight_val','$tear_weight_val','$pallet_tare','$tare_per_carton','$number_of_cartons')";
				$y = mysqli_query($conn, $x);
				
			}else{
				# Single Weight Value
				$weight = $_POST['single_weight_val'];
				
				
				$x = "INSERT INTO `weights` (product_id,status_id,weight_gross,weight_tear) VALUES ('$product_id','$status_id','$weight','$weight')";
				$y = mysqli_query($conn, $x);
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
	weight: <?php echo $weight; ?><br/>
	species_id: <?php echo $species_id; ?><br/>
	temperature_id: <?php echo $temperature_id; ?><br/>
	nationality_id: <?php echo $nationality_id; ?><br/>
	brand_id: <?php echo $brand_id; ?><br/>
	quantity: <?php echo $_POST['quantity']; ?><br/>
	individualweights: <?php echo $_POST['individualweights']; ?><br/>
	single_weight_val: <?php echo $_POST['single_weight_val']; ?><br/>
</div>
<br/>
<script>
	window.location = '../intake.php?id=<?php  echo $intake_id; ?>';
</script>
