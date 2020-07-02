<?php
	include('functions.php');
	
	
	$supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
	$date_received = mysqli_real_escape_string($conn, $_POST['date_received']);
	$vehicle_reg = mysqli_real_escape_string($conn, $_POST['vehicle_reg']);
	$vehicle_temperature = mysqli_real_escape_string($conn, $_POST['vehicle_temperature']);
 
	
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d 00:00:00', strtotime($date_received));
	
	addIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature);
	
	header('location:intakelist.php');
?>