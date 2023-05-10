<?php
	include('functions.php');
	
	
	$supplier_id = $mysqli->real_escape_string( request()->input('supplier_id'));
	$date_received = $mysqli->real_escape_string( request()->input('date_received'));
	$vehicle_reg = $mysqli->real_escape_string( request()->input('vehicle_reg'));
	$vehicle_temperature = $mysqli->real_escape_string( request()->input('vehicle_temperature'));
	
	
	$id = addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature);
	
	
	header('location:pallet.php?id='.$id);
?>