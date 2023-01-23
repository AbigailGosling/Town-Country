<?php
	include('functions.php');
	
	
	$supplier_id = $mysqli->real_escape_string( request('supplier_id'));
	$date_received = $mysqli->real_escape_string( request('date_received'));
	$vehicle_reg = $mysqli->real_escape_string( request('vehicle_reg'));
	$vehicle_temperature = $mysqli->real_escape_string( request('vehicle_temperature'));
 
	
	$date_received = str_replace('/', '-', $date_received);
	$date_received = date('Y-m-d 00:00:00', strtotime($date_received));
	
	addIntake($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature);
	
	header('location:intakelist.php');
?>