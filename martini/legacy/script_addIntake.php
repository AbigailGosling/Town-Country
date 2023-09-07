<?php
	include('functions.php');
	
	
	$supplier_id = request()->input('supplier_id');
	$date_received = request()->input('date_received');
	$vehicle_reg = request()->input('vehicle_reg');
	$vehicle_temperature = request()->input('vehicle_temperature');
 
	
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d 00:00:00', strtotime($date_received));
	
	addIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature);
	
	header('location:intakelist.php');
?>