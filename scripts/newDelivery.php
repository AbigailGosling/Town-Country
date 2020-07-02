<?php
	require('../functions.php');
	
	
	$supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
	$purchase_id = mysqli_real_escape_string($conn, $_POST['purchase_id']);
	$security_id = mysqli_real_escape_string($conn, $_POST['security_id']);
	$date_received = mysqli_real_escape_string($conn, $_POST['date_received']);
	$vehicle_reg = mysqli_real_escape_string($conn, $_POST['vehicle_reg']);
	$vehicle_temperature = mysqli_real_escape_string($conn, $_POST['vehicle_temperature']);
	$product_temperature = '';
	$delivery_note_number = mysqli_real_escape_string($conn, $_POST['delivery_note_number']);
	$staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
	
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d 00:00:00', strtotime($date_received));
	
	$id = addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature,$product_temperature, $delivery_note_number, $staff_id, $security_id, $purchase_id);
?>
<script>
	window.location = '../intake.php?id=<?php echo $id; ?>';
</script>
