<?php
	include('functions.php');
	
	
	$supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
	$date_received = mysqli_real_escape_string($conn, $_POST['date_received']);
	$vehicle_reg = mysqli_real_escape_string($conn, $_POST['vehicle_reg']);
	$vehicle_temperature = mysqli_real_escape_string($conn, $_POST['vehicle_temperature']);
	
	
	$id = addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature);
	
	
	header('location:pallet.php?id='.$id);
?>