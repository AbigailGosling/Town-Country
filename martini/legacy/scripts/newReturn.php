<?php
	require(__DIR__.'/../functions.php');
	
	
	$supplier_id = request()->input('supplier_id');
	$purchase_id = request()->input('purchase_id');
	$security_id = request()->input('security_id');
	$date_received = request()->input('date_received');
	$vehicle_reg = request()->input('vehicle_reg');
	$vehicle_temperature = request()->input('vehicle_temperature');
	$product_temperature = request()->input('product_temperature');
	$delivery_note_number = request()->input('delivery_note_number');
	$staff_id = request()->input('staff_id');
	
	$original_intake_id = request()->input('original_intake_id');
	
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d 00:00:00', strtotime($date_received));
	
	$id = addReturnIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature,$product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id);
?>
<script>
	window.location = 'intake.php?id=<?php echo $id; ?>';
</script>
