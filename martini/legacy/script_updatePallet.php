<?php
	include('functions.php');
	
	
	$pallet_id = $mysqli->real_escape_string( request()->input('pallet_id'));
	$intake_id = $mysqli->real_escape_string( request()->input('intake_id'));
	$status_id = $mysqli->real_escape_string( request()->input('statuses_id'));
	$cut_id = $mysqli->real_escape_string( request()->input('cut_id'));
	$weight = $mysqli->real_escape_string( request()->input('weight'));
	$weight_amount = $mysqli->real_escape_string( request()->input('weight_amount'));
	$best_by = $mysqli->real_escape_string( request()->input('best_by'));
	$best_by_range_from = $mysqli->real_escape_string( request()->input('best_by_range_from'));
	$best_by_range_to = $mysqli->real_escape_string( request()->input('best_by_range_to'));
	$species_id = $mysqli->real_escape_string( request()->input('species_id'));
	$temperature_id = $mysqli->real_escape_string( request()->input('temperature_id'));
	$comments = $mysqli->real_escape_string( request()->input('comments'));
	
	
	updatePallet($pallet_id, $intake_id, $status_id, $cut_id, $weight, $weight_amount, $best_by, $best_by_range_from, $best_by_range_to, $species_id, $temperature_id, $comments);
	
	header('location:intake.php?id=' . $intake_id);
	
	
?>