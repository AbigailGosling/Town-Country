<?php
	require(__DIR__.'/../functions.php');
	
	
	$intake_id = mysqli_real_escape_string($conn, request()->input('intake_id'));
	echo "<br/>";
	$pallet_id = mysqli_real_escape_string($conn, request()->input('pallet_id'));
	echo "<br/>";
	$product_id  = mysqli_real_escape_string($conn, request()->input('product_id'));
	
	
	# if($product_id != '' && $pallet_id != ''){
		loggedDataChange('pallet_force_delete',$pallet_id,'User Deleted Pallet');
		
		$x = "SELECT * FROM `product` WHERE pallet_id = $pallet_id";
		$y = prepareExecuteQuery($x) or die(mysqli_error($conn));
		
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
