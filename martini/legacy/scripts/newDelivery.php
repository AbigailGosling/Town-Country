<?php
	require(__DIR__.'/../functions.php');
	
	
	$supplier_id = $mysqli->real_escape_string( request()->input('supplier_id'));
	$purchase_id = $mysqli->real_escape_string( request()->input('purchase_id'));
	$security_id = $mysqli->real_escape_string( request()->input('security_id'));
	$date_received = $mysqli->real_escape_string( request()->input('date_received'));
	$vehicle_reg = $mysqli->real_escape_string( request()->input('vehicle_reg'));
	$vehicle_temperature = $mysqli->real_escape_string( request()->input('vehicle_temperature'));
	$product_temperature = '';
	$delivery_note_number = $mysqli->real_escape_string( request()->input('delivery_note_number'));
	$staff_id = $mysqli->real_escape_string( request()->input('staff_id'));
	
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d H:i:s', strtotime($date_received));
	
	$id = addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature,$product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id);
?>
<script>
	window.location = 'intake.php?id=<?php echo $id; ?>';
</script>
