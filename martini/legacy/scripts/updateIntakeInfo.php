<?php
	require(__DIR__.'/../functions.php');
	
	$supplier_id = $mysqli->real_escape_string( request()->input('supplier'));
	$vehicle_reg = $mysqli->real_escape_string( request()->input('vehicle_reg'));
	$date = $mysqli->real_escape_string( request()->input('date'));
	$vehicle_temp = $mysqli->real_escape_string( request()->input('vehicle_temp'));
	$product_temp = $mysqli->real_escape_string( request()->input('product_temp'));
	$delivery_note_number = $mysqli->real_escape_string( request()->input('delivery_note_number'));
	
	$id = request()->input('id');
	
	$x = "UPDATE `intake` SET date_received='$date', vehicle_reg='$vehicle_reg', vehicle_temperature='$vehicle_temp', product_temperature='$product_temp', delivery_note_number='$delivery_note_number' WHERE id = '$id'";
	$y = prepareExecuteQuery($x) or die(mysqli_error($conn));
	loggedDataChange("intake",$id,$delivery_note_number);
?>
<script>
	window.location = '../intake.php?id=<?php echo $id; ?>';
</script>