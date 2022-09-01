<?php
	require('../functions.php');
	
	$supplier_id = mysqli_real_escape_string($conn, $_POST['supplier']);
	$vehicle_reg = mysqli_real_escape_string($conn, $_POST['vehicle_reg']);
	$date = mysqli_real_escape_string($conn, $_POST['date']);
	$vehicle_temp = mysqli_real_escape_string($conn, $_POST['vehicle_temp']);
	$product_temp = mysqli_real_escape_string($conn, $_POST['product_temp']);
	$delivery_note_number = mysqli_real_escape_string($conn, $_POST['delivery_note_number']);
	
	$id = $_POST['id'];
	
	$x = "UPDATE `intake` SET date_received='$date', vehicle_reg='$vehicle_reg', vehicle_temperature='$vehicle_temp', product_temperature='$product_temp', delivery_note_number='$delivery_note_number' WHERE id = '$id'";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	loggedDataChange("intake",$id,$delivery_note_number);
?>
<script>
	window.location = '../intake.php?id=<?php echo $id; ?>';
</script>