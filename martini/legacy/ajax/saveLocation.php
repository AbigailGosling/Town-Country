<?php

	require(__DIR__.'/../functions.php');

	$pallet = request()->input('pallet');
	$location = trim(request()->input('location'));
	
	
	$x = "UPDATE `pallet` SET storage_location=? WHERE id=?";
	$y = prepareExecuteQuery($x,'si',[$location,$pallet]);
	
	
	
?>