<?php
	require(__DIR__.'/../functions.php');
	
	$supplier_id = request()->input('supplier');
	$vehicle_reg = request()->input('vehicle_reg');
	$date = request()->input('date');
	$vehicle_temp = request()->input('vehicle_temp');
	$product_temp = request()->input('product_temp');
	$delivery_note_number = request()->input('delivery_note_number');
	
	$id = request()->input('id');
	
	$x = "UPDATE `intake` SET date_received='$date', vehicle_reg='$vehicle_reg', vehicle_temperature='$vehicle_temp', product_temperature='$product_temp', delivery_note_number='$delivery_note_number' WHERE id = '$id'";
	$y = prepareExecuteQuery($x) or die(mysqli_error($mysqli));
	loggedDataChange("intake",$id,$delivery_note_number);
?>
<script>
	window.location = '../intake.php?id=<?php echo $id; ?>';
</script>