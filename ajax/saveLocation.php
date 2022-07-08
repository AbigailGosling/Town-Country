<?php

	require('../functions.php');

	$pallet = mysqli_real_escape_string($conn,$_GET['pallet']);
	$location = trim(mysqli_real_escape_string($conn,$_GET['location']));
	
	
	$x = "UPDATE `pallet` SET storage_location='$location' WHERE id='$pallet'";
	$y = mysqli_query($conn, $x);
	
	
	
?>