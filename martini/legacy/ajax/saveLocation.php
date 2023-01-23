<?php

	require(__DIR__.'/../functions.php');

	$pallet = $mysqli->real_escape_string(request('pallet'));
	$location = trim($mysqli->real_escape_string(request('location')));
	
	
	$x = "UPDATE `pallet` SET storage_location=? WHERE id=?";
	$y = prepareExecuteQuery($x,'si',[$location,$pallet]);
	
	
	
?>