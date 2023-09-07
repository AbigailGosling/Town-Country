<?php
	include('functions.php');
	
	
	$pallet_id = request()->input('pallet_id');
	$intake_id = request()->input('intake_id');
	$status_id = request()->input('statuses_id');
	$cut_id = request()->input('cut_id');
	$weight = request()->input('weight');
	$weight_amount = request()->input('weight_amount');
	$best_by = request()->input('best_by');
	$best_by_range_from = request()->input('best_by_range_from');
	$best_by_range_to = request()->input('best_by_range_to');
	$species_id = request()->input('species_id');
	$temperature_id = request()->input('temperature_id');
	$comments = request()->input('comments');
	
	
	updatePallet($pallet_id, $intake_id, $status_id, $cut_id, $weight, $weight_amount, $best_by, $best_by_range_from, $best_by_range_to, $species_id, $temperature_id, $comments);
	
	header('location:intake.php?id=' . $intake_id);
	
	
?>