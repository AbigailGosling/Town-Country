<?php
	require(__DIR__.'/../functions.php');
	
	
	$intake_id = request()->input('intake_id');
	echo "<br/>";
	$pallet_id = request()->input('pallet_id');
	echo "<br/>";
	$product_id  = request()->input('product_id');
	
	$t = prepareExecuteQuery("SELECT COUNT(*) as `rows` FROM pallet WHERE id = ? AND intake_id = ?",'ii',[$pallet_id,$intake_id])->fetch_assoc();
	if($t['rows'] == 1 && $intake_id != '' && $intake_id != null && $intake_id != -1 && $intake_id != "-1" && $pallet_id != '' && $pallet_id != null && $pallet_id != -1 && $pallet_id != "-1"){
		loggedDataChange('pallet_force_delete',$pallet_id,'User Deleted Pallet');
		
		$x = "SELECT * FROM `product` WHERE pallet_id = $pallet_id";
		$y = prepareExecuteQuery($x) or die(mysqli_error($mysqli));
		
		while($row = mysqli_fetch_array($y)){
			
			deleteWeightsFor($row['id']);
		}
		
		deleteProductsFor($pallet_id);
		
		deletePallet($pallet_id);
		
	}
?>
<script>
	window.location = '../intake.php?id=<?php echo $intake_id; ?>';
</script>
