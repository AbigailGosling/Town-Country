<?php

	require('../functions.php');

	
	$pallet = $_GET['pallet'];
	$location = $_GET['location'];
	
	
	$x = "UPDATE `pallet` SET storage_location='$location' WHERE id='$pallet'";
	$y = mysqli_query($conn, $x);
	
	
	
?>