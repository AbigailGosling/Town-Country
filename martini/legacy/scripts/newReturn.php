<?php
	require(__DIR__.'/../functions.php');
	
	
	$supplier_id = $mysqli->real_escape_string( request('supplier_id'));
	$purchase_id = $mysqli->real_escape_string( request('purchase_id'));
	$security_id = $mysqli->real_escape_string( request('security_id'));
	$date_received = $mysqli->real_escape_string( request('date_received'));
	$vehicle_reg = $mysqli->real_escape_string( request('vehicle_reg'));
	$vehicle_temperature = $mysqli->real_escape_string( request('vehicle_temperature'));
	$product_temperature = $mysqli->real_escape_string( request('product_temperature'));
	$delivery_note_number = $mysqli->real_escape_string( request('delivery_note_number'));
	$staff_id = $mysqli->real_escape_string( request('staff_id'));
	
	$original_intake_id = $mysqli->real_escape_string( request('original_intake_id'));
	
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d 00:00:00', strtotime($date_received));
	
	$id = addReturnIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature,$product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id);
?>
<script>
	window.location = '../intake.php?id=<?php echo $id; ?>';
</script>
