<?php
	include('functions.php');
	
	
	$supplier_id = request()->input('supplier_id');
	$date_received = request()->input('date_received');
	$vehicle_reg = request()->input('vehicle_reg');
	$vehicle_temperature = request()->input('vehicle_temperature');
	
	
	$id = addIntakeDupe($supplier_id, $date_received, $vehicle_reg, $vehicle_temperature);
	
	
	header('location:pallet.php?id='.$id);
?>