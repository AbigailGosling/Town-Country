<?php
	require(__DIR__.'/../functions.php');


	$supplier_id = request()->input('supplier_id');
	$purchase_id = request()->input('purchase_id');
	$security_id = request()->input('security_id');
	$date_received = request()->input('date_received');
	$vehicle_reg = request()->input('vehicle_reg');
	$vehicle_temperature = request()->input('vehicle_temperature');
	$product_temperature = '';
	$delivery_note_number = request()->input('delivery_note_number');
	$staff_id = request()->input('staff_id');
	$site_id = request()->input('site_id');
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d H:i:s', strtotime($date_received));
	$id = addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature,$product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id, $site_id);
?>
<script>
	window.location = 'intake.php?id=<?php echo $id; ?>';
</script>
