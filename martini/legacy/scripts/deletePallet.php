<?php
	require(__DIR__.'/../functions.php');
	
	
	$intake_id = $mysqli->real_escape_string( request('intake_id'));
	echo "<br/>";
	$pallet_id = $mysqli->real_escape_string( request('pallet_id'));
	echo "<br/>";
	$product_id  = $mysqli->real_escape_string( request('product_id'));
	
	
	# if($product_id != '' && $pallet_id != ''){
		
		
		$x = "SELECT * FROM `product` WHERE pallet_id = ?";
		$y = prepareExecuteQuery($x,'i',[$pallet_id]);
		
		while($row = mysqli_fetch_array($y)){
			
			deleteWeightsFor($row['id']);
		}
		
		deleteProductsFor($pallet_id);
		
		deletePallet($pallet_id);
		
	# }
?>
<script>
	window.location = '../intake.php?id=<?php echo $intake_id; ?>';
</script>
