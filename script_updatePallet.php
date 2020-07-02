<?php
	include('functions.php');
	
	
	$pallet_id = mysqli_real_escape_string($conn, $_POST['pallet_id']);
	$intake_id = mysqli_real_escape_string($conn, $_POST['intake_id']);
	$status_id = mysqli_real_escape_string($conn, $_POST['statuses_id']);
	$cut_id = mysqli_real_escape_string($conn, $_POST['cut_id']);
	$weight = mysqli_real_escape_string($conn, $_POST['weight']);
	$weight_amount = mysqli_real_escape_string($conn, $_POST['weight_amount']);
	$best_by = mysqli_real_escape_string($conn, $_POST['best_by']);
	$best_by_range_from = mysqli_real_escape_string($conn, $_POST['best_by_range_from']);
	$best_by_range_to = mysqli_real_escape_string($conn, $_POST['best_by_range_to']);
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']);
	$temperature_id = mysqli_real_escape_string($conn, $_POST['temperature_id']);
	$comments = mysqli_real_escape_string($conn, $_POST['comments']);
	
	
	updatePallet($pallet_id, $intake_id, $status_id, $cut_id, $weight, $weight_amount, $best_by, $best_by_range_from, $best_by_range_to, $species_id, $temperature_id, $comments);
	
	header('location:intake.php?id=' . $intake_id);
	
	
?>