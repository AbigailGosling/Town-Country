<?php
	include('functions.php');
	
	
	$pallet_id = $mysqli->real_escape_string( request('pallet_id'));
	$intake_id = $mysqli->real_escape_string( request('intake_id'));
	$status_id = $mysqli->real_escape_string( request('statuses_id'));
	$cut_id = $mysqli->real_escape_string( request('cut_id'));
	$weight = $mysqli->real_escape_string( request('weight'));
	$weight_amount = $mysqli->real_escape_string( request('weight_amount'));
	$best_by = $mysqli->real_escape_string( request('best_by'));
	$best_by_range_from = $mysqli->real_escape_string( request('best_by_range_from'));
	$best_by_range_to = $mysqli->real_escape_string( request('best_by_range_to'));
	$species_id = $mysqli->real_escape_string( request('species_id'));
	$temperature_id = $mysqli->real_escape_string( request('temperature_id'));
	$comments = $mysqli->real_escape_string( request('comments'));
	
	
	updatePallet($pallet_id, $intake_id, $status_id, $cut_id, $weight, $weight_amount, $best_by, $best_by_range_from, $best_by_range_to, $species_id, $temperature_id, $comments);
	
	header('location:intake.php?id=' . $intake_id);
	
	
?>